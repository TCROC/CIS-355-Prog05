<?php
/**
 * Created by PhpStorm.
 * User: travi
 * Date: 3/29/2019
 * Time: 10:59 PM
 */

require 'database.php';

class fileUploader
{

    function uploadFile1(string $fileName, string $tempFileName, string $fileLocation)
    {
        $fileFullPath = $fileLocation . $fileName;

        if (!file_exists($fileLocation))
            mkdir ($fileLocation); // create subdirectory, if necessary

// debugging code...
// echo phpinfo(); exit(); // to see location of php.ini
// note: can't set php.ini:file_uploads on the fly
// echo ini_set('file_uploads', '1'); // "set" does not work
// echo ini_get('file_uploads'); // "get" does work
// echo "<pre>"; print_r(ini_get_all()); echo "</pre>"; exit();
// echo "<pre>"; print_r($_FILES); echo "</pre>"; exit(); // view $_FILES array

// if file does not already exist, upload it
        if (!file_exists($fileFullPath)) {
            $result = move_uploaded_file($tempFileName, $fileFullPath);
            if ($result) {
                echo "File <b><i>" . $fileName
                    . "</i></b> has been successfully uploaded.";
                // code below assumes filepath is same as filename of this file
                // minus the 12 characters of this file, "Upload.php"
                // plus the string, $fileLocation, i.e. "uploads/"
                echo "<br>To see all uploaded files, visit: "
                    . "<a href='"
                    . substr(get_current_url(), 0, -12)
                    . "$fileLocation'>"
                    . substr(get_current_url(), 0, -12)
                    . "$fileLocation</a>";
            } else {
                echo "Upload denied for file. " . $fileName
                    . "</i></b>. Verify file size < 2MB. ";
            }
        }
// otherwise, show error message
        else {
            echo "File <b><i>" . $fileName
                . "</i></b> already exists. Please rename file.";
        }
    }

    function uploadFile2 (string $fileName, string $tempFileName, int $fileSize, string $fileType, string $fileLocation, string $fileDescription)
    {
        // set server location (subdirectory) to store uploaded files
        $fileLocation = "uploads/";
        $fileFullPath = $fileLocation . $fileName;
        if (!file_exists($fileLocation))
            mkdir ($fileLocation); // create subdirectory, if necessary

// execute debugging code...
// echo phpinfo(); exit(); // to see location of php.ini
// note: can't set php.ini:file_uploads on the fly
// echo ini_set('file_uploads', '1'); // "set" does not work
// echo ini_get('file_uploads'); // "get" does work
// echo "<pre>"; print_r(ini_get_all()); echo "</pre>"; exit();
// echo "<pre>"; print_r($_FILES); echo "</pre>"; exit();

// connect to database
        $pdo = Database::connect();

// exit, if requested file already exists -- in the database table
        $fileExists = false;
        $sql = "SELECT filename FROM upload02 WHERE filename='$fileName'";
        foreach ($pdo->query($sql) as $row) {
            if ($row['filename'] == $fileName) {
                $fileExists = true;
            }
        }
        if ($fileExists) {
            echo "File <html><b><i>" . $fileName
                . "</i></b></html> already exists in DB. Please rename file.";
            exit();
        }

// exit, if requested file already exists -- in the subdirectory
        if(file_exists($fileFullPath)) {
            echo "File <html><b><i>" . $fileName
                . "</i></b></html> already exists in file system, "
                . "but not in database table. Cannot upload.";
            exit();
        }

// if all of above is okay, then upload the file
        $result = move_uploaded_file($tempFileName, $fileFullPath);

// if upload was successful, then add a record to the SQL database
        if ($result) {
            echo "Your file <html><b><i>" . $fileName
                . "</i></b></html> has been successfully uploaded";
            $sql = "INSERT INTO upload02(filename,filetype,filesize,description)"
                . " VALUES ('$fileName','$fileType',$fileSize,"
                . "'$fileDescription')";
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $q = $pdo->prepare($sql);
            $q->execute(array());
// otherwise, report error
        } else {
            echo "Upload denied for this file. Verify file size < 2MB. ";
        }

// list all files in database
// ORDER BY BINARY filename ASC (sorts case-sensitive, like Linux)
        echo '<br><br>All files in database...<br><br>';
        $sql = 'SELECT * FROM upload02 '
            . 'ORDER BY BINARY filename ASC;';
        $i = 0;
        foreach ($pdo->query($sql) as $row) {
            echo ' ... [' . $i++ . '] --- ' . $row['filename'] . '<br>';
        }
        echo '<br><br>';

// list all files in subdirectory
        echo 'All files in subdirectory...<br>';
        echo '<pre>';
        $arr = array_slice(scandir("$fileLocation"), 2);
        asort($arr);
        print_r($arr);
        echo '<pre>';
        echo '<br><br>';

// disconnect
        Database::disconnect();
    }

    function uploadFile3 (string $fileName, string $tempFileName, int $fileSize, string $fileType, string $fileLocation, string $fileDescription)
    {
        // abort if no filename
        if (!$fileName) {
            die("No filename.");
        }

// abort if file is not an image
// never assume the upload succeeded
        if ($_FILES['Filename']['error'] !== UPLOAD_ERR_OK) {
            die("Upload failed with error code " . $_FILES['file']['error']);
        }
        $info = getimagesize($_FILES['Filename']['tmp_name']);
        if ($info === FALSE) {
            die("Error Unable to determine <i>image</i> type of uploaded file");
        }
        if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG)
            && ($info[2] !== IMAGETYPE_PNG)) {
            die("Not a gif/jpeg/png");
        }

// abort if file is too big
        if($fileSize > 2000000) { echo "Error: file exceeds 2MB."; exit(); }

// fix slashes in $fileType variable, if necessary
        $fileType=(get_magic_quotes_gpc()==0 ? mysqli_real_escape_string(null, $_FILES['Filename']['type']) : mysqli_real_escape_string(null, stripslashes ($_FILES['Filename'])));

// put the content of the file into a variable, $content
        $fp      = fopen($tempFileName, 'r');
        $content = fread($fp, filesize($tempFileName));
        $content = addslashes($content);
        fclose($fp);

// no longer needed - feature removed from php
// http://php.net/manual/en/function.get-magic-quotes-gpc.php
// restore slashes in $fileType variable, if necessary
        if(!get_magic_quotes_gpc()) { $fileName = addslashes($fileName); }

// connect to database
        $pdo = Database::connect();

// insert file info and content into table
        $sql = "INSERT INTO upload03 (filename, filesize, filetype, content, description) "
            . "VALUES ('$fileName', '$fileSize', '$fileType', '$content', '$fileDescription')";
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = $pdo->prepare($sql);
        $q->execute(array());

// list all uploads in database
// ORDER BY BINARY filename ASC (sorts case-sensitive, like Linux)
        echo '<br><br>All files in database...<br><br>';
        $sql = 'SELECT * FROM upload03 '
            . 'ORDER BY BINARY filename ASC;';

        foreach ($pdo->query($sql) as $row) {
            $id = $row['id'];
            $sql = "SELECT * FROM upload03 where id=$id";
            echo $row['id'] . ' - ' . $row['filename'] . '<br>'
                . '<img width=100 src="data:image/jpeg;base64,'
                . base64_encode( $row['content'] ).'"/>'
                . '<br><br>';
        }
        echo '<br><br>';

// disconnect
        Database::disconnect();
    }
}
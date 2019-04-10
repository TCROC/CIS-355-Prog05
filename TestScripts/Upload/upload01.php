<?php

// see HTML form (Upload.html) for overview of this program

require_once('functions01.php');

// set PHP variables from data in HTML form
$fileName       = $_FILES['Filename']['name'];
$tempFileName   = $_FILES['Filename']['tmp_name'];
$fileSize       = $_FILES['Filename']['size'];
$fileType       = $_FILES['Filename']['type'];
// $fileDescription = $_POST['Description']; // not used

// set server location (subdirectory) to store uploaded files
$fileLocation = "uploads/";
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
<?php
require "database.php";

$verificationMessage = "Verification Failed!";

if (isset($_GET["email"]) && isset($_GET["password"])) {
    $email = $_GET['email'];
    $password = $_GET['password'];

    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add the data to the database.
    $sql = "UPDATE customers SET isVerified=true WHERE email=? AND password_hash=?";
    $q = $pdo->prepare($sql);
    $q->execute(array($email, $password));

    // Now try to query that username / password combination to make sure the email was verified successfully.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM customers WHERE email = ? AND password_hash = ? LIMIT 1";
    $q = $pdo->prepare($sql);
    $q->execute(array($email,$password));
    $data = $q->fetch(PDO::FETCH_ASSOC);

    if ($data && $data['isVerified']) {
        $verificationMessage = "Verification Successful!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='UTF-8'>
    <script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"
            integrity=\"sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=\"
            crossorigin=\"anonymous\"></script>
    <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
    <style>label {width: 5em;}</style>
</head>

<div class="container">
    <?php
    // Displays an error message if there is one.
    if ($verificationMessage == "Verification Failed!") {
        echo "<p class=\"alert alert-danger\" role=\"alert\">$verificationMessage</p>";
    } else if ($verificationMessage == "Verification Successful!") {
        echo "<p class=\"alert alert-success\" role=\"alert\">$verificationMessage</p>";
    }
    ?>
    <a href="customer.php" class="btn btn-info">Continue To Site</a>
</div>
</html>

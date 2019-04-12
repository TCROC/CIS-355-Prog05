<?php
session_start();
require "database.php";

if ($_GET)
    $errorMessage = $_GET["errorMessage"];
else
    $errorMessage='';

if ($_POST){
    // Get the username and password from the post.
    $username = $_POST['username'];
    $password = MD5 ($_POST['password']);
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // See if this username / password combination exists in the database.
    $sql = "SELECT * FROM customers WHERE email='$username' AND password_hash ='$password' LIMIT 1";
    $q = $pdo -> prepare($sql);
    $q -> execute(array());
    $data = $q->fetch(PDO::FETCH_ASSOC);

    // If we got data back, the username / password combination was correct.
    if ($data) {
        $_SESSION["username"] = $username;

        // Go to the customer page.
        header('Content-Type: application/json');
        echo json_encode(['location'=>'customer.html']);
        exit();
    } else {// Otherwise, try to log in again.
        header('Content-Type: application/json');
        echo json_encode(['location'=>'login.html?errorMessage=Invalid Username or Password!']);
        exit();
    }
}
?>

<div class="container">
    <h1>Log In</h1>
    <form class="form-horizontal" action="login.html" method="post">
        <input name="username" type="text" placeholder="me@email.com" required>
        <input name="password" type="password" placeholder="password" required>
        <button type="submit" class="btn btn-success">Sign In</button>
        <a href="createAccount.html" class="btn btn-info">Join</a>
        <?php
        // Displays an error message if there is one.
        if ($errorMessage) {
            echo "<p class=\"alert alert-danger\" role=\"alert\">$errorMessage</p>";
        }
        ?>
    </form>
</div>

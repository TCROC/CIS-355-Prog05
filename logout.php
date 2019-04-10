<?php // Simply destroys the session and logs the user out.
session_start();
session_destroy();
header("Location: login.php");
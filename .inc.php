<?php
    $db = new mysqli("localhost", "ip", "ippass", "ip");

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    function reload()
    {
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    function show_error($str)
    {
        echo "<font color=red>$str</font><br>";
    }
?>
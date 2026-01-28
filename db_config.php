<?php
$con = mysqli_connect("localhost", "root", "", "db_zyroid");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php
$user = "root";
$password = '';
$host = "127.0.0.1";
$db_name = "business";

$con = mysqli_connect($host, $user, $password, $db_name);  
if(mysqli_connect_errno()) {  
    die("Failed to connect with MySQL: ". mysqli_connect_error());  
}  
?>

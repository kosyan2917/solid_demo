<?php


$servername = "127.0.0.1";
$db_username = "kokowaf";
$db_password = "ctf123";
$dbname = "kokowaf";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

?>
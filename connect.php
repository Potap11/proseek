<?php
    
$host = "localhost"; 
$dbname = "proseek"; 
$username = "root";  
$password = "";   


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    error_log("Ошибка подключения к базе данных: " . $conn->connect_error);
    die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
}


$conn->set_charset("utf8");


?>
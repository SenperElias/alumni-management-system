<?php
$host="localhost";
$username="root";
$password="";
$database="alumni_management";
$conn= new mysqli($host,$username,$password,$database);

if($conn->connect_error){
    die("database connection failed: " . 
$conn->connect_error);
}
$conn->set_charset ("utf8mb4");
?>
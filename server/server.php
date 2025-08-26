<?php 
$database	= 'esdb';
$username	= 'root';
$host		= 'localhost';
$password	= '';

$conn = new mysqli($host,$username,$password,$database);

if($conn->connect_error){
	die("Connection Failed: ". $conn->connect_error());
}
// if(!isset($_SESSION)){
// 	session_start();
// }
?>
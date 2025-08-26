<?php 
$database	= 'u977775529_esdb';
$username	= 'u977775529_escdms';
$host		= 'essss-centralized-dms.com';
$password	= 'Essss1999';

$conn = new mysqli($host,$username,$password,$database);

if($conn->connect_error){
	die("Connection Failed: ". $conn->connect_error());
}
// if(!isset($_SESSION)){
// 	session_start();
// }
?>
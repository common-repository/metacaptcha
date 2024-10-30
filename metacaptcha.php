<?php
include_once "metacaptcha_lib.php";
header('Content-Type: text/html');

if(isset($_POST['metacaptcha_content']))
{
	include_once "metacaptcha_lib.php"; //path to metacaptcha_lib on your server
	$content = preg_replace('/\s+/',' ',$_POST['metacaptcha_content']);
	$response = _metacaptcha_return_initial_cookie(stripslashes($content));
	echo json_encode($response);
}
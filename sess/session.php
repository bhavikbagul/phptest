<?php
/* error_reporting(E_ALL);
//echo $_SERVER['DOCUMENT_ROOT'];exit;
echo session_save_path();
ini_set('session.save_path',realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/session'));
ini_set('session.gc_probability', 1);
echo session_save_path();
    session_start();
	$_SESSION['test1'] = "SessionTest41";
    $_SESSION['test2'] = "SessionTest2";
	//echo $id = $_GET['sess'];
    var_export($_SESSION);
echo session_save_path(); */
session_id($_GET['sid']); session_start(); $_SESSION['testing'] = 1234; //session_write_close(); 
?>

<p><a href="index.php">Back</a></p>
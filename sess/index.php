<?php
//SELECT name,salary FROM `employee` WHERE salary=(SELECT max(`salary`) from employee where salary<(SELECT max(`salary`) from employee))
    /* session_start();
	echo $id = session_id();
    $_SESSION['test1'] = "SessionTest41";
    $_SESSION['test2'] = "SessionTest2"; */
session_start(); echo session_id()."<br/>\n"; print_r($_SESSION); 
?>
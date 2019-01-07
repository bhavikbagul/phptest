<?php

class Base{
	public function add($a,$b){
		return $a+$b;
		//echo 'base';
	}
		
}

//Class Ext extends Base{
Class Ext {	
	public function add11($a,$b){
		return $a+$b;
		//echo 'base';
	}
	public function add($a,$b,$c){
		return $a+$b+$c;
		//parent::add($a,$b);
		//return 'ext';
	}
}

 /* function add($a,$b){
		return $a+$b;
		//echo 'base';
	}
	 function add($a,$b,$c){
		return $a+$b+$c;
		//parent::add($a,$b);
		//return 'ext';
	}

	exit; */
$obj = new Ext();
$obj1 = new Ext();

if($obj==$obj1)
	echo 'same';
exit;
echo $obj->add(2,3);
exit;
class Test{
	public $var1='test';
	public $var2='test2';
	
	public static function testvar(){
		echo $this->var1.' '.$this->var2;	
	}
}
$obj = new Test();
//$obj->testvar();
Test::testvar();

?>
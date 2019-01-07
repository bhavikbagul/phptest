<?php
/* class SimpleClass {
    private $privateData = 2;
}
 
$simpleClosure = function() {
    return $this->privateData;
};
 
$resultClosure = Closure::bind($simpleClosure, new SimpleClass(),
							   'SimpleClass');
 
echo $resultClosure(); */


class SimpleClass {
    private $privateData = 2;
}
 
$simpleClosure = function() {
    return $this->privateData;
};

$resultClosure = Closure::bind($simpleClosure,new SimpleClass(),'SimpleClass');
//print_r($resultClosure);
echo $resultClosure();
<?php
/* $input = array("red", "green", "blue", "yellow");
echo '<pre>';
print_r($input);
array_splice($input, 3, 0, "purple");
print_r($input); */

/* function test($a){
	if($a>5)
		return 1;
	else
		return 0;
}

echo $t = test(4);
if(!$t)
	echo 'here';
else
	echo 'there';
$f = 'microwave';
if($f == 'Microwave')
	echo 'ss';
else
	echo 'dd'; */

//$a = array('a','c','d');
//echo implode(",",array_reverse($a));
/* error_reporting(E_ALL);
$output = shell_exec('dir');
echo "<pre>$output</pre>"; */


?>

<?php
$userdb=Array
(
'0' => Array
    (
        'uid' => '100',
        'name' => 'Sandra Shush',
        'url' => 'urlof100'
    ),

'1' => Array
    (
        'uid' => '5465',
        'name' => 'Stefanie Mcmohn',
        'pic_square' => 'urlof100'
    ),

'2' => Array
    (
        'uid' => '40489',
        'name' => 'Michael',
        'pic_square' => 'urlof40489'
    )
);
print_r(array_column($userdb, 'uid')); 
if(in_array(100, array_column($userdb, 'uid'))) { // search value in the array
    echo "FOUND";
}

if(!in_array($host, array_column($ip_route_all, 'host_name'))) { // search value in the array
    
}
?>
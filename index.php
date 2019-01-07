<?php
$original = array( array('a'), array('b'), array('c'), array('d'), array( 'e') );
$inserted = array( array('x','y') ); // Not necessarily an array
for($i=0;$i<sizeof($original);$i++){
	echo $i.'<br>';
}
echo '<pre>';
print_r($original);
array_splice( $original, 2, 0, $inserted ); // splice in at position 3
print_r($original); */
echo 'ss'.$fig = pow(10,2);
$input = array("a", "b", "c", "d", "e");
$cnt = floor(count($input)/2);

$output = array_slice($input, $cnt); 
$output1 = array_slice($input, 0, $cnt);
echo '<pre>';
print_r($input);

print_r($output1);
print_r($output);
$temp = array_reverse($output);
print_r($temp);
?>
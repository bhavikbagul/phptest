<?php

function test($name){
	return 'hi '.$name;
}
$names = array( "fred", "mary", "sally" );
print_r( array_map( 'test', $names ) );
//echo test('bhavik');
exit;
/* function nameToGreeting($name=''){
  echo $name;
}
$name='abc'; 
echo nameToGreeting($name);
exit; */
 // Anonymous function  
 // assigned to variable  
 $greeting = function () {  
 return "Hello world";  
 };

// Call function  
 echo $greeting();  
 // Returns "Hello world"  
 
/* $languages = [
    ["name" => "JavaScript"],
    ["name" => "PHP"],
    ["name" => "Ruby"],
];

$prefix = "language: ";

var_dump(
    array_map(function($language) use ($prefix) {
        return $prefix . $language;
    }, $languages);
);

print_R($languages); */
exit;
$many= ['a','y','b','c'];
$few = array_slice($many, 1, 3);
print_R($few);
exit;

function pbr(&$var) {
  $var++;
}

function pbv($var) {
  $var++;
}

$num = 1;
pbr($num);
echo $num.'<br>'; // 2

$num = 1;
pbv($num);
echo $num; // 1

exit;
/* preg_match_all("/(a-z)/",'Te0/0/0/9',$match);
print_r($match);
exit; */
$str = explode('/','Te0/0/0/9');
print_R($str);exit;
define( 'a',4);
//a=2;
echo a;
$cssringdetails = explode('_', 'ABDD_1044_2_9',2);
print_R($cssringdetails);
exit;
class Entity {

protected $meta;

public function __construct(array $meta)  
{  
$this->meta = $meta;  
}

}

class Tweet extends Entity {

public $id;  
public $text;

public function __construct($id, $text)  
{  
$this->id = $id;  
$this->text = $text;

//parent::__construct($meta);  
}

}  
$tweet = new Tweet(123, 'hello world');  
echo $tweet->text; // ‘hello world’  

exit;
echo strstr('teng0/4/1/3','0');
exit;
echo preg_replace("/^(Te)+(a-z)(\d+)/", 'TenGigabitEthernet$1','Tea0/0/0/10');
echo '<br>';
echo preg_replace("/[a-z](\d+)/i", 'Tengig$1','Teng0/0/0/9');
exit;
preg_match('/^[a-z]/', 'te0', $matches);
preg_match('/^([a-z]+)+([0-9]+[\/]+[0-9]+[\/]+[0-9])/i', 'tengig0/0/9', $matches);
echo '<pre>';
print_r($matches);
if(preg_match('/^([a-z]+)+([0-9])+[\/]+[0-9]+[\/]+[0-9]/i', 'tengig0/0/9')){
	echo 'match';
}
exit;
preg_match_all("/[a-z]/i",'Te0/0/0/9',$match);
print_r($match);
exit;

function getHopCount($sq_no=1){
        $hopcnt = 1;
        if($seqno==1)  $hopcnt = 3;
        else if($seqno==2)  $hopcnt = 4;
        else if($seqno==3)  $hopcnt = 4;
        else if($seqno==4)  $hopcnt = 3;
        return $hopcnt;
    }
echo ceil(5/2);
exit;
$my_email = "name@company.com";
if (preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+.[a-zA-Z.]{2,5}$/", $my_email)) {
echo "$my_email is a valid email address<br>";
}

if(preg_match("/^[a-z]{3}+[-]+[a-z]{4}+[-]+[a-z0-9]{4}+[-]+[0-9]{4}$/i", 'AAS-GWHT-JC02-0148')){
	echo 'mat';
}
else
	echo 'not';
exit;
for($i=0;$i<=5;$i++){
	if($i==2)
		continue;
	echo $i.'<br>';
}
echo '<br>';
echo 'test'==='test';
echo '<br>';
echo microtime().'<br>';
echo microtime(TRUE).'<br>';
exit;
$myArray = array("f"=>array("id"=>30,"name"=>"b","order"=>"GigabitEthernet0/4/7"),"t"=>array("id"=>30,"name"=>"b","order"=>"GigabitEthernet0/4/4"),"z"=>array("id"=>50,"name"=>"d","order"=>"GigabitEthernet0/4/6"),"s"=>array("id"=>40,"name"=>"a","order"=>"GigabitEthernet0/4/2"),"u"=>array("id"=>20,"name"=>"d","order"=>"GigabitEthernet0/4/5"));
echo '<pre>';
//print_r($myArray);
/* usort($myArray, function($a, $b) {
	//echo $a['order'].' - '.$b['order'].'<br>';
	//preg_match("/[0-9]+/", $a['order'],$result);
	//echo preg_replace("/Gig(\d+)/",'',$a['order']);
	//echo str_replace('/','',$a['order']);
    return str_replace('/','',$a['order']) - str_replace('/','',$b['order']);
}); 
usort($myArray, 'sortById');
function sortById($x, $y) {
	echo $x['order'].' - '.$y['order'].'<br>';
    return $x['order'] - $y['order'];
}*/


function sortByPorts (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

sortByPorts($myArray,"order");


print_r($myArray);
exit;
echo preg_replace("/Te(\d+)/", 'Tengig$1','Te0/0/0/9');

exit;
$input = 'JPURTBNRPAR001';
echo substr_replace($input, 'ESR', 8, 3);

exit;
$content = 'Gig0/0/1';
$a = explode('/',$content);
print_r($a);
$res = preg_match("/[0-9]+/", $a[0],$result);
print_R($result);
$b = array_shift($a);
print_R($a);
echo '>>>'.$str = implode('/',$a).'/'.$result[0];
exit;
echo $_SERVER ['HTTP_USER_AGENT'];
?><html>
    <head></head>
    <script src="jquery-3.2.1.min.js"></script>
    <body>
        <div class="row">
            <div><select class="project"><option>project1</option><option>project2</option></select></div>
            <div><select class="subproject"><option>subproject1</option><option>subproject2</option></select></div>
        </div>
        
    </body>
    <script>
    $(document).ready(function(){
        $(".project").on('change',function(){
            console.log($(this).val())
			$(".subproject").html('');
            /* var opt = '<option>edited1</option><option>edited2</option>';
            $(this).parents('.row').find('.subproject').html(opt) */
        })
        
    })
    </script>
</html>
<?php
include_once "smaz.php";

$strs = array(
    "http://www.baidu.com",
    "This is a small string",
    "THIS IS A SMALL STRING",
    "Nothing is more difficult, and therefore more precious, than to be able to decide",
    "http://github.com/antirez/smaz/tree/master",
    "1000 numbers 2000 will 10 20 30 compress very little", 
    "good", 
    "luck",
    "好好学习，天天向上",
    "白日依山尽，黄河入海流，欲穷千里目，更上一层楼",
);

foreach($strs as $str)
{
    $x = Smaz::encode($str);
    $t = Smaz::decode($x);

    $rate = 100 - round(strlen($x)/strlen($str) * 100, 2); 

    echo $rate > 0 ? "-" : "+", abs($rate), "%\t", $t, "\n";
}

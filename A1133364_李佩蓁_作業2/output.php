<?php

echo "以下為填寫的表單內容<br/>";

echo "1.你的姓名是:".$_POST["nName"]."<br/>";

$nGender=$_POST["mGender"];
$nDate=$_POST["nDate"];
$nCamp=$_POST["nCamp"];
$nInterest=$_POST["ninterest"];
$nEmail=$_POST["nEmail"];
$nColor=$_POST["nColor"];
$msg=$_POST["msg"];


if($nGender=="m"){
    echo "2.你的性別是:生理男<br/>";
}
else {
    echo "2.你的性別是:生理女<br/>";
}

echo "3.你的出生年月日是:".$nDate."<br/>";

echo "4.你選擇的營隊場次:".$nCamp."<br/>";

echo "5.你的興趣是:";
foreach($nInterest as $In2){
    switch($In2){
         case "a";
            echo "炫彩小屋 ";
            break;
        case "b";
            echo "模擬下雪機 ";
            break;
        case "c";
            echo "遙控動力船 ";
            break;
        case "d";
            echo "太陽能風車 ";
            break;
    }
}
echo "</br>";

echo "6.常用email為:".$nEmail."<br/>";

echo "7.你選的顏色是:<font color=".$nColor.">這個顏色</font></br>";

echo "8.想對我們說的話:";
echo stripslashes(nl2br(strip_tags($msg)));
echo "<br/>";

?>
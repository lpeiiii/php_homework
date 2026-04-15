<?php
session_start();

$fID='student';
$fPWD='12345';

$aID='admin';
$aPWD='54321';

$tID='teacher';
$tPWD='13579';

$uID=$_POST['uName'];
$uPwd=$_POST['uPwd'];

$date=strtotime("+1 days",time()); #(有效期限,現在時間)

if($uID==$fID && $uPwd==$fPWD){
    $_SESSION['login']='user';
    setcookie("uName",$uID,$date);
    header("Refresh:0;url=student.php");

}elseif($uID==$aID && $uPwd==$aPWD){
    $_SESSION['login']='admin';
    setcookie("uName",$uID,$date);
    header("Refresh:0;url=admin.php");

}elseif($uID==$tID && $uPwd==$tPWD){
    $_SESSION['login']='teacher';
    setcookie("uName",$uID,$date);
    header("Refresh:0;url=teacher.php");

}else{
    echo "<h2>登入失敗!系統將在2秒後返回首頁</h2>";
    header("Refresh:2;url=index.php");
}

?>
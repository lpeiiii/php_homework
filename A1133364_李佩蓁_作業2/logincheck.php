<?php

$fID="aaa";
$fPWD="12345";
if(isset($_POST["uID"]) && isset($_POST["uPWD"])){
    $uID=$_POST["uID"];
    $uPWD=$_POST["uPWD"];

    if($fID==$uID && $fPWD==$uPWD){
        header("Location:A1133364_李佩蓁_作業1.php");
    }
    else {
        echo "登入失敗";
        header("Refresh:2;url=login.php");
    }
}

?>
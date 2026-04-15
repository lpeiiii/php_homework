<?php
session_start();


if(isset($_SESSION['ID'])){
    $ID=$_SESSION['ID'];
    $Name=$_SESSION['Name'];
    $Price=$_SESSION['Price'];
    $Number=$_SESSION['numbers'];

    setcookie($ID.'[ID]',$ID,time()+1000,'/');
    setcookie($ID.'[Name]',$Name,time()+1000,'/');
    setcookie($ID.'[Price]',$Price,time()+1000,'/');
    setcookie($ID.'[numbers]',$Number,time()+1000,'/');
}

header("Refresh:0;url=shoppingcart.php");



?>
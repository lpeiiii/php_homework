<?php

if(isset($_GET['Id'])){
    $ID=$_GET['Id'];

    setcookie($ID.'[ID]', '', time() - 3600, '/');
    setcookie($ID.'[Name]', '', time() - 3600, '/');
    setcookie($ID.'[Price]', '', time() - 3600, '/');
    setcookie($ID.'[numbers]', '', time() - 3600, '/');
}
header("Refresh:0;url=shoppingcart.php");



?>
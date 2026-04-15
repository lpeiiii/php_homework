<?php
session_start();

session_destroy();
header("Refresh:2;url=index.php");

?>

<center>
    <br><br><br>
    <h2>系統登出中...</h2>
</center>
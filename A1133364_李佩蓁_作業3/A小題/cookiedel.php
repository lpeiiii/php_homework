<?php

setcookie('uName','',time()-10); #只要是負的都可以刪除
header("Refresh:0;url=index.php");



?>
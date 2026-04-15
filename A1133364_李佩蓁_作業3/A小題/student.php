<?php
session_start();

if(isset($_SESSION['login'])){
    if($_SESSION['login']=='user'){
        echo "<h1>學生身分登入成功!</h1><br>";
        echo "<a href='logout.php'>Logout</a>";
    }
    else {
        echo "<h1>非法登入!2秒回首頁</h1>";
        header("Refresh:2;url=index.php");
    }
}
else {
    echo "<h1>非法登入!2秒回首頁</h1>";
    header("Refresh:2;url=index.php");
}

?>

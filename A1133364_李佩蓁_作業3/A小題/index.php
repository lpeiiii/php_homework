<?php

if(isset($_COOKIE['uName'])){
    echo $_COOKIE['uName']."歡迎回來";
    #echo "<a href='cookiedel.php'>刪除cookie</a>";
    echo "<br>";
    echo "<a href='cookiedel.php'>[刪除Cookie紀錄]</a>";
}   


?>

<h2>網站登入系統</h2>
<form action="logincheck.php" method="POST">

    <table border="0" cellpadding="8" cellspacing="0">
        <tr>
            <td>ID：</td>
            <td>
                <input type="text" name="uName" placeholder="請輸入帳號">
            </td>
        </tr>
        <tr>
            <td>Password：</td>
            <td>
                <input type="password" name="uPwd" placeholder="請輸入密碼" size="20" required>
            </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="登入">
                </td>
            </tr>
        </table>

</form>
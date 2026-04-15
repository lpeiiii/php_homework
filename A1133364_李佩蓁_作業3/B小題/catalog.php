<?php
session_start();


if(isset($_POST['items'])){
    $_SESSION['numbers']=$_POST['numbers'];
    $ID=$_POST['items'];
    $_SESSION['ID']=$ID;

    if($ID=='N01'){
        $_SESSION['Name']='辣炒年糕餅乾';
        $_SESSION['Price']='150';
    }
    else if($ID=='N02'){
        $_SESSION['Name']='生巧克力';
        $_SESSION['Price']='350';
    }
    else if($ID=='N03'){
        $_SESSION['Name']='鳳梨酥';
        $_SESSION['Price']='280';
    }
    else if($ID=='N04'){
        $_SESSION['Name']='薯條三兄弟';
        $_SESSION['Price']='300';
    }
    header("Refresh:0;url=savecart.php");
}


?>

<center>
    <h2>零食購物網站</h2>
    <hr width="300" />
        <form action="catalog.php" method="POST">
            <table border="0" cellpadding="8">
                <tr>
                    <td align="right"><b>選擇商品：</b></td>
                    <td>
                        <select name="items">
                            <option value="N01">辣炒年糕餅乾 - $150</option>
                            <option value="N02">生巧克力 - $350</option>
                            <option value="N03">鳳梨酥 - $280</option>
                            <option value="N04">薯條三兄弟 - $300</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right"><b>購買數量：</b></td>
                    <td>
                        <input type="number" name="numbers" value="1" min="1">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="加入購物車">
                    </td>
                </tr>
            </table>
        </form>
        <br>
        <a href="shoppingcart.php">[ 檢視我的購物車 ]</a>
</center>
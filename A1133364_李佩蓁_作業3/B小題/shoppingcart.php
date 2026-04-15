<?php
session_start();

?>
<center>
    <h2>我的購物車</h2>
    <hr width="400" />
    <table border="1" cellpadding="8" cellspacing="0" width="400">
        <tr>
            <th>操作</th>
            <th>商品編號</th>
            <th>商品名稱</th>
            <th>單價</th>
            <th>數量</th>
        </tr>
<?php
$total = 0;
$flag = true; 

foreach($_COOKIE as $arr => $cookie_array){
    if(is_array($cookie_array) && isset($cookie_array['ID'])){
        if($flag){
            $color = "#CCCCCC";
            $flag = false;
        } else {
            $color = "#F9F9F9";
            $flag = true;
        }
        
        echo "<tr bgcolor='".$color."'>";
        echo "<td><a href='delete.php?Id=".$arr."'>刪除</a></td>";
        
        $price = 0;
        $numbers = 0;
        
        foreach($cookie_array as $name => $value){
            echo "<td>".$value."</td>"; 
            if($name == "Price"){
                $price = $value;
            }
            if($name == "numbers"){
                $numbers = $value;
            }
        }
        $total += $price * $numbers;
        echo "</tr>";
    }
}
?>
        <tr>
            <td colspan="5" align="right"><b>總金額：<?php echo $total; ?> 元</b></td>
        </tr>
    </table>
    <br>
    <a href="catalog.php">[ 繼續購物 ]</a>
</center>

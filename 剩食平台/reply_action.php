<?php
include("db.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg_id = (int)$_POST['msg_id'];
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    
    $sql = "UPDATE admin_messages 
            SET admin_reply = '$reply', status = 'replied' 
            WHERE message_id = $msg_id";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('回覆成功！'); window.location.href='admin_questions.php';</script>";
    } else {
        echo "錯誤: " . mysqli_error($conn);
    }
}
?>
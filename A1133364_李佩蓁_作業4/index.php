<?php

session_start();

//連線資料庫
$link = @mysqli_connect('localhost', 'root', '', 'mail');
$dbname = 'mail';

if (!$link || !mysqli_select_db($link, $dbname)) {
    die("無法開啟 $dbname 資料庫，請確認 XAMPP 是否開啟！<br/>");
}

$message_output = ""; 

//檢查有沒有從上面導向過來的暫存訊息
if (isset($_SESSION['flash_msg'])) {
    $message_output = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']); // 顯示完就立刻刪除，確保下一次重新整理完全乾淨
}

//處理個別刪除名單功能
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['no'])) {
    $no_to_delete = mysqli_real_escape_string($link, $_GET['no']);
    $sql_delete = "DELETE FROM `sendmail` WHERE `No` = '$no_to_delete'";
    if (mysqli_query($link, $sql_delete)) {
        $_SESSION['flash_msg'] = "<div class='alert success'>成功刪除該筆收件人！</div>";
        header("Location: index.php");
        exit;
    } else {
        $message_output = "<div class='alert error'>刪除失敗: " . mysqli_error($link) . "</div>";
    }
}

//功能A：使用者輸入email位址，存入MySQL資料庫
if (isset($_POST['add_email'])) {
    $email = mysqli_real_escape_string($link, trim($_POST['new_email']));
    
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        //檢查是否重複
        $sql_check = "SELECT * FROM `sendmail` WHERE `email` = '$email'";
        $check_result = mysqli_query($link, $sql_check);
        
        if ($check_result && mysqli_num_rows($check_result) == 0) {
            
            //寫入資料庫
            $sql_insert = "INSERT INTO `sendmail` (`email`) VALUES ('$email')";
            
            if (mysqli_query($link, $sql_insert)) {
                //成功存入
                $_SESSION['flash_msg'] = "<div class='alert success'>成功將 [{$email}] 存入MySQL資料庫</div>";
                header("Location: index.php");
                exit;
            } else {
                // 寫入錯誤
                $_SESSION['flash_msg'] = "<div class='alert error'>寫入失敗: " . mysqli_error($link) . "</div>";
                header("Location: index.php");
                exit;
            }
        } else {
            //重複錯誤
            $_SESSION['flash_msg'] = "<div class='alert error'>該Email已經存在於資料庫中</div>";
            header("Location: index.php");
            exit;
        }
    } else {
        //格式錯誤
        $_SESSION['flash_msg'] = "<div class='alert error'>請輸入正確的Email格式</div>";
        header("Location: index.php");
        exit;
    }
}

//從資料庫撈出目前現有的名單，供下方表格與 B 區發信功能使用 
$email_list = [];
$email_data_array = []; 
$sql_select = "SELECT * FROM sendmail ORDER BY No ASC";
$result = mysqli_query($link, $sql_select);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $email_list[] = $row['email']; 
        $email_data_array[] = $row;    
    }
}

// 關閉連線
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>垃圾郵件寄送系統</title>
    <style>
        body { font-family: "Microsoft JhengHei", Arial, sans-serif; background-color: #f4f6f9; padding: 30px; color: #333; }
        .container { max-width: 750px; margin: auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { text-align: center; color: #1a73e8; margin-bottom: 25px; }
        h3 { color: #1a73e8; border-bottom: 2px solid #1a73e8; padding-bottom: 5px; margin-top: 30px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 6px; color: #495057; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .form-group textarea { height: 120px; resize: vertical; }
        .btn-send { width: 100%; background-color: #1a73e8; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .btn-send:hover { background-color: #1557b0; }
        .alert { padding: 15px; border-radius: 6px; margin-top: 20px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* 表格與單筆刪除樣式 */
        .mail-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .mail-table th, .mail-table td { border: 1px solid #ced4da; padding: 8px 12px; text-align: left; }
        .mail-table th { background-color: #f8f9fa; color: #495057; }
        .btn-del-single { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-del-single:hover { background-color: #bd2130; }

        .progress-box { display: none; background-color: #e9ecef; border: 1px solid #ced4da; height: 30px; border-radius: 6px; overflow: hidden; margin-top: 15px; }
        .progress-bar { background-color: #28a745; width: 0%; height: 100%; text-align: center; line-height: 30px; color: white; font-weight: bold; transition: width 0.2s; }
        .status-log { background-color: #333; color: #fff; padding: 12px; border-radius: 6px; margin-top: 10px; max-height: 120px; overflow-y: auto; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>

<div class="container">
    <h2>垃圾郵件寄送系統</h2>
    
    <?php if (!empty($message_output)) echo $message_output; ?>

    <h3>A. 建構收件者名單資料庫</h3>
    <form action="" method="post">
        <div class="form-group">
            <label for="new_email">請輸入Email位址：</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" name="new_email" id="new_email" placeholder="student@example.com" style="flex: 1;" required>
                <button type="submit" name="add_email" class="btn-send" style="width: auto; padding: 0 20px;">儲存名單</button>
            </div>
        </div>
    </form>

    <div class="form-group">
        <label>資料表內的名單：</label>

        <?php if (!empty($email_data_array)): ?>
            <table class="mail-table">
                <thead>
                    <tr>
                        <th width="15%">No.</th>
                        <th width="70%">電子郵件 (Email)</th>
                        <th width="15%">管理操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($email_data_array as $index => $row): ?>
                        <tr>
                            <td>No.<?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <a href="index.php?action=delete&no=<?php echo $row['No']; ?>" 
                                   class="btn-del-single" 
                                   onclick="return confirm('確定要刪除這筆收件人嗎？')">刪除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #6c757d; font-size: 14px; margin-top: 10px;">資料庫目前為空，請在上方輸入信箱並儲存。</p>
        <?php endif; ?>
    </div>

    <h3>B. 發送模式與時間間隔排程設定</h3>
    <form id="mailForm">
        <div class="form-group">
            <label for="send_mode">發送對象模式：</label>
            <select id="send_mode" onchange="toggleRandomCount()">
                <option value="all">全部寄送</option>
                <option value="random">隨機寄送幾筆</option>
            </select>
        </div>

        <div class="form-group" id="random_count_div" style="display: none;">
            <label for="random_count">指定隨機寄送的筆數：</label>
            <input type="number" id="random_count" min="1" value="2">
        </div>

        <div class="form-group">
            <label for="interval_seconds">寄送郵件間隔秒數：</label>
            <input type="number" id="interval_seconds" min="0" value="3" required>
        </div>

        <div class="form-group">
            <label for="subject">郵件標題：</label>
            <input type="text" id="subject" value="PHP信件" required>
        </div>

        <div class="form-group">
            <label for="content">郵件內容：</label>
            <textarea id="content" required>這是一封PHP的測試信件。</textarea>
        </div>

        <button type="button" id="sendBtn" class="btn-send" onclick="startSendingQueue()">開始寄信</button>
    </form>

    <div class="progress-box" id="progressBox">
        <div class="progress-bar" id="progressBar">0%</div>
    </div>
    <div class="status-log" id="statusLog" style="display: none;"></div>
</div>

<script>
const emailDatabase = <?php echo json_encode($email_list); ?>;

function toggleRandomCount() {
    const mode = document.getElementById('send_mode').value;
    document.getElementById('random_count_div').style.display = (mode === 'random') ? 'block' : 'none';
}

function shuffleList(array) {
    let newArr = [...array];
    for (let i = newArr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [newArr[i], newArr[j]] = [newArr[j], newArr[i]];
    }
    return newArr;
}

async function startSendingQueue() {
    if (emailDatabase.length === 0) {
        alert("目前資料庫內沒有 any 收件者，請先在A區新增名單。");
        return;
    }

    const subject = document.getElementById('subject').value.trim();
    const content = document.getElementById('content').value.trim();
    const mode = document.getElementById('send_mode').value;
    const intervalSec = parseInt(document.getElementById('interval_seconds').value) || 0;

    if (!subject || !content) {
        alert("主旨與內容不可為空！");
        return;
    }

    let targets = [...emailDatabase];
    if (mode === 'random') {
        const count = parseInt(document.getElementById('random_count').value) || 1;
        targets = shuffleList(targets).slice(0, Math.min(count, targets.length));
    }

    const sendBtn = document.getElementById('sendBtn');
    const progressBar = document.getElementById('progressBar');
    const progressBox = document.getElementById('progressBox');
    const statusLog = document.getElementById('statusLog');

    sendBtn.disabled = true;
    sendBtn.innerText = "排程群發中...";
    progressBox.style.display = "block";
    statusLog.style.display = "block";
    statusLog.innerHTML = `[系統] 任務啟動，共從 MySQL 載入 ${targets.length} 筆發送目標。<br>`;

    for (let i = 0; i < targets.length; i++) {
        const currentEmail = targets[i];
        statusLog.innerHTML += `[發送] (${i+1}/${targets.length}) 正在處理: ${currentEmail}...<br>`;
        statusLog.scrollTop = statusLog.scrollHeight;

        const postData = new FormData();
        postData.append('to_email', currentEmail);
        postData.append('subject', subject);
        postData.append('body', content);

        try {
            const response = await fetch('send.php', { method: 'POST', body: postData });
            const result = await response.json();
            
            if (result.ok) {
                statusLog.innerHTML += `<span style="color: #6cf0a3;"> 成功寄出！</span><br>`;
            } else {
                statusLog.innerHTML += `<span style="color: #f06c6c;"> 失敗: ${result.msg}</span><br>`;
            }
        } catch (error) {
            statusLog.innerHTML += `<span style="color: #f06c6c;"> 網路連線錯誤</span><br>`;
        }

        const percentage = Math.round(((i + 1) / targets.length) * 100);
        progressBar.style.width = percentage + '%';
        progressBar.innerText = percentage + '%';
        statusLog.scrollTop = statusLog.scrollHeight;

        if (i < targets.length - 1) {
            statusLog.innerHTML += `[等待] 設定間隔 ${intervalSec} 秒後繼續...<br>`;
            statusLog.scrollTop = statusLog.scrollHeight;
            await new Promise(resolve => setTimeout(resolve, intervalSec * 1000));
        }
    }

    statusLog.innerHTML += `[完成] 群發任務結束，共處理 ${targets.length} 筆信箱。<br>`;
    statusLog.scrollTop = statusLog.scrollHeight;
    sendBtn.disabled = false;
    sendBtn.innerText = "開始寄信";
}
</script>

</body>
</html>
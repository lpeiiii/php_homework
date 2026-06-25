<?php
session_start();

if (!isset($_SESSION['login']) || !isset($_SESSION['uName'])) {
    header("Location: index.php");
    exit();
}

$uName = $_SESSION['uName'];
$role  = $_SESSION['login'];

$link = @mysqli_connect('localhost', 'root', '', 'food_waste');
if (!$link) die("資料庫連線失敗: " . mysqli_connect_error());
mysqli_set_charset($link, "utf8mb4");

// 撈取當前登入使用者的所有個人通知 (保留在主檔，因為這是此頁面專屬資料)
$uName_escaped = mysqli_real_escape_string($link, $uName);
$sql_notify = "SELECT * FROM notifications WHERE user_name = '$uName_escaped' ORDER BY No DESC";
$result_notify = mysqli_query($link, $sql_notify);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>通知管理 — EcoBox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        /* 這裡只保留通知頁面專屬的 CSS (其他共用的都在 shared_header.php 裡了) */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: "Noto Sans TC", sans-serif; background: #f7f6f2; color: #1a1a1a; overflow: hidden; }

        .main-wrapper { height: calc(100vh - 75px); width: 100%; overflow-y: auto; padding: 40px; }
        .content-container { max-width: 880px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }
        .page-block-title { font-size: 22px; font-weight: 900; color: #132a13; display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }

        .notify-list-wrapper { display: flex; flex-direction: column; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.04); overflow: hidden; }
        .notify-item-row { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; gap: 30px; border-bottom: 1px solid #eef0f2; transition: background 0.2s; }
        .notify-item-row:last-child { border-bottom: none; }
        .notify-item-row:hover { background-color: #fafafa; }

        .notify-left-body { display: flex; align-items: center; gap: 20px; }
        .notify-index { font-size: 18px; font-weight: 900; color: #3a5a40; min-width: 25px; }
        .notify-text { font-size: 16px; font-weight: 500; color: #111111; }
        .notify-time { font-size: 14px; color: #888888; white-space: nowrap; font-weight: 500; }

        .no-data { text-align: center; padding: 60px 20px; color: #888888; font-size: 16px; font-weight: 500; border: 2px dashed #cccccc; border-radius: 12px; background: #fff; }
        .no-data i { font-size: 44px; margin-bottom: 12px; display: block; color: #b8975a; }
    </style>
</head>
<body>

    <?php include 'shared_header.php'; ?>

    <main class="main-wrapper">
        <div class="content-container">
            
            <div class="page-block-title">
                <i class="fa-regular fa-bell" style="color: #b8975a;"></i> 實時系統通知
            </div>
            
            <div class="notify-list-wrapper">
                <?php
                if ($result_notify && mysqli_num_rows($result_notify) > 0) {
                    $idx = 1;
                    while ($row_notify = mysqli_fetch_assoc($result_notify)) {
                        $timestamp = strtotime($row_notify['created_at']);
                        $hour = date('H', $timestamp);
                        $ampm = ($hour >= 12) ? '下午' : '上午';
                        $time_str = $ampm . date('g:i', $timestamp);
                ?>
                        <div class="notify-item-row">
                            <div class="notify-left-body">
                                <div class="notify-index"><?php echo $idx; ?>.</div>
                                <div class="notify-text"><?php echo htmlspecialchars($row_notify['content']); ?></div>
                            </div>
                            <div class="notify-time"><?php echo $time_str; ?></div>
                        </div>
                <?php
                        $idx++;
                    }
                } else {
                    // 防呆展示資料
                    $samples = [
                        ["content" => "商品已備好，可取餐", "time" => "下午2:56"],
                        ["content" => "收藏中的商家已有商品可選購", "time" => "上午10:11"]
                    ];
                    foreach ($samples as $index => $sample) {
                ?>
                        <div class="notify-item-row">
                            <div class="notify-left-body">
                                <div class="notify-index"><?php echo ($index + 1); ?>.</div>
                                <div class="notify-text"><?php echo $sample['content']; ?></div>
                            </div>
                            <div class="notify-time"><?php echo $sample['time']; ?></div>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
            
        </div>
    </main>

</body>
</html>
<?php mysqli_close($link); ?>
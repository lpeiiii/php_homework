<?php 
    session_start();
    
    $link = @mysqli_connect('localhost', 'root', '', 'food_waste');
    mysqli_set_charset($link, "utf8mb4");

    $notifications = [
        'finance' => [],
        'order'   => [],
        'review'  => [],
    ];

    if (isset($_SESSION['store_name'])) {
        $store_name = mysqli_real_escape_string($link, $_SESSION['store_name']);
        
        $res_store = mysqli_query($link, "SELECT No FROM store WHERE store_name = '$store_name' LIMIT 1");
        $store_row = mysqli_fetch_assoc($res_store);
        $store_id  = $store_row ? $store_row['No'] : 0;
        
        if ($store_id > 0) {
            // JOIN orders_history 撈訂單明細
            $query = "SELECT sn.id, sn.store_id, sn.order_id, sn.type, sn.content, sn.is_read, sn.created_at,
                             oh.items_desc, oh.total_price, oh.user_name AS buyer_name
                      FROM seller_notifications sn
                      LEFT JOIN orders_history oh ON sn.order_id = oh.No
                      WHERE sn.store_id = $store_id
                      ORDER BY sn.created_at DESC";
            $result = mysqli_query($link, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $type = $row['type'];
                    if (array_key_exists($type, $notifications)) {
                        $notifications[$type][] = $row;
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>EcoBox - 通知中心</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; background-color: #f7f6f2; margin-top: 70px; }

        .container { max-width: 800px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

        .tabs { display: flex; justify-content: center; gap: 20px; border-bottom: 2px solid #ddd; margin-bottom: 25px; flex-wrap: wrap; }
        .tab-btn { background: none; border: none; font-size: 16px; font-weight: bold; color: #aaa; padding: 8px 16px; cursor: pointer; transition: 0.3s; position: relative; top: 2px; }
        .tab-btn.active { color: #222; border-bottom: 4px solid #5a5a5a; }
        .tab-btn:hover { color: #555; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* 一般通知列（帳款、評論用） */
        .notify-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px dashed #eee; font-size: 16px; }
        .notify-text { flex: 1; color: #333; line-height: 1.5; }
        .notify-time { font-size: 13px; color: #888; margin-left: 15px; min-width: 70px; text-align: right; white-space: nowrap; }

        /* 訂單卡片 */
        .order-card {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 14px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        .order-card.done { opacity: 0.55; }

        .order-checkbox {
            width: 22px; height: 22px; cursor: pointer;
            accent-color: #659287; flex-shrink: 0; margin-top: 4px;
        }

        .order-detail { flex: 1; }
        .order-detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .order-buyer { font-size: 15px; font-weight: 700; color: #23342b; }
        .order-time { font-size: 12px; color: #aaa; }

        .order-items { font-size: 14px; color: #555; margin-bottom: 4px; }
        .order-price { font-size: 15px; font-weight: 700; color: #e63946; }

        .order-status-done {
            display: inline-block; background: #e6f4ea; color: #3a5a40;
            font-size: 12px; font-weight: 700; padding: 2px 10px;
            border-radius: 12px; margin-top: 6px;
        }

        .empty-state { text-align: center; color: #999; padding: 40px 20px; font-size: 16px; }
        .warning-badge { background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

<?php include('seller_header.php'); ?>

<div class="container">
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab(event, 'finance')">帳款通知</button>
        <button class="tab-btn" onclick="switchTab(event, 'order')">訂單通知</button>
        <button class="tab-btn" onclick="switchTab(event, 'review')">評論通知</button>
    </div>

    <!-- 帳款通知 -->
    <div id="finance" class="tab-content active">
        <?php if(empty($notifications['finance'])): ?>
            <div class="empty-state">目前沒有帳款通知。</div>
        <?php else: ?>
            <?php foreach($notifications['finance'] as $n): ?>
            <div class="notify-item">
                <div class="notify-text">💰 <?php echo htmlspecialchars($n['content']); ?></div>
                <div class="notify-time"><?php echo date('m-d H:i', strtotime($n['created_at'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 訂單通知 -->
    <div id="order" class="tab-content">
        <?php if(empty($notifications['order'])): ?>
            <div class="empty-state">目前沒有訂單通知。</div>
        <?php else: ?>
            <?php foreach($notifications['order'] as $n): ?>
            <?php $is_done = ($n['is_read'] == 1); ?>
            <div class="order-card <?php echo $is_done ? 'done' : ''; ?>" id="order_card_<?php echo $n['id']; ?>">
                
                <?php if (!$is_done): ?>
                    <input type="checkbox"
                           class="order-checkbox"
                           onclick="markReady(<?php echo $n['id']; ?>, this)">
                <?php else: ?>
                    <input type="checkbox" class="order-checkbox" checked disabled>
                <?php endif; ?>

                <div class="order-detail">
                    <div class="order-detail-header">
                        <span class="order-buyer">
                            <i class="fa-solid fa-user" style="color:#659287;"></i>
                            <?php echo htmlspecialchars($n['buyer_name'] ?? '消費者'); ?>
                        </span>
                        <span class="order-time"><?php echo date('m-d H:i', strtotime($n['created_at'])); ?></span>
                    </div>
                    <div class="order-items">
                        🍔 <?php echo htmlspecialchars($n['items_desc'] ?? $n['content']); ?>
                    </div>
                    <?php if (!empty($n['total_price'])): ?>
                    <div class="order-price">$<?php echo number_format((int)$n['total_price']); ?></div>
                    <?php endif; ?>
                    <?php if ($is_done): ?>
                    <span class="order-status-done">✓ 已通知消費者取餐</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 評論通知 -->
    <div id="review" class="tab-content">
        <?php if(empty($notifications['review'])): ?>
            <div class="empty-state">目前沒有評論通知。</div>
        <?php else: ?>
            <?php foreach($notifications['review'] as $n): ?>
            <div class="notify-item">
                <div class="notify-text">⭐ <?php echo htmlspecialchars($n['content']); ?></div>
                <div class="notify-time"><?php echo date('m-d H:i', strtotime($n['created_at'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.querySelector('.sidebar'); 
        if (sidebar && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });

    function switchTab(evt, tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    function markReady(notifId, checkboxElement) {
        if (!checkboxElement.checked) return;

        if (!confirm('確定餐點已準備完成，並發送通知與 Email 給消費者嗎？')) {
            checkboxElement.checked = false;
            return;
        }

        checkboxElement.disabled = true;

        fetch('ajax_order_ready.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'order_id=' + encodeURIComponent(notifId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('✓ 已成功發送通知與信件給消費者！');
                const card = document.getElementById('order_card_' + notifId);
                if (card) {
                    card.classList.add('done');
                    card.querySelector('.order-detail').insertAdjacentHTML('beforeend',
                        '<span class="order-status-done">✓ 已通知消費者取餐</span>');
                }
            } else {
                alert('❌ 錯誤：' + (data.message || '未知錯誤'));
                checkboxElement.disabled = false;
                checkboxElement.checked = false;
            }
        })
        .catch(error => {
            console.error('AJAX 錯誤:', error);
            alert('系統發生錯誤，請稍後再試。');
            checkboxElement.disabled = false;
            checkboxElement.checked = false;
        });
    }
</script>
</body>
</html>
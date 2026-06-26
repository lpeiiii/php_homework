<?php
session_start();
include("db.php");
include("admin-header.php");

// ── 處理審核動作 ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $withdraw_id = (int)$_POST['withdraw_id'];
    $store_id    = (int)$_POST['store_id'];
    $amount      = (float)$_POST['amount'];
    $action      = $_POST['action']; // 'approved' 或 'rejected'

    if ($action === 'approved') {
        // 1. 更新申請狀態
        mysqli_query($conn, "UPDATE withdraw_requests SET status = 'approved' WHERE withdraw_id = $withdraw_id");

        // 2. 扣除商家錢包餘額
        mysqli_query($conn, "UPDATE store_wallets SET balance = balance - $amount WHERE store_id = $store_id");

        // 3. 寫入帳款通知
        $content = mysqli_real_escape_string($conn, "您的提款申請 \${$amount} 元已審核通過，款項將於 3-5 個工作天內匯入您的帳戶。");
        mysqli_query($conn, "INSERT INTO seller_notifications (store_id, type, content, is_read, created_at) 
                             VALUES ($store_id, 'finance', '$content', 0, NOW())");

    } elseif ($action === 'rejected') {
        // 1. 更新申請狀態
        mysqli_query($conn, "UPDATE withdraw_requests SET status = 'rejected' WHERE withdraw_id = $withdraw_id");

        // 2. 寫入帳款通知
        $content = mysqli_real_escape_string($conn, "您的提款申請 \${$amount} 元審核未通過，請重新確認帳戶資料後再次申請。");
        mysqli_query($conn, "INSERT INTO seller_notifications (store_id, type, content, is_read, created_at) 
                             VALUES ($store_id, 'finance', '$content', 0, NOW())");
    }

    echo "<script>alert('審核完成！'); window.location.href='admin_withdraw.php';</script>";
    exit();
}

// ── 撈取所有提款申請 ──
$res = mysqli_query($conn, "
    SELECT w.*, s.store_name, sw.balance
    FROM withdraw_requests w
    LEFT JOIN store s ON w.store_id = s.No
    LEFT JOIN store_wallets sw ON w.store_id = sw.store_id
    ORDER BY 
        CASE w.status WHEN 'pending' THEN 0 ELSE 1 END,
        w.created_at DESC
");
?>

<style>
    .withdraw-container { max-width: 1100px; margin: 0 auto; padding: 20px; }

    .withdraw-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 4px 16px rgba(35,52,43,0.05);
        border: 1px solid rgba(101,146,135,0.15);
    }

    .withdraw-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }

    .store-name { font-size: 17px; font-weight: 800; color: #23342b; }
    .apply-time { font-size: 13px; color: #888; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .info-item { display: flex; flex-direction: column; gap: 4px; }
    .info-label { font-size: 12px; color: #888; font-weight: 700; }
    .info-value { font-size: 15px; font-weight: 700; color: #111; }
    .info-value.amount { color: #e63946; font-size: 18px; }
    .info-value.balance { color: #3a5a40; }

    .btn-group { display: flex; gap: 12px; }

    .btn-approve {
        background: #3a5a40; color: #fff; border: none;
        padding: 10px 24px; border-radius: 8px; font-size: 14px;
        font-weight: 700; cursor: pointer; transition: 0.2s;
    }
    .btn-approve:hover { background: #132a13; }

    .btn-reject {
        background: #fff0f0; color: #e63946; border: 1.5px solid #e63946;
        padding: 10px 24px; border-radius: 8px; font-size: 14px;
        font-weight: 700; cursor: pointer; transition: 0.2s;
    }
    .btn-reject:hover { background: #ffe0e0; }

    .status-badge {
        padding: 5px 14px; border-radius: 20px;
        font-size: 13px; font-weight: 700;
    }
    .badge-pending  { background: #fff0e6; color: #d97706; }
    .badge-approved { background: #e6f4ea; color: #3a5a40; }
    .badge-rejected { background: #fde8e8; color: #e63946; }

    .empty-state {
        text-align: center; padding: 60px; color: #888;
        background: #fff; border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
</style>

<main class="admin-main">
    <div class="withdraw-container">
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 2.2rem; color: #23342b; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-money-bill-transfer"></i> 提款審核管理
            </h2>
        </div>

        <?php if (mysqli_num_rows($res) === 0): ?>
            <div class="empty-state">
                <i class="fa-solid fa-check-circle" style="font-size:40px; color:#3a5a40; margin-bottom:10px; display:block;"></i>
                目前沒有提款申請。
            </div>
        <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <div class="withdraw-card">
                <div class="withdraw-header">
                    <div>
                        <div class="store-name">
                            <i class="fa-solid fa-store"></i> <?php echo htmlspecialchars($row['store_name']); ?>
                        </div>
                        <div class="apply-time">
                            申請時間：<?php echo htmlspecialchars($row['created_at']); ?>
                        </div>
                    </div>
                    <?php
                        $badge = match($row['status']) {
                            'approved' => ['class' => 'badge-approved', 'text' => '✓ 已核准'],
                            'rejected' => ['class' => 'badge-rejected', 'text' => '✗ 已拒絕'],
                            default    => ['class' => 'badge-pending',  'text' => '⏳ 待審核'],
                        };
                    ?>
                    <span class="status-badge <?php echo $badge['class']; ?>">
                        <?php echo $badge['text']; ?>
                    </span>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">提款金額</span>
                        <span class="info-value amount">$<?php echo number_format($row['amount'], 0); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">目前錢包餘額</span>
                        <span class="info-value balance">$<?php echo number_format($row['balance'] ?? 0, 0); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">銀行</span>
                        <span class="info-value"><?php echo htmlspecialchars($row['bank_code'] . ' ' . $row['bank_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">帳戶名稱</span>
                        <span class="info-value"><?php echo htmlspecialchars($row['account_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">帳戶號碼</span>
                        <span class="info-value"><?php echo htmlspecialchars($row['account_number']); ?></span>
                    </div>
                </div>

                <?php if ($row['status'] === 'pending'): ?>
                <form method="POST" action="admin_withdraw.php">
                    <input type="hidden" name="withdraw_id" value="<?php echo $row['withdraw_id']; ?>">
                    <input type="hidden" name="store_id"    value="<?php echo $row['store_id']; ?>">
                    <input type="hidden" name="amount"      value="<?php echo $row['amount']; ?>">
                    <div class="btn-group">
                        <button type="submit" name="action" value="approved" class="btn-approve"
                                onclick="return confirm('確定核准此筆提款申請？')">
                            <i class="fa-solid fa-check"></i> 核准
                        </button>
                        <button type="submit" name="action" value="rejected" class="btn-reject"
                                onclick="return confirm('確定拒絕此筆提款申請？')">
                            <i class="fa-solid fa-xmark"></i> 拒絕
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<?php mysqli_close($conn); ?>
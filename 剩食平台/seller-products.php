<?php
session_start();
include("db.php");

if (!isset($_SESSION['store_name'])) {
    $_SESSION['store_name'] = 1; 
}
$current_id = $_SESSION['store_name'];

// 1. 抓取商家名稱
$sql_store = "SELECT store_name FROM store WHERE store_name = '$current_id'";
$res_store = mysqli_query($conn, $sql_store);
$data_store = mysqli_fetch_assoc($res_store);
$store_name = $data_store ? $data_store['store_name'] : "未知商家";


/* ==========================================
   動作 A：處理「刪除商品」請求 (🟢 修正處：id 改為 No)
   ========================================== */
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    // 🟢 修正處：將 WHERE 條件的 id = ... 改成 No = ...
    $sql_del = "DELETE FROM seller_product WHERE No = $delete_id AND store_name = '$current_id'";
    mysqli_query($conn, $sql_del);
    header("Location: seller-products.php"); 
    exit;
}


/* ==========================================
   動作 B：處理「新增商品」表單提交 (含圖片上傳)
   ========================================== */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_add'])) {
    $p_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $p_desc = mysqli_real_escape_string($conn, $_POST['product_desc']);
    $price  = intval($_POST['price']);
    $qty    = intval($_POST['quantity']);
    
    $img_name = "";
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] == 0) {
        $target_dir = "uploads/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES["product_img"]["name"], PATHINFO_EXTENSION);
        $img_name = time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $img_name;
        
        move_uploaded_file($_FILES["product_img"]["tmp_name"], $target_file);
    }

    $sql_insert = "INSERT INTO seller_product (store_name, product_img, product_name, product_desc, price, quantity) 
                   VALUES ('$current_id', '$img_name', '$p_name', '$p_desc', $price, $qty)";
    
    if (mysqli_query($conn, $sql_insert)) {
        echo "<script>alert('商品上傳成功！'); window.location.href='seller-products.php';</script>";
        exit;
    } else {
        echo "<script>alert('資料庫寫入失敗');</script>";
    }
}

/* ==========================================
   動作 C：撈取該商家的「真實架上商品」 (🟢 修正處：ORDER BY id 改為 No)
   ========================================== */
$sql_products = "SELECT * FROM seller_product WHERE store_name = '$current_id' ORDER BY No DESC";
$result_products = mysqli_query($conn, $sql_products);

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>商品管理 - EcoBox 剩食平台</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="seller.css">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* ==========================================================================
       主要內容區塊調整：往下推避開 Fixed 導覽列
       ========================================================================== */
    body {
        background-color: #f7f6f2;
    }
    .products-container { 
      padding: 30px; 
      max-width: 1200px; 
      margin: 20px auto 0 auto; /* 距離 Header 的間距 */
      box-sizing: border-box; 
    }
    
    .page-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 24px; color: #333; }

    /* 表格滾動外框 */
    .product-table-wrapper {
      width: 100%;
      overflow-x: auto; 
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      margin-bottom: 40px;
    }
    
    .product-table {
      width: 100%;
      min-width: 850px; 
      border-collapse: collapse;
      text-align: left;
    }

    .product-table th, .product-table td {
      padding: 16px;
      border-bottom: 1px solid #eee;
      font-size: 1.5rem; 
      color: #333;
      vertical-align: middle;
    }

    .product-table th {
      background-color: #f9f9f9;
      font-weight: 700;
      color: #666;
      border-bottom: 2px solid #ccc;
    }

    .p-img-box { width: 80px; height: 60px; object-fit: cover; border: 1px solid #999; border-radius: 4px; background: #fafafa; }
    .no-img-text { font-size: 1.2rem; color: #999; text-align: center; line-height: 60px; border: 1px solid #999; width: 80px; height: 60px; display:inline-block; }

    .btn-action { padding: 6px 14px; border-radius: 20px; border: 1px solid #333; background: white; cursor: pointer; font-size: 1.3rem; margin-right: 5px; text-decoration: none; color: #333; display: inline-block; }
    .btn-action:hover { background: #eee; }

    /* 表單區塊 */
    .add-product-section { background: #ffffff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .section-subtitle { font-size: 1.6rem; background: #e0e0e0; display: inline-block; padding: 4px 12px; border-radius: 6px; margin-bottom: 15px; font-weight: bold;}
    
    .add-form-grid {
      border: 1px solid #333; padding: 20px;
      display: grid; grid-template-columns: 160px repeat(4, 1fr);
      align-items: center; gap: 15px;
    }
    .upload-box { border: 1px dashed #333; height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; font-size: 1.3rem; background: #fafafa; }
    .form-input { width: 100%; padding: 10px; border: 1px solid #999; box-sizing: border-box; font-size: 1.4rem; border-radius: 4px; }
    .btn-submit { grid-column: span 5; justify-self: end; background: #e0e0e0; border: 1px solid #333; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 1.5rem; font-weight: bold; }
    .btn-submit:hover { background: #d4d4d4; }
  </style>
</head>
<body>

  <?php 
    $link = $conn; 
    include("seller_header.php"); 
  ?>

  <main class="products-container">
    
    <div class="product-table-wrapper">
      <table class="product-table">
        <thead>
          <tr>
            <th style="width: 110px;">商品圖</th>
            <th>商品名</th>
            <th>商品描述</th>
            <th style="width: 90px;">價錢</th>
            <th style="width: 90px;">數量</th>
            <th style="width: 160px;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (mysqli_num_rows($result_products) > 0) {
              while($row = mysqli_fetch_assoc($result_products)) {
                  echo "<tr>";
                  if(!empty($row['product_img'])) {
                      echo "<td><img src='uploads/".htmlspecialchars($row['product_img'])."' class='p-img-box'></td>";
                  } else {
                      echo "<td><span class='no-img-text'>無商品圖</span></td>";
                  }
                  echo "<td style='font-weight:bold;'>".htmlspecialchars($row['product_name'])."</td>";
                  echo "<td style='color:#666;'>".htmlspecialchars($row['product_desc'])."</td>";
                  echo "<td>$".intval($row['price'])."</td>";
                  echo "<td>".intval($row['quantity'])."</td>";
                  
                  // 🟢 修正處：將修改與刪除傳遞的 $row['id'] 統一替換為 $row['No']
                  echo "<td>
                          <a href='seller-edit-product.php?id=".$row['No']."' class='btn-action'>修改</a>
                          <a href='seller-products.php?action=delete&id=".$row['No']."' class='btn-action' onclick='return confirm(\"確定要刪除此商品嗎？\")'>刪除</a>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color:#999;'>目前架上沒有任何商品，請在下方新增！</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="add-product-section">
      <div class="section-subtitle">新增商品</div>
      
      <form action="seller-products.php" method="POST" enctype="multipart/form-data">
        <div class="add-form-grid">
          
          <label class="upload-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;margin-bottom:3px;">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span style="font-size:1.2rem;">選擇檔案</span>
            <input type="file" name="product_img" accept="image/*" style="display:none;">
          </label>

          <div>
            <input type="text" name="product_name" class="form-input" placeholder="新增名稱" required>
          </div>
          <div>
            <input type="text" name="product_desc" class="form-input" placeholder="新增商品描述">
          </div>
          <div>
            <input type="number" name="price" class="form-input" placeholder="新增價錢" required min="0">
          </div>
          <div>
            <input type="number" name="quantity" class="form-input" placeholder="新增數量" required min="0">
          </div>

          <button type="submit" name="submit_add" class="btn-submit">新增</button>
        </div>
      </form>
    </div>

  </main>

  <?php mysqli_close($conn); ?>
</body>
</html>
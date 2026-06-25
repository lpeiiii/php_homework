<?php
// admin-header.php
if (!isset($active_page)) { $active_page = ''; }
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title><?php echo $page_title ?? 'EcoBox 管理後台'; ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
    :root {
      --green-dark:   #6cae8b;
      --green-mid:    #6cae8b;
      --green-light:  #d0eadc;
      --cream:        #e3e2d4;
      --cream-dark:   #d4d3c6;
      --text-dark:    #23342b;
      --text-mid:     #444;
      --text-muted:   #5a6e63;
      --gold:         #fbc02d;
      --sidebar-w:    240px;
    }
      --cream:        #f8f4ec;
      --cream-dark:   #ede8df;
      --text-dark:    #1b1b1b;
      --text-mid:     #444;
      --text-muted:   #888;
      --gold:         #e8a320;
      --sidebar-w:    240px;
  
 
    body {
      font-family: 'Noto Sans TC', sans-serif;
      background: var(--cream);
      color: var(--text-dark);
      display: flex;
      min-height: 100vh;
    }
 
    /* ── Sidebar ── */
    .admin-sidebar {
      width: var(--sidebar-w);
      background: var(--green-dark);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      height: 100vh;
      z-index: 100;
      padding: 28px 0 20px;
    }
 
    .sidebar-brand {
      color: #fff;
      font-size: 17px;
      font-weight: 700;
      padding: 0 22px 6px;
      letter-spacing: .5px;
    }
    .sidebar-badge {
      display: inline-block;
      font-size: 11px;
      font-weight: 500;
      background: var(--gold);
      color: #fff;
      border-radius: 4px;
      padding: 2px 8px;
      margin: 4px 22px 20px;
    }
 
    .sidebar-section-label {
      color: rgba(255,255,255,.5);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      padding: 0 22px 10px;
      border-bottom: 1px solid rgba(255,255,255,.12);
      margin-bottom: 8px;
    }
 
    .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 2px; padding: 0 12px; }
 
    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 14px;
      border-radius: 10px;
      text-decoration: none;
      color: rgba(255,255,255,.8);
      font-size: 14.5px;
      font-weight: 500;
      transition: background .18s, color .18s;
    }
    .nav-item:hover { background: rgba(255,255,255,.12); color: #fff; }
    .nav-item.active { background: rgba(255,255,255,.2); color: #fff; }
    .nav-icon { font-size: 17px; flex-shrink: 0; }
 
    /* ── Top bar ── */
    .admin-topbar {
      position: fixed;
      top: 0;
      left: var(--sidebar-w);
      right: 0;
      height: 58px;
      background: var(--cream);
      border-bottom: 1px solid var(--cream-dark);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      z-index: 90;
    }
    .topbar-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--green-dark);
    }
    .topbar-actions { display: flex; align-items: center; gap: 14px; }
    .topbar-icon {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: var(--cream-dark);
      display: flex; align-items: center; justify-content: center;
      font-size: 17px;
      cursor: pointer;
      transition: background .18s;
      text-decoration: none;
    }
    .topbar-icon:hover { background: var(--green-light); }
 
    /* ── Main content wrapper ── */
    .admin-main {
    margin-left: var(--sidebar-w);
    margin-top: 58px;
    padding: 28px 32px;
    flex: 1;
    min-height: calc(100vh - 58px);
    box-sizing: border-box;
  }

  /* 讓 main 裡的每個區塊都置中、並限制最大寬度 */
  .admin-main > * {
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
  }

  /* 排除像 chart-modal 這種需要滿版蓋住整個畫面的元素 */
  .admin-main > .chart-modal {
    max-width: none;
    margin: 0;
  }
    .user-card {
    background: #fff;
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 14px;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    transition: box-shadow .18s;
  }
  .user-card:hover { box-shadow: 0 4px 14px rgba(45,106,79,.1); }

  .user-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: var(--green-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }

  .user-info { flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px 20px; }

  .info-item { display: flex; flex-direction: column; }
  .info-label { font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 2px; }
  .info-value { font-size: 13.5px; color: var(--text-dark); word-break: break-all; }
  .info-value.masked { color: var(--text-muted); letter-spacing: 2px; }

  /* 按鈕組件 */
  .card-actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; align-self: center; }
  .btn { padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: opacity .15s; }
  .btn-edit { background: var(--green-light); color: var(--green-dark); }
  .btn-delete { background: #fde8e8; color: #c0392b; }

  /* 統一搜尋框風格 */
  .search-wrap { position: relative; }
  .search-wrap input {
      padding: 10px 16px 10px 40px;
      border: 1px solid var(--cream-dark);
      border-radius: 20px;
      width: 250px;
      outline: none;
      transition: all 0.3s ease;
  }
  .search-wrap input:focus { border-color: var(--green-dark); box-shadow: 0 0 8px rgba(108, 174, 139, 0.2); }
  .search-wrap::before {
      content: '🔍';
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%); font-size: 14px;
  }
    /* 評論卡片專用樣式 */
  .comment-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      width: 100%;       /* 卡片佔滿容器寬度 */
      box-sizing: border-box; /* 防止 padding 撐開寬度導致溢出 */
  }

  .comment-box {
      padding: 15px;
      background: #f9f9f9;
      border-left: 4px solid var(--green-mid);
      margin-bottom: 10px;
  }

  .reply-box {
      padding: 15px;
      background: #fff8e1; /* 給商家回覆一個淡黃色區分 */
      border-left: 4px solid var(--gold);
      margin-left: 30px;
      margin-top: 10px;
  }
  </style>
</head>
<body>
 
<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">🛠️ EcoBox 管理後台</div>
  <span class="sidebar-badge">系統管理員</span>
  <div class="sidebar-section-label">核心管理</div>
  <nav class="sidebar-nav">
    <a href="admin_index.php"   class="nav-item <?php echo $active_page=='users'   ?'active':''; ?>"><span class="nav-icon">👥</span>使用者管理</a>
    <a href="admin_stores.php"  class="nav-item <?php echo $active_page=='stores'  ?'active':''; ?>"><span class="nav-icon">🏪</span>商家管理</a>
    <a href="admin_reviews.php" class="nav-item <?php echo $active_page=='reviews' ?'active':''; ?>"><span class="nav-icon">💬</span>評論管理</a>
    <a href="admin_data.php"    class="nav-item <?php echo $active_page=='data'    ?'active':''; ?>"><span class="nav-icon">📈</span>數據管理</a>
    <a href="admin_questions.php"    class="nav-item <?php echo $active_page=='help'    ?'active':''; ?>"><span class="nav-icon">❓</span>問題中心</a>
  </nav>
</aside>
 
<!-- Top bar -->
<div class="admin-topbar">
  <div class="topbar-title"><?php echo $page_title ?? 'EcoBox 管理後台'; ?></div>
  <div class="topbar-actions">
    <a class="topbar-icon" href="#">🔔</a>
    <a class="topbar-icon" href="#">🏠</a>
    <a class="topbar-icon" href="#">👤</a>
  </div>
</div>
 
<!-- Main content starts here -->
<main class="admin-main">
 
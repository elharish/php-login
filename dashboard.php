<?php
require_once __DIR__ . '/config/database.php';
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch fresh user info
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT full_name, username, email, created_at, last_login FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$joinDate  = date('F j, Y', strtotime($user['created_at']));
$lastLogin = $user['last_login'] ? date('F j, Y \a\t g:i A', strtotime($user['last_login'])) : 'First login';
$initials  = strtoupper(substr($user['full_name'], 0, 1));
if (strpos($user['full_name'], ' ') !== false) {
    $parts     = explode(' ', $user['full_name']);
    $initials  = strtoupper($parts[0][0] . end($parts)[0]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AuthPortal user dashboard">
    <title>Dashboard — AuthPortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Animated Background -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="dashboard-wrapper">

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <div class="brand-icon small">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <span>AuthPortal</span>
        </div>
        <div class="navbar-user">
            <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
            <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
            <a href="logout.php" class="btn-logout" id="logout-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <main class="dashboard-main">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-avatar"><?= htmlspecialchars($initials) ?></div>
            <div class="welcome-text">
                <h1>Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>! 👋</h1>
                <p>You're successfully authenticated. Here's your account overview.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Full Name</span>
                    <span class="stat-value"><?= htmlspecialchars($user['full_name']) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Username</span>
                    <span class="stat-value">@<?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Email Address</span>
                    <span class="stat-value"><?= htmlspecialchars($user['email']) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Member Since</span>
                    <span class="stat-value"><?= htmlspecialchars($joinDate) ?></span>
                </div>
            </div>
        </div>

        <!-- Account Details Card -->
        <div class="detail-card">
            <h2 class="detail-title">Account Details</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-key">User ID</span>
                    <span class="detail-val">#<?= htmlspecialchars($_SESSION['user_id']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-key">Last Login</span>
                    <span class="detail-val"><?= htmlspecialchars($lastLogin) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-key">Account Status</span>
                    <span class="detail-val"><span class="badge-active">Active</span></span>
                </div>
                <div class="detail-item">
                    <span class="detail-key">Security</span>
                    <span class="detail-val">bcrypt hashed password</span>
                </div>
            </div>
        </div>

    </main>
</div>

<script src="assets/js/auth.js"></script>
</body>
</html>

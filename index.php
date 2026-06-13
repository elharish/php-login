<?php
require_once __DIR__ . '/config/database.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $conn = getDBConnection();
            $stmt = $conn->prepare(
                "SELECT id, username, email, full_name, password 
                 FROM users 
                 WHERE (email = ? OR username = ?) AND is_active = 1"
            );
            $stmt->bind_param('ss', $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Update last login
                    $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $upd->bind_param('i', $user['id']);
                    $upd->execute();
                    $upd->close();

                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['username']  = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email']     = $user['email'];

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid credentials. Please try again.';
                }
            } else {
                $error = 'No account found with those details.';
            }

            $stmt->close();
            $conn->close();
        }
    }

    if ($action === 'register') {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username']  ?? '');
        $email     = trim($_POST['email']     ?? '');
        $password  = $_POST['password']       ?? '';
        $confirm   = $_POST['confirm']        ?? '';

        if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = 'Username must be 3–20 characters (letters, numbers, underscores only).';
        } else {
            $conn = getDBConnection();

            // Check duplicates
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $chk->bind_param('ss', $email, $username);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $error = 'An account with that email or username already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $conn->prepare(
                    "INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)"
                );
                $ins->bind_param('ssss', $full_name, $username, $email, $hashed);

                if ($ins->execute()) {
                    $success = 'Account created successfully! You can now log in.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }

                $ins->close();
            }

            $chk->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure PHP login and registration portal with modern UI">
    <title>AuthPortal — Sign In &amp; Register</title>
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

<main class="auth-wrapper">

    <!-- Brand Header -->
    <div class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <h1 class="brand-name">Portal Loginnnnnnnnnnnn</h1>
        <p class="brand-tagline">Secure &amp; Simple Authentication</p>
    </div>

    <!-- Alert Messages -->
    <?php if ($error): ?>
    <div class="alert alert-error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="card">
        <div class="tabs" role="tablist">
            <button class="tab active" id="tab-login"    role="tab" data-target="panel-login"    aria-selected="true">Sign In</button>
            <button class="tab"        id="tab-register" role="tab" data-target="panel-register" aria-selected="false">Register</button>
            <span class="tab-indicator"></span>
        </div>

        <!-- LOGIN PANEL -->
        <div class="panel active" id="panel-login" role="tabpanel" aria-labelledby="tab-login">
            <form method="POST" action="index.php" id="login-form" novalidate>
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="login-identifier">Email or Username</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text"
                               id="login-identifier"
                               name="identifier"
                               placeholder="Enter email or username"
                               autocomplete="username"
                               value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password"
                               id="login-password"
                               name="password"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="toggle-pw" data-target="login-password" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="login-submit">
                    <span>Sign In</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
        </div>

        <!-- REGISTER PANEL -->
        <div class="panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
            <form method="POST" action="index.php" id="register-form" novalidate>
                <input type="hidden" name="action" value="register">

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-fullname">Full Name</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text"
                                   id="reg-fullname"
                                   name="full_name"
                                   placeholder="John Doe"
                                   autocomplete="name"
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-username">Username</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text"
                                   id="reg-username"
                                   name="username"
                                   placeholder="johndoe"
                                   autocomplete="username"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-email">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email"
                               id="reg-email"
                               name="email"
                               placeholder="john@example.com"
                               autocomplete="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password"
                                   id="reg-password"
                                   name="password"
                                   placeholder="Min. 6 characters"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="toggle-pw" data-target="reg-password" aria-label="Toggle password visibility">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-confirm">Confirm Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password"
                                   id="reg-confirm"
                                   name="confirm"
                                   placeholder="Repeat password"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="toggle-pw" data-target="reg-confirm" aria-label="Toggle password visibility">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Password Strength -->
                <div class="strength-wrap">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <span class="strength-label" id="strength-label"></span>
                </div>

                <button type="submit" class="btn-primary" id="register-submit">
                    <span>Create Account</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
        </div>

    </div><!-- /card -->

    <p class="footer-note">&copy; <?= date('Y') ?> AuthPortal. Passwords are securely hashed with bcrypt.</p>

</main>

<script src="assets/js/auth.js"></script>
</body>
</html>

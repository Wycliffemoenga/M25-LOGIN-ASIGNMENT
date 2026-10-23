<?php
/**
 * Login Portal for School Gate Entry Management System
 */

require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . BASE_URL . "pages/dashboard.php");
    exit;
}

$error = '';
$schoolName = get_setting('school_name', 'St. Jude International Academy');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . BASE_URL . "pages/dashboard.php");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | SGEMS - School Gate Entry System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    .login-container {
      max-width: 460px;
      width: 100%;
      background: #ffffff;
      border-radius: var(--radius-lg);
      padding: 36px;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3), 0 8px 10px -6px rgba(0,0,0,0.2);
    }
    .login-header {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-logo {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      background: linear-gradient(135deg, #2563eb, #8b5cf6);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin: 0 auto 14px;
      box-shadow: 0 8px 16px rgba(37,99,235,0.35);
    }
    .demo-roles-box {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      background: #f8fafc;
      border-radius: var(--radius-sm);
      padding: 14px;
    }
    .demo-role-btns {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
      margin-top: 8px;
    }
    .role-fill-btn {
      padding: 8px 10px;
      font-size: 11.5px;
      font-weight: 600;
      border-radius: 6px;
      border: 1px solid var(--border);
      background: #fff;
      cursor: pointer;
      text-align: left;
      transition: all 0.15s;
    }
    .role-fill-btn:hover {
      border-color: var(--primary);
      background: var(--primary-light);
    }
    .role-fill-btn strong {
      display: block;
      color: var(--text-main);
    }
    .role-fill-btn span {
      font-size: 10.5px;
      color: var(--text-muted);
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="login-header">
    <div class="login-logo">🛡️</div>
    <h2 style="font-size: 22px; font-weight: 800; color: #0f172a;"><?php echo htmlspecialchars($schoolName); ?></h2>
    <p style="font-size: 13px; color: #64748b; margin-top: 4px;">School Gate Entry Management System (SGEMS)</p>
  </div>

  <?php if (!empty($error)): ?>
  <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #b91c1c; padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 20px;">
    ⚠️ <?php echo htmlspecialchars($error); ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px; font-size: 15px;">
      Sign In to Portal
    </button>
  </form>

  <div class="demo-roles-box">
    <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
      Quick 1-Click Demo Logins (Per Task PDF Roles)
    </span>
    <div class="demo-role-btns">
      <button type="button" class="role-fill-btn" onclick="fillLogin('guard', 'guard123')">
        <strong>👮 Officer Michael</strong>
        <span>Role: Gate Guard</span>
      </button>

      <button type="button" class="role-fill-btn" onclick="fillLogin('teacher', 'teacher123')">
        <strong>👩‍🏫 Sarah Jenkins</strong>
        <span>Role: Teacher</span>
      </button>

      <button type="button" class="role-fill-btn" onclick="fillLogin('principal', 'principal123')">
        <strong>🎓 Dr. Evelyn Mbeki</strong>
        <span>Role: Principal</span>
      </button>

      <button type="button" class="role-fill-btn" onclick="fillLogin('admin', 'admin123')">
        <strong>💻 Arthur Pendelton</strong>
        <span>Role: IT Admin</span>
      </button>
    </div>
  </div>
</div>

<script>
function fillLogin(u, p) {
  document.getElementById('username').value = u;
  document.getElementById('password').value = p;
  document.forms[0].submit();
}
</script>

</body>
</html>

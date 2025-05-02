<?php
session_start();
$errors = [];

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'tesla';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Auto login using remember_token
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        $cookieToken = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $cookieToken);

        $stmt = $conn->prepare("SELECT id, name, email FROM login WHERE remember_token = ? AND token_expiry > NOW()");
        $stmt->execute([$hashedToken]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit;
        }
    }

    // Handle form login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id, name, email, pass FROM login WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['pass'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $hashedToken = hash('sha256', $token);
                    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

                    setcookie('remember_token', $token, time() + (86400 * 30), "/");

                    $update = $conn->prepare("UPDATE login SET remember_token = ?, token_expiry = ? WHERE id = ?");
                    $update->execute([$hashedToken, $expiry, $user['id']]);
                }

                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid email or password";
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Database error. Please try again later.";
    error_log("Login error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="login.css"/>
  <title>TESLA - Login</title>
  <link rel="icon" href="imgs/R.png" type="image/x-icon"/>
  <style>
    .password-group { position: relative; }
    .toggle-password {
      position: absolute;
      right: 12px;
      transform: translateX(-70%);
      background: transparent;
      width: 10px;
      border: none;
      color: #00f;
      cursor: pointer;
      font-size: 1em;
    }
  </style>
</head>
<body class="bg">
  <div class="container">
    <div class="login-box">
      <div class="login-tab">Login</div>

      <?php if (!empty($errors)): ?>
        <div class="error-container">
          <?php foreach ($errors as $error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="input-group">
          <input type="text" name="email" placeholder="Email"
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
          <span class="icon"></span>
        </div>

        <div class="input-group password-group">
          <input type="password" name="password" placeholder="Password" required>
          <button type="button" class="toggle-password">🙈</button>
        </div>

        <div class="options">
          <label><input type="checkbox" name="remember"> Remember me</label>
          <a href="Forget_password.html">Forgot password?</a>
        </div>

        <button type="submit">Login</button>
        <p class="register">Don't have an account? <a href="Register.html">Register</a></p>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll(".toggle-password").forEach((btn) => {
      btn.addEventListener("click", function () {
        const input = this.previousElementSibling;
        if (input.type === "password") {
          input.type = "text";
          this.textContent = "🙉";
        } else {
          input.type = "password";
          this.textContent = "🙈";
        }
      });
    });
  </script>
</body>
</html>

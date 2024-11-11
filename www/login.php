<?php
session_start();
include 'utils/db.php';

// If user is already logged in, redirect to the home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect_to = $_POST['redirect'] ?? 'index.php';

    if (empty($login) || empty($password)) {
        $error = "Please enter both email/username and password.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, email, username, password_hash, first_name, last_name, role FROM user WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct, start a new session
                session_regenerate_id(true); // Regenerate session ID for security
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['last_activity'] = time(); // Add last activity time
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the original page or home page
                header("Location: " . $redirect_to);
                exit();
            } else {
                $error = "Invalid email/username or password.";
            }
        } else {
            $error = "Invalid email/username or password.";
        }
    }
}

$message = '';
if (isset($_GET['must']) && $_GET['must'] == 1) {
    $message = "You must be logged in to access this page.";
}
if (isset($_GET['logout'])) {
    $message = "You have been successfully logged out.";
}
if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $message = "Your session has expired. Please log in again.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Libriverse</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <h1>Login with your Account</h1>
        <?php if (!empty($message)): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="details-container">
                <div class="form-group">
                    <label for="login">Email or Username:</label>
                    <input type="text" id="login" name="login" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="togglePassword" class="toggle-password">Show</button>
                    </div>
                </div>
                <div class="btn-container">

                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_to); ?>">
                    <button type="submit" class="btn-login">Login</button>
                </div>
            </div>
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>

<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            this.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            this.textContent = 'Show';
        }
    });
</script>

</html>
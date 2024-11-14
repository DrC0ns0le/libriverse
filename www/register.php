<?php
session_start();
include 'utils/db.php';

// If user is already logged in, redirect to the home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO user (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username, $email, $password_hash, $first_name, $last_name);

                if ($stmt->execute()) {
                    $user_id = $stmt->insert_id;

                    // Start session for the new user
                    session_regenerate_id(true); // Regenerate session ID for security
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['username'] = $username;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['last_activity'] = time(); // Add last activity time

                    // Redirect to home page or dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again later.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Libriverse</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="container">
        <h1>Create an Account</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <fieldset>
                <legend>Account Information</legend>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required minlength="8">
                        <button type="button" class="toggle-password">Show</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-input">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        <button type="button" class="toggle-password">Show</button>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Personal Information</legend>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </fieldset>

            <button type="submit" class="btn-register">Register</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>

<script>
    // Add password strength indicator
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.createElement('div');
        passwordStrength.id = 'password-strength';
        passwordInput.parentNode.insertBefore(passwordStrength, passwordInput.nextSibling);

        form.addEventListener('input', function(e) {
            if (e.target.id === 'username') {
                validateUsername(e.target);
            } else if (e.target.id === 'email') {
                validateEmail(e.target);
            } else if (e.target.id === 'password') {
                validatePassword(e.target);
            } else if (e.target.id === 'confirm_password') {
                validateConfirmPassword(e.target);
            }
        });

        function validateUsername(input) {
            if (input.value.length < 3) {
                setError(input, 'Username must be at least 3 characters long');
            } else {
                setSuccess(input);
            }
        }

        function validateEmail(input) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(input.value)) {
                setError(input, 'Please enter a valid email address');
            } else {
                setSuccess(input);
            }
        }

        function validatePassword(input) {
            if (input.value.length < 8) {
                setError(input, 'Password must be at least 8 characters long');
            } else {
                setSuccess(input);
            }
        }

        function validateConfirmPassword(input) {
            const passwordInput = document.getElementById('password');
            if (input.value !== passwordInput.value) {
                setError(input, 'Passwords do not match');
            } else {
                setSuccess(input);
            }
        }

        function setError(input, message) {
            const formGroup = input.closest('.form-group');
            let error = formGroup.querySelector('.error-message');
            if (!error) {
                error = document.createElement('div');
                error.className = 'error-message';
                formGroup.appendChild(error);
            }
            error.textContent = message;
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
        }

        function setSuccess(input) {
            const formGroup = input.closest('.form-group');
            formGroup.classList.remove('error');
            formGroup.classList.add('success');
            const error = formGroup.querySelector('.error-message');
            if (error) {
                formGroup.removeChild(error);
            }
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Hide';
            } else {
                input.type = 'password';
                this.textContent = 'Show';
            }
        });
    });
</script>

</html>
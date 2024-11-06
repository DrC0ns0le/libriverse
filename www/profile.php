<?php
require_once 'utils/auth.php';
require_once 'utils/db.php';

// Initialize the authenticated session
init_authenticated_session();

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, first_name, last_name, payment_method FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success_message = '';
$error_message = '';

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = trim($_POST['email']);
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_payment_method = $_POST['payment_method'];

    // Validate email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Update user information
        $update_stmt = $conn->prepare("UPDATE user SET email = ?, first_name = ?, last_name = ?, payment_method = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $new_email, $new_first_name, $new_last_name, $new_payment_method, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully";
            $user['email'] = $new_email;
            $user['first_name'] = $new_first_name;
            $user['last_name'] = $new_last_name;
            $user['payment_method'] = $new_payment_method;
            $_SESSION['user_email'] = $new_email;
        } else {
            $error_message = "Failed to update profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Libriverse</title>
    <link rel="stylesheet" href="profile.css">
</head>

<body>
    <div class="container">
        <h1>User Profile</h1>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>

        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="username-display">
                <h2><?php echo htmlspecialchars($user['username'] ?? ''); ?></h2>
            </div>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    <span id="email-error" class="error-message"></span>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select id="payment_method" name="payment_method">
                        <option value="">Select Payment Method</option>
                        <option value="Credit Card" <?php echo $user['payment_method'] == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="PayPal" <?php echo $user['payment_method'] == 'PayPal' ? 'selected' : ''; ?>>PayPal</option>
                        <option value="Bank Transfer" <?php echo $user['payment_method'] == 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>
                <button type="submit" class="btn-update">Update Profile</button>
            </form>
        </div>
    </div>
</body>

<script>
    document.getElementById('email').addEventListener('input', function(e) {
        var email = e.target.value;
        var emailError = document.getElementById('email-error');
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
            emailError.textContent = 'Please enter a valid email address.';
        } else {
            emailError.textContent = '';
        }
    });
</script>


</html>
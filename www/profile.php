<?php
require_once 'utils/auth.php';
require_once 'utils/db.php';

// Initialize the authenticated session
init_authenticated_session();

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, payment_method FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success_message = '';
$error_message = '';

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = trim($_POST['email']);
    $new_payment_method = $_POST['payment_method'];

    // Validate email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Update user information
        $update_stmt = $conn->prepare("UPDATE user SET email = ?, payment_method = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_email, $new_payment_method, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully";
            $user['email'] = $new_email;
            $user['payment_method'] = $new_payment_method;
            $_SESSION['user_email'] = $new_email; // Update session email
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
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>

        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="profile.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
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
</body>

</html>
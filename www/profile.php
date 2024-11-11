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

// Fetch user's payment methods and default payment id
$payment_stmt = $conn->prepare("SELECT p.id, p.type, p.last_4_digits, CASE WHEN u.payment_method = p.id THEN 1 ELSE 0 END AS is_default 
                                FROM payment p 
                                LEFT JOIN user u ON u.id = p.user_id 
                                WHERE p.user_id = ?");
$payment_stmt->bind_param("i", $user_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment_methods = $payment_result->fetch_all(MYSQLI_ASSOC);

$success_message = '';
$error_message = '';

// Handle form submission for updating profile and managing payment methods
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_email'])) {
        $email = $_POST['email'];
        // Update email
        $update_email_stmt = $conn->prepare("UPDATE user SET email = ? WHERE id = ?");
        $update_email_stmt->bind_param("si", $email, $user_id);

        if ($update_email_stmt->execute()) {
            $success_message = "Email updated successfully";
        } else {
            $error_message = "Failed to update email";
        }
    } elseif (isset($_POST['update_password'])) {
        // Handle Password Update
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch the current password hash for verification
        $password_stmt = $conn->prepare("SELECT password_hash FROM user WHERE id = ?");
        $password_stmt->bind_param("i", $user_id);
        $password_stmt->execute();
        $password_result = $password_stmt->get_result();
        $password_data = $password_result->fetch_assoc();

        // Validate password change
        if (empty($current_password)) {
            $error_message = "Current password is required";
        } elseif (!password_verify($current_password, $password_data['password_hash'])) {
            $error_message = "Current password is incorrect";
        } elseif (strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match";
        } else {
            // Update password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_stmt = $conn->prepare("UPDATE user SET password_hash = ? WHERE id = ?");
            $update_password_stmt->bind_param("si", $password_hash, $user_id);

            if ($update_password_stmt->execute()) {
                $success_message = "Password updated successfully";
            } else {
                $error_message = "Failed to update password";
            }
        }
    } elseif (isset($_POST['update_personal_details'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];

        $update_details_stmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ? WHERE id = ?");
        $update_details_stmt->bind_param("ssi", $first_name, $last_name, $user_id);

        if ($update_details_stmt->execute()) {
            $success_message = "Personal details updated successfully";
        } else {
            $error_message = "Failed to update personal details";
        }
    } elseif (isset($_POST['add_payment'])) {
        $type = $_POST['payment_type'];
        $last_4_digits = $_POST['last_4_digits'];

        $add_payment_stmt = $conn->prepare("INSERT INTO payment (user_id, type, last_4_digits) VALUES (?, ?, ?)");
        $add_payment_stmt->bind_param("iss", $user_id, $type, $last_4_digits);

        if ($add_payment_stmt->execute()) {
            $success_message = "Payment method added successfully";
        } else {
            $error_message = "Failed to add payment method";
        }
    } elseif (isset($_POST['delete_payment'])) {
        $payment_id = $_POST['payment_id'];

        $delete_payment_stmt = $conn->prepare("DELETE FROM payment WHERE id = ? AND user_id = ?");
        $delete_payment_stmt->bind_param("ii", $payment_id, $user_id);

        if ($delete_payment_stmt->execute()) {
            // If the deleted payment was the default, set payment_method to NULL
            $update_default_stmt = $conn->prepare("UPDATE user SET payment_method = CASE WHEN payment_method = ? THEN NULL ELSE payment_method END WHERE id = ?");
            $update_default_stmt->bind_param("ii", $payment_id, $user_id);
            $update_default_stmt->execute();

            $success_message = "Payment method deleted successfully";
        } else {
            $error_message = "Failed to delete payment method";
        }
    } elseif (isset($_POST['set_default_payment'])) {
        $default_payment_id = $_POST['default_payment_id'];

        $set_default_stmt = $conn->prepare("UPDATE user SET payment_method = ? WHERE id = ?");
        $set_default_stmt->bind_param("ii", $default_payment_id, $user_id);

        if ($set_default_stmt->execute()) {
            $success_message = "Default payment method updated successfully";
        } else {
            $error_message = "Failed to update default payment method";
        }
    }

    // Refresh user data and payment methods after any changes
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment_methods = $payment_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Libriverse</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body>
    <!-- Sidebar Navbar (Left Sidebar) -->
    <div class="navbar">

        <!-- Logo/Title Section -->
        <div class="navbar-logo-section">
            <a href="index.php" class="navbar-logo">
                <img src="assets/logo/libriverse_logo.png" alt="Libriverse Logo" class="navbar-logo-image">
                <span class="navbar-logo-text">Libriverse</span>
            </a>
        </div>

        <!-- Pages Section -->
        <ul class="navbar-pages">
            <li><a href="index.php" class="navbar-item"><img src="assets/icons/home.svg" alt="Home" class="navbar-icon white"><span class="navbar-label">Home</span></a></li>
            <li><a href="discover.php" class="navbar-item"><img src="assets/icons/explore.svg" alt="Discover" class="navbar-icon white"><span class="navbar-label">Discover</span></a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="bookshelf.php" class="navbar-item"><img src="assets/icons/collections.svg" alt="Bookshelf" class="navbar-icon white"><span class="navbar-label">Bookshelf</span></a></li>
            <?php endif; ?>
            <!-- Admin Section -->
            <?php if (is_admin()): ?>
                <!-- Separator -->
                <li class="navbar-separator"></li>
                <!-- <li><a href="admin.php" class="navbar-item"><img src="assets/icons/admin.svg" alt="Admin" class="navbar-icon white"><span class="navbar-label">Admin</span></a></li> -->
                <li><a href="admin/add_book.php" class="navbar-item"><img src="assets/icons/add_book.svg" alt="Add Book" class="navbar-icon white"><span class="navbar-label">Add Book</span></a></li>
                <li><a href="admin/requests.php" class="navbar-item"><img src="assets/icons/pad.svg" alt="Requests" class="navbar-icon white"><span class="navbar-label">Manage Requests</span></a></li>
                <!-- <li><a href="admin/comments.php" class="navbar-item"><img src="assets/icons/pen.svg" alt="Comments" class="navbar-icon white"><span class="navbar-label">Reports</span></a></li> -->
        </ul>
    <?php endif; ?>
    </ul>



    <!-- User Section -->
    <ul class="navbar-user-section">
        <?php if (is_logged_in()): ?>
            <!-- Profile photo and username -->
            <li><a href="profile.php" class="navbar-item active username">
                    <img src="assets/profile-photo/golden_retriever.jpeg" alt="User Photo" class="navbar-user-photo">
                    <span class="navbar-username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </a><a href="logout.php" class="navbar-item">
                    <img src="assets/icons/logout.svg" alt="Logout" class="navbar-icon white logout">
                </a>
            </li>
        <?php else: ?>
            <!-- Login link -->
            <li><a href="login.php" class="navbar-item">
                    <img src="assets/icons/login.svg" alt="Login" class="navbar-icon white">
                    <span class="navbar-label">Login</span>
                </a></li>
        <?php endif; ?>
    </ul>
    </div>

    <div class="main-container">
        <div class="profile-header">
            <h1>User Profile</h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Email Update Form -->
            <h3>Account Management</h3>
            <form action="profile.php" method="post" class="account-management-form">
                <section class="profile-section email-section">


                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                            required
                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                            class="form-control">
                        <span id="email-error" class="error-message"></span>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_email" class="btn-update">Update Email</button>
                    </div>
                </section>
            </form>

            <!-- Password Update Form -->
            <h3>Password Management</h3>
            <form action="profile.php" method="post" class="account-management-form">
                <section class="profile-section password-section">


                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <div class="password-input">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control"
                                required>
                            <button
                                type="button"
                                class="toggle-password"
                                aria-label="Toggle current password visibility">
                                <span class="toggle-text">Show</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <div class="password-input">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                minlength="8"
                                class="form-control"
                                required>
                            <button
                                type="button"
                                class="toggle-password"
                                aria-label="Toggle new password visibility">
                                <span class="toggle-text">Show</span>
                            </button>
                        </div>
                        <small class="help-text">Minimum 8 characters required</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <div class="password-input">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                minlength="8"
                                class="form-control"
                                required>
                            <button
                                type="button"
                                class="toggle-password"
                                aria-label="Toggle confirm password visibility">
                                <span class="toggle-text">Show</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update_password" class="btn-update">Update Password</button>
                    </div>
                </section>
            </form>

            <h3>Personal Details</h3>
            <form action="profile.php" method="post" class="personal-details-form">
                <section class="profile-section">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_personal_details" class="btn-update">Update Personal Details</button>
                    </div>
                </section>
            </form>

            <h3>Payment Methods</h3>
            <section class="profile-section">
                <div class="payment-methods">
                    <?php if (empty($payment_methods)): ?>
                        <p>No payment methods added yet.</p>
                    <?php else: ?>
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Last 4 Digits</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_methods as $method): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($method['type']); ?></td>
                                        <td>**** <?php echo htmlspecialchars($method['last_4_digits']); ?></td>
                                        <td class="payment-actions">
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="payment_id" value="<?php echo $method['id']; ?>">
                                                <button type="submit" name="delete_payment" class="btn btn-danger">Delete</button>
                                            </form>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="default_payment_id" value="<?php echo $method['id']; ?>">
                                                <button type="submit" name="set_default_payment" class="btn btn-primary" <?php echo $method['is_default'] ? 'disabled' : ''; ?>>
                                                    <?php echo $method['is_default'] ? 'Default' : 'Set as Default'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <h4>Add New Payment Method</h4>
                <form method="post" class="add-payment-form">
                    <div class="form-group">
                        <label for="payment_type">Payment Type:</label>
                        <select id="payment_type" name="payment_type" required>
                            <option value="">Select Payment Type</option>
                            <option value="Debit">Debit Card</option>
                            <option value="Credit">Credit Card</option>
                            <option value="Paypal">PayPal</option>
                            <option value="Grabpay">GrabPay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="last_4_digits">Last 4 Digits:</label>
                        <input type="text" id="last_4_digits" name="last_4_digits" placeholder="Last 4 digits" required pattern="\d{4}" maxlength="4">
                    </div>
                    <button type="submit" name="add_payment" class="btn-update">Add Payment Method</button>
                </form>
            </section>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.password-section').closest('form');
        const currentPasswordInput = document.getElementById('current_password');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        const MIN_PASSWORD_LENGTH = 8;

        form.addEventListener('submit', function(e) {
            // Only validate password fields if new password is being set
            if (newPasswordInput.value || confirmPasswordInput.value) {
                if (!currentPasswordInput.value) {
                    e.preventDefault();
                    setError(currentPasswordInput, 'Current password is required to change password');
                }

                if (newPasswordInput.value.length < MIN_PASSWORD_LENGTH) {
                    e.preventDefault();
                    setError(newPasswordInput, `Password must be at least ${MIN_PASSWORD_LENGTH} characters long`);
                }

                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    e.preventDefault();
                    setError(confirmPasswordInput, 'Passwords do not match');
                }
            }
        });

        form.addEventListener('input', function(e) {
            if (e.target.id === 'new_password') {
                validatePassword(e.target);
                validateConfirmPassword(confirmPasswordInput);
            } else if (e.target.id === 'confirm_password') {
                validateConfirmPassword(e.target);
            }
        });

        function validatePassword(input) {
            if (input.value.length === 0) {
                resetValidation(input);
            } else if (input.value.length < MIN_PASSWORD_LENGTH) {
                setError(input, `Password must be at least ${MIN_PASSWORD_LENGTH} characters long`);
            } else {
                setSuccess(input);
            }
        }

        function validateConfirmPassword(input) {
            const passwordInput = document.getElementById('new_password');
            if (input.value.length === 0 && passwordInput.value.length === 0) {
                resetValidation(input);
            } else if (input.value !== passwordInput.value) {
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

        function resetValidation(input) {
            const formGroup = input.closest('.form-group');
            formGroup.classList.remove('error', 'success');
            const error = formGroup.querySelector('.error-message');
            if (error) {
                formGroup.removeChild(error);
            }
        }

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const toggleText = this.querySelector('.toggle-text');
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleText.textContent = 'Hide';
                } else {
                    input.type = 'password';
                    toggleText.textContent = 'Show';
                }
            });
        });
    });
</script>


</html>
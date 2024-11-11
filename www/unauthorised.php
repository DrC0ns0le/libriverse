<?php
session_start();
$redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - Libriverse</title>
    <link rel="stylesheet" href="css/base.css">
    <style>
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <h1>Unauthorized Access</h1>
        <p>Sorry, you don't have permission to access this page.</p>
        <a href="<?php echo htmlspecialchars($redirect_to); ?>" class="back-link">Go Back</a>
    </div>
</body>

</html>
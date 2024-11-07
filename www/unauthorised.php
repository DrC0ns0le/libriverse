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
    <link rel="stylesheet" href="base.css">
    <style>
        .unauthorized-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }

        h1 {
            color: #721c24;
        }

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
    <div class="unauthorized-container">
        <h1>Unauthorized Access</h1>
        <p>Sorry, you don't have permission to access this page.</p>
        <a href="<?php echo htmlspecialchars($redirect_to); ?>" class="back-link">Go Back</a>
    </div>
</body>

</html>
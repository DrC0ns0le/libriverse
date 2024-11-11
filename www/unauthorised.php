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
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;

            text-align: center;
        }

        .back-link {
            text-decoration: none;
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: var(--search-border-radius);
            background-color: white;
            color: var(--text-color);
            font-weight: 500;
            cursor: pointer;
            font-family: "IBM Plex Sans", sans-serif;
            font-size: 0.95rem;
            border: 1px solid #e1e1e1;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            background-color: #f8f9fa;
            border-color: #838383;
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
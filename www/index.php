<?php
include 'utils/db.php';
include 'utils/auth.php';

// Fetch all banners from the database
$sql = "SELECT * FROM banner ORDER BY id";
$result = $conn->query($sql);
$banners = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibriVerse - Home</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="index.css">

    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo/Title Section -->
            <div class="navbar-logo-section">
                <a href="index.php" class="navbar-logo">Libriverse</a>
            </div>

            <!-- Pages Section -->
            <ul class="navbar-pages">
                <li><a href="index.php" class="navbar-item" style="font-weight: bold;">Home</a></li>
                <li><a href="discover.php" class="navbar-item">Discover</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="bookshelf.php" class="navbar-item">Bookshelf</a></li>
                <?php endif; ?>
            </ul>

            <!-- User Section -->
            <div class="navbar-user-section">
                <?php if (is_logged_in()): ?>
                    <a href="profile.php" class="navbar-item">Profile</a>
                    <a href="logout.php" class="navbar-item">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="navbar-item">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</head>

<body>
    <div class="main-container">
        <h1>Hello!</h1>
        <div class="banner-section">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="banner">
                    <div class="banner-image" style="background-image: url('<?php echo htmlspecialchars($banner['image_link']); ?>');">
                        <div class="banner-nav">
                            <?php foreach ($banners as $navIndex => $navBanner): ?>
                                <button onclick="showBanner(<?php echo $navIndex; ?>)" <?php echo $navIndex === $index ? 'class="active"' : ''; ?>></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="banner-content">
                        <h2><?php echo htmlspecialchars($banner['title']); ?></h2>
                        <p><?php echo htmlspecialchars($banner['description']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Rest of your page content goes here -->

    <script>
        let currentBanner = 0;
        const banners = document.querySelectorAll('.banner');
        const navButtons = document.querySelectorAll('.banner-nav button');

        function showBanner(index) {
            banners[currentBanner].style.display = 'none';
            navButtons[currentBanner].classList.remove('active');

            currentBanner = index;
            banners[currentBanner].style.display = 'flex';
            navButtons[currentBanner].classList.add('active');
        }

        function nextBanner() {
            showBanner((currentBanner + 1) % banners.length);
        }

        showBanner(0);
        setInterval(nextBanner, 5000);
    </script>
</body>

</html>
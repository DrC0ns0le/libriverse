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
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/index.css">

    <script>
        // Immediately set the theme before the page renders
        (function() {
            let theme = localStorage.getItem('theme');

            // If no theme is saved, check system preference
            if (!theme) {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            // Apply theme immediately
            document.documentElement.setAttribute('data-theme', theme);

            // Optional: Add a class to body to indicate JS is loaded
            document.documentElement.classList.add('theme-loaded');
        })();
    </script>
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
            <li><a href="index.php" class="navbar-item active"><img src="assets/icons/home.svg" alt="Home" class="navbar-icon white"><span class="navbar-label">Home</span></a></li>
            <li><a href="discover.php" class="navbar-item"><img src="assets/icons/explore.svg" alt="Discover" class="navbar-icon white"><span class="navbar-label">Discover</span></a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="bookshelf.php" class="navbar-item"><img src="assets/icons/collections.svg" alt="Bookshelf" class="navbar-icon white"><span class="navbar-label">Bookshelf</span></a></li>
            <?php endif; ?>
        </ul>

        <!-- User Section -->
        <ul class="navbar-user-section">
            <?php if (is_logged_in()): ?>
                <!-- Profile photo and username -->
                <li><a href="profile.php" class="navbar-item username">
                        <img src="assets/profile-photo/golden_retriever.jpeg" alt="User Photo" class="navbar-user-photo">
                        <span class="navbar-username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    </a>
                </li>
                <!-- Icon Toggle -->
                <li class="icon-toggle">
                    <button id="themeToggle" class="navbar-item">
                        <img src="assets/icons/light-mode.svg" alt="Light Mode" class="navbar-icon theme-light">
                        <img src="assets/icons/dark-mode.svg" alt="Dark Mode" class="navbar-icon theme-dark">
                    </button>
                    <a href="logout.php" class="navbar-item logout">
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
        <h1>Hello! What are we reading today?</h1>
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

        <div class="search-container">
            <h2>Looking for something?</h2>
            <form action="discover.php" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search for books, authors, or genres...">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>
    </div>

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

        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('themeToggle');

            // Check for saved theme preference or default to system preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }

            // Toggle theme
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        });
    </script>
</body>

</html>
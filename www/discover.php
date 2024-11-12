<?php
include 'utils/db.php';
include 'utils/auth.php';

// Get filter parameters from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
$selectedRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$selectedAuthor = isset($_GET['author']) ? $_GET['author'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Handle items per page selection with cookie persistence
$validItemsPerPage = [12, 24, 48, 96];
$itemsPerPage = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : (isset($_COOKIE['items_per_page']) ? $_COOKIE['items_per_page'] : 12);
if (!in_array($itemsPerPage, $validItemsPerPage)) {
    $itemsPerPage = 12; // Default if invalid value provided
}

// Save items per page preference to cookie if it changed
if (isset($_GET['items_per_page']) && in_array($itemsPerPage, $validItemsPerPage)) {
    setcookie('items_per_page', $itemsPerPage, time() + (86400 * 30), '/');
}

// Utility function to calculate combined rating
function getCombinedRatingSQL()
{
    return "CASE 
                WHEN c.ratings IS NOT NULL AND r.avg_rating IS NOT NULL 
                    THEN (c.ratings + r.avg_rating) / 2
                WHEN c.ratings IS NOT NULL THEN c.ratings
                WHEN r.avg_rating IS NOT NULL THEN r.avg_rating
                ELSE NULL
            END as combined_rating";
}

// Function to get total number of results
function getTotalResults($conn, $query, $genre, $rating, $author)
{
    $sql = "SELECT COUNT(*) as total 
        FROM (
            SELECT c.id, c.title, c.author, c.genre,
                   " . getCombinedRatingSQL() . "
            FROM catalog c
            LEFT JOIN (
                SELECT catalog_id, AVG(rating) as avg_rating
                FROM review
                GROUP BY catalog_id
            ) r ON c.id = r.catalog_id
        ) as combined_books
        WHERE (title LIKE ? OR author LIKE ? OR genre LIKE ?)";

    $params = ["%$query%", "%$query%", "%$query%"];
    $types = "sss";

    if (!empty($genre)) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
        $types .= "s";
    }

    if ($rating > 0) {
        $sql .= " AND combined_rating >= ?";
        $params[] = $rating;
        $types .= "d";
    }

    if (!empty($author)) {
        $sql .= " AND author = ?";
        $params[] = $author;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Function to get books based on search query and filters with pagination
function searchBooks($conn, $query, $genre, $rating, $author, $page, $itemsPerPage)
{
    $offset = ($page - 1) * $itemsPerPage;

    $sql = "SELECT c.id, c.title, c.author, c.genre, c.image_link, c.description, 
            " . getCombinedRatingSQL() . "
            FROM catalog c
            LEFT JOIN (
                SELECT catalog_id, AVG(rating) as avg_rating
                FROM review
                GROUP BY catalog_id
            ) r ON c.id = r.catalog_id
            WHERE (c.title LIKE ? OR c.author LIKE ? OR c.genre LIKE ?)";

    $params = ["%$query%", "%$query%", "%$query%"];
    $types = "sss";

    if (!empty($genre)) {
        $sql .= " AND c.genre = ?";
        $params[] = $genre;
        $types .= "s";
    }

    if ($rating > 0) {
        $sql .= " AND " . getCombinedRatingSQL() . " >= ?";
        $params[] = $rating;
        $types .= "d";
    }

    if (!empty($author)) {
        $sql .= " AND c.author = ?";
        $params[] = $author;
        $types .= "s";
    }

    $sql .= " ORDER BY c.title ASC LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get distinct genres, authors for filter options
$genres = $conn->query("SELECT DISTINCT genre FROM catalog WHERE genre IS NOT NULL ORDER BY genre")->fetch_all(MYSQLI_ASSOC);
$authors = $conn->query("SELECT DISTINCT author FROM catalog WHERE author IS NOT NULL ORDER BY author")->fetch_all(MYSQLI_ASSOC);

// If there's a search query or filters are set, use them to find books
if (!empty($search) || !empty($selectedGenre) || $selectedRating > 0 || !empty($selectedAuthor)) {
    $totalResults = getTotalResults($conn, $search, $selectedGenre, $selectedRating, $selectedAuthor);
    $totalPages = ceil($totalResults / $itemsPerPage);
    $searchResults = searchBooks($conn, $search, $selectedGenre, $selectedRating, $selectedAuthor, $page, $itemsPerPage);
}


function getRandomBooks($conn, $limit = 6, $orderBy = '')
{
    $sql = "SELECT c.id, c.title, c.author, c.genre, c.image_link, 
            " . getCombinedRatingSQL() . "
            FROM catalog c
            LEFT JOIN (
                SELECT catalog_id, AVG(rating) as avg_rating
                FROM review
                GROUP BY catalog_id
            ) r ON c.id = r.catalog_id";

    if (!empty($orderBy)) {
        $sql .= " " . $orderBy;
    } else {
        $sql .= " ORDER BY RAND()";
    }

    $sql .= " LIMIT " . (int)$limit;

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$newReleases = getRandomBooks($conn, 6, "ORDER BY id DESC");
$popularBooks = getRandomBooks($conn, 10, "ORDER BY ratings DESC");

// Get 3 random genres
$genreQuery = "SELECT DISTINCT genre 
               FROM catalog 
               WHERE genre IS NOT NULL 
               ORDER BY RAND() 
               LIMIT 3";
$genreResult = $conn->query($genreQuery);
$randomGenres = $genreResult->fetch_all(MYSQLI_ASSOC);

// Get books for each random genre
$booksByGenre = [];
foreach ($randomGenres as $genre) {
    $genreName = $genre['genre'];
    $genreSQL = "SELECT c.id, c.title, c.author, c.genre, c.image_link, 
                 " . getCombinedRatingSQL() . "
                 FROM catalog c
                 LEFT JOIN (
                     SELECT catalog_id, AVG(rating) as avg_rating
                     FROM review
                     GROUP BY catalog_id
                 ) r ON c.id = r.catalog_id
                 WHERE c.genre = '" . $conn->real_escape_string($genreName) . "'
                 ORDER BY RAND() 
                 LIMIT 12";
    $result = $conn->query($genreSQL);
    if ($result && $result->num_rows > 0) {
        $booksByGenre[$genreName] = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Books - Libriverse</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/discover.css">

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
            <li><a href="index.php" class="navbar-item"><img src="assets/icons/home.svg" alt="Home" class="navbar-icon white"><span class="navbar-label">Home</span></a></li>
            <li><a href="discover.php" class="navbar-item active"><img src="assets/icons/explore.svg" alt="Discover" class="navbar-icon white"><span class="navbar-label">Discover</span></a></li>
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
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="logo" title="Discover Books ?>">
            <h1>Let's find something new to read.</h1>
        </a>

        <div class="search-container">
            <form action="" method="GET" id="searchForm">
                <input type="text" name="search" class="search-input" placeholder="Search for books, authors, or genres..." value="<?php echo htmlspecialchars($search); ?>"><button type="submit" class="search-button">Search</button>

                <div class="filter-row">
                    <select name="sort" class="sort-select" onchange="sortBooks(this.value)">
                        <option value="">Sort by</option>
                        <option value="title_asc">Title (A-Z)</option>
                        <option value="title_desc">Title (Z-A)</option>
                        <option value="author_asc">Author (A-Z)</option>
                        <option value="author_desc">Author (Z-A)</option>
                        <option value="rating_desc">Highest Rated</option>
                        <option value="rating_asc">Lowest Rated</option>
                    </select>

                    <select name="genre">
                        <option value="">All Genres</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre['genre']); ?>" <?php if ($selectedGenre == $genre['genre']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($genre['genre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="rating">
                        <option value="0">All Ratings</option>
                        <option value="4" <?php if ($selectedRating == 4) echo 'selected'; ?>>4+ Stars</option>
                        <option value="3" <?php if ($selectedRating == 3) echo 'selected'; ?>>3+ Stars</option>
                        <option value="2" <?php if ($selectedRating == 2) echo 'selected'; ?>>2+ Stars</option>
                        <option value="1" <?php if ($selectedRating == 1) echo 'selected'; ?>>1+ Stars</option>
                    </select>

                    <select name="author">
                        <option value="">All Authors</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo htmlspecialchars($author['author']); ?>" <?php if ($selectedAuthor == $author['author']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($author['author']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="items_per_page" class="items-per-page" onchange="this.form.submit()">
                        <?php foreach ($validItemsPerPage as $value): ?>
                            <option value="<?php echo $value; ?>" <?php if ($itemsPerPage == $value) echo 'selected'; ?>>
                                <?php echo $value; ?> items
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if (isset($searchResults)): ?>
            <section class="section results-section">
                <div class="section-header">
                    <div class="section-title">
                        <span>Results (<?php echo $totalResults; ?> books found)</span>
                    </div>
                    <div class="results-info">
                        Showing <?php echo min(($page - 1) * $itemsPerPage + 1, $totalResults); ?>-<?php echo min($page * $itemsPerPage, $totalResults); ?> of <?php echo $totalResults; ?>
                    </div>
                </div>

                <div class="scroll-container vertical-scroll">
                    <div class="book-grid vertical-grid">
                        <?php foreach ($searchResults as $book): ?>
                            <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                                <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                                <div class="book-info">
                                    <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
                                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                                    <div class="book-rating">★ <?php echo number_format($book['combined_rating'], 1); ?></div>
                                    <div class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 150)) . '...'; ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-container">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination">&laquo; Previous</a>
                            <?php endif; ?>

                            <?php
                            // Show page numbers with ellipsis
                            $range = 2;
                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)) {
                                    if ($i == $page) {
                                        echo "<span class='pagination active'>$i</span>";
                                    } else {
                                        echo "<a href='?" . http_build_query(array_merge($_GET, ['page' => $i])) . "' class='pagination'>$i</a>";
                                    }
                                } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                                    echo "<span class='page-ellipsis'>...</span>";
                                }
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!isset($searchResults)): ?>
            <section class="section">
                <div class="section-title">
                    <span>Popular Now</span>
                </div>
                <div class="scroll-container">
                    <div class="book-grid">
                        <?php foreach ($popularBooks as $book): ?>
                            <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                                <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                                <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="book-rating">★ <?php echo number_format($book['combined_rating'], 1); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!isset($searchResults)): ?>
            <?php foreach ($booksByGenre as $genre => $books): ?>
                <section class="section">
                    <div class="section-title">
                        <span>
                            <?php echo htmlspecialchars($genre); ?>
                        </span>
                        <!-- <a href="<?php echo $_SERVER['PHP_SELF'] . '?genre=' . urlencode($genre); ?>" class="see-all">See all</a> -->
                    </div>
                    <div class="scroll-container">
                        <div class="book-grid">
                            <?php foreach ($books as $book): ?>
                                <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                                    <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                    <div class="book-rating">★ <?php echo number_format($book['combined_rating'], 1); ?></div>
                                </a>
                            <?php endforeach; ?>
                            <a href="<?php echo $_SERVER['PHP_SELF'] . '?genre=' . urlencode($genre); ?>" class="book-card see-more">
                                <div class="see-more-content">
                                    <span>See More →</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

<script>
    function sortBooks(sortBy) {
        // Save sort preference to sessionStorage
        sessionStorage.setItem('sort', sortBy);

        const bookContainer = document.querySelector('.book-grid');
        const books = Array.from(bookContainer.children);

        books.sort((a, b) => {
            let aValue, bValue;
            switch (sortBy) {
                case 'title_desc':
                    aValue = a.querySelector('.book-title').textContent;
                    bValue = b.querySelector('.book-title').textContent;
                    return bValue.localeCompare(aValue);

                case 'author_desc':
                    aValue = a.querySelector('.book-author').textContent.replace('by ', '');
                    bValue = b.querySelector('.book-author').textContent.replace('by ', '');
                    return bValue.localeCompare(aValue);

                case 'rating_desc':
                    aValue = parseFloat(a.querySelector('.book-rating').textContent.replace('★ ', ''));
                    bValue = parseFloat(b.querySelector('.book-rating').textContent.replace('★ ', ''));
                    return bValue - aValue;

                case 'rating_asc':
                    aValue = parseFloat(a.querySelector('.book-rating').textContent.replace('★ ', ''));
                    bValue = parseFloat(b.querySelector('.book-rating').textContent.replace('★ ', ''));
                    return aValue - bValue;

                case 'author_asc':
                case 'title_asc':
                default:
                    aValue = (sortBy === 'author_asc' ?
                        a.querySelector('.book-author').textContent.replace('by ', '') :
                        a.querySelector('.book-title').textContent);
                    bValue = (sortBy === 'author_asc' ?
                        b.querySelector('.book-author').textContent.replace('by ', '') :
                        b.querySelector('.book-title').textContent);
                    return aValue.localeCompare(bValue);
            }
        });

        books.forEach(book => bookContainer.appendChild(book));
    }

    // Apply saved sort preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Get sort preference from sessionStorage instead of localStorage
        const sortPreference = sessionStorage.getItem('sort');
        if (sortPreference) {
            const sortSelect = document.querySelector('.sort-select');
            if (sortSelect) {
                sortSelect.value = sortPreference;
                sortBooks(sortPreference);
            }
        }

        // Listener to handle dark/light mode toggle

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

</html>
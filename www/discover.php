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

// Function to get total number of results
function getTotalResults($conn, $query, $genre, $rating, $author)
{
    $sql = "SELECT COUNT(*) as total 
            FROM catalog 
            WHERE (title LIKE ? OR author LIKE ? OR genre LIKE ?)";

    $params = ["%$query%", "%$query%", "%$query%"];
    $types = "sss";

    if (!empty($genre)) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
        $types .= "s";
    }

    if ($rating > 0) {
        $sql .= " AND ratings >= ?";
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

    $sql = "SELECT id, title, author, genre, ratings, description, image_link 
            FROM catalog
            WHERE (title LIKE ? OR author LIKE ? OR genre LIKE ?)";

    $params = ["%$query%", "%$query%", "%$query%"];
    $types = "sss";

    if (!empty($genre)) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
        $types .= "s";
    }

    if ($rating > 0) {
        $sql .= " AND ratings >= ?";
        $params[] = $rating;
        $types .= "d";
    }

    if (!empty($author)) {
        $sql .= " AND author = ?";
        $params[] = $author;
        $types .= "s";
    }

    $sql .= " ORDER BY title ASC LIMIT ? OFFSET ?";
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
    $sql = "SELECT id, title, author, genre, ratings, image_link 
            FROM catalog";

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
    $genreSQL = "SELECT id, title, author, genre, ratings, image_link 
                 FROM catalog 
                 WHERE genre = '" . $conn->real_escape_string($genreName) . "'
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
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="discover.css">

    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo/Title Section -->
            <div class="navbar-logo-section">
                <a href="index.php" class="navbar-logo">Libriverse</a>
            </div>

            <!-- Pages Section -->
            <ul class="navbar-pages">
                <li><a href="index.php" class="navbar-item">Home</a></li>
                <li><a href="discover.php" class="navbar-item" style="font-weight: bold;">Discover</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="bookshelf.php" class="navbar-item">Bookshelf</a></li>
                <?php endif; ?>
            </ul>

            <!-- User Section -->
            <div class="navbar-user-section">
                <?php if (is_logged_in()): ?>
                    <span class="navbar-username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'null'); ?></span>
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
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="logo" title="Discover Books ?>">
            <h1>Discover</h1>
        </a>

        <div class="search-container">
            <form action="" method="GET" id="searchForm">
                <input type="text" name="search" class="search-input" placeholder="Search for books, authors, or genres..." value="<?php echo htmlspecialchars($search); ?>">

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
                                <?php echo $value; ?> per page
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="search-button">Search</button>
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
                                    <div class="book-rating">★ <?php echo number_format($book['ratings'], 1); ?></div>
                                    <div class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 150)) . '...'; ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">&laquo; Previous</a>
                            <?php endif; ?>

                            <?php
                            // Show page numbers with ellipsis
                            $range = 2;
                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)) {
                                    if ($i == $page) {
                                        echo "<span class='page-link active'>$i</span>";
                                    } else {
                                        echo "<a href='?" . http_build_query(array_merge($_GET, ['page' => $i])) . "' class='page-link'>$i</a>";
                                    }
                                } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                                    echo "<span class='page-ellipsis'>...</span>";
                                }
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">Next &raquo;</a>
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
                                <div class="book-rating">★ <?php echo number_format($book['ratings'], 1); ?></div>
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
                                    <div class="book-rating">★ <?php echo number_format($book['ratings'], 1); ?></div>
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
    });
</script>

</html>
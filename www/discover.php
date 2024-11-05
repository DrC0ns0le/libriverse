<?php
include 'utils/db.php';

// Get filter parameters from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
$selectedRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$selectedAuthor = isset($_GET['author']) ? $_GET['author'] : '';

// Function to get books based on search query and filters
function searchBooks($conn, $query, $genre, $rating, $author, $limit = 12)
{
    $sql = "SELECT id, title, author, genre, ratings, image_link 
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

    $sql .= " ORDER BY RAND() LIMIT ?";
    $params[] = $limit;
    $types .= "i";

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
    $searchResults = searchBooks($conn, $search, $selectedGenre, $selectedRating, $selectedAuthor);
}

// Function to get random books with optional limit and conditions
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

// Get new releases (increased limit to 6 for scrolling)
$newReleases = getRandomBooks($conn, 6, "ORDER BY id DESC");

// Get popular books (increased limit to 6 for scrolling)
$popularBooks = getRandomBooks($conn, 6, "ORDER BY ratings DESC");

// Get top 3 genres by book count
$genreQuery = "SELECT genre, COUNT(*) as count 
               FROM catalog 
               WHERE genre IS NOT NULL 
               GROUP BY genre 
               ORDER BY count DESC 
               LIMIT 3";
$genreResult = $conn->query($genreQuery);
$topGenres = $genreResult->fetch_all(MYSQLI_ASSOC);

// Get books for each top genre
$booksByGenre = [];
foreach ($topGenres as $genre) {
    $genreName = $genre['genre'];
    $genreSQL = "SELECT id, title, author, genre, ratings, image_link 
                 FROM catalog 
                 WHERE genre = '" . $conn->real_escape_string($genreName) . "'
                 ORDER BY RAND() 
                 LIMIT 6";
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
    <link rel="stylesheet" href="discover.css">
</head>

<body>
    <h1>Discover</h1>

    <div class="search-container">
        <form action="" method="GET">
            <input type="text" name="search" class="search-input" placeholder="Search for books, authors, or genres..." value="<?php echo htmlspecialchars($search); ?>">

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

            <button type="submit" class="search-button">Search</button>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="clear-button">Clear Search</a>
        </form>
    </div>

    <?php if (isset($searchResults)): ?>
        <section class="section">
            <div class="section-title">
                <span>Search Results</span>
            </div>
            <div class="scroll-container">
                <div class="book-grid">
                    <?php foreach ($searchResults as $book): ?>
                        <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                            <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
                            <div class="book-rating">★ <?php echo number_format($book['ratings'], 1); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!isset($searchResults)): ?>
        <section class="section">
            <div class="section-title">
                <span>Popular Now</span>
                <a href="#" class="see-all">See all</a>
            </div>
            <div class="scroll-container">
                <div class="book-grid">
                    <?php foreach ($popularBooks as $book): ?>
                        <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                            <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
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
                    <span><?php echo htmlspecialchars($genre); ?></span>
                    <a href="#" class="see-all">See all</a>
                </div>
                <div class="scroll-container">
                    <div class="book-grid">
                        <?php foreach ($books as $book): ?>
                            <a href="/item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-card">
                                <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
                                <div class="book-rating">★ <?php echo number_format($book['ratings'], 1); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>
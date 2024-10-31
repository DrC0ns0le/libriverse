<?php
// Start the session and include database connection
session_start();
include 'db_connection.php'; // Adjust the path as needed

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selectedAuthor = isset($_GET['author']) ? $_GET['author'] : '';
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
$selectedLanguage = isset($_GET['language']) ? $_GET['language'] : '';
$selectedType = isset($_GET['type']) ? $_GET['type'] : '';

// Build the SQL query
$query = "SELECT * FROM catalog WHERE 1=1"; // 1=1 for easier appending of conditions
$params = [];

if ($search) {
    $query .= " AND title LIKE ?";
    $params[] = "%" . $conn->real_escape_string($search) . "%";
}
if ($selectedAuthor) {
    $query .= " AND author = ?";
    $params[] = $selectedAuthor;
}
if ($selectedGenre) {
    $query .= " AND genre = ?";
    $params[] = $selectedGenre;
}
if ($selectedLanguage) {
    $query .= " AND language = ?";
    $params[] = $selectedLanguage;
}
if ($selectedType) {
    $query .= " AND type = ?";
    $params[] = $selectedType;
}

// Prepare the statement
$stmt = $conn->prepare($query);

// Bind parameters dynamically
if ($params) {
    $types = str_repeat('s', count($params)); // Assuming all parameters are strings
    $stmt->bind_param($types, ...$params);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Fetch all books for display
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Fetch distinct values for filters
$authors = $conn->query("SELECT DISTINCT author FROM catalog")->fetch_all(MYSQLI_ASSOC);
$genres = $conn->query("SELECT DISTINCT genre FROM catalog")->fetch_all(MYSQLI_ASSOC);
$languages = $conn->query("SELECT DISTINCT language FROM catalog")->fetch_all(MYSQLI_ASSOC);
$types = ['physical', 'electronic'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LibriVerse - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">

        <!-- Search Bar -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for books..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Filters -->
        <div class="filters">
            <form method="GET">
                <!-- Author Filter -->
                <label for="author">Author:</label>
                <select name="author">
                    <option value="">All Authors</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo htmlspecialchars($author['author']); ?>" <?php if ($selectedAuthor == $author['author']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($author['author']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Genre Filter -->
                <label for="genre">Genre:</label>
                <select name="genre">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo htmlspecialchars($genre['genre']); ?>" <?php if ($selectedGenre == $genre['genre']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($genre['genre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Language Filter -->
                <label for="language">Language:</label>
                <select name="language">
                    <option value="">All Languages</option>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo htmlspecialchars($language['language']); ?>" <?php if ($selectedLanguage == $language['language']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($language['language']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Type Filter -->
                <label for="type">Type:</label>
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo strtolower($type); ?>" <?php if ($selectedType == strtolower($type)) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Apply Filters</button>
            </form>
        </div>

        <!-- Book Gallery -->
        <div class="book-gallery">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-item">
                        <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover" class="book-cover">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                        <p><strong>Price:</strong> $<?php echo htmlspecialchars($book['price']); ?></p>
                        <a href="book.php?id=<?php echo $book['id']; ?>" class="view-details">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

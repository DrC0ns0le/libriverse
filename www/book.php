<?php
// Start the session and include database connection
session_start();
include 'db_connection.php'; // Adjust the path as needed

// Get the book ID from the URL (e.g., book.php?id=1)
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query to fetch book details
$query = "SELECT * FROM catalog WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $book = $result->fetch_assoc();
} else {
    // Handle the case where the book does not exist
    die("Book not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?> - LibriVerse</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
        <img src="<?php echo htmlspecialchars($book['image_link']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover" class="book-cover">

        <div class="book-details">
            <h2>Details</h2>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></p>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></p>
            <p><strong>Language:</strong> <?php echo htmlspecialchars($book['language']); ?></p>
            <p><strong>Ratings:</strong> <?php echo htmlspecialchars($book['ratings']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($book['price']); ?></p>
            <p><strong>Inventory:</strong> <?php echo htmlspecialchars($book['inventory']); ?></p>
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            <?php if (!empty($book['pdf_link'])): ?>
                <p><a href="<?php echo htmlspecialchars($book['pdf_link']); ?>" target="_blank">Download PDF</a></p>
            <?php endif; ?>
        </div>

        <a href="index.php" class="back-button">Back to Home</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

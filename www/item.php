<?php
include 'utils/db.php';

// Get book ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prepare and execute query
$stmt = $conn->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    // Return book not found page
    echo "Book not found";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>

    <div class="book-container">
        <div class="cover-section">
            <img
                src="<?php echo htmlspecialchars($book['image_link']); ?>"
                alt="<?php echo htmlspecialchars($book['title']); ?> cover"
                class="cover-image">
        </div>

        <div class="details-section">
            <div class="details-box">
                <h2>Details</h2>
                <div class="detail-item">
                    <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?>
                </div>
                <div class="detail-item">
                    <strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?>
                </div>
                <div class="detail-item">
                    <strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?>
                </div>
                <div class="detail-item">
                    <strong>Language:</strong> <?php echo htmlspecialchars($book['language']); ?>
                </div>
                <div class="detail-item">
                    <strong>Rating:</strong>
                    <span class="rating-stars">
                        <?php echo str_repeat('★', floor($book['ratings'])) . str_repeat('☆', 5 - floor($book['ratings'])); ?>
                    </span>
                    (<?php echo number_format($book['ratings'], 2); ?>)
                </div>
            </div>

            <div class="bookmark-box">
                <button onclick="bookmarkBook(<?php echo $book['id']; ?>)">Add to Bookmarks</button>
            </div>
        </div>
    </div>

    <?php
    // Calculate daily rate
    $daily_rate = ($book['price'] * 1.5) / 30;
    ?>

    <div class="rental-box">
        <h2>Rental Details</h2>
        <div class="detail-item">
            <strong>Daily Rate:</strong> $<?php echo number_format($daily_rate, 2); ?>
        </div>
        <?php if ($book['inventory'] > 0): ?>
            <a href="reserve.php?id=<?php echo $book['id']; ?>" class="button reserve-button">Reserve Now</a>
        <?php else: ?>
            <button class="button reserve-button" disabled>Not Available</button>
        <?php endif; ?>
    </div>

    <div class="reviews-box">
        <h2>Reviews</h2>
        <div id="reviews-container">Loading reviews...</div>
    </div>

    <script>
        function bookmarkBook(bookId) {
            // Add your bookmark functionality here
            alert('Book added to bookmarks!');
        }

        function rentBook(bookId) {
            // Add your rental functionality here
            alert('Rental process started!');
        }

        // Simulate loading reviews
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.getElementById('reviews-container').innerHTML =
                    '<p>WIP.</p>';
            }, 1000);
        });
    </script>
</body>

</html>
<?php
session_start();
include 'utils/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch bookmarks
$bookmark_stmt = $conn->prepare("
    SELECT b.id as bookmark_id, c.* 
    FROM bookmark b 
    JOIN catalog c ON b.catalog_id = c.id 
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$bookmark_stmt->bind_param("i", $user_id);
$bookmark_stmt->execute();
$bookmarks = $bookmark_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch rental requests
$rental_stmt = $conn->prepare("
    SELECT r.*, c.title, c.author, c.image_link, pl.library_name, pl.address
    FROM request r 
    JOIN catalog c ON r.catalog_id = c.id 
    LEFT JOIN pickup_location pl ON r.pickup_location_id = pl.id
    WHERE r.user_id = ? 
    ORDER BY r.status_last_updated DESC
");
$rental_stmt->bind_param("i", $user_id);
$rental_stmt->execute();
$rentals = $rental_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle bookmark removal
if (isset($_POST['remove_bookmark'])) {
    $bookmark_id = intval($_POST['remove_bookmark']);
    $remove_stmt = $conn->prepare("DELETE FROM bookmark WHERE id = ? AND user_id = ?");
    $remove_stmt->bind_param("ii", $bookmark_id, $user_id);
    $remove_stmt->execute();
    header('Location: bookshelf.php');
    exit;
}

// Handle request cancellation
if (isset($_POST['cancel_request'])) {
    $request_id = intval($_POST['cancel_request']);
    $cancel_stmt = $conn->prepare("UPDATE request SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Requested'");
    $cancel_stmt->bind_param("ii", $request_id, $user_id);
    $cancel_stmt->execute();
    header('Location: bookshelf.php');
    exit;
}

// Handle review submission
if (isset($_POST['submit_review'])) {
    $request_id = intval($_POST['request_id']);
    $rating = intval($_POST['rating']);
    $comment = $_POST['comment'];
    $review_stmt = $conn->prepare("INSERT INTO review (user_id, catalog_id, rating, comment) SELECT user_id, catalog_id, ?, ? FROM request WHERE id = ? AND user_id = ?");
    $review_stmt->bind_param("isii", $rating, $comment, $request_id, $user_id);
    $review_stmt->execute();
    header('Location: bookshelf.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookshelf - Libriverse</title>
    <link rel="stylesheet" href="bookshelf.css">
</head>

<body>
    <h1>Bookshelf</h1>

    <section class="bookmarks-section">
        <h2>Bookmarks</h2>
        <div class="bookmark-grid">
            <?php if (empty($bookmarks)): ?>
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <div class="book-card empty">
                        <p>Empty slot</p>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php foreach ($bookmarks as $book): ?>
                    <div class="book-card">
                        <div class="book-cover-container">
                            <img src="<?php echo htmlspecialchars($book['image_link']); ?>"
                                alt="<?php echo htmlspecialchars($book['title']); ?>"
                                class="book-cover">
                            <div class="book-actions">
                                <a href="item.php?id=<?php echo $book['id']; ?>"
                                    class="button view">View</a>
                                <form method="POST" class="remove-form">
                                    <button type="submit"
                                        name="remove_bookmark"
                                        value="<?php echo $book['bookmark_id']; ?>"
                                        class="button remove">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="book-info">
                            <h3 title="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h3>
                            <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php
                // Fill remaining slots with empty cards
                $remaining_slots = 7 - count($bookmarks);
                for ($i = 0; $i < $remaining_slots; $i++):
                ?>
                    <div class="book-card empty">
                        <p>Empty slot</p>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="requests-section">
        <h2>Manage & Track Requests</h2>
        <div class="rental-list">
            <?php if (empty($rentals)): ?>
                <div class="rental-item empty">
                    <p>No active rental requests</p>
                </div>
            <?php else: ?>
                <?php foreach ($rentals as $rental): ?>
                    <div class="rental-item">
                        <img src="<?php echo htmlspecialchars($rental['image_link']); ?>"
                            alt="<?php echo htmlspecialchars($rental['title']); ?>"
                            class="book-cover">

                        <div class="rental-details">
                            <h3><?php echo htmlspecialchars($rental['title']); ?></h3>
                            <p>by <?php echo htmlspecialchars($rental['author']); ?></p>

                            <div class="status-indicator">
                                <span class="status-badge <?php echo strtolower($rental['status']); ?>">
                                    <?php echo htmlspecialchars($rental['status']); ?>
                                </span>
                                <?php if ($rental['status'] === 'Ready'): ?>
                                    <span>To return by <?php echo date('d/m/Y', strtotime($rental['status_last_updated'] . ' + ' . $rental['rental_duration'] . ' days')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="actions">
                            <?php if ($rental['status'] === 'Requested'): ?>
                                <form method="POST">
                                    <button type="submit" name="cancel_request"
                                        value="<?php echo $rental['id']; ?>"
                                        class="button cancel">Cancel</button>
                                </form>
                            <?php elseif ($rental['status'] === 'Returned'): ?>
                                <button class="button review"
                                    onclick="showReviewForm(<?php echo $rental['id']; ?>)">Review</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
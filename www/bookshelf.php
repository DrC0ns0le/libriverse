<?php
session_start();
include 'utils/db.php';
include 'utils/auth.php';

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
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bookshelf.css">


    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo/Title Section -->
            <div class="navbar-logo-section">
                <a href="index.php" class="navbar-logo">Libriverse</a>
            </div>

            <!-- Pages Section -->
            <ul class="navbar-pages">
                <li><a href="index.php" class="navbar-item">Home</a></li>
                <li><a href="discover.php" class="navbar-item">Discover</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="bookshelf.php" class="navbar-item" style="font-weight: bold;">Bookshelf</a></li>
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
        <h1>Bookshelf</h1>

        <section class="section bookmarks-section">
            <div class="section-title">
                <span>My Bookmarks</span>
            </div>
            <div class="scroll-container">
                <div class="book-grid">
                    <?php if (!empty($bookmarks)): ?>
                        <?php foreach ($bookmarks as $book): ?>
                            <div class="book-card">
                                <form method="POST" class="remove-form">
                                    <button type="submit"
                                        name="remove_bookmark"
                                        value="<?php echo htmlspecialchars($book['bookmark_id']); ?>"
                                        class="remove-button">&times;</button>
                                </form>
                                <a href="item.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="book-link">
                                    <img src="<?php echo htmlspecialchars($book['image_link']); ?>"
                                        alt="<?php echo htmlspecialchars($book['title']); ?>"
                                        class="book-image">
                                    <div class="book-info">
                                        <p class="book-title"><?php echo htmlspecialchars($book['title']); ?></p>
                                        <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="requests-section">
            <div class="section-title">
                <span>Manage & Track Requests</span>
            </div>
            <div class="scroll-container vertical-scroll">
                <div class="rental-grid vertical-grid">
                    <?php if (empty($rentals)): ?>
                        <div class="rental-card empty">
                            <p>No active rental requests</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rentals as $rental): ?>
                            <div class="rental-card">
                                <img src="<?php echo htmlspecialchars($rental['image_link']); ?>"
                                    alt="<?php echo htmlspecialchars($rental['title']); ?>"
                                    class="book-image">
                                <div class="rental-info">
                                    <a href="item.php?id=<?php echo htmlspecialchars($rental['catalog_id']); ?>">
                                        <div class="book-title"><?php echo htmlspecialchars($rental['title']); ?></div>
                                        <div class="book-author">by <?php echo htmlspecialchars($rental['author']); ?></div>
                                    </a>
                                    <div class="status-indicator">
                                        <span class="status-badge <?php echo strtolower($rental['status']); ?>">
                                            Status: <?php echo htmlspecialchars($rental['status']); ?>
                                        </span>

                                        <?php
                                        switch ($rental['status']) {
                                            case 'Collected':
                                        ?>
                                                <div class="return-date">
                                                    <?php
                                                    $return_date = strtotime($rental['status_last_updated'] . ' + ' . $rental['rental_duration'] . ' days');
                                                    $current_date = time();
                                                    $days_diff = round(($return_date - $current_date) / (60 * 60 * 24));

                                                    if ($days_diff > 0) {
                                                        echo "Return in " . $days_diff . " day" . ($days_diff != 1 ? "s" : "") . " (by " . date('d/m/Y', $return_date) . ")";
                                                    } elseif ($days_diff == 0) {
                                                        echo "<span class='due-today'>Due today!</span>";
                                                    } else {
                                                        $overdue_days = abs($days_diff);
                                                        echo "<span class='overdue'>Overdue by " . $overdue_days . " day" . ($overdue_days != 1 ? "s" : "") . "</span>";
                                                    }
                                                    ?>
                                                </div>
                                            <?php
                                                break;

                                            case 'Ready':
                                            ?>
                                                <div class="collection-info">
                                                    Your book is ready for collection at
                                                    <strong><?php echo htmlspecialchars($rental['library_name']); ?></strong>
                                                    <br>
                                                    Full Address: <?php echo htmlspecialchars($rental['address']); ?>
                                                </div>
                                            <?php
                                                break;

                                            case 'Requested':
                                            ?>
                                                <div class="actions">
                                                    <form method="POST">
                                                        <button type="submit"
                                                            name="cancel_request"
                                                            value="<?php echo $rental['id']; ?>"
                                                            class="button cancel">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php
                                                break;

                                            case 'Returned':
                                            ?>
                                                <div class="actions">
                                                    <button class="button review"
                                                        onclick="showReviewForm(<?php echo $rental['id']; ?>)">
                                                        Review
                                                    </button>
                                                </div>
                                        <?php
                                                break;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</body>

</html>
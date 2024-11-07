<?php
include 'utils/db.php';
include 'utils/auth.php';

init_authenticated_session();

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

// Pagination settings
// Items per page setting
$valid_items_per_page = [4, 10, 30, 50, 100];
$default_items_per_page = 10;

if (isset($_GET['items_per_page']) && in_array((int)$_GET['items_per_page'], $valid_items_per_page)) {
    $items_per_page = (int)$_GET['items_per_page'];
    setcookie('items_per_page', $items_per_page, time() + (86400 * 30), "/"); // 30 days cookie
} elseif (isset($_COOKIE['items_per_page']) && in_array((int)$_COOKIE['items_per_page'], $valid_items_per_page)) {
    $items_per_page = (int)$_COOKIE['items_per_page'];
} else {
    $items_per_page = $default_items_per_page;
}

$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Search and filter parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

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

// Build the rental query with search and filters
$rental_query = "
    SELECT SQL_CALC_FOUND_ROWS r.*, c.title, c.author, c.image_link, pl.library_name, pl.address,
           rv.id as review_id, rv.rating, rv.review as comment
    FROM request r 
    JOIN catalog c ON r.catalog_id = c.id 
    LEFT JOIN pickup_location pl ON r.pickup_location_id = pl.id
    LEFT JOIN review rv ON rv.user_id = r.user_id AND rv.catalog_id = r.catalog_id
    WHERE r.user_id = ?
";

$query_params = [$user_id];
$param_types = "i";

if ($search_term) {
    $rental_query .= " AND (c.title LIKE ? OR c.author LIKE ?)";
    $search_param = "%{$search_term}%";
    array_push($query_params, $search_param, $search_param);
    $param_types .= "ss";
}

if ($status_filter) {
    $rental_query .= " AND r.status = ?";
    array_push($query_params, $status_filter);
    $param_types .= "s";
}

$rental_query .= " ORDER BY r.status_last_updated DESC LIMIT ? OFFSET ?";
array_push($query_params, $items_per_page, $offset);
$param_types .= "ii";

// Prepare and execute the rental query
$rental_stmt = $conn->prepare($rental_query);
$rental_stmt->bind_param($param_types, ...$query_params);
$rental_stmt->execute();
$rentals = $rental_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total number of results for pagination
$total_results = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$total_pages = ceil($total_results / $items_per_page);

// Get distinct status values for filter dropdown
$status_stmt = $conn->prepare("
    SELECT DISTINCT status 
    FROM request 
    WHERE user_id = ? 
    ORDER BY status
");
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_options = $status_stmt->get_result()->fetch_all(MYSQLI_NUM);

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

// Handle review submission/update
if (isset($_POST['submit_review'])) {
    $request_id = intval($_POST['request_id']);
    $rating = intval($_POST['rating']);
    $review_text = $_POST['comment'];

    // Check if review exists
    $check_review_stmt = $conn->prepare("
        SELECT id FROM review 
        WHERE user_id = ? AND catalog_id = (SELECT catalog_id FROM request WHERE id = ?)
    ");
    $check_review_stmt->bind_param("ii", $user_id, $request_id);
    $check_review_stmt->execute();
    $existing_review = $check_review_stmt->get_result()->fetch_assoc();

    if ($existing_review) {
        // Update existing review
        $update_stmt = $conn->prepare("
            UPDATE review 
            SET rating = ?, review = ? 
            WHERE id = ?
        ");
        $update_stmt->bind_param("isi", $rating, $review_text, $existing_review['id']);
        $update_stmt->execute();
    } else {
        // Insert new review
        $review_stmt = $conn->prepare("
            INSERT INTO review (user_id, catalog_id, rating, review) 
            SELECT user_id, catalog_id, ?, ? FROM request WHERE id = ? AND user_id = ?
        ");
        $review_stmt->bind_param("isii", $rating, $review_text, $request_id, $user_id);
        $review_stmt->execute();
    }

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

        <?php if (empty($search_term) && empty($status_filter)): ?>
            <section class="section bookmarks-section">
                <div class="section-title">
                    <span>Your Bookmarks</span>
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
                        <?php else: ?>
                            <p class="no-bookmarks-message">Seems like you haven't added any bookmarks yet. Head on over the <a href="discover.php">Discover</a> page to find some great books!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php endif; ?>

        <section class="requests-section">
            <div class="section-title">
                <span>Manage & Track Requests</span>
            </div>

            <div class="filters-container">
                <form method="GET" id="filterForm">
                    <div class="search-container">
                        <input type="text"
                            name="search"
                            id="searchInput"
                            class="search-box"
                            placeholder="Search by title or author..."
                            value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="button" id="clearSearch" class="clear-search">&times;</button>
                    </div>

                    <select name="status" class="status-filter" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <?php foreach ($status_options as $status): ?>
                            <option value="<?php echo htmlspecialchars($status[0]); ?>"
                                <?php echo $status_filter === $status[0] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status[0]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="items_per_page" class="items-per-page" onchange="this.form.submit()">
                        <?php foreach ($valid_items_per_page as $option): ?>
                            <option value="<?php echo $option; ?>"
                                <?php echo $items_per_page === $option ? 'selected' : ''; ?>>
                                <?php echo $option; ?> items
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>

            <div class="scroll-container vertical-scroll">
                <div class="rental-grid vertical-grid">
                    <?php if (empty($rentals)): ?>
                        <div class="rental-card empty">
                            <p>No active rental requests found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rentals as $rental): ?>
                            <div class="rental-card">
                                <div class="book-image-container">
                                    <a href="item.php?id=<?php echo htmlspecialchars($rental['catalog_id']); ?>">
                                        <img src="<?php echo htmlspecialchars($rental['image_link']); ?>"
                                            alt="<?php echo htmlspecialchars($rental['title']); ?>"
                                            class="book-image">
                                    </a>
                                </div>
                                <div class="rental-info">
                                    <div class="status-indicator">
                                        <span class="status-badge <?php echo strtolower($rental['status']); ?>">
                                            <?php echo htmlspecialchars($rental['status']); ?>
                                        </span>
                                        <span class="last-updated">
                                            Last updated: <?php echo date('M j, Y g:i A', strtotime($rental['status_last_updated'])); ?>
                                        </span>
                                    </div>
                                    <a href="item.php?id=<?php echo htmlspecialchars($rental['catalog_id']); ?>">
                                        <div class="book-title"><?php echo htmlspecialchars($rental['title']); ?></div>
                                        <div class="book-author">by <?php echo htmlspecialchars($rental['author']); ?></div>
                                    </a>
                                    <div class="rental-details">

                                        <?php
                                        switch ($rental['status']) {
                                            case 'Collected':
                                        ?>
                                                <div class="collection-info">
                                                    Your book was collected on <?php echo date('M j, Y', strtotime($rental['status_last_updated'])); ?>. </br>
                                                </div>
                                                <div class="return-date">
                                                    <?php
                                                    $return_date = strtotime($rental['status_last_updated'] . ' + ' . $rental['rental_duration'] . ' days');
                                                    $current_date = time();
                                                    $days_diff = round(($return_date - $current_date) / (60 * 60 * 24));

                                                    if ($days_diff > 0) {
                                                        echo "Please return the book in " . $days_diff . " day" . ($days_diff != 1 ? "s" : "") . " (by " . date('M j, Y', $return_date) . ")";
                                                    } elseif ($days_diff == 0) {
                                                        echo "<span class='due-today'>Your book is due today!</span>";
                                                    } else {
                                                        $overdue_days = abs($days_diff);
                                                        echo "<span class='overdue'>Your book is overdue by " . $overdue_days . " day" . ($overdue_days != 1 ? "s" : "") . "</span>";
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
                                                        onclick="showReviewForm(<?php echo $rental['id']; ?>, <?php
                                                                                                                echo htmlspecialchars(
                                                                                                                    json_encode([
                                                                                                                        'rating' => $rental['rating'],
                                                                                                                        'comment' => $rental['comment'],
                                                                                                                        'isEdit' => !is_null($rental['review_id'])
                                                                                                                    ]),
                                                                                                                    ENT_QUOTES
                                                                                                                );
                                                                                                                ?>)">
                                                        <?php echo !is_null($rental['review_id']) ? 'Edit Review' : 'Review'; ?>
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
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php
                                // Previous button
                                if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="page-link">&laquo; Prev</a>
                                <?php endif; ?>

                                <?php
                                // Calculate range of pages to show
                                $range = 2; // Number of pages to show on each side of current page
                                $start_page = max(1, $current_page - $range);
                                $end_page = min($total_pages, $current_page + $range);

                                // First page + ellipsis
                                if ($start_page > 1): ?>
                                    <a href="?page=1&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="page-link">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span class="page-ellipsis">&hellip;</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php
                                // Main page numbers
                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php
                                // Last page + ellipsis
                                if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span class="page-ellipsis">&hellip;</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="page-link"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php
                                // Next button
                                if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="page-link">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="reviewModalTitle">Write a Review</h2>
            <form id="reviewForm" method="POST">
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" id="requestId" name="request_id" value="">

                <label for="rating">How would you rate this book?</label>
                <div class="rating-container">
                    <div class="stars">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comment">What do you think about this book?</label>
                    <textarea id="comment" name="comment" rows="4" required></textarea>
                </div>

                <button type="submit" class="submit-review">Submit Review</button>
            </form>
        </div>
    </div>
</body>

<script>
    // Get modal elements
    const modal = document.getElementById('reviewModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    const modalTitle = document.getElementById('reviewModalTitle');
    const commentField = document.getElementById('comment');
    let currentRequestId = null;

    // Show review modal function
    function showReviewForm(requestId, reviewData = null) {
        currentRequestId = requestId;
        document.getElementById('requestId').value = requestId;

        // Reset form first
        resetForm();

        if (reviewData && reviewData.isEdit) {
            modalTitle.textContent = 'Edit Your Review';

            // Set the rating
            if (reviewData.rating) {
                const ratingInput = document.querySelector(`input[name="rating"][value="${reviewData.rating}"]`);
                if (ratingInput) {
                    ratingInput.checked = true;
                }
            }

            // Set the comment
            if (reviewData.comment) {
                commentField.value = reviewData.comment;
            }
        } else {
            modalTitle.textContent = 'Write a Review';
        }

        modal.style.display = 'block';
    }

    // Close modal when clicking (X)
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        resetForm();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            resetForm();
        }
    }

    // Reset form function
    function resetForm() {
        document.getElementById('reviewForm').reset();
        currentRequestId = null;
    }

    // Form validation
    document.getElementById('reviewForm').onsubmit = function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        const comment = document.getElementById('comment').value.trim();

        if (!rating) {
            e.preventDefault();
            alert('Please select a rating');
            return false;
        }

        if (!comment) {
            e.preventDefault();
            alert('Please write a review');
            return false;
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearSearch');
        const filterForm = document.getElementById('filterForm');

        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            this.style.display = 'none';
            filterForm.submit();
        });

        searchInput.addEventListener('input', function() {
            clearButton.style.display = this.value ? 'block' : 'none';
        });
    });
</script>

</html>
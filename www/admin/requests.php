<?php
// www/admin/requests.php

include '../utils/db.php';
include '../utils/auth.php';

// Ensure only admins can access this page
init_admin_session();

// Get unique statuses from database
$status_query = "SELECT DISTINCT status FROM request ORDER BY status";
$status_result = $conn->query($status_query);
$available_statuses = [];
while ($status_row = $status_result->fetch_assoc()) {
    $available_statuses[] = $status_row['status'];
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$book_filter = isset($_GET['book']) ? $_GET['book'] : '';

// Build query with prepared statements
$query = "
    SELECT r.*, u.username, c.title as book_title
    FROM request r
    JOIN user u ON r.user_id = u.id
    JOIN catalog c ON r.catalog_id = c.id
    WHERE 1=1
";
$params = array();
$types = "";

if ($status_filter) {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if ($user_filter) {
    $query .= " AND u.username LIKE ?";
    $params[] = "%$user_filter%";
    $types .= "s";
}
if ($book_filter) {
    $query .= " AND c.title LIKE ?";
    $params[] = "%$book_filter%";
    $types .= "s";
}

// Get total count
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total_requests = $result->num_rows;
$total_pages = ceil($total_requests / $per_page);

// Apply pagination
$query .= " ORDER BY status_last_updated DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $per_page;
$types .= "ii";

// Get paginated results
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];

    // Validate that the new status exists in our available statuses
    if (in_array($new_status, $available_statuses)) {
        $update_query = "UPDATE request SET status = ?, status_last_updated = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $request_id);
        $stmt->execute();
    }

    header("Location: requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Admin</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="css/requests.css">
</head>

<body>
    <h1>Manage Requests</h1>

    <form method="GET" class="filters">
        <input type="text" name="user" placeholder="Filter by username" value="<?php echo htmlspecialchars($user_filter); ?>">
        <input type="text" name="book" placeholder="Filter by book title" value="<?php echo htmlspecialchars($book_filter); ?>">
        <select name="status">
            <option value="">All Statuses</option>
            <?php foreach ($available_statuses as $status): ?>
                <option value="<?php echo htmlspecialchars($status); ?>"
                    <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Book</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Duration</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['status_last_updated']); ?></td>
                    <td><?php echo htmlspecialchars($row['rental_duration']); ?> days</td>
                    <td>
                        <form method="POST" class="update-form">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            <select name="new_status">
                                <?php foreach ($available_statuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>"
                                        <?php echo $row['status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <?php
        // Previous button
        if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo urlencode($status_filter); ?>&user=<?php echo urlencode($user_filter); ?>&book=<?php echo urlencode($book_filter); ?>"
                class="pagination">&laquo; Previous</a>
        <?php endif; ?>

        <?php
        // Calculate range of pages to show
        $range = 2; // Number of pages to show on each side of current page
        $start_page = max(1, $page - $range);
        $end_page = min($total_pages, $page + $range);

        // First page + ellipsis
        if ($start_page > 1): ?>
            <a href="?page=1&status=<?php echo urlencode($status_filter); ?>&user=<?php echo urlencode($user_filter); ?>&book=<?php echo urlencode($book_filter); ?>"
                class="pagination">1</a>
            <?php if ($start_page > 2): ?>
                <span class="page-ellipsis">&hellip;</span>
        <?php endif;
        endif; ?>

        <?php
        // Main page numbers
        for ($i = $start_page; $i <= $end_page; $i++): ?>
            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&user=<?php echo urlencode($user_filter); ?>&book=<?php echo urlencode($book_filter); ?>"
                class="pagination <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php
        // Last page + ellipsis
        if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
                <span class="page-ellipsis">&hellip;</span>
            <?php endif; ?>
            <a href="?page=<?php echo $total_pages; ?>&status=<?php echo urlencode($status_filter); ?>&user=<?php echo urlencode($user_filter); ?>&book=<?php echo urlencode($book_filter); ?>"
                class="pagination"><?php echo $total_pages; ?></a>
        <?php endif; ?>

        <?php
        // Next button
        if ($page < $total_pages): ?>
            <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo urlencode($status_filter); ?>&user=<?php echo urlencode($user_filter); ?>&book=<?php echo urlencode($book_filter); ?>"
                class="pagination">Next &raquo;</a>
        <?php endif; ?>
    </div>
</body>

</html>
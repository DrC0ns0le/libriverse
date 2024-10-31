<?php
session_start();
include 'utils/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['book_id'])) {
    echo json_encode(['success' => false, 'message' => 'Book ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id']);

$check_stmt = $conn->prepare("SELECT id FROM bookmark WHERE user_id = ? AND catalog_id = ?");
$check_stmt->bind_param("ii", $user_id, $book_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Bookmark exists, remove it
    $delete_stmt = $conn->prepare("DELETE FROM bookmark WHERE user_id = ? AND catalog_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $book_id);
    $success = $delete_stmt->execute();
    echo json_encode(['success' => $success, 'bookmarked' => false]);
} else {
    // Bookmark doesn't exist, add it
    $insert_stmt = $conn->prepare("INSERT INTO bookmark (user_id, catalog_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $book_id);
    $success = $insert_stmt->execute();
    echo json_encode(['success' => $success, 'bookmarked' => true]);
}

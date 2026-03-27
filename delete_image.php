<?php
session_start();

if (!isset($_POST['id']) || empty($_POST['id'])) {
    header("Location: index.html?status=invalid");
    exit;
}

$id = $_POST['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get image path before deleting
$stmt = $conn->prepare("SELECT image_path FROM dacdimgtemp WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image_path = $row['image_path'];

    // Delete from database
    $delete_stmt = $conn->prepare("DELETE FROM dacdimgtemp WHERE id = ?");
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        // Delete the image file
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Clear session data
        unset($_SESSION['image_analysis_result']);
        unset($_SESSION['last_inserted_id']);

        header("Location: index.html?status=deleted");
    } else {
        header("Location: index.html?status=error");
    }

    $delete_stmt->close();
} else {
    header("Location: index.html?status=not_found");
}

$stmt->close();
$conn->close();
?>
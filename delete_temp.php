<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Check if an ID was submitted
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM tempdata WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to web_analyse.php
        header("Location: web_analyse.php");
        exit();
    } else {
        echo "Hiba a törlés során: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
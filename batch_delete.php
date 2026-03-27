<?php
// batch_delete.php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

// Delete selected records
if (isset($_POST['action_type']) && $_POST['action_type'] == 'delete_selected') {
    if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) && !empty($_POST['selected_ids'])) {
        $selected_ids = $_POST['selected_ids'];

        // Create placeholder string for IN clause with the correct number of parameters
        $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';

        // Prepare and execute the delete query
        $delete_query = "DELETE FROM tempdata WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($delete_query);

        // Bind parameters dynamically
        $types = str_repeat('i', count($selected_ids));
        $stmt->bind_param($types, ...$selected_ids);

        if ($stmt->execute()) {
            $_SESSION['batch_message'] = "A kijelölt rekordok sikeresen törölve!";
        } else {
            $_SESSION['batch_message'] = "Hiba történt a törlés során: " . $stmt->error;
        }
    } else {
        $_SESSION['batch_message'] = "Nincs kijelölt rekord a törléshez!";
    }
}

// Delete all records
if (isset($_POST['action_type']) && $_POST['action_type'] == 'delete_all') {
    $delete_all_query = "TRUNCATE TABLE tempdata";
    if ($conn->query($delete_all_query) === TRUE) {
        $_SESSION['batch_message'] = "Az összes rekord sikeresen törölve!";
    } else {
        $_SESSION['batch_message'] = "Hiba történt az összes rekord törlése során: " . $conn->error;
    }
}

$conn->close();
header("Location: web_analyse.php");
exit();
?>
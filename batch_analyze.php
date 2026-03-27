<?php
// batch_analyze.php - Handle batch analysis operations
session_start();

// Connect to database
$conn = new mysqli("localhost", "root", "", "temi");
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Python path
$python_path = 'C:\\Users\\w10\\ai_env\\Scripts\\python.exe';

// Get action type
$action_type = $_POST['action_type'] ?? '';

// Determine which records to process
$records_to_process = [];

if ($action_type === 'analyze_all') {
    // Get all records
    $query = "SELECT * FROM tempdata";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $records_to_process[] = $row;
    }
} else if ($action_type === 'analyze_selected' || $action_type === 'reanalyze_selected') {
    // Get only selected records
    if (!empty($_POST['selected_ids'])) {
        $ids = array_map('intval', $_POST['selected_ids']);
        $ids_str = implode(',', $ids);
        $query = "SELECT * FROM tempdata WHERE id IN ($ids_str)";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $records_to_process[] = $row;
        }
    }
}

// Process each record
$processed_count = 0;
foreach ($records_to_process as $record) {
    // Skip id field for Python script
    $input_data = $record;
    unset($input_data['id']);

    // Call Python script for analysis
    $input_json = json_encode($input_data);
    $command = '"' . $python_path . '" "' . __DIR__ . '\\run_model.py" "' . addslashes($input_json) . '" 2>&1';
    $output = shell_exec($command);

    // Process Python output
    $lines = explode("\n", $output);
    $diagnosis_value = 0; // Default to benign
    $probability = 0.0;   // Default probability value

    foreach ($lines as $line) {
        if (strpos($line, 'Diagnosztikai vegeredmeny: Rosszindulatu') !== false) {
            $diagnosis_value = 1;
        } else if (strpos($line, 'Diagnosztika pontossaga:') !== false) {
            preg_match('/(\d+\.\d+)%/', $line, $matches);
            if (isset($matches[1])) {
                $probability = floatval($matches[1]);
            }
        }
    }

    // Save to data table with probability value
    $stmt = $conn->prepare("INSERT INTO data (szsz, radius_mean, texture_mean, perimeter_mean, area_mean, smoothness_mean, compactness_mean, concavity_mean, concave_points_mean, symmetry_mean, fractal_dimension_mean, radius_se, texture_se, perimeter_se, area_se, smoothness_se, compactness_se, concavity_se, concave_points_se, symmetry_se, fractal_dimension_se, radius_worst, texture_worst, perimeter_worst, area_worst, smoothness_worst, compactness_worst, concavity_worst, concave_points_worst, symmetry_worst, fractal_dimension_worst, diagnosis, probability) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sddddddddddddddddddddddddddddddidd",
        $record["szsz"], $record["radius_mean"], $record["texture_mean"], $record["perimeter_mean"],
        $record["area_mean"], $record["smoothness_mean"], $record["compactness_mean"], $record["concavity_mean"],
        $record["concave_points_mean"], $record["symmetry_mean"], $record["fractal_dimension_mean"],
        $record["radius_se"], $record["texture_se"], $record["perimeter_se"], $record["area_se"],
        $record["smoothness_se"], $record["compactness_se"], $record["concavity_se"],
        $record["concave_points_se"], $record["symmetry_se"], $record["fractal_dimension_se"],
        $record["radius_worst"], $record["texture_worst"], $record["perimeter_worst"], $record["area_worst"],
        $record["smoothness_worst"], $record["compactness_worst"], $record["concavity_worst"],
        $record["concave_points_worst"], $record["symmetry_worst"], $record["fractal_dimension_worst"],
        $diagnosis_value, $probability
    );

    $stmt->execute();
    $stmt->close();
    $processed_count++;
}

// Delete processed records from tempdata
if ($action_type === 'analyze_all') {
    $conn->query("TRUNCATE TABLE tempdata");
} else if ($action_type === 'analyze_selected') {
    $ids = array_map('intval', $_POST['selected_ids']);
    $ids_str = implode(',', $ids);
    $conn->query("DELETE FROM tempdata WHERE id IN ($ids_str)");
}

$conn->close();

// Set session notification
$_SESSION['batch_message'] = "$processed_count eset sikeresen elemezve és mentve.";

// Redirect back
header("Location: web_analyse.php");
exit();
?>
<?php
// process_analysis.php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

// Check if we received record_id
if (isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];

    // Get the diagnosis value and probability from hidden fields
    $diagnosis_cell = $_POST['diagnosis_value'] ?? ''; // Get diagnosis value (0 or 1)
    $probability = $_POST['probability_value'] ?? ''; // Get probability value

    // Fetch the record data from tempdata
    $query = "SELECT * FROM tempdata WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();

        // Insert into data table
        // Add probability column to the query between diagnosis and radius_mean
        $insert_query = "INSERT INTO data (szsz, diagnosis, probability, radius_mean, texture_mean, perimeter_mean, area_mean, smoothness_mean, compactness_mean, concavity_mean, concave_points_mean, symmetry_mean, fractal_dimension_mean, radius_se, texture_se, perimeter_se, area_se, smoothness_se, compactness_se, concavity_se, concave_points_se, symmetry_se, fractal_dimension_se, radius_worst, texture_worst, perimeter_worst, area_worst, smoothness_worst, compactness_worst, concavity_worst, concave_points_worst, symmetry_worst, fractal_dimension_worst) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insert_query);

        // Convert probability to double
        $probability_double = floatval($probability);

        // Bind all parameters - add probability (use the specific $_POST values where needed)
        $stmt->bind_param("sdddddddddddddddddddddddddddddddd",
            $record['szsz'],
            $diagnosis_cell,
            $probability_double,
            $record['radius_mean'],
            $record['texture_mean'],
            $record['perimeter_mean'],
            $record['area_mean'],
            $record['smoothness_mean'],
            $record['compactness_mean'],
            $record['concavity_mean'],
            $record['concave_points_mean'],
            $record['symmetry_mean'],
            $record['fractal_dimension_mean'],
            $record['radius_se'],
            $record['texture_se'],
            $record['perimeter_se'],
            $record['area_se'],
            $record['smoothness_se'],
            $record['compactness_se'],
            $record['concavity_se'],
            $record['concave_points_se'],
            $record['symmetry_se'],
            $record['fractal_dimension_se'],
            $record['radius_worst'],
            $record['texture_worst'],
            $record['perimeter_worst'],
            $record['area_worst'],
            $record['smoothness_worst'],
            $record['compactness_worst'],
            $record['concavity_worst'],
            $record['concave_points_worst'],
            $record['symmetry_worst'],
            $record['fractal_dimension_worst']
        );

        if ($stmt->execute()) {
            // Delete from tempdata after successful save
            $delete_query = "DELETE FROM tempdata WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $record_id);
            $stmt->execute();

            $_SESSION['batch_message'] = "Rekord sikeresen elmentve!";
        } else {
            $_SESSION['batch_message'] = "Hiba történt a mentés során: " . $stmt->error;
        }
    }
}

$conn->close();
header("Location: web_analyse.php");
exit();
?>
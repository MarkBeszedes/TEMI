<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Prepare the statement to insert into tempdata
$stmt = $conn->prepare("INSERT INTO tempdata (
    szsz, radius_mean, texture_mean, perimeter_mean, area_mean, 
    smoothness_mean, compactness_mean, concavity_mean, concave_points_mean, 
    symmetry_mean, fractal_dimension_mean, 
    radius_se, texture_se, perimeter_se, area_se, 
    smoothness_se, compactness_se, concavity_se, concave_points_se, 
    symmetry_se, fractal_dimension_se, 
    radius_worst, texture_worst, perimeter_worst, area_worst, 
    smoothness_worst, compactness_worst, concavity_worst, concave_points_worst, 
    symmetry_worst, fractal_dimension_worst
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)");

// Bind parameters
$stmt->bind_param(
    "sdddddddddddddddddddddddddddddd",
    $_POST["szsz"],
    $_POST["radius_mean"], $_POST["texture_mean"], $_POST["perimeter_mean"], $_POST["area_mean"],
    $_POST["smoothness_mean"], $_POST["compactness_mean"], $_POST["concavity_mean"], $_POST["concave_points_mean"],
    $_POST["symmetry_mean"], $_POST["fractal_dimension_mean"],
    $_POST["radius_se"], $_POST["texture_se"], $_POST["perimeter_se"], $_POST["area_se"],
    $_POST["smoothness_se"], $_POST["compactness_se"], $_POST["concavity_se"], $_POST["concave_points_se"],
    $_POST["symmetry_se"], $_POST["fractal_dimension_se"],
    $_POST["radius_worst"], $_POST["texture_worst"], $_POST["perimeter_worst"], $_POST["area_worst"],
    $_POST["smoothness_worst"], $_POST["compactness_worst"], $_POST["concavity_worst"], $_POST["concave_points_worst"],
    $_POST["symmetry_worst"], $_POST["fractal_dimension_worst"]
);

// Execute the statement
if ($stmt->execute()) {
    // Redirect to web_analyse.php
    header("Location: web_analyse.php");
    exit();
} else {
    echo "Hiba az adatok mentése közben: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
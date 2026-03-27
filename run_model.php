<?php
// hibakezeles on
ini_set('display_errors', 1);
error_reporting(E_ALL);

// python eleresi utja
$python_path = 'C:\\Users\\w10\\ai_env\\Scripts\\python.exe';

// check if data comes from tempdata or direct input
$input_data = $_POST;
session_start();
$_SESSION['last_submission'] = $_POST;

// ff reanalysis and previous entry exists, delete first
$is_reanalysis = isset($_POST['reanalysis']) && $_POST['reanalysis'] == 1;

if ($is_reanalysis && isset($_SESSION['last_inserted_id'])) {
    $delete_id = $_SESSION['last_inserted_id'];
    $conn = new mysqli("localhost", "root", "", "temi");
    if (!$conn->connect_error) {
        $delete_stmt = $conn->prepare("DELETE FROM data WHERE id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        $conn->close();
    }
}

// python szkript futtatasa inputra
$input_json = json_encode($input_data);
$command = '"' . $python_path . '" "' . __DIR__ . '\\run_model.py" "' . addslashes($input_json) . '" 2>&1';
$output = shell_exec($command);

// ment adatbazisba checkb
$should_save = true; // save when coming from webanalyse.html
$last_inserted_id = null;

//mentes
if ($should_save) {
    $conn = new mysqli("localhost", "root", "", "temi");
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }
    $stmt = $conn->prepare("INSERT INTO data (szsz, radius_mean, texture_mean, perimeter_mean, area_mean, smoothness_mean, compactness_mean, concavity_mean, concave_points_mean, symmetry_mean, fractal_dimension_mean, radius_se, texture_se, perimeter_se, area_se, smoothness_se, compactness_se, concavity_se, concave_points_se, symmetry_se, fractal_dimension_se, radius_worst, texture_worst, perimeter_worst, area_worst, smoothness_worst, compactness_worst, concavity_worst, concave_points_worst, symmetry_worst, fractal_dimension_worst) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sdddddddddddddddddddddddddddddd",
        $_POST["szsz"],$_POST["radius_mean"], $_POST["texture_mean"], $_POST["perimeter_mean"], $_POST["area_mean"], $_POST["smoothness_mean"],
        $_POST["compactness_mean"], $_POST["concavity_mean"], $_POST["concave_points_mean"], $_POST["symmetry_mean"], $_POST["fractal_dimension_mean"],
        $_POST["radius_se"], $_POST["texture_se"], $_POST["perimeter_se"], $_POST["area_se"], $_POST["smoothness_se"],
        $_POST["compactness_se"], $_POST["concavity_se"], $_POST["concave_points_se"], $_POST["symmetry_se"], $_POST["fractal_dimension_se"],
        $_POST["radius_worst"], $_POST["texture_worst"], $_POST["perimeter_worst"], $_POST["area_worst"], $_POST["smoothness_worst"],
        $_POST["compactness_worst"], $_POST["concavity_worst"], $_POST["concave_points_worst"], $_POST["symmetry_worst"], $_POST["fractal_dimension_worst"]
    );

    //atrakas
    $stmt->execute();
    //legutobbi id mentese
    $last_inserted_id = $conn->insert_id;
    $stmt->close();
    $conn->close();
    // id mentese, delete.php hoz
    $_SESSION['last_inserted_id'] = $last_inserted_id;
}

// Python kimenet feldolgozása - csak a 3 fontos sort tartjuk meg
$lines = explode("\n", $output);
$filtered_lines = [];

foreach ($lines as $line) {
    if (strpos($line, 'Diagnosztikai vegeredmeny:') !== false ||
        strpos($line, 'Diagnosztika pontossaga:') !== false ||
        strpos($line, 'Hibalehetoseg:') !== false) {
        $filtered_lines[] = $line;
    }
}

// diagnosis type -> kimenet.css
$diagnosis_type = "";
$diagnosis_value = 0; //benign (0)
foreach ($filtered_lines as $line) {
    if (strpos($line, 'Diagnosztikai vegeredmeny: Rosszindulatu') !== false) {
        $diagnosis_type = "malignant";
        $diagnosis_value = 1; // malignant (1)
        break;
    } else if (strpos($line, 'Diagnosztikai vegeredmeny: Joindulatu') !== false) {
        $diagnosis_type = "benign";
        break;
    }
}

// add diagnosis value if the entry was saved
if ($should_save && $last_inserted_id) {
    $conn = new mysqli("localhost", "root", "", "temi");
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    $update_stmt = $conn->prepare("UPDATE data SET diagnosis = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $diagnosis_value, $last_inserted_id);
    $update_stmt->execute();
    $update_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.png">
    <title>Diagnosztika Eredménye</title>
    <link rel="stylesheet" href="kimenetstyle.css">
</head>
<body>
<h1>MI Diagnosztika Eredménye</h1>
<div class="output-container <?php echo $diagnosis_type; ?>">
    <?php
    foreach ($filtered_lines as $line) {
        if (strpos($line, 'Diagnosztikai vegeredmeny:') !== false) {
            echo '<div class="result diagnosis"><pre>' . htmlspecialchars($line) . '</pre></div>';
        } else if (strpos($line, 'Diagnosztika pontossaga:') !== false || strpos($line, 'Hibalehetoseg:') !== false) {
            echo '<div class="result metric"><pre>' . htmlspecialchars($line) . '</pre></div>';
        }
    }
    ?>
</div>
<br>
<div class="button-container">
    <a href="web_analyse.php" class="b-btn"> ⮘ Vissza az elemzéshez</a>
    <a href="index.html" class="b-btn"> ⮘ Vissza a kezdőlapra</a>
</div>
</body>
</html>
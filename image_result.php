<?php
// session to access result data
session_start();

// if results exist in session
if (!isset($_SESSION['image_analysis_result'])) {
    header("Location: imagein.html");
    exit;
}

$result = $_SESSION['image_analysis_result'];
$diagnosis_type = $result['diagnosis'] == 1 ? 'malignant' : 'benign';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.png">
    <title>Kép Elemzés Eredménye</title>
    <link rel="stylesheet" href="kimenetstyle.css">
</head>
<body>
<div class="output-container <?php echo $diagnosis_type; ?>">
    <h1>Képelemzés eredménye:</h1>

    <img src="<?php echo htmlspecialchars($result['file_path']); ?>" alt="Elemzett Kép" class="analyzed-image">

    <div class="result-details">
        <div class="result diagnosis">
            <p>Személyi szám: <?php echo htmlspecialchars($result['szsz']); ?></p>
            <p>Diagnózis: <?php echo htmlspecialchars($result['diagnosis_text']); ?></p>
        </div>

        <div class="result metric">
            <p>Diagnózis pontossága:</p>
            <div class="probability-bar">
                <div class="probability-fill" style="width: <?php echo $result['probability']; ?>%"></div>
            </div>
            <p><?php echo number_format($result['probability'], 2); ?>%</p>
        </div>

        <div class="result metric">
            <p>Hibalehetőség:</p>
            <div class="probability-bar">
                <div class="probability-fill" style="width: <?php echo $result['error_rate']; ?>%"></div>
            </div>
            <p><?php echo number_format($result['error_rate'], 2); ?>%</p>
        </div>
    </div>

    <div class="button-container">
        <a href="image_analysis.html" class="b-btn">⮘ Új elemzés</a>

        <a href="index.html" class="g-btn">Kezdőlap</a>
    </div>
</div>
</body>
</html>
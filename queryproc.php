<?php
$conn = new mysqli("localhost", "root", "", "temi");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $szsz = $_POST['szsz'];

    $stmt = $conn->prepare("SELECT * FROM data WHERE szsz = ?");
    $stmt->bind_param("s", $szsz);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $diagnosis = $row['diagnosis'] == 1 ? "Rosszindulatú" : "Jóindulatú";
        $probability = $row['probability'];
        $foundPatient = true;
    } else {
        $diagnosis = "Nem található ilyen személyi szám az adatbázisban.";
        $foundPatient = false;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosztikai Eredmenyek</title>
    <link rel="icon" href="icon.png">
    <link rel="stylesheet" href="kimenetstyle.css">
</head>
<body>
<div class="output-container <?php echo $foundPatient ? ($row['diagnosis'] == 1 ? 'malignant' : 'benign') : ''; ?>">
    <h1>Diagnosztikai eredmények</h1>
    <div class="result diagnosis">
        <p>Lekérdezett páciens személyi száma: <?php echo htmlspecialchars($szsz); ?></p>
        <p>Diagnózis: <?php echo htmlspecialchars($diagnosis); ?></p>
        <p>Diagnózis valószínűsége: <?php echo htmlspecialchars($probability); ?></p>
    </div>

    <?php if (isset($foundPatient) && $foundPatient): ?>
        <div class="scroll-container">
            <div class="data-section">
                <div class="section-title">Páciens részletes adatai</div>
                <table class="data-table">
                    <tr>
                        <th>Érték típusa</th>
                        <th>Mean</th>
                        <th>SE</th>
                        <th>Worst</th>
                    </tr>
                    <tr>
                        <td>Radius</td>
                        <td><?php echo number_format($row['radius_mean'], 6); ?></td>
                        <td><?php echo number_format($row['radius_se'], 6); ?></td>
                        <td><?php echo number_format($row['radius_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Texture</td>
                        <td><?php echo number_format($row['texture_mean'], 6); ?></td>
                        <td><?php echo number_format($row['texture_se'], 6); ?></td>
                        <td><?php echo number_format($row['texture_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Perimeter</td>
                        <td><?php echo number_format($row['perimeter_mean'], 6); ?></td>
                        <td><?php echo number_format($row['perimeter_se'], 6); ?></td>
                        <td><?php echo number_format($row['perimeter_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Area</td>
                        <td><?php echo number_format($row['area_mean'], 6); ?></td>
                        <td><?php echo number_format($row['area_se'], 6); ?></td>
                        <td><?php echo number_format($row['area_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Smoothness</td>
                        <td><?php echo number_format($row['smoothness_mean'], 6); ?></td>
                        <td><?php echo number_format($row['smoothness_se'], 6); ?></td>
                        <td><?php echo number_format($row['smoothness_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Compactness</td>
                        <td><?php echo number_format($row['compactness_mean'], 6); ?></td>
                        <td><?php echo number_format($row['compactness_se'], 6); ?></td>
                        <td><?php echo number_format($row['compactness_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Concavity</td>
                        <td><?php echo number_format($row['concavity_mean'], 6); ?></td>
                        <td><?php echo number_format($row['concavity_se'], 6); ?></td>
                        <td><?php echo number_format($row['concavity_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Concave Points</td>
                        <td><?php echo number_format($row['concave_points_mean'], 6); ?></td>
                        <td><?php echo number_format($row['concave_points_se'], 6); ?></td>
                        <td><?php echo number_format($row['concave_points_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Symmetry</td>
                        <td><?php echo number_format($row['symmetry_mean'], 6); ?></td>
                        <td><?php echo number_format($row['symmetry_se'], 6); ?></td>
                        <td><?php echo number_format($row['symmetry_worst'], 6); ?></td>
                    </tr>
                    <tr>
                        <td>Fractal Dimension</td>
                        <td><?php echo number_format($row['fractal_dimension_mean'], 6); ?></td>
                        <td><?php echo number_format($row['fractal_dimension_se'], 6); ?></td>
                        <td><?php echo number_format($row['fractal_dimension_worst'], 6); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="button-container">
        <a href="query.html" class="b-btn">⮘ Vissza</a>
        <a href="index.html" class="g-btn">Kezdőlap</a>
    </div>
</div>
</body>
</html>
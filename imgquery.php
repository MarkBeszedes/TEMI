<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $szsz = isset($_POST['szsz']) ? $_POST['szsz'] : '';

    if (empty($szsz)) {
        echo "HIBA: Nincs személyi szám megadva.";
        exit;
    }

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "temi");

    if ($conn->connect_error) {
        die("Database connection failed: 404" . $conn->connect_error);
    }

    // Query for image analysis results
    $stmt = $conn->prepare("SELECT * FROM dacdimg WHERE szsz = ? ORDER BY timestamp DESC");
    $stmt->bind_param("s", $szsz);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start HTML output
    ?>
    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="icon.png">
        <title>Lekérdezési Eredmények</title>
        <link rel="stylesheet" href="kimenetstyle.css">
    </head>
    <body>
    <div class="output-container">
        <h1>Képelemzés Eredménye</h1>

        <?php if ($result->num_rows > 0) : ?>
            <div class="scroll-container">
                <?php while ($row = $result->fetch_assoc()) :
                    $diagnosis_type = $row['diagnosis'] == 1 ? 'malignant' : 'benign';
                    $diagnosis_text = $row['diagnosis'] == 1 ? 'Rosszindulatu' : 'Joindulatu';
                    $file_name = basename($row['image_path']);
                    ?>
                    <div class="result-details <?php echo $diagnosis_type; ?>">
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Elemzett Kép" class="analyzed-image">

                        <div class="result diagnosis">
                            <p>Személyi szám: <?php echo htmlspecialchars($szsz); ?></p>
                            <p>Diagnózis: <?php echo $diagnosis_text; ?></p>
                            <p>Fájl neve: <?php echo htmlspecialchars($file_name); ?></p>
                        </div>

                        <div class="result metric">
                            <p>Diagnózis pontossága:</p>
                            <div class="probability-bar">
                                <div class="probability-fill" style="width: <?php echo $row['probability']; ?>%"></div>
                            </div>
                            <p><?php echo number_format($row['probability'], 2); ?>%</p>
                        </div>

                        <div class="result metric">
                            <p>Hibalehetőség:</p>
                            <div class="probability-bar">
                                <div class="probability-fill" style="width: <?php echo (100 - $row['probability']); ?>%"></div>
                            </div>
                            <p><?php echo number_format((100 - $row['probability']), 2); ?>%</p>
                        </div>

                        <p class="txt">Vizsgálat ideje: <?php echo $row['timestamp']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="result">
                <p>Nincs találat a megadott személyi számmal.</p>
            </div>
        <?php endif; ?>

        <div class="button-container">
            <a href="imgquery.html" class="b-btn">⮘ Új lekérdezés</a>
            <a href="index.html" class="g-btn">Kezdőlap</a>
        </div>
    </div>
    </body>
    </html>
    <?php

    $stmt->close();
    $conn->close();
}
?>
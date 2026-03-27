<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="icon.png">
    <title>TEMI-Elemzési Eredmények</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Table specific styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(15, 77, 146, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .data-table th {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: bold;
            color: #d4f1f9;
            text-shadow: 0 0 5px #42a5f5;
        }

        .data-table tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Button styles */
        .action-btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .analyze-btn {
            background-color: #4caf50;
            box-shadow: 0 2px 5px rgba(76, 175, 80, 0.5);
        }

        .analyze-btn:hover {
            background-color: #43a047;
            box-shadow: 0 0 10px #69f0ae;
            transform: scale(0.98);
        }

        .delete-btn {
            background-color: #d32f2f;
            box-shadow: 0 2px 5px rgba(211, 47, 47, 0.5);
        }

        .delete-btn:hover {
            background-color: #c62828;
            box-shadow: 0 0 10px #ff5252;
            transform: scale(0.98);
        }

        /* Container styling */
        .content-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        /* Status indicators */
        .status-benign {
            background-color: rgba(76, 175, 80, 0.2);
            color: #69f0ae;
            padding: 5px 10px;
            border-radius: 50px;
            border: 1px solid #4caf50;
            font-weight: bold;
        }

        .status-malignant {
            background-color: rgba(211, 47, 47, 0.2);
            color: #ff5252;
            padding: 5px 10px;
            border-radius: 50px;
            border: 1px solid #d32f2f;
            font-weight: bold;
        }

        /* No data message */
        .no-data {
            text-align: center;
            padding: 30px;
            color: white;
            font-size: 1.2rem;
        }

        /* Form containers */
        .action-form {
            display: inline;
        }

        /* Alternating row colors */
        .data-table tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Filter section */
        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label {
            color: white;
            font-weight: bold;
        }

        .filter-select {
            padding: 8px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #42a5f5;
        }

        /* Table with rotated attributes */
        .attribute-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(15, 77, 146, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .attribute-table th {
            width: 200px;
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: bold;
            color: #d4f1f9;
            text-shadow: 0 0 5px #42a5f5;
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .attribute-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
</head>
<body>
<h1>Elemzési Eredmények</h1>
<div class="button-container">
    <a href="index.html" class="btn"> ⮘ Vissza a kezdőlapra</a>
</div>
<div class="content-container">
    <?php
    // Start session
    session_start();

    // Database connection
    $conn = new mysqli("localhost", "root", "", "temi");

    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    // Get filter parameters
    $filter_diagnosis = isset($_GET['diagnosis']) ? $_GET['diagnosis'] : 'all';

    // Build the SQL query with filter
    $query = "SELECT * FROM data";
    if ($filter_diagnosis != 'all') {
        $query .= " WHERE diagnosis = " . ($filter_diagnosis == 'malignant' ? "1" : "0");
    }
    $query .= " ORDER BY id DESC";

    $result = $conn->query($query);

    // Show filter controls
    echo '<div class="filter-section">';
    echo '<div class="filter-controls">';
    echo '<span class="filter-label">Szűrés diagnózis szerint:</span>';
    echo '<form method="get" action="">';
    echo '<select name="diagnosis" class="filter-select" onchange="this.form.submit()">';
    echo '<option value="all"' . ($filter_diagnosis == 'all' ? ' selected' : '') . '>Összes</option>';
    echo '<option value="benign"' . ($filter_diagnosis == 'benign' ? ' selected' : '') . '>Jóindulatú</option>';
    echo '<option value="malignant"' . ($filter_diagnosis == 'malignant' ? ' selected' : '') . '>Rosszindulatú</option>';
    echo '</select>';
    echo '</form>';
    echo '</div>';
    echo '<div>';
    echo '<a href="web_analyse.php" class="action-btn analyze-btn">Vissza az elemzéshez</a>';
    echo '</div>';
    echo '</div>';

    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo "<table class='data-table'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>SZSZ</th>";
        echo "<th>Dátum</th>";
        echo "<th>Diagnózis</th>";
        echo "<th>Műveletek</th>";
        echo "</tr>";

        // Fetch and display data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['szsz']) . "</td>";

            // Format date (assuming there's a date field, if not, you can remove this)
            $date = isset($row['date']) ? $row['date'] : date('Y-m-d');
            echo "<td>" . htmlspecialchars($date) . "</td>";

            // Format diagnosis
            $diagnosis = $row['diagnosis'] == 1 ? 'Rosszindulatú' : 'Jóindulatú';
            $diagnosis_class = $row['diagnosis'] == 1 ? 'status-malignant' : 'status-benign';
            echo "<td><span class='" . $diagnosis_class . "'>" . $diagnosis . "</span></td>";

            // Actions
            echo "<td>";
            echo "<a href='view_analysis.php?id=" . $row['id'] . "' class='action-btn analyze-btn'>Részletek</a>";
            echo "<form method='post' action='delete_temp.php' class='action-form' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
            echo "<button type='submit' class='action-btn delete-btn' onclick='return confirm(\"Biztosan törölni szeretné ezt az elemzést?\");'>Törlés</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo '</div>';
    } else {
        echo "<div class='no-data'>Nincs elérhető elemzési eredmény.</div>";
    }

    $conn->close();
    ?>
</div>
</body>
</html>
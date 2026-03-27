<?php
// Start session at the very beginning of the file
session_start();

// Display messages if set
$message = '';
if (isset($_SESSION['batch_message'])) {
    $message = '<div class="message">' . $_SESSION['batch_message'] . '</div>';
    unset($_SESSION['batch_message']);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.png">
    <title>TEMI-Elemzés</title>
    <style>
        /* Combined and improved CSS */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('best.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            min-height: 100vh;
        }

        .container {
            width: 95%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 2.2em;
            color: #d4f1f9;
            text-shadow: 0 0 10px #42a5f5, 0 0 20px #42a5f5, 0 0 30px #42a5f5;
            margin-bottom: 20px;
            text-align: left;
            padding-left: 20px;
        }

        .message {
            background-color: rgba(66, 165, 245, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
            text-align: left;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #0d47a1;
            color: white;
            border-radius: 30px;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(13, 71, 161, 0.5);
            margin: 10px 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #1565c0;
            box-shadow: 0 0 10px #42a5f5, 0 0 20px #42a5f5;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .button-container {
            padding: 0 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-container {
            background: rgba(15, 77, 146, 0.75);
            border-radius: 10px;
            padding: 20px;
            margin: 0 20px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .a-btn {
            background-color: #4CAF50;
            color: white;
        }

        .a-btn:hover {
            background-color: #45a049;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.7);
        }

        .a-btn:disabled {
            background-color: #cccccc;
            color: #888888;
            cursor: not-allowed;
        }

        .s-btn {
            background-color: #2196F3;
            color: white;
        }

        .s-btn:hover {
            background-color: #0b7dda;
            box-shadow: 0 0 10px rgba(33, 150, 243, 0.7);
        }

        .d-btn {
            background-color: #f44336;
            color: white;
        }

        .d-btn:hover {
            background-color: #d32f2f;
            box-shadow: 0 0 10px rgba(244, 67, 54, 0.7);
        }

        .g-btn {
            background-color: #ff9800;
            color: white;
        }

        .g-btn:hover {
            background-color: #e68a00;
            box-shadow: 0 0 10px rgba(255, 152, 0, 0.7);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            min-width: 1000px;
        }

        .data-table th {
            background-color: rgba(13, 71, 161, 0.8);
            color: white;
            padding: 12px 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
            border: 1px solid #0a5cb9;
        }

        .data-table tr {
            background-color: rgba(255, 255, 255, 0.8);
            transition: background-color 0.3s;
        }

        .data-table tr:nth-child(even) {
            background-color: rgba(240, 240, 240, 0.8);
        }

        .data-table tr:hover {
            background-color: rgba(224, 242, 255, 0.9);
        }

        .data-table td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .checkbox-column {
            width: 30px;
            text-align: center;
        }

        .checkbox-column input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .action-column {
            width: 100px;
        }

        .operations-column {
            vertical-align: top;
        }

        .operations-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .operations-container button {
            width: 100%;
            padding: 6px 10px;
            font-size: 0.85rem;
        }

        .diagnosis-benign {
            color: green;
            font-weight: bold;
        }

        .diagnosis-malignant {
            color: red;
            font-weight: bold;
        }

        /* Stats section */
        .stats-container {
            background: rgba(15, 77, 146, 0.6);
            border-radius: 10px;
            padding: 15px;
            margin: 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .stat-box {
            background: rgba(33, 150, 243, 0.3);
            border-radius: 8px;
            padding: 10px 15px;
            margin: 5px;
            min-width: 150px;
            flex: 1;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            margin: 5px 0;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        /* Responsive styles */
        @media screen and (max-width: 768px) {
            h1 {
                font-size: 1.8em;
                text-align: center;
                padding-left: 0;
            }

            .button-container {
                text-align: center;
                padding: 0;
                flex-direction: column;
            }

            .form-container {
                margin: 0;
                padding: 10px;
            }

            .button-group {
                justify-content: center;
            }

            button {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .data-table {
                min-width: 650px;
            }

            .data-table th, .data-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }

            .operations-container button {
                padding: 5px 8px;
                font-size: 0.8rem;
            }

            .stats-container {
                flex-direction: column;
                gap: 10px;
            }

            .stat-box {
                width: 100%;
            }
        }

        @media (pointer: coarse) {
            input[type="checkbox"], button {
                min-height: 44px;
                min-width: 44px;
            }
        }
    </style>
    <script>
        // Function for real-time analysis
        function analyzeRecord(recordId) {
            // Show loading indicator
            const diagnosisCell = document.getElementById(`diagnosis_${recordId}`);
            if (diagnosisCell) {
                diagnosisCell.innerHTML = '<span style="color:#2196F3">Analyzing...</span>';
            }

            // Get all form inputs for this record
            const formData = {};
            document.querySelectorAll(`input[name^="record_${recordId}_"]`).forEach(input => {
                const fieldName = input.name.replace(`record_${recordId}_`, '');
                formData[fieldName] = input.value;
            });

            // Send data to server for analysis
            fetch('real_time_analyzis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.json())
                .then(data => {
                    // Update the diagnosis cell
                    if (diagnosisCell) {
                        if (data.diagnosis === 1) {
                            diagnosisCell.innerHTML = '<span class="diagnosis-malignant">Malignant</span>';
                            diagnosisCell.setAttribute('data-value', '1');
                        } else {
                            diagnosisCell.innerHTML = '<span class="diagnosis-benign">Benign</span>';
                            diagnosisCell.setAttribute('data-value', '0');
                        }

                        // Show analysis details
                        diagnosisCell.title = `Accuracy: ${data.accuracy}%, Error: ${data.error}%`;

                        // Update probability cell
                        const probabilityCell = document.getElementById(`probability_${recordId}`);
                        if (probabilityCell) {
                            probabilityCell.textContent = `${data.accuracy.toFixed(2)}%`;
                        }

                        // Enable the save button
                        document.getElementById(`save_btn_${recordId}`).disabled = false;
                    }

                    // Update stats counters
                    updateStats();
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (diagnosisCell) {
                        diagnosisCell.innerHTML = '<span style="color:red">Error</span>';
                    }
                    alert('Analysis failed. Please try again.');
                });
        }

        // Function to check/uncheck all checkboxes
        function toggleAllCheckboxes(source) {
            const checkboxes = document.getElementsByName('selected_ids[]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        // Function to analyze all records via JavaScript
        function analyzeAllRecords() {
            const diagnosisCells = document.querySelectorAll('[id^="diagnosis_"]');
            diagnosisCells.forEach(cell => {
                const recordId = cell.id.split('_')[1];
                analyzeRecord(recordId);
            });
        }

        // Function to analyze selected records
        function analyzeSelectedRecords() {
            const checkboxes = document.getElementsByName('selected_ids[]');
            let hasSelection = false;

            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    hasSelection = true;
                    const recordId = checkboxes[i].value;
                    analyzeRecord(recordId);
                }
            }

            if (!hasSelection) {
                alert('Kérjük, jelöljön ki legalább egy esetetot az elemzéshez!');
            }

            // Prevent form submission
            return false;
        }

        // function to save records
        function saveRecord(recordId) {
            // Get the current diagnosis value and probability from the cells
            const diagnosisCell = document.getElementById(`diagnosis_${recordId}`);
            const probabilityCell = document.getElementById(`probability_${recordId}`);

            // Set the hidden input values for the form submission
            const diagnosisValue = diagnosisCell.getAttribute('data-value');
            const probabilityValue = probabilityCell.textContent.replace('%', '');

            document.getElementById(`diagnosis_value_${recordId}`).value = diagnosisValue;
            document.getElementById(`probability_value_${recordId}`).value = probabilityValue;

            // Submit the form
            document.getElementById(`save_form_${recordId}`).submit();
        }

        // Function to update statistics
        function updateStats() {
            let totalAnalyzed = 0;
            let totalBenign = 0;
            let totalMalignant = 0;

            const diagnosisCells = document.querySelectorAll('[id^="diagnosis_"]');
            diagnosisCells.forEach(cell => {
                const value = cell.getAttribute('data-value');
                if (value !== '') {
                    totalAnalyzed++;
                    if (value === '1') {
                        totalMalignant++;
                    } else if (value === '0') {
                        totalBenign++;
                    }
                }
            });

            document.getElementById('total-records').textContent = Math.floor(diagnosisCells.length / 2);
            document.getElementById('analyzed-records').textContent = Math.floor(totalAnalyzed / 2);
            document.getElementById('benign-count').textContent = totalBenign;
            document.getElementById('malignant-count').textContent = totalMalignant;

            // Calculate and update percentages
            const benignPercent = totalAnalyzed > 0 ? ((totalBenign / totalAnalyzed) * 100).toFixed(1) : '0';
            const malignantPercent = totalAnalyzed > 0 ? ((totalMalignant / totalAnalyzed) * 100).toFixed(1) : '0';

            document.getElementById('benign-percent').textContent = `${benignPercent*2}%`;
            document.getElementById('malignant-percent').textContent = `${malignantPercent*2}%`;
        }
    </script>
</head>
<body>
<div class="container">
    <?php
    // Display session message if it exists
    if (!empty($message)) {
        echo $message;
    }
    ?>

    <h1>Adatok elemzése</h1>

    <div class="button-container">
        <a href="index.html" class="btn">⮘ Vissza a kezdőlapra</a>
        <a href="webin.html" class="btn">Új esettanulmány felvétele</a>
    </div>

    <!-- Stats dashboard -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number" id="total-records">0</div>
            <div class="stat-label">Összes eset</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="analyzed-records">0</div>
            <div class="stat-label">Elemzett eset</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="benign-count">0</div>
            <div class="stat-label">Jóindulatú esetszám</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="benign-percent">0%</div>
            <div class="stat-label">Jóindulatú esetek (%)</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="malignant-count">0</div>
            <div class="stat-label">Rosszinduatú esetszám</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="malignant-percent">0%</div>
            <div class="stat-label">Rosszindulatú esetek (%)</div>
        </div>
    </div>

    <div class="form-container">
        <form method="post" id="batch_form">
            <div class="button-group">
                <button type="button" onclick="analyzeAllRecords()" class="a-btn">Összes elemzése</button>
                <button type="button" onclick="return analyzeSelectedRecords()" class="a-btn">Kijelöltek elemzése</button>
                <button type="submit" name="action_type" value="save_all_analyzed" class="s-btn" formaction="batch_save.php">Összes eset Mentése</button>
                <button type="submit" name="action_type" value="delete_all" class="d-btn" formaction="batch_delete.php" onclick="return confirm('Biztosan törölni szeretné az összes esetet?');">Összes törlése</button>
                <button type="submit" name="action_type" value="delete_selected" class="d-btn" formaction="batch_delete.php" onclick="return confirm('Biztosan törölni szeretné a kijelölt eseteket?');">Kijelöltek törlése</button>
            </div>

            <div style="overflow-x: auto;">
                <?php
                // Database connection
                $conn = new mysqli("localhost", "root", "", "temi");

                if ($conn->connect_error) {
                    die("Kapcsolódási hiba: " . $conn->connect_error);
                }

                // Fetch all records from tempdata
                $query = "SELECT * FROM tempdata";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<table class='data-table'>";
                    echo "<tr>";
                    echo "<th class='checkbox-column'><input type='checkbox' onClick='toggleAllCheckboxes(this)'></th>";

                    // Rearranged columns: Műveletek, diagnózis, probability, then the rest
                    echo "<th class='action-column'>Műveletek</th>";
                    echo "<th>Diagnózis</th>";
                    echo "<th>Probability</th>";
                    echo "<th>SZSZ</th>";

                    // Get remaining field names, excluding id, szsz, and diagnosis
                    $fields = $result->fetch_fields();
                    foreach ($fields as $field) {
                        if ($field->name !== 'id' && $field->name !== 'szsz' && $field->name !== 'diagnosis') {
                            echo "<th>" . htmlspecialchars($field->name) . "</th>";
                        }
                    }

                    echo "</tr>";

                    // Fetch and display data
                    while ($row = $result->fetch_assoc()) {
                        $record_id = $row['id'];
                        echo "<tr>";
                        echo "<td class='checkbox-column'><input type='checkbox' name='selected_ids[]' value='" . $record_id . "'></td>";

                        // Operations cell (moved to first column)
                        echo "<td class='operations-column'>";
                        echo "<div class='operations-container'>";
                        // Analysis button
                        echo "<button type='button' class='g-btn' onclick='analyzeRecord(" . $record_id . ")'>Elemzés</button>";

                        // Add to database button (initially disabled)
                        echo "<button type='button' id='save_btn_" . $record_id . "' class='a-btn' disabled onclick=\"saveRecord(" . $record_id . ");\">Mentés</button>";
                        echo "<form id='save_form_" . $record_id . "' method='post' action='process_analysis.php' style='display:none;'>";
                        foreach ($row as $key => $value) {
                            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
                        }
                        echo "<input type='hidden' name='record_id' value='" . $record_id . "'>";
                        echo "<input type='hidden' id='diagnosis_value_" . $record_id . "' name='diagnosis_value' value=''>";
                        echo "<input type='hidden' id='probability_value_" . $record_id . "' name='probability_value' value=''>";
                        echo "</form>";

                        // Delete button
                        echo "<button type='button' class='d-btn' onclick=\"if(confirm('Biztosan törölni szeretné ezt a rekordot?')){document.getElementById('delete_form_" . $record_id . "').submit();}\">Törlés</button>";
                        echo "<form id='delete_form_" . $record_id . "' method='post' action='delete_temp.php' style='display:none;'>";
                        echo "<input type='hidden' name='id' value='" . $record_id . "'>";
                        echo "</form>";
                        echo "</div>";
                        echo "</td>";

                        // Diagnosis cell (empty initially)
                        echo "<td id='diagnosis_" . $record_id . "' data-value=''></td>";

                        // New probability cell
                        echo "<td id='probability_" . $record_id . "'></td>";

                        // SZSZ value
                        echo "<td>" . htmlspecialchars($row['szsz']) . "</td>";

                        // Store all field values in hidden inputs for JavaScript access
                        foreach ($row as $key => $value) {
                            if ($key !== 'id' && $key !== 'szsz' && $key !== 'diagnosis') {
                                echo "<td>" . htmlspecialchars($value) . "</td>";
                                echo "<input type='hidden' name='record_" . $record_id . "_" . $key . "' value='" . htmlspecialchars($value) . "'>";
                            }
                        }

                        // Also include SZSZ in hidden inputs
                        echo "<input type='hidden' name='record_" . $record_id . "_szsz' value='" . htmlspecialchars($row['szsz']) . "'>";

                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div style='text-align:center; padding:30px; color:white;'>Nincs elérhető adat elemzésre.</div>";
                }

                $conn->close();
                ?>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to update the "save" button's status based on diagnosis
    document.addEventListener('DOMContentLoaded', function() {
        // Check if any diagnoses are already available
        const diagnosisCells = document.querySelectorAll('[id^="diagnosis_"]');
        diagnosisCells.forEach(cell => {
            const recordId = cell.id.split('_')[1];
            if (cell.getAttribute('data-value') !== '') {
                document.getElementById(`save_btn_${recordId}`).disabled = false;
            }
        });

        // Initialize stats
        updateStats();
    });
</script>
</body>
</html>
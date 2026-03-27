    <?php
    // batch_save.php - Handle batch save operations after analysis
    session_start();

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "temi");
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    // Get all records from tempdata
    $query = "SELECT * FROM tempdata";
    $result = $conn->query($query);
    $saved_count = 0;

    if ($result->num_rows > 0) {
        // Python path for getting diagnoses
        $python_path = 'C:\\Users\\w10\\ai_env\\Scripts\\python.exe';

        while ($row = $result->fetch_assoc()) {
            // Skip id field for Python script
            $input_data = $row;
            unset($input_data['id']);
            $record_id = $row['id'];

            // Call Python script for analysis
            $input_json = json_encode($input_data);
            $command = '"' . $python_path . '" "' . __DIR__ . '\\run_model.py" "' . addslashes($input_json) . '" 2>&1';
            $output = shell_exec($command);

            // Process Python output
            $lines = explode("\n", $output);
            $diagnosis_value = 0; // Default to benign
            $probability = 0.0;   // Default probability

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

            // Prepare statement for database insertion
            $stmt = $conn->prepare("INSERT INTO data (szsz, radius_mean, texture_mean, perimeter_mean, area_mean, 
        smoothness_mean, compactness_mean, concavity_mean, concave_points_mean, symmetry_mean, 
        fractal_dimension_mean, radius_se, texture_se, perimeter_se, area_se, 
        smoothness_se, compactness_se, concavity_se, concave_points_se, symmetry_se, 
        fractal_dimension_se, radius_worst, texture_worst, perimeter_worst, area_worst, 
        smoothness_worst, compactness_worst, concavity_worst, concave_points_worst, symmetry_worst, 
        fractal_dimension_worst, diagnosis, probability) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

    // Bind parameters - ensure type string matches number of parameters
            $stmt->bind_param(
                "sddddddddddddddddddddddddddddddid", // Módosított karakterlánc
                $row["szsz"],
                $row["radius_mean"],
                $row["texture_mean"],
                $row["perimeter_mean"],
                $row["area_mean"],
                $row["smoothness_mean"],
                $row["compactness_mean"],
                $row["concavity_mean"],
                $row["concave_points_mean"],
                $row["symmetry_mean"],
                $row["fractal_dimension_mean"],
                $row["radius_se"],
                $row["texture_se"],
                $row["perimeter_se"],
                $row["area_se"],
                $row["smoothness_se"],
                $row["compactness_se"],
                $row["concavity_se"],
                $row["concave_points_se"],
                $row["symmetry_se"],
                $row["fractal_dimension_se"],
                $row["radius_worst"],
                $row["texture_worst"],
                $row["perimeter_worst"],
                $row["area_worst"],
                $row["smoothness_worst"],
                $row["compactness_worst"],
                $row["concavity_worst"],
                $row["concave_points_worst"],
                $row["symmetry_worst"],
                $row["fractal_dimension_worst"],
                $diagnosis_value,   // Diagnózis (0 vagy 1) -> Integer ("i")
                $probability        // Valószínűség (%) -> Double ("d")
            );

            if ($stmt->execute()) {
                $saved_count++;
            } else {
                error_log("Error saving record: " . $stmt->error);
            }

            $stmt->close();
        }

        // Delete all records from tempdata after successful transfer
        if ($saved_count > 0) {
            $conn->query("TRUNCATE TABLE tempdata");
        }
    }

    $conn->close();

    // Set session notification
    $_SESSION['batch_message'] = "$saved_count eset sikeresen elemezve és mentve.";

    // Redirect back
    header("Location: web_analyse.php");
    exit();
    ?>
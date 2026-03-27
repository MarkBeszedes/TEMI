<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

//session to save data
session_start();

//if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //upload directory
    $upload_dir = "uploads/";

    // create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $szsz = $_POST['szsz'];
    $should_save = isset($_POST['save_to_db']) ? 1 : 0;

    // check file
    if (isset($_FILES["tumor_image"]) && $_FILES["tumor_image"]["error"] == 0) {
        $allowed_types = ["image/jpeg", "image/jpg", "image/png"];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_type = $_FILES["tumor_image"]["type"];
        $file_size = $_FILES["tumor_image"]["size"];

        if (!in_array($file_type, $allowed_types)) {
            echo "Error: Only JPG and PNG files are allowed.";
            exit;
        }
        /*
        if ($file_size > $max_size) {
            echo "Error: File size exceeds the maximum limit of 5MB.";
            exit;
        }*/

        //unique filename
        $file_name = uniqid() . '_' . basename($_FILES["tumor_image"]["name"]);
        $file_path = $upload_dir . $file_name;

        // move img to target location
        if (move_uploaded_file($_FILES["tumor_image"]["tmp_name"], $file_path)) {
            // if file uploaded, call py script
            $python_path = 'C:\\Users\\w10\\ai_env\\Scripts\\python.exe';
            $command = '"' . $python_path . '" "' . __DIR__ . '\\analyze_image.py" "' . $file_path . '" 2>&1';

            $output = shell_exec($command);

            // ch if output is null
            if ($output === null) {
                echo "Error: Python script execution failed.";
                exit;
            }

            // extract JSON output
            $lines = explode("\n", $output);
            $json_line = '';

            // search for valid JSON
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (!empty($line)) {
                    // Try to parse as JSON
                    $test_json = json_decode($line, true);
                    if ($test_json !== null && json_last_error() === JSON_ERROR_NONE) {
                        $json_line = $line;
                        break;
                    }
                }
            }

            if (empty($json_line)) {
                echo "Error: Could not find valid JSON in Python output.";
                echo "<pre>Raw output: " . htmlspecialchars($output) . "</pre>";
                exit;
            }

            // parse valid JSON line
            $result = json_decode($json_line, true);

            // double ch if result is valid
            if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "Error parsing Python output: " . json_last_error_msg();
                echo "<pre>Raw output: " . htmlspecialchars($output) . "</pre>";
                exit;
            }

            // ch if result contains keys
            if (!isset($result['diagnosis']) || !isset($result['probability'])) {
                echo "Error: Python script returned incomplete data.";
                echo "<pre>Returned data: " . htmlspecialchars($json_line) . "</pre>";
                exit;
            }

            $last_inserted_id = null;
            if ($should_save) {
                $conn = new mysqli("localhost", "root", "", "temi");

                if ($conn->connect_error) {
                    die("Database connection failed: " . $conn->connect_error);
                }

                // insert to db
                $stmt = $conn->prepare("INSERT INTO dacdimg (szsz, image_path, diagnosis, probability, timestamp) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssdd", $szsz, $file_path, $result['diagnosis'], $result['probability']);

                if ($stmt->execute()) {
                    $last_inserted_id = $conn->insert_id;
                    $_SESSION['last_inserted_id'] = $last_inserted_id;
                } else {
                    echo "Error saving to database: " . $stmt->error;
                }

                $stmt->close();
                $conn->close();
            }

            // save results to session for display
            $_SESSION['image_analysis_result'] = [
                'file_path' => $file_path,
                'diagnosis' => $result['diagnosis'],
                'diagnosis_text' => $result['diagnosis'] == 1 ? 'Rosszindulatu' : 'Joindulatu',
                'probability' => $result['probability'],
                'error_rate' => 100 - $result['probability'],
                'id' => $last_inserted_id,
                'szsz' => $szsz,
                'should_save' => $should_save
            ];

            // b to result pg
            header("Location: image_result.php");
            exit;
        } else {
            echo "Error: There was an error uploading your file.";
        }
    } else {
        echo "Error: " . $_FILES["tumor_image"]["error"];
    }
}
?>
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

    // check file
    if (isset($_FILES["tumor_image"]) && $_FILES["tumor_image"]["error"] == 0) {
        $allowed_types = ["image/jpeg", "image/jpg", "image/png"];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_type = $_FILES["tumor_image"]["type"];
        $file_size = $_FILES["tumor_image"]["size"];

        if (!in_array($file_type, $allowed_types)) {
            echo "Hiba: Csak JPG és PNG fájlok támogatottak.";
            exit;
        }

        if ($file_size > $max_size) {
            echo "Hiba: A fájl mérete túllépi a maximális 5MB méretet.";
            exit;
        }

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
                echo "Hiba: Python script végrehajtás sikertelen.";
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
                echo "Hiba: Nem található érvényes JSON a Python kimenetben.";
                echo "<pre>Nyers kimenet: " . htmlspecialchars($output) . "</pre>";
                exit;
            }

            // parse valid JSON line
            $result = json_decode($json_line, true);

            // double ch if result is valid
            if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "Hiba a Python kimenet feldolgozása során: " . json_last_error_msg();
                echo "<pre>Nyers kimenet: " . htmlspecialchars($output) . "</pre>";
                exit;
            }

            // ch if result contains keys
            if (!isset($result['diagnosis']) || !isset($result['probability'])) {
                echo "Hiba: A Python script hiányos adatot adott vissza.";
                echo "<pre>Visszaadott adat: " . htmlspecialchars($json_line) . "</pre>";
                exit;
            }

            // save results to session for display
            $_SESSION['image_analysis_result'] = [
                'file_path' => $file_path,
                'diagnosis' => $result['diagnosis'],
                'diagnosis_text' => $result['diagnosis'] == 1 ? 'Rosszindulatu' : 'Joindulatu',
                'probability' => $result['probability'],
                'error_rate' => 100 - $result['probability'],
                'szsz' => $szsz,
                'should_save' => false // We don't save to database from this script
            ];

            // redirect to result page
            header("Location: image_result.php");
            exit;
        } else {
            echo "Hiba: Probléma történt a fájl feltöltése közben.";
        }
    } else {
        echo "Hiba: " . $_FILES["tumor_image"]["error"];
    }
}
?>
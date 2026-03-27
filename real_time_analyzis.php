<?php
// real_time_analysis.php
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Python path
$python_path = 'C:\\Users\\w10\\ai_env\\Scripts\\python.exe';

// Call Python script for analysis
$input_json = json_encode($data);
$command = '"' . $python_path . '" "' . __DIR__ . '\\run_model.py" "' . addslashes($input_json) . '" 2>&1';
$output = shell_exec($command);

// Process Python output
$lines = explode("\n", $output);
$analysis_results = [
    'diagnosis' => 0,
    'accuracy' => 0,
    'error' => 0,
    'raw_output' => $output
];

// In real_time_analyzis.php, modify the output processing:
foreach ($lines as $line) {
    if (strpos($line, 'Diagnosztikai vegeredmeny: Rosszindulatu') !== false) {
        $analysis_results['diagnosis'] = 1;
    } else if (strpos($line, 'Diagnosztikai vegeredmeny: Joindulatu') !== false) {
        $analysis_results['diagnosis'] = 0;
    } else if (strpos($line, 'Diagnosztika pontossaga:') !== false) {
        preg_match('/(\d+\.\d+)%/', $line, $matches);
        if (isset($matches[1])) {
            $analysis_results['accuracy'] = floatval($matches[1]);
        }
    } else if (strpos($line, 'Hibalehetoseg:') !== false) {
        preg_match('/(\d+\.\d+)%/', $line, $matches);
        if (isset($matches[1])) {
            $analysis_results['error'] = floatval($matches[1]);
        }
    }
}

echo json_encode($analysis_results);
?>
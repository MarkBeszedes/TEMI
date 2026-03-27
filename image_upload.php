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
            $_SESSION['error'] = "Hiba: Csak JPG és PNG fájlok támogatottak.";
            header("Location: imagein.html");
            exit;
        }

        if ($file_size > $max_size) {
            $_SESSION['error'] = "Hiba: A fájl mérete túllépi a maximális 5MB méretet.";
            header("Location: imagein.html");
            exit;
        }

        //unique filename
        $file_name = uniqid() . '_' . basename($_FILES["tumor_image"]["name"]);
        $file_path = $upload_dir . $file_name;

        // move img to target location
        if (move_uploaded_file($_FILES["tumor_image"]["tmp_name"], $file_path)) {
            // Connect to database
            $conn = new mysqli("localhost", "root", "", "temi");

            if ($conn->connect_error) {
                $_SESSION['error'] = "Adatbázis kapcsolódási hiba: " . $conn->connect_error;
                header("Location: imagein.html");
                exit;
            }

            // Insert image info to database
            $stmt = $conn->prepare("INSERT INTO dacdimgtemp (szsz, image_path, timestamp) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $szsz, $file_path);

            if ($stmt->execute()) {
                $_SESSION['success'] = "A kép sikeresen feltöltve az adatbázisba.";
                $last_id = $conn->insert_id;
                $stmt->close();
                $conn->close();

                // Redirect to image analyzer page
                header("Location: image_analyze.php");
                exit;
            } else {
                $_SESSION['error'] = "Adatbázis hiba: " . $stmt->error;
                $stmt->close();
                $conn->close();
                header("Location: imagein.html");
                exit;
            }
        } else {
            $_SESSION['error'] = "Hiba: Probléma történt a fájl feltöltése közben.";
            header("Location: imagein.html");
            exit;
        }
    } else {
        $_SESSION['error'] = "Hiba: " . $_FILES["tumor_image"]["error"];
        header("Location: imagein.html");
        exit;
    }
}
?>
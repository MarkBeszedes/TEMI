<?php
$conn = new mysqli("localhost", "root", "","temi");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// char to utf8
$conn->set_charset("utf8");

$name = $_POST['name'];
$personalId = $_POST['personalId'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash passw
$isDoctor = isset($_POST['isDoctor']) ? 1 : 0;

// email ch
$checkEmail = $conn->prepare("SELECT email FROM usr WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if ($result->num_rows > 0) {
    echo "<script>
            alert('Ez az email cím már regisztrálva van!');
            window.location.href = 'signup.html';
          </script>";
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO usr (nev, szsz, email, passw, orvos) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $name, $personalId, $email, $password, $isDoctor);

if ($stmt->execute()) {
    echo "<script>
            alert('Sikeres regisztráció! Most már bejelentkezhet.');
            window.location.href = 'signup.html';
          </script>";
} else {
    echo "<script>
            alert('Hiba történt a regisztráció során: " . $stmt->error . "');
            window.location.href = 'signup.html';
          </script>";
}

$stmt->close();
$conn->close();
?>
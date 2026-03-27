<?php
session_start();
$conn = new mysqli("localhost", "root", "","temi");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// char to utf-8
$conn->set_charset("utf8");

$email = $_POST['loginEmail'];
$password = $_POST['loginPassword'];

// prepare, execute query
$stmt = $conn->prepare("SELECT id, nev, passw, orvos FROM usr WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // verify password
    if (password_verify($password, $row['passw'])) {
        // if passw ok
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['nev'];
        $_SESSION['is_doctor'] = $row['orvos'];

        echo "<script>
                alert('Sikeres bejelentkezés! Üdvözöljük, " . $row['nev'] . "!');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "<script>
                alert('Hibás jelszó!');
                window.location.href = 'signup.html';
              </script>";
    }
} else {
    echo "<script>
            alert('Nincs regisztrált felhasználó ezzel az email címmel!');
            window.location.href = 'signup.html';
          </script>";
}

$stmt->close();
$conn->close();
?>
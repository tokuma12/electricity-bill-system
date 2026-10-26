
<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "student_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$id = (int)($_POST["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare(
        "DELETE FROM students WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: index.php");
exit;
?>

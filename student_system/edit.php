
<?php
$conn = new mysqli("localhost", "root", "", "student_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

if ($id < 1) {
    die("Invalid student ID.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $department = trim($_POST["department"] ?? "");

    if ($fullname !== "" && $student_id !== "" && $department !== "") {
        $stmt = $conn->prepare(
            "UPDATE students
             SET fullname = ?, student_id = ?, department = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssi", $fullname, $student_id, $department, $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: index.php");
            exit;
        }

        echo "Update failed.";
        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
}

$stmt = $conn->prepare(
    "SELECT fullname, student_id, department
     FROM students WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($fullname, $student_id, $department);

if (!$stmt->fetch()) {
    die("Student not found.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
</head>
<body>
    <h1>Edit Student</h1>

    <form method="POST">
        <input type="hidden" name="id"
               value="<?= (int)$id ?>">

        <label>Full Name</label><br>
        <input type="text" name="fullname"
               value="<?= htmlspecialchars($fullname) ?>" required>
        <br><br>

        <label>Student ID</label><br>
        <input type="text" name="student_id"
               value="<?= htmlspecialchars($student_id) ?>" required>
        <br><br>

        <label>Department</label><br>
        <select name="department" required>
            <option value="CSE"
                <?= $department === "CSE" ? "selected" : "" ?>>
                Computer Science
            </option>
            <option value="IT"
                <?= $department === "IT" ? "selected" : "" ?>>
                Information Technology
            </option>
            <option value="SE"
                <?= $department === "SE" ? "selected" : "" ?>>
                Software Engineering
            </option>
        </select>
        <br><br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>

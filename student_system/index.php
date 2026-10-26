
<?php
$conn = new mysqli("localhost", "root", "", "student_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$message = "";

// Register a new student
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $department = trim($_POST["department"] ?? "");

    if (
        $fullname !== "" &&
        $student_id !== "" &&
        $department !== ""
    ) {
        $stmt = $conn->prepare(
            "INSERT INTO students
             (fullname, student_id, department)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $fullname,
            $student_id,
            $department
        );

        if ($stmt->execute()) {
            $message = "Student registered successfully!";
        } else {
            $message = "Registration failed. Please try again.";
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// Search students by name, Student ID, or department
$search = trim($_GET["search"] ?? "");
$searchStmt = null;

if ($search !== "") {
    $searchStmt = $conn->prepare(
        "SELECT id, fullname, student_id, department
         FROM students
         WHERE fullname LIKE ?
            OR student_id LIKE ?
            OR department LIKE ?
         ORDER BY id DESC"
    );

    if (!$searchStmt) {
        die("Search query could not be prepared.");
    }

    $term = "%" . $search . "%";

    $searchStmt->bind_param(
        "sss",
        $term,
        $term,
        $term
    );

    $searchStmt->execute();
    $result = $searchStmt->get_result();
} else {
    $result = $conn->query(
        "SELECT id, fullname, student_id, department
         FROM students
         ORDER BY id DESC"
    );
}

if (!$result) {
    die("Could not load student records.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaf1f8;
            margin: 0;
            padding: 30px 15px;
        }

        .card {
            background: white;
            max-width: 850px;
            margin: 0 auto 30px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px #cccccc;
        }

        h1, h2 {
            text-align: center;
            color: #1769aa;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #1769aa;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            text-align: center;
            color: #16803c;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }

        th {
            background: #1769aa;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f6fa;
        }

        /* Search form */
        .search-form {
            display: flex;
            gap: 8px;
            margin: 20px 0 10px;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            width: auto;
            margin: 0;
        }

        .search-form button {
            width: auto;
            margin: 0;
            white-space: nowrap;
        }

        .show-all {
            display: inline-block;
            margin: 10px 0 15px;
            color: #1769aa;
        }

        /* Edit and Delete */
        .actions {
            white-space: nowrap;
        }

        .edit-button {
            display: inline-block;
            background: #16803c;
            color: white;
            padding: 8px 12px;
            margin: 3px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .delete-form {
            display: inline;
        }

        .delete-button {
            width: auto;
            padding: 8px 12px;
            margin: 3px;
            background: #d93636;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #555555;
        }
    </style>
</head>

<body>

    <!-- Student Registration -->
    <div class="card">
        <h1>Student Registration</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <label for="fullname">Full Name</label>
            <input
                type="text"
                id="fullname"
                name="fullname"
                required
            >

            <label for="student_id">Student ID</label>
            <input
                type="text"
                id="student_id"
                name="student_id"
                required
            >

            <label for="department">Department</label>
            <select
                id="department"
                name="department"
                required
            >
                <option value="">Choose Department</option>
                <option value="CSE">Computer Science</option>
                <option value="IT">Information Technology</option>
                <option value="SE">Software Engineering</option>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>

    <!-- Registered Students -->
    <div class="card">
        <h2>Registered Students</h2>

        <!-- Search Form -->
        <form
            method="GET"
            action="index.php"
            class="search-form"
        >
            <input
                type="text"
                name="search"
                placeholder="Search name, Student ID, or department..."
                value="<?= htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
            >

            <button type="submit">Search</button>
        </form>

        <a href="index.php" class="show-all">
            Show All Students
        </a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Student ID</th>
                        <th>Department</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= (int)$row["id"] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row["fullname"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row["student_id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row["department"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <!-- Edit and Delete buttons -->
                                <td class="actions">
                                    <a
                                        class="edit-button"
                                        href="edit.php?id=<?= (int)$row["id"] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        class="delete-form"
                                        method="POST"
                                        action="delete.php"
                                        onsubmit="return confirm('Are you sure you want to delete this student?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row["id"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-button"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-results">
                                No students found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>

<?php
$result->free();

if ($searchStmt instanceof mysqli_stmt) {
    $searchStmt->close();
}

$conn->close();
?>

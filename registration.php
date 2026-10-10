<?php
// Initialize variables
$name = $student_id = $email = $phone = $course = $message = "";
$errors = [];
$success_msg = "";
$csv_file = "submissions.csv";

// Server-side Sanitization Helper
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check POST Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Sanitize Data
    $name       = clean_input($_POST["name"] ?? "");
    $student_id = clean_input($_POST["student_id"] ?? "");
    $email      = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
    $phone      = clean_input($_POST["phone"] ?? "");
    $course     = clean_input($_POST["course"] ?? "");
    $message    = clean_input($_POST["message"] ?? "");

    // 2. Server-side Validation
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Full Name must only contain alphabetic characters and spaces.";
    }

    if (empty($student_id)) {
        $errors[] = "Student ID is required (e.g., 25CS080).";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email format.";
    }

    if (empty($phone)) {
        $errors[] = "Contact Phone Number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 numerical digits.";
    }

    if (empty($course)) {
        $errors[] = "Please select an academic course.";
    }

    if (empty($message)) {
        $errors[] = "Query / Remarks description cannot be empty.";
    }

    // 3. Safe CSV File Storage
    if (empty($errors)) {
        $timestamp = date("Y-m-d H:i:s");
        $record = [$timestamp, $name, $student_id, $email, $phone, $course, $message];

        $file_exists = file_exists($csv_file);
        $fp = fopen($csv_file, 'a');

        if ($fp) {
            flock($fp, LOCK_EX); // Prevent race condition/file corruption
            
            // Write Header Row if file is freshly created
            if (!$file_exists) {
                fputcsv($fp, ["Timestamp", "Full Name", "Student ID", "Email", "Phone", "Course", "Message"]);
            }

            fputcsv($fp, $record);
            flock($fp, LOCK_UN);
            fclose($fp);

            $success_msg = "Registration details successfully validated and recorded in CSV format!";
            
            // Clear input variables upon success
            $name = $student_id = $email = $phone = $course = $message = "";
        } else {
            $errors[] = "Unable to access submissions.csv for writing. Check write permissions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 7 - Student Registration Form</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

    <!-- Header Section -->
    <header>
        <img src="charusat logo.jpg" alt="Charusat Logo" style="max-height: 80px; width: auto;" onerror="this.style.display='none'">
        <h1 class="brand-title">CHARUSAT Student Portal</h1>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="index.html">Home</a>
        <a href="login.html">Login</a>
        <a href="dashboard-student.html">Student Dashboard</a>
        <a href="dashboard-admin.html">Admin Dashboard</a>
        <a href="profile.html">Profile</a>
        <a href="courses.html">Courses</a>
        <a href="registration.php" class="active">Registration</a>
        <a href="results.html">Grades</a>
        <a href="announcements.html">Announcements</a>
        <a href="feedback.html">Feedback</a>
        <a href="logout.html">Logout</a>
    </nav>

    <!-- Main Content -->
    <main>
        <h2>Practical 7: Course Registration & Feedback Form</h2>

        <!-- Error Alert Message -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please resolve the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Success Alert Message -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form via POST -->
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            <div class="form-group">
                <label for="name">Student Full Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Shreyas Patel">
            </div>

            <div class="form-group">
                <label for="student_id">Student ID / Roll No:</label>
                <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" placeholder="e.g. 25CS080">
            </div>

            <div class="form-group">
                <label for="email">CHARUSAT / Personal Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. shreyas@example.com">
            </div>

            <div class="form-group">
                <label for="phone">Contact Number (10 Digits):</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="e.g. 9876543210">
            </div>

            <div class="form-group">
                <label for="course">Select Course:</label>
                <select id="course" name="course">
                    <option value="">-- Choose Course --</option>
                    <option value="B.Tech CSE" <?= ($course === "B.Tech CSE") ? "selected" : "" ?>>B.Tech Computer Science & Engineering</option>
                    <option value="B.Tech IT" <?= ($course === "B.Tech IT") ? "selected" : "" ?>>B.Tech Information Technology</option>
                    <option value="B.Tech CE" <?= ($course === "B.Tech CE") ? "selected" : "" ?>>B.Tech Computer Engineering</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Query / Remarks:</label>
                <textarea id="message" name="message" rows="3" placeholder="Enter remarks or elective choices..."><?= htmlspecialchars($message) ?></textarea>
            </div>

            <button type="submit" class="btn">Submit Registration</button>
        </form>

        <!-- Stored CSV Records Display Table (Intermediate Extension) -->
        <?php if (file_exists($csv_file)): ?>
            <h2 style="margin-top: 40px;">Stored Submissions (CSV Data View)</h2>
            <table>
                <?php
                $rows = array_map('str_getcsv', file($csv_file));
                foreach ($rows as $index => $row):
                    echo "<tr>";
                    foreach ($row as $cell) {
                        if ($index === 0) {
                            echo "<th>" . htmlspecialchars($cell) . "</th>";
                        } else {
                            echo "<td>" . htmlspecialchars($cell) . "</td>";
                        }
                    }
                    echo "</tr>";
                endforeach;
                ?>
            </table>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 CHARUSAT University. All rights reserved.</p>
    </footer>

    <!-- Shreyas Patel 25CS080 -->
</body>
</html>
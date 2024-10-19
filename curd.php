<?php
error_reporting(1);
// Database connection
$servername = "localhost"; // Change this as per your configuration
$username = "root"; // Change this as per your configuration
$password = ""; // Change this as per your configuration
$dbname = "user_management"; // Change this as per your configuration

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Class Update
if (isset($_POST['class_update'])){
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $sql = "UPDATE classes SET class_name = '$class_name' WHERE id = $class_id";

    if ($conn->query($sql) === TRUE) {
        echo "Class updated successfully.";
    } else {
        echo "Error updating class: " . $conn->error;
    }
}

// Handle Class Delete
if (isset($_POST['delete_class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $sql = "DELETE FROM classes WHERE id = $class_id";

    if ($conn->query($sql) === TRUE) {
        echo "Class deleted successfully.";
    } else {
        echo "Error deleting class: " . $conn->error;
    }
}

// Handle Section Update
if (isset($_POST['section_id']) && isset($_POST['section_name'])) {
    $section_id = $conn->real_escape_string($_POST['section_id']);
    $section_name = $conn->real_escape_string($_POST['section_name']);
    $sql = "UPDATE sections SET section_name = '$section_name' WHERE id = $section_id";

    if ($conn->query($sql) === TRUE) {
        echo "Section updated successfully.";
    } else {
        echo "Error updating section: " . $conn->error;
    }
}

// Handle Section Delete
if (isset($_POST['delete_section_id'])) {
    $section_id = $conn->real_escape_string($_POST['section_id']);
    $sql = "DELETE FROM sections WHERE id = $section_id";

    if ($conn->query($sql) === TRUE) {
        echo "Section deleted successfully.";
    } else {
        echo "Error deleting section: " . $conn->error;
    }
}

// Handle Subject Update
if (isset($_POST['subject_id']) && isset($_POST['subject_name'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $subject_name = $conn->real_escape_string($_POST['subject_name']);
    $sql = "UPDATE subjects SET subject_name = '$subject_name' WHERE id = $subject_id";

    if ($conn->query($sql) === TRUE) {
        echo "Subject updated successfully.";
    } else {
        echo "Error updating subject: " . $conn->error;
    }
}

// Handle Subject Delete
if (isset($_POST['delete_subject_id'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $sql = "DELETE FROM subjects WHERE id = $subject_id";

    if ($conn->query($sql) === TRUE) {
        echo "Subject deleted successfully.";
    } else {
        echo "Error deleting subject: " . $conn->error;
    }
}
// header('Location: dashboard.php');
// exit();
// Close the database connection
$conn->close();
?>
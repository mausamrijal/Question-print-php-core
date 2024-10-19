<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost'; // Change to your host
$db = 'user_management'; // Change to your database name
$user = 'root'; // Change to your database username
$pass = ''; // Change to your database password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

class Dashboard {
    public function showPopup($message) {
        echo '
        <div id="popup" class="popup">
            <div class="popup-content">
                <span class="close" onclick="closePopup()">&times;</span>
                <p>' . htmlspecialchars($message) . '</p>
                <button onclick="redirectToDashboard()">Go to Dashboard</button>
            </div>
        </div>

        <script>
            function closePopup() {
                document.getElementById("popup").style.display = "none";
            }

            function redirectToDashboard() {
                window.location.href = "dashboard.php";
            }

            // Display the popup
            document.getElementById("popup").style.display = "block";
        </script>

        <style>
            .popup {
                display: none; /* Hidden by default */
                position: fixed; /* Stay in place */
                z-index: 1; /* Sit on top */
                left: 0;
                top: 0;
                width: 100%; /* Full width */
                height: 100%; /* Full height */
                overflow: auto; /* Enable scroll if needed */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            }

            .popup-content {
                background-color: #fefefe;
                margin: 15% auto; /* 15% from the top and centered */
                padding: 20px;
                border: 1px solid #888;
                width: 80%; /* Could be more or less, depending on screen size */
                text-align: center;
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }

            button {
                padding: 10px 15px;
                background-color: #4CAF50; /* Green */
                color: white;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }

            button:hover {
                background-color: #45a049;
            }
        </style>
        ';
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user submitted the form with selected questions
    if (isset($_POST['question_ids'])) {
        $selected_ids = $_POST['question_ids'];
        
        // Bulk Delete Action
        if (isset($_POST['bulk_delete'])) {
            // Prepare delete query
            $id_list = implode(',', array_map('intval', $selected_ids)); // Safely convert IDs to integers
            $delete_query = "DELETE FROM questions WHERE id IN ($id_list)";
            
            if ($conn->query($delete_query)) {
           
                 $dashboard = new Dashboard();
$dashboard->showPopup("Selected questions have been deleted.");

            } else {
                echo "Error deleting questions: " . $conn->error;
            }
        }
        
        // Bulk Print Action
        if (isset($_POST['bulk_print'])) {
            // Redirect to a new page to handle printing with selected question IDs
            // You can pass the IDs via query string or a POST request
            $id_list = implode(',', $selected_ids); // Convert selected IDs into a string
            
            // Redirect to print page with the selected question IDs
            header("Location: print_questions.php?ids=" . urlencode($id_list));
            exit;
        }
    } else {
        echo "No questions were selected.";
    }
}
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'get_sections' && isset($_POST['class_id'])) {
        $class_id = $_POST['class_id'];
        $sections = $conn->query("SELECT * FROM sections WHERE class_id = $class_id");
        echo '<option value="">Select Section</option>';
        while ($section = $sections->fetch_assoc()) {
            echo '<option value="'.$section['id'].'">'.$section['section_name'].'</option>';
        }
    }

    if ($_POST['action'] == 'get_subjects' && isset($_POST['section_id'])) {
        $section_id = $_POST['section_id'];
        $subjects = $conn->query("SELECT * FROM subjects WHERE section_id = $section_id");
        echo '<option value="">Select Subject</option>';
        while ($subject = $subjects->fetch_assoc()) {
            echo '<option value="'.$subject['id'].'">'.$subject['subject_name'].'</option>';
        }
    }

    if ($_POST['action'] == 'get_groups' && isset($_POST['subject_id'])) {
        $groups = $conn->query("SELECT * FROM qgroup");
        echo '<option value="">Select Group</option>';
        while ($group = $groups->fetch_assoc()) {
            echo '<option value="'.$group['id'].'">'.$group['group_name'].'</option>';
        }
    }
    exit;
}


// If class is selected, fetch sections
if (isset($_POST['classSelect'])) {
    $class_id = $_POST['classSelect'];
    $sections = $conn->query("SELECT * FROM sections WHERE class_id = $class_id");
}

// If section is selected, fetch subjects
if (isset($_POST['sectionSelect'])) {
    $section_id = $_POST['sectionSelect'];
    $subjects = $conn->query("SELECT * FROM subjects WHERE section_id = $section_id");
}

// If subject is selected, get ready for question form
if (isset($_POST['subjectSelect'])) {
    $subject_id = $_POST['subjectSelect'];
}

if (isset($_POST['add_questions'])) {
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $subject_id = $_POST['subject_id'];
    
    $questions = $_POST['question_text'];
    $marks = $_POST['marks'];
    $group_id = $_POST['group_id'];
    // Insert each question into the database
    for ($i = 0; $i < count($questions); $i++) {
        $question_text = $questions[$i];
        $mark = $marks[$i];
        $groups_name = $group_id[$i];
        $sql = "INSERT INTO questions (question_text, marks, class_id, section_id, subject_id, group_id) 
                VALUES ('$question_text', '$mark', '$class_id', '$section_id', '$subject_id', '$groups_name')";
        $conn->query($sql);
    }

    $dashboard = new Dashboard();
    $dashboard->showPopup("Questions added successfully!.");
}
// Add class
if (isset($_POST['add_class'])) {
$class_name = $_POST['class_name'];
 $sql = "SELECT * FROM classes WHERE class_name = '$class_name'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "1";}

else {
    
    $sql = "INSERT INTO classes (class_name) VALUES ('$class_name')";
    if ($conn->query($sql) === TRUE) {
       $dashboard = new Dashboard();
$dashboard->showPopup("Class added successfully.");
        
    } 
}
}


// group
if (isset($_POST['group_name'])) {
$group_name = $_POST['group_name'];
 $sql = "SELECT * FROM qgroup WHERE group_name = '$group_name'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "1";}

else {
    
    $sql = "INSERT INTO qgroup (group_name) VALUES ('$group_name')";
    if ($conn->query($sql) === TRUE) {
       $dashboard = new Dashboard();
$dashboard->showPopup("Group added successfully.");
        
    } 
}
}

// delete group

if (isset($_POST['delete_group_id'])) {
    $group_id = $conn->real_escape_string($_POST['group_id']);
    $sql = "DELETE FROM qgroup WHERE id = $group_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Group deleted successfully.");
 echo "";
    } else {
        echo "Error deleting class: " . $conn->error;
    }
}




// delete question


if (isset($_POST['delete_question'])) {
    $question_id = $conn->real_escape_string($_POST['question_id']);
    $sql = "DELETE FROM questions WHERE id = $question_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Question deleted successfully.");
 echo "";
    } else {
        echo "Error deleting class: " . $conn->error;
    }
}



// update gruop

if (isset($_POST['group_update'])){
    $group_id = $conn->real_escape_string($_POST['group_id']);
    $group_name = $conn->real_escape_string($_POST['group_name']);
    $sql = "UPDATE qgroup SET group_name = '$group_name' WHERE id = $group_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Group updated successfully.");
 
    } else {
        echo "Error updating class: " . $conn->error;
    }
}

// update question
if (isset($_POST['question_update'])){
    $question_id = $conn->real_escape_string($_POST['question_id']);
    $question_text = $conn->real_escape_string($_POST['question_text']);
    $sql = "UPDATE questions SET question_text = '$question_text' WHERE id = $question_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Question updated successfully.");
 
    } else {
        echo "Error updating class: " . $conn->error;
    }
}



// Add section
if (isset($_POST['add_section'])) {
$class_id = $_POST['class_id'];
$section_name =$_POST['section_name'];

    $sql = "SELECT * FROM sections WHERE section_name = '$section_name' AND class_id = '$class_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "1";
    } else {
        $sql = "INSERT INTO sections (section_name, class_id) VALUES ('$section_name', '$class_id')";
        if ($conn->query($sql) === TRUE) {
            header('Location: dashboard.php'); // Redirect to prevent resubmission
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Add subject
if (isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    
    // Check for duplicate subject in the same class and section
    $sql = "SELECT * FROM subjects WHERE subject_name = '$subject_name' AND class_id = '$class_id' AND section_id = '$section_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Subject already exists in this class and section.");
 
    } else {
        $sql = "INSERT INTO subjects (subject_name, class_id, section_id) VALUES ('$subject_name', '$class_id', '$section_id')";
        if ($conn->query($sql) === TRUE) {
            header('Location: dashboard.php'); // Redirect to prevent resubmission
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Fetch all classes, sections, and subjects for display
$groups = $conn->query("SELECT * FROM qgroup");

$classes = $conn->query("SELECT * FROM classes");
$sections = $conn->query("SELECT sections.id, sections.section_name, classes.class_name FROM sections JOIN classes ON sections.class_id = classes.id");
$subjects = $conn->query("SELECT subjects.id, subjects.subject_name, classes.class_name, sections.section_name FROM subjects JOIN classes ON subjects.class_id = classes.id JOIN sections ON subjects.section_id = sections.id");
$classes_for_form = $conn->query("SELECT * FROM classes");
$sections_for_form = $conn->query("SELECT * FROM sections");




// Fetch all sections
$sections = $conn->query("SELECT sections.id, sections.section_name, classes.class_name FROM sections JOIN classes ON sections.class_id = classes.id");

// Fetch all classes for the section dropdown
$classes_for_section = $conn->query("SELECT * FROM classes");
if (isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $sql = "INSERT INTO subjects (subject_name, class_id, section_id) VALUES ('$subject_name', '$class_id', '$section_id')";
    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Subject added successfully.");
 
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_POST['class_update'])){
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $sql = "UPDATE classes SET class_name = '$class_name' WHERE id = $class_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Class updated successfully.");
 
    } else {
        echo "Error updating class: " . $conn->error;
    }
}

// Handle Class Delete
if (isset($_POST['delete_class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $sql = "DELETE FROM classes WHERE id = $class_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Class deleted successfully.");
 echo "";
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
      $dashboard = new Dashboard();
$dashboard->showPopup("Section updated successfully.");
 echo "";
    } else {
        echo "Error updating section: " . $conn->error;
    }
}

// Handle Section Delete
if (isset($_POST['delete_section_id'])) {
    $section_id = $conn->real_escape_string($_POST['section_id']);
    $sql = "DELETE FROM sections WHERE id = $section_id";

    if ($conn->query($sql) === TRUE) {
       $dashboard = new Dashboard();
$dashboard->showPopup("Section deleted successfully.");
 echo "";
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
       $dashboard = new Dashboard();
$dashboard->showPopup("Subject updated successfully.");
 echo "";
    } else {
        echo "Error updating subject: " . $conn->error;
    }
}

// Handle Subject Delete
if (isset($_POST['delete_subject_id'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $sql = "DELETE FROM subjects WHERE id = $subject_id";

    if ($conn->query($sql) === TRUE) {
      $dashboard = new Dashboard();
$dashboard->showPopup("Subject deleted successfully.");

    } else {
        echo "Error deleting subject: " . $conn->error;
    }
}

// Fetch all subjects
$subjects = $conn->query("SELECT subjects.id, subjects.subject_name, classes.class_name, sections.section_name FROM subjects 
JOIN classes ON subjects.class_id = classes.id
JOIN sections ON subjects.section_id = sections.id");

// Fetch all classes for the subject dropdown
$classes_for_subject = $conn->query("SELECT * FROM classes");
$sections_for_subject = $conn->query("SELECT * FROM sections");



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            padding: 15px;
            display: block;
            color: #ffffff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>
<body>


<div class="sidebar">
    <p class="text-center">Welcome, <?php echo $_SESSION['username']; ?></p>
    <h4 class="text-light text-center mt-4">LMS Dashboard</h4>
    <a href="#class-management">Class Management</a>
    <a href="#section-management">Section Management</a>
    <a href="#subject-management">Subject Management</a>
    <a href="#question-group">Question Group</a>
    <a href="#question-management">Question Create</a>
    <a href="logout.php">Logout</a>
</div>


    <!-- Class Management Section -->
    <div id="class-management" class="content mt-5">
    <h4>Class Management</h4>
    <form method="POST" action="">
        <div class="form-group">
            <label for="class_name">Class Name</label>
            <input type="text" class="form-control" id="class_name" name="class_name" required>
        </div>
        <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
    </form>

    <h4 class="mt-5">All Classes</h4>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Class Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $classes->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['class_name']; ?></td>
            <td>
                <!-- Edit Button -->
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editClassModal<?php echo $row['id']; ?>">Edit</button>

                <!-- Delete Button -->
                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteClassModal<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editClassModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="class_name">Class Name</label>
                                <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo $row['class_name']; ?>" required>
                                <input type="hidden" name="class_id" value="<?php echo $row['id']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary" name="class_update">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteClassModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this class: <strong><?php echo $row['class_name']; ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="class_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger" name="delete_class_id">Yes, Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </tbody>
</table>


</div>
<div id="section-management" class="content mt-5">
<!-- Section Management -->
<h4>Section Management</h4>
<form method="POST" action="">
    <div class="form-group">
        <label for="section_name">Section Name</label>
        <input type="text" class="form-control" id="section_name" name="section_name" required>
    </div>
    <div class="form-group">
        <label for="class_id">Class</label>
        <select class="form-control" id="class_id" name="class_id" required>
            <?php while ($class = $classes_for_section->fetch_assoc()) { ?>
                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <button type="submit" name="add_section" class="btn btn-primary">Add Section</button>
</form>

<h4 class="mt-5">All Sections</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Section Name</th>
            <th>Class Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $sections->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['section_name']; ?></td>
            <td><?php echo $row['class_name']; ?></td>
            <td>
                <!-- Edit Button -->
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editSectionModal<?php echo $row['id']; ?>">Edit</button>

                <!-- Delete Button -->
                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteSectionModal<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>










        <!-- Edit Modal -->
        <div class="modal fade" id="editSectionModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Section</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="section_name">Section Name</label>
                                <input type="text" class="form-control" id="section_name" name="section_name" value="<?php echo $row['section_name']; ?>" required>
                                <input type="hidden" name="section_id" value="<?php echo $row['id']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteSectionModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Section</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this section: <strong><?php echo $row['section_name']; ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="section_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger" name="delete_section_id">Yes, Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </tbody>
</table>

</div>


















<div id="question-group" class="content mt-5">


 <h4>Group Management</h4>
    <form method="POST" action="">
        <div class="form-group">
            <label for="class_name">Group Name</label>
            <input type="text" class="form-control" id="group_name" name="group_name" required>
        </div>
        <button type="submit" name="group_name" class="btn btn-primary">Add Group</button>
    </form>

    <h4 class="mt-5">All Groups</h4>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Group Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $groups->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['group_name']; ?></td>
            <td>
                <!-- Edit Button -->
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#eidtGroupModel<?php echo $row['id']; ?>">Edit</button>

                <!-- Delete Button -->
                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteGroupModel<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="eidtGroupModel<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="eidtGroupModelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="group_name">Group Name</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" value="<?php echo $row['group_name']; ?>" required>
                                <input type="hidden" name="group_id" value="<?php echo $row['id']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary" name="group_update">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteGroupModel<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteGroupModelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this class: <strong><?php echo $row['group_name']; ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="group_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger" name="delete_group_id">Yes, Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </tbody>
</table>

</div>























<div id="subject-management" class="content mt-5">
<!-- Subject Management -->
<h4>Subject Management</h4>
<form method="POST" action="">
    <div class="form-group">
        <label for="subject_name">Subject Name</label>
        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
    </div>
    <div class="form-group">
        <label for="class_id">Class</label>
        <select class="form-control" id="class_id" name="class_id" required>
            <?php while ($class = $classes_for_subject->fetch_assoc()) { ?>
                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="section_id">Section</label>
        <select class="form-control" id="section_id" name="section_id">
            <?php while ($section = $sections_for_subject->fetch_assoc()) { ?>
                <option value="<?php echo $section['id']; ?>"><?php echo $section['section_name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
</form>

<h4 class="mt-5">All Subjects</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Subject Name</th>
            <th>Class Name</th>
            <th>Section Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $subjects->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['subject_name']; ?></td>
            <td><?php echo $row['class_name']; ?></td>
            <td><?php echo $row['section_name']; ?></td>
            <td>
                <!-- Edit Button -->
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editSubjectModal<?php echo $row['id']; ?>">Edit</button>

                <!-- Delete Button -->
                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteSubjectModal<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editSubjectModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Subject</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="subject_name">Subject Name</label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo $row['subject_name']; ?>" required>
                                <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteSubjectModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Subject</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this subject: <strong><?php echo $row['subject_name']; ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger" name="delete_subject_id">Yes, Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </tbody>
</table>

</div>

<?php
// Include database connection


// Fetch all classes
$classes = $conn->query("SELECT * FROM classes");

// If class is selected, fetch sections
if (isset($_POST['classSelect'])) {
    $class_id = $_POST['classSelect'];
    $sections = $conn->query("SELECT * FROM sections WHERE class_id = $class_id");
}

// If section is selected, fetch subjects
if (isset($_POST['sectionSelect'])) {
    $section_id = $_POST['sectionSelect'];
    $subjects = $conn->query("SELECT * FROM subjects WHERE section_id = $section_id");
}

// If subject is selected, get ready for question form
if (isset($_POST['subjectSelect'])) {
    $subject_id = $_POST['subjectSelect'];
}
?>

<section id="question-management" class="content mt-5">
    <h4>Question Management</h4>




    <?php
    // Fetch all classes, sections, subjects, and groups initially
    $classes = $conn->query("SELECT * FROM classes");
    $sections = $conn->query("SELECT * FROM sections");
    $subjects = $conn->query("SELECT * FROM subjects");
    $groups = $conn->query("SELECT * FROM qgroup");
    $questions = $conn->query("SELECT q.id, q.question_text, q.marks, g.group_name FROM questions q JOIN qgroup g ON q.group_id = g.id");
    // Prepare PHP arrays to output as JavaScript arrays
    $class_data = [];
    while ($class = $classes->fetch_assoc()) {
        $class_data[$class['id']] = $class['class_name'];
    }

    $section_data = [];
    while ($section = $sections->fetch_assoc()) {
        $section_data[] = [
            'id' => $section['id'],
            'class_id' => $section['class_id'],
            'section_name' => $section['section_name'],
        ];
    }

    $subject_data = [];
    while ($subject = $subjects->fetch_assoc()) {
        $subject_data[] = [
            'id' => $subject['id'],
            'section_id' => $subject['section_id'],
            'subject_name' => $subject['subject_name'],
        ];
    }

    $group_data = [];
    while ($group = $groups->fetch_assoc()) {
        $group_data[] = [
            'id' => $group['id'],
            'group_name' => $group['group_name']
        ];
    }
    ?>











<form id="bulkActionForm" action="" method="POST">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Question</th>
                <th>Marks</th>
                <th>Group</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($questions->num_rows > 0): ?>
                <?php while ($question = $questions->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="question_ids[]" value="<?php echo $question['id']; ?>"></td>
                        <td><?php echo $question['question_text']; ?></td>
                        <td><?php echo $question['marks']; ?></td>
                        <td><?php echo $question['group_name']; ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editquestionModal<?php echo $question['id']; ?>">Edit</button>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deletequestionModal<?php echo $question['id']; ?>">Delete</button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editquestionModal<?php echo $question['id']; ?>" tabindex="-1" aria-labelledby="editquestionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Question</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="">
                                        <div class="form-group">
                                            <label for="question_text">Question Text</label>
                                            <input type="text" class="form-control" id="question_text" name="question_text" value="<?php echo $question['question_text']; ?>" required>
                                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="question_update">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deletequestionModal<?php echo $question['id']; ?>" tabindex="-1" aria-labelledby="deletequestionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Question</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this question: <strong><?php echo $question['question_text']; ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="btn btn-danger" name="delete_question">Yes, Delete</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No questions available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Bulk Action Buttons -->
    <button type="submit" name="bulk_print" class="btn btn-primary">Print Selected</button>
    <button type="submit" name="bulk_delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete selected questions?');">Delete Selected</button>
</form>





















    <form id="questionForm" action="dashboard.php" method="POST">
        <!-- Class Selection -->
        <div class="form-group">
            <label for="classSelect">Select Class</label>
            <select id="classSelect" name="class_id" class="form-control" required>
                <option value="">Select Class</option>
                <?php foreach ($class_data as $class_id => $class_name) { ?>
                    <option value="<?php echo $class_id; ?>"><?php echo $class_name; ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- Section Selection -->
        <div class="form-group">
            <label for="sectionSelect">Select Section</label>
            <select id="sectionSelect" name="section_id" class="form-control" required>
                <option value="">Select Section</option>
                <!-- Options will be populated dynamically -->
            </select>
        </div>

        <!-- Subject Selection -->
        <div class="form-group">
            <label for="subjectSelect">Select Subject</label>
            <select id="subjectSelect" name="subject_id" class="form-control" required>
                <option value="">Select Subject</option>
                <!-- Options will be populated dynamically -->
            </select>
        </div>

        <!-- Add question fields and group dropdown -->
        <div id="questionFields"></div>

        <!-- Button to add more questions -->
        <button type="button" id="addMoreQuestions" class="btn btn-primary">Add More Questions</button>

        <br><br>

        <button type="submit" name="add_questions" class="btn btn-success">Submit Questions</button>
    </form>



</section>

<!-- Pass PHP data to JavaScript -->
<script>
    // PHP data for sections, subjects, and groups
    const sections = <?php echo json_encode($section_data); ?>;
    const subjects = <?php echo json_encode($subject_data); ?>;
    const groups = <?php echo json_encode($group_data); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('classSelect');
        const sectionSelect = document.getElementById('sectionSelect');
        const subjectSelect = document.getElementById('subjectSelect');

        // When the class is selected, populate the section dropdown
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            // Clear previous section and subject options
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';

            // Filter and populate sections based on the selected class
            sections.forEach(section => {
                if (section.class_id == classId) {
                    sectionSelect.innerHTML += `<option value="${section.id}">${section.section_name}</option>`;
                }
            });
        });

        // When the section is selected, populate the subject dropdown
        sectionSelect.addEventListener('change', function() {
            const sectionId = this.value;
            // Clear previous subject options
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';

            // Filter and populate subjects based on the selected section
            subjects.forEach(subject => {
                if (subject.section_id == sectionId) {
                    subjectSelect.innerHTML += `<option value="${subject.id}">${subject.subject_name}</option>`;
                }
            });
        });

  document.getElementById('selectAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

        // Add more questions dynamically when button is clicked
        document.getElementById('addMoreQuestions').addEventListener('click', function() {
            // Generate group options dynamically
            let groupOptions = '';
            groups.forEach(group => {
                groupOptions += `<option value="${group.id}">${group.group_name}</option>`;
            });

            // Add question fields dynamically
            document.getElementById('questionFields').insertAdjacentHTML('beforeend', `
                <div class="question-group">
                    <div class="form-group">
                        <label for="question_text">Question</label>
                        <input type="text" class="form-control" name="question_text[]" required>
                    </div>
                    <div class="form-group">
                        <label for="marks">Marks</label>
                        <input type="text" class="form-control" name="marks[]" required>
                    </div>
                    <div class="form-group">
                        <label for="groupSelect">Select Group</label>
                        <select name="group_id[]" class="form-control" required>
                            ${groupOptions}
                        </select>
                    </div>
                    <hr>
                </div>
            `);
        });
    });
</script>


<script type="text/javascript">function hideOtherSections(activeSection) {
  const sections = document.querySelectorAll('.mt-5');
  sections.forEach(section => {
    if (section.id !== activeSection) {
      section.style.display = 'none';
    } else {
      section.style.display = 'block';
    }
  });
}

// Assuming you have a function to handle the link clicks:
function handleLinkClick(event) {
  const targetSection = event.target.getAttribute('href').substring(1); // Remove the '#'
  hideOtherSections(targetSection);
}

// Attach event listeners to all links:
const links = document.querySelectorAll('a[href^="#"]');
links.forEach(link => {
  link.addEventListener('click', handleLinkClick);
});

// Initially hide all sections except for the first one:
hideOtherSections(links[0].getAttribute('href').substring(1));</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

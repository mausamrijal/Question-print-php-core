

<?php
require 'vendor/autoload.php'; // Include Composer's autoload file for dompdf

use Dompdf\Dompdf;

// Database connection setup
$host = 'localhost';
$db = 'user_management';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if question IDs are passed via GET
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("No question IDs were passed.");
}

// Split the IDs into an array
$selected_ids = explode(',', $_GET['ids']);

// Check if form is submitted
if (isset($_POST['print'])) {
    // Validate form fields
    $required_fields = ['school_name', 'full_mark', 'pass_mark', 'school_location', 'exam_name', 'subject_name', 'class_name', 'section_name'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die(ucwords(str_replace('_', ' ', $field)) . " is missing.");
        }
    }

    // Fetch form inputs
    $school_name = $_POST['school_name'];
    $full_mark = $_POST['full_mark'];
    $pass_mark = $_POST['pass_mark'];
    $school_location = $_POST['school_location'];
    $exam_name = $_POST['exam_name'];
    $subject_name = $_POST['subject_name'];
    $class_name = $_POST['class_name'];
    $section_name = $_POST['section_name'];

    // Ensure question IDs are passed from the form
    if (isset($_POST['question_ids']) && !empty($_POST['question_ids'])) {
        $selected_ids = explode(',', $_POST['question_ids']);
        $id_list = implode(',', array_map('intval', $selected_ids)); // Ensure IDs are safe for SQL

        // Fetch selected questions from the database
        $query = "SELECT q.question_text, q.marks, g.group_name 
                  FROM questions q
                  JOIN qgroup g ON q.group_id = g.id
                  WHERE q.id IN ($id_list)
                  ORDER BY g.group_name, q.id";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            // Build the printable content in HTML
            $html = "
            <div style='font-family: Arial, sans-serif;'>
                <h1 style='text-align: center;'>$school_name</h1>
                <p style='text-align: center;'>$school_location</p>
                <p style='text-align: center;'>$exam_name</p>

                <div style='clear: both; margin: 0;'>
                    <div style='float: left;'><strong>Class:</strong> $class_name</div>
                    <div style='float: right;'><strong>Full Marks:</strong> $full_mark</div>
                </div>
                <br style='clear: both;'>
                
                <div style='clear: both; margin: 0;'>
                    <div style='float: left;'><strong>Subject:</strong> $subject_name</div>
                    <div style='float: right;'><strong>Pass Marks:</strong> $pass_mark</div>
                </div>
                <br style='clear: both;'>
                <hr>";

            $current_group = '';
            $sn = 1; // Initialize serial number

            while ($row = $result->fetch_assoc()) {
                // If a new group starts, reset the SN counter
                if ($current_group !== $row['group_name']) {
                    if ($current_group !== '') {
                        $html .= "<hr>";
                    }
                    $current_group = $row['group_name'];
                    $html .= "<h3 style='text-align: center;'>Group: $current_group</h3>";
                    $sn = 1; // Reset SN for each new group
                }

                // Display SN and question with marks
                $html .= "
                <div style='width: 100%;'>
                    <div style='float: left;'>Q.N $sn: {$row['question_text']}</div>
                    <div style='float: right;'>{$row['marks']}</div>
                </div>
                <br style='clear: both;'>";
                
                $sn++; // Increment SN for the next question
            }

            $html .= "</div>";

            // Initialize Dompdf and load the HTML
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);

            // (Optional) Setup the paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the HTML as PDF
            $dompdf->render();

            // Output the generated PDF (inline or force download)
            $dompdf->stream('exam_questions.pdf', ['Attachment' => 0]);

        } else {
            echo "No questions found.";
        }
    } else {
        echo "No question IDs were passed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Details Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <!-- Form with Styling -->
<form action="print_questions.php?ids=<?php echo implode(',', $selected_ids); ?>" method="POST" style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;">
    <h2 style="text-align: center;">Exam Details</h2>
    <div style="margin-bottom: 15px;">
        <label for="school_name" style="font-weight: bold;">School Name:</label>
        <input type="text" id="school_name" name="school_name" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="school_location" style="font-weight: bold;">School Location:</label>
        <input type="text" id="school_location" name="school_location" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="exam_name" style="font-weight: bold;">Exam Name:</label>
        <input type="text" id="exam_name" name="exam_name" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="subject_name" style="font-weight: bold;">Subject Name:</label>
        <input type="text" id="subject_name" name="subject_name" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <input type="hidden" name="question_ids" value="<?php echo implode(',', $selected_ids); ?>">

    <div style="margin-bottom: 15px;">
        <label for="class_name" style="font-weight: bold;">Class:</label>
        <input type="text" id="class_name" name="class_name" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="section_name" style="font-weight: bold;">Section:</label>
        <input type="text" id="section_name" name="section_name" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="full_mark" style="font-weight: bold;">Full Mark:</label>
        <input type="number" id="full_mark" name="full_mark" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="pass_mark" style="font-weight: bold;">Pass Mark:</label>
        <input type="number" id="pass_mark" name="pass_mark" required style="width: 100%; padding: 8px; border-radius: 5px;">
    </div>

    <button type="submit" name="print" class="btn btn-primary" style="width: 100%; padding: 10px; border-radius: 5px;">Print Questions</button>
</form>
</body>
</html>

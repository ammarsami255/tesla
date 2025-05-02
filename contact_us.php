<?php
// Database credentials
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "tesla"; // The database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $first_name = $_POST['firstname'];
    $last_name = $_POST['lastname'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $message = $_POST['message'];

    // Prepare SQL query to insert the data into the 'contact_us' table
    $sql = "INSERT INTO contact_us (first_name, last_name, email, mobile, message, submission_date) 
            VALUES ('$first_name', '$last_name', '$email', '$mobile', '$message', NOW())";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
      header("Location: index.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hms";
$port = 3306; // Add the port as a variable if needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your code to prepare statements
$sql = "SELECT * FROM user WHERE email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$email = "example@example.com"; // Example email
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Process the result
    while ($row = $result->fetch_assoc()) {
        echo "User: " . $row["email"] . "\n";
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

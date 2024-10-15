<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Connect";

// Delete the boats.csv file
$csv_file = 'boats.csv';
if (file_exists($csv_file)) {
    if (unlink($csv_file)) {
        echo "File 'boats.csv' deleted successfully.\n";
    } else {
        echo "Error: Could not delete 'boats.csv'.\n";
    }
} else {
    echo "'boats.csv' does not exist.\n";
}

// Create connection to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to drop the 'boats' table
$sql = "DROP TABLE IF EXISTS boats";

if ($conn->query($sql) === TRUE) {
    echo "Table 'boats' deleted successfully.\n";
} else {
    echo "Error deleting table: " . $conn->error . "\n";
}

// Close the database connection
$conn->close();
?>

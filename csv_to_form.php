<?php
// MySQL credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "connect";

// Function to connect to the MySQL database
function connect_to_db($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to handle form submission, create table, and insert CSV data
function handle_form_submission($csv_file_path, $conn) {
    $filename = substr($csv_file_path, 0, strrpos($csv_file_path, "."));
    $filename = preg_replace('/\s+/', '', $filename);

    $table_name = basename($csv_file_path, ".csv");

    // Open CSV file
    if (($handle = fopen($csv_file_path, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ";"); // First row of CSV

        // SQL to create the table
        $sql_create = "CREATE TABLE IF NOT EXISTS `$table_name` (";
        
        // Add an auto-incrementing id column
        $sql_create .= "`id` INT AUTO_INCREMENT PRIMARY KEY,";

        // Loop through form POST data to define column types
        foreach ($_POST as $column_name => $column_info) {
            // Skip 'csv_file' hidden field and 'id' if it's already in the headers
            if ($column_name != 'csv_file' && strtolower($column_name) != 'id') {
                $column_name = htmlspecialchars($column_name);
                $column_type = $column_info['type'];

                if ($column_type == 'all text') {
                    $sql_create .= "`$column_name` VARCHAR(255),";
                } elseif ($column_type == 'all numbers') {
                    $sql_create .= "`$column_name` INT,";
                } elseif ($column_type == 'a date') {
                    $sql_create .= "`$column_name` DATE,";
                } elseif ($column_type == 'a date and time') {
                    $sql_create .= "`$column_name` DATETIME,";
                }
            }
        }

        $sql_create = rtrim($sql_create, ',') . ')';  // Finish table creation query

        
        // Execute SQL to create table
        if ($conn->query($sql_create) === TRUE) {
            echo "Table '$table_name' created successfully.<br>";
        } else {
            die("Error creating table: " . $conn->error);
        }

        // Insert the remaining rows into the table
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $sql_insert = "INSERT INTO `$table_name` (";

            // Only include columns that are not 'csv_file' and 'id'
            $columns = array_keys(array_filter($_POST, function($key) {
                return $key != 'csv_file' && strtolower($key) != 'id';
            }, ARRAY_FILTER_USE_KEY));

            // Join the column names for the SQL query
            $sql_insert .= implode(", ", $columns) . ") VALUES (";

            foreach ($data as $i => $value) {
                $value = $conn->real_escape_string($value);

                // Check if the header is in POST (excluding 'csv_file' and 'id')
                if (isset($headers[$i]) && $headers[$i] != 'csv_file' && strtolower($headers[$i]) != 'id') {
                    $column_info = $_POST[$headers[$i]];

                    if ($column_info['type'] == 'a date' || $column_info['type'] == 'a date and time') {
                        // Use the custom format specified by the user
                        $format = $column_info['format'];

                        if ($format) {
                            $date = DateTime::createFromFormat($format, $value);
                            if ($date) {
                                $value = $date->format('Y-m-d H:i:s');
                            }
                        }
                    }
                    $sql_insert .= is_numeric($value) ? "$value," : "'$value',";
                }
            }

            $sql_insert = rtrim($sql_insert, ',') . ')';

            if ($conn->query($sql_insert) === TRUE) {
                echo "New record inserted successfully.<br>";
            } else {
                echo "Error: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
    } else {
        die("Unable to open file.");
    }
}


// Main logic: check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csv_file'])) {
    // Get the CSV file path
    $csv_file_path = $_POST['csv_file'];

    // Connect to the database
    $conn = connect_to_db($servername, $username, $password, $dbname);

    // Handle the form submission
    handle_form_submission($csv_file_path, $conn);

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid form submission.";
}
?>

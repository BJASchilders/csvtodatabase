<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to convert XLSX to CSV
function convertXlsxToCsv($xlsxFilePath, $csvFilePath) {
    $spreadsheet = IOFactory::load($xlsxFilePath); // Load the .xlsx file
    $writer = IOFactory::createWriter($spreadsheet, 'Csv');
    $writer->setDelimiter(';'); // Set semicolon delimiter
    $writer->save($csvFilePath); // Save as CSV
}

// Function to display the form based on the first row of the CSV file
function display_form($csv_file_path) {
    if (($handle = fopen($csv_file_path, "r")) !== FALSE) {
        if (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            echo "<form method='post' action='csv_to_form.php'>";
            echo "<input type='hidden' name='csv_file' value='$csv_file_path'>";

            // Loop through each variable in the first row and create input fields
            foreach ($data as $variable) {
                $variable_cleaned = htmlspecialchars($variable);
                echo "<label for='{$variable_cleaned}'>{$variable_cleaned} is: </label>";
                echo "<select name='{$variable_cleaned}[type]'>";
                echo "<option value='all text'>all text</option>";
                echo "<option value='all numbers'>all numbers</option>";
                echo "<option value='a date'>a date</option>";
                echo "<option value='a date and time'>a date and time</option>";
                echo "</select>";

                // Add format input field for date and datetime
                echo "<span id='{$variable_cleaned}_format' style='display:none;'>
                        <label for='{$variable_cleaned}_format'>Format: </label>
                        <input type='text' name='{$variable_cleaned}[format]' placeholder='Enter format (e.g., YYYY/MM/DD)'>
                      </span>";

                echo "<br><br>";
            }

            echo "<input type='submit' value='Submit'>";
            echo "</form>";
        } else {
            echo "No data in CSV file.";
        }
        fclose($handle);
    } else {
        echo "Unable to open file.";
    }
}

// Check if an XLSX file has been uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['xlsx_file'])) {
    $xlsxFile = $_FILES['xlsx_file']['tmp_name'];
    
    // Get the original filename without extension and append .csv
    $originalFileName = pathinfo($_FILES['xlsx_file']['name'], PATHINFO_FILENAME);
    $csvFile = $originalFileName . '.csv'; // Retain the same filename, change extension to .csv

    // Convert XLSX to CSV
    convertXlsxToCsv($xlsxFile, $csvFile);

    // Display form based on converted CSV file
    display_form($csvFile);
} else {
    // Show file upload form
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<label>Select an XLSX file to convert to CSV: </label>";
    echo "<input type='file' name='xlsx_file' accept='.xlsx'><br><br>";
    echo "<input type='submit' value='Upload and Convert'>";
    echo "</form>";
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show the format input field when 'a date' or 'a date and time' is selected
    document.querySelectorAll('select').forEach(function(select) {
        select.addEventListener('change', function() {
            // Ensure we correctly get the format input associated with the selected column
            let formatField = document.querySelector(`[name="${this.name}[type]"]`);
            console.log(formatField.style.display);
            console.log(this.value);
            
            // Only show the format field for 'a date' or 'a date and time'
            if (this.value === 'a date' || this.value === 'a date and time') {
                if (formatField) {
                    formatField.style.display = 'inline'; // Show the format field
                }
            } else {
                if (formatField) {
                    formatField.style.display = 'none'; // Hide the format field for other types
                }
            }
        });
    });
});
</script>


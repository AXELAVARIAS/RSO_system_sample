<?php
// Test script to verify publication counting
$pub_file = __DIR__ . '/php/publication_presentation.csv';

echo "Testing publication counting logic...\n";
echo "File: $pub_file\n";

if (file_exists($pub_file)) {
    echo "File exists: YES\n";
    
    // Read and display all rows
    $file = fopen($pub_file, 'r');
    $all_rows = [];
    while (($row = fgetcsv($file)) !== false) {
        $all_rows[] = $row;
    }
    fclose($file);
    
    echo "Total rows in file: " . count($all_rows) . "\n";
    
    // Test the counting logic (without skipping header)
    $publications_count = 0;
    $file = fopen($pub_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && !empty(array_filter($row))) {
            $publications_count++;
        }
    }
    fclose($file);
    
    echo "Publications count (corrected logic): $publications_count\n";
    
    // Test the old counting logic (with skipping header)
    $old_count = 0;
    $file = fopen($pub_file, 'r');
    fgetcsv($file); // Skip header
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && !empty(array_filter($row))) {
            $old_count++;
        }
    }
    fclose($file);
    
    echo "Publications count (old logic with header skip): $old_count\n";
    
    // Display all rows
    echo "\nAll rows in file:\n";
    foreach ($all_rows as $index => $row) {
        echo "Row $index: " . implode(', ', $row) . "\n";
    }
    
} else {
    echo "File does not exist!\n";
}
?> 
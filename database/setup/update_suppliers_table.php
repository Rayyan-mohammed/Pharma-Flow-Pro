<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this script from the command line.'); }
require_once '../../app/init.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/../../database/add_contact_to_suppliers.sql');
    $db->exec($sql);
    
    echo "Suppliers table updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
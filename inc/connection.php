<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=FoxFace", 'ronv', 'mi9nk'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch ( \Exception $e ) {
    echo 'Error connecting to the Database: ' . $e->getMessage();
    exit;
}
?>

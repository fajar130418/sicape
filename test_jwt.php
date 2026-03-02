<?php
require 'vendor/autoload.php';
try {
    if (class_exists('Firebase\JWT\JWT')) {
        echo "SUCCESS: Firebase\JWT\JWT found\n";
    } else {
        echo "FAILURE: Firebase\JWT\JWT not found\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

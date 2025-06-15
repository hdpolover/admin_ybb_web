<?php

require_once 'vendor/autoload.php';

// Test AbstractFeedbackModel methods
use App\Models\AbstractFeedbackModel;

try {
    $model = new AbstractFeedbackModel();
    echo "AbstractFeedbackModel loaded successfully!\n";
    
    // Test a simple query
    $result = $model->findAll();
    echo "Basic query works!\n";
    
    // Test the specific methods we need
    if (method_exists($model, 'getReviewerStats')) {
        echo "getReviewerStats method exists!\n";
    }
    
    if (method_exists($model, 'getFeedbacksByReviewer')) {
        echo "getFeedbacksByReviewer method exists!\n";
    }
    
    if (method_exists($model, 'getFeedbackDetails')) {
        echo "getFeedbackDetails method exists!\n";
    }
    
    if (method_exists($model, 'submitFeedback')) {
        echo "submitFeedback method exists!\n";
    }
    
    echo "All required methods are available!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

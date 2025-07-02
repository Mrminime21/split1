<?php
/**
 * Plisio Webhook Handler
 * Processes payment notifications from Plisio.net
 */

require_once '../../includes/database.php';
require_once '../../includes/plisio.php';

header('Content-Type: application/json');

try {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Get signature from headers
    $signature = $_SERVER['HTTP_X_PLISIO_SIGNATURE'] ?? '';
    
    if (empty($signature)) {
        throw new Exception('Missing signature');
    }
    
    // Initialize Plisio handler
    $plisio = new PlisioPayment();
    
    // Verify the webhook signature
    if (!$plisio->verifyCallback($data, $signature)) {
        throw new Exception('Invalid signature');
    }
    
    // Process the callback
    $result = $plisio->processCallback($data);
    
    if ($result['success']) {
        // Log successful webhook
        error_log('Plisio webhook processed successfully: ' . json_encode($data));
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Webhook processed successfully'
        ]);
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    // Log error
    error_log('Plisio webhook error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
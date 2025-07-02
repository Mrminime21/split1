<?php
/**
 * Payment Status Update Cron Job
 * Checks and updates payment statuses from external providers
 */

require_once '../includes/database.php';
require_once '../includes/plisio.php';

$db = Database::getInstance();

try {
    echo "Starting payment status update...\n";
    
    // Get pending crypto payments
    $pendingPayments = $db->fetchAll("
        SELECT * FROM payments 
        WHERE payment_method = 'crypto' 
        AND payment_provider = 'plisio'
        AND status IN ('pending', 'processing')
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    
    if (empty($pendingPayments)) {
        echo "No pending payments to check.\n";
        exit;
    }
    
    $plisio = new PlisioPayment();
    
    foreach ($pendingPayments as $payment) {
        try {
            if (empty($payment['external_id'])) {
                continue;
            }
            
            // Get invoice status from Plisio
            $invoice = $plisio->getInvoice($payment['external_id']);
            
            if (isset($invoice['data']['status'])) {
                $plisioStatus = $invoice['data']['status'];
                $newStatus = $plisio->mapPlisioStatus($plisioStatus);
                
                if ($newStatus !== $payment['status']) {
                    // Update payment status
                    $updateData = [
                        'status' => $newStatus,
                        'provider_response' => json_encode($invoice),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($newStatus === 'completed') {
                        $updateData['processed_at'] = date('Y-m-d H:i:s');
                        
                        // Update user balance for deposits
                        if ($payment['type'] === 'deposit') {
                            $db->query("
                                UPDATE users 
                                SET balance = balance + ? 
                                WHERE id = ?
                            ", [$payment['amount'], $payment['user_id']]);
                            
                            echo "Credited $" . $payment['amount'] . " to user " . $payment['user_id'] . "\n";
                        }
                    }
                    
                    $db->update('payments', $updateData, 'id = ?', [$payment['id']]);
                    
                    echo "Updated payment {$payment['transaction_id']} status: {$payment['status']} -> {$newStatus}\n";
                }
            }
            
        } catch (Exception $e) {
            echo "Error checking payment {$payment['transaction_id']}: " . $e->getMessage() . "\n";
        }
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 seconds
    }
    
    echo "Payment status update completed.\n";
    
} catch (Exception $e) {
    echo "Error updating payment statuses: " . $e->getMessage() . "\n";
}
?>
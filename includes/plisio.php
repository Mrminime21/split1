<?php
/**
 * Plisio.net Payment Gateway Integration
 * Handles cryptocurrency payments via Plisio API
 */

class PlisioPayment {
    private $apiKey;
    private $apiUrl = 'https://plisio.net/api/v1/';
    private $db;

    public function __construct($apiKey = null) {
        $this->db = Database::getInstance();
        
        if ($apiKey) {
            $this->apiKey = $apiKey;
        } else {
            // Get API key from database settings
            $setting = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'plisio_api_key'");
            $this->apiKey = $setting ? $setting['setting_value'] : '';
        }
    }

    /**
     * Create a new payment invoice
     */
    public function createInvoice($params) {
        $data = [
            'source_amount' => $params['amount'],
            'source_currency' => $params['currency'] ?? 'USD',
            'order_number' => $params['order_id'],
            'order_name' => $params['description'] ?? 'Starlink Rent Deposit',
            'callback_url' => $params['callback_url'],
            'success_callback_url' => $params['success_url'] ?? '',
            'fail_callback_url' => $params['fail_url'] ?? '',
            'email' => $params['email'] ?? '',
            'plugin' => 'starlink-rent',
            'version' => '1.0'
        ];

        if (isset($params['currency_to'])) {
            $data['source_currency'] = $params['currency_to'];
        }

        return $this->makeRequest('invoices/new', $data);
    }

    /**
     * Get invoice details
     */
    public function getInvoice($invoiceId) {
        return $this->makeRequest('invoices/' . $invoiceId);
    }

    /**
     * Get supported currencies
     */
    public function getCurrencies() {
        return $this->makeRequest('currencies');
    }

    /**
     * Get current exchange rates
     */
    public function getExchangeRates($source = 'USD') {
        return $this->makeRequest('currencies/' . $source);
    }

    /**
     * Verify webhook callback
     */
    public function verifyCallback($data, $signature) {
        $expectedSignature = hash_hmac('sha1', json_encode($data), $this->apiKey);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process webhook callback
     */
    public function processCallback($data) {
        try {
            $orderId = $data['order_number'] ?? '';
            $status = $data['status'] ?? '';
            $amount = $data['source_amount'] ?? 0;
            $cryptoAmount = $data['amount'] ?? 0;
            $currency = $data['source_currency'] ?? 'USD';
            $cryptoCurrency = $data['currency'] ?? '';
            $txnId = $data['txn_id'] ?? '';
            
            // Find the payment record
            $payment = $this->db->fetch("
                SELECT * FROM payments 
                WHERE transaction_id = ? AND payment_method = 'crypto'
            ", [$orderId]);
            
            if (!$payment) {
                throw new Exception('Payment not found: ' . $orderId);
            }
            
            // Update payment status based on Plisio status
            $newStatus = $this->mapPlisioStatus($status);
            
            $updateData = [
                'status' => $newStatus,
                'provider_transaction_id' => $txnId,
                'crypto_currency' => $cryptoCurrency,
                'crypto_amount' => $cryptoAmount,
                'provider_response' => json_encode($data),
                'webhook_received' => true,
                'webhook_data' => json_encode($data),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($newStatus === 'completed') {
                $updateData['processed_at'] = date('Y-m-d H:i:s');
                
                // Update user balance for deposits
                if ($payment['type'] === 'deposit') {
                    $this->db->query("
                        UPDATE users 
                        SET balance = balance + ? 
                        WHERE id = ?
                    ", [$amount, $payment['user_id']]);
                }
            }
            
            $this->db->update('payments', $updateData, 'id = ?', [$payment['id']]);
            
            // Log the webhook
            $this->db->insert('payment_webhooks', [
                'provider' => 'plisio',
                'webhook_id' => $txnId,
                'event_type' => $status,
                'payment_id' => $payment['id'],
                'raw_data' => json_encode($data),
                'processed' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'processed_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'payment_id' => $payment['id'],
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            error_log('Plisio callback error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Map Plisio status to our payment status
     */
    private function mapPlisioStatus($plisioStatus) {
        $statusMap = [
            'new' => 'pending',
            'pending' => 'pending',
            'expired' => 'expired',
            'completed' => 'completed',
            'error' => 'failed',
            'cancelled' => 'cancelled'
        ];
        
        return $statusMap[$plisioStatus] ?? 'pending';
    }

    /**
     * Make API request to Plisio
     */
    private function makeRequest($endpoint, $data = null) {
        if (empty($this->apiKey)) {
            throw new Exception('Plisio API key not configured');
        }

        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Starlink-Rent/1.0'
        ]);

        if ($data) {
            $data['api_key'] = $this->apiKey;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            $url .= '?api_key=' . $this->apiKey;
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Curl error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP error: ' . $httpCode);
        }

        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }

        if (isset($decoded['status']) && $decoded['status'] === 'error') {
            throw new Exception('Plisio API error: ' . ($decoded['message'] ?? 'Unknown error'));
        }

        return $decoded;
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl($invoiceData) {
        if (isset($invoiceData['data']['invoice_url'])) {
            return $invoiceData['data']['invoice_url'];
        }
        return null;
    }

    /**
     * Format amount for display
     */
    public function formatAmount($amount, $currency = 'USD') {
        return number_format($amount, 2) . ' ' . strtoupper($currency);
    }

    /**
     * Get popular cryptocurrencies
     */
    public function getPopularCurrencies() {
        return [
            'BTC' => ['name' => 'Bitcoin', 'icon' => '₿'],
            'ETH' => ['name' => 'Ethereum', 'icon' => 'Ξ'],
            'USDT' => ['name' => 'Tether', 'icon' => '₮'],
            'USDC' => ['name' => 'USD Coin', 'icon' => '$'],
            'LTC' => ['name' => 'Litecoin', 'icon' => 'Ł'],
            'BCH' => ['name' => 'Bitcoin Cash', 'icon' => '₿'],
            'DOGE' => ['name' => 'Dogecoin', 'icon' => 'Ð'],
            'TRX' => ['name' => 'TRON', 'icon' => 'T']
        ];
    }
}
?>
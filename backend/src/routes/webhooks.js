import express from 'express';
import { v4 as uuidv4 } from 'uuid';
import crypto from 'crypto';
import db from '../config/database.js';
import emailService from '../config/email.js';

const router = express.Router();

// Plisio webhook handler
router.post('/plisio', async (req, res) => {
  try {
    // Get the raw POST data and signature
    const signature = req.headers['x-plisio-signature'];
    const data = req.body;
    
    // Verify signature
    if (!verifyPlisioSignature(data, signature)) {
      return res.status(401).json({
        success: false,
        message: 'Invalid signature'
      });
    }
    
    // Log webhook
    const webhookId = uuidv4();
    await db.insert('payment_webhooks', {
      id: webhookId,
      provider: 'plisio',
      webhook_id: data.txn_id || '',
      event_type: data.status || '',
      raw_data: JSON.stringify(data),
      processed: false,
      created_at: new Date()
    });
    
    // Process webhook
    const orderId = data.order_number;
    const status = data.status;
    const amount = parseFloat(data.source_amount) || 0;
    const cryptoAmount = parseFloat(data.amount) || 0;
    const cryptoCurrency = data.currency || '';
    const txnId = data.txn_id || '';
    
    // Find payment record
    const payment = await db.getOne(
      "SELECT * FROM payments WHERE transaction_id = ? AND payment_method = 'crypto'",
      [orderId]
    );
    
    if (!payment) {
      return res.status(404).json({
        success: false,
        message: 'Payment not found'
      });
    }
    
    // Map Plisio status to our payment status
    const newStatus = mapPlisioStatus(status);
    
    // Update payment record
    const updateData = {
      status: newStatus,
      provider_transaction_id: txnId,
      crypto_currency: cryptoCurrency,
      crypto_amount: cryptoAmount,
      provider_response: JSON.stringify(data),
      webhook_received: true,
      webhook_data: JSON.stringify(data),
      updated_at: new Date()
    };
    
    if (newStatus === 'completed') {
      updateData.processed_at = new Date();
      
      // Update user balance for deposits
      if (payment.type === 'deposit') {
        await db.update(
          'users',
          { 
            balance: db.query('SELECT balance + ? FROM users WHERE id = ?', [amount, payment.user_id]),
            updated_at: new Date()
          },
          'id = ?',
          [payment.user_id]
        );
        
        // Get user data for email
        const user = await db.getOne(
          'SELECT * FROM users WHERE id = ?',
          [payment.user_id]
        );
        
        // Send deposit confirmation email
        try {
          await emailService.sendDepositConfirmation(user, {
            ...payment,
            ...updateData
          });
        } catch (emailError) {
          console.error('Failed to send deposit confirmation email:', emailError);
        }
      }
    }
    
    await db.update('payments', updateData, 'id = ?', [payment.id]);
    
    // Update webhook as processed
    await db.update(
      'payment_webhooks',
      { 
        processed: true,
        payment_id: payment.id,
        processed_at: new Date()
      },
      'id = ?',
      [webhookId]
    );
    
    return res.status(200).json({
      success: true,
      message: 'Webhook processed successfully'
    });
  } catch (error) {
    console.error('Plisio webhook error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Verify Plisio webhook signature
const verifyPlisioSignature = (data, signature) => {
  try {
    if (!process.env.PLISIO_API_KEY || !signature) {
      return false;
    }
    
    const expectedSignature = crypto
      .createHmac('sha1', process.env.PLISIO_API_KEY)
      .update(JSON.stringify(data))
      .digest('hex');
    
    return crypto.timingSafeEqual(
      Buffer.from(expectedSignature),
      Buffer.from(signature)
    );
  } catch (error) {
    console.error('Signature verification error:', error);
    return false;
  }
};

// Map Plisio status to our payment status
const mapPlisioStatus = (plisioStatus) => {
  const statusMap = {
    'new': 'pending',
    'pending': 'pending',
    'expired': 'expired',
    'completed': 'completed',
    'error': 'failed',
    'cancelled': 'cancelled'
  };
  
  return statusMap[plisioStatus] || 'pending';
};

export default router;
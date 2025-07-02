import express from 'express';
import { v4 as uuidv4 } from 'uuid';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';
import emailService from '../config/email.js';

const router = express.Router();

// Get user's payments
router.get('/', verifyToken, async (req, res) => {
  try {
    const payments = await db.query(
      `SELECT * FROM payments
      WHERE user_id = ?
      ORDER BY created_at DESC`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      payments
    });
  } catch (error) {
    console.error('Get payments error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Create deposit request
router.post('/deposit', verifyToken, async (req, res) => {
  try {
    const { amount, method } = req.body;
    
    // Validate input
    if (!amount || !method) {
      return res.status(400).json({
        success: false,
        message: 'Please provide amount and payment method'
      });
    }
    
    // Check if amount is valid
    if (amount < 50) {
      return res.status(400).json({
        success: false,
        message: 'Minimum deposit amount is $50'
      });
    }
    
    if (amount > 10000) {
      return res.status(400).json({
        success: false,
        message: 'Maximum deposit amount is $10,000'
      });
    }
    
    // Check if payment method is valid
    const validMethods = ['crypto', 'binance', 'card', 'bank_transfer'];
    if (!validMethods.includes(method)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid payment method'
      });
    }
    
    // Generate unique order ID
    const orderId = `DEP_${Date.now()}_${req.user.id.substring(0, 8)}_${Math.floor(Math.random() * 10000)}`;
    
    // Create payment record
    const paymentId = uuidv4();
    const paymentData = {
      id: paymentId,
      user_id: req.user.id,
      transaction_id: orderId,
      amount,
      currency: 'USD',
      payment_method: method,
      status: 'pending',
      type: 'deposit',
      description: `Account deposit via ${method.charAt(0).toUpperCase() + method.slice(1)}`,
      created_at: new Date(),
      updated_at: new Date()
    };
    
    await db.insert('payments', paymentData);
    
    // If crypto payment, return payment info for frontend processing
    if (method === 'crypto') {
      return res.status(201).json({
        success: true,
        message: 'Deposit request created successfully',
        payment: {
          id: paymentId,
          orderId,
          amount,
          method,
          redirectUrl: `/payment/plisio?amount=${amount}&type=deposit&order_id=${orderId}`
        }
      });
    }
    
    return res.status(201).json({
      success: true,
      message: 'Deposit request created successfully',
      payment: {
        id: paymentId,
        orderId,
        amount,
        method,
        status: 'pending'
      }
    });
  } catch (error) {
    console.error('Create deposit error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Create withdrawal request
router.post('/withdraw', verifyToken, async (req, res) => {
  try {
    const { amount, method, address, notes } = req.body;
    
    // Validate input
    if (!amount || !method || !address) {
      return res.status(400).json({
        success: false,
        message: 'Please provide amount, withdrawal method, and address'
      });
    }
    
    // Check if amount is valid
    if (amount < 20) {
      return res.status(400).json({
        success: false,
        message: 'Minimum withdrawal amount is $20'
      });
    }
    
    // Get user balance
    const user = await db.getOne(
      'SELECT balance FROM users WHERE id = ?',
      [req.user.id]
    );
    
    if (amount > user.balance) {
      return res.status(400).json({
        success: false,
        message: 'Insufficient balance'
      });
    }
    
    // Check if withdrawal method is valid
    const validMethods = ['crypto', 'bank_transfer', 'paypal', 'binance'];
    if (!validMethods.includes(method)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid withdrawal method'
      });
    }
    
    // Calculate fees
    const feePercentage = 2.0; // 2%
    const feeAmount = Math.max((amount * feePercentage) / 100, 5.0); // Minimum $5 fee
    const netAmount = amount - feeAmount;
    
    // Start transaction
    await db.transaction(async (connection) => {
      // Create withdrawal request
      const withdrawalId = uuidv4();
      await connection.execute(
        `INSERT INTO withdrawal_requests (
          id, user_id, amount, fee_amount, net_amount, withdrawal_method, 
          withdrawal_address, user_notes, status, requested_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          withdrawalId, req.user.id, amount, feeAmount, netAmount, method,
          address, notes || null, 'pending', new Date()
        ]
      );
      
      // Update user balance
      await connection.execute(
        'UPDATE users SET balance = balance - ?, updated_at = ? WHERE id = ?',
        [amount, new Date(), req.user.id]
      );
      
      // Create payment record
      const paymentId = uuidv4();
      const orderId = `WD_${Date.now()}_${req.user.id.substring(0, 8)}`;
      
      await connection.execute(
        `INSERT INTO payments (
          id, user_id, transaction_id, amount, fee_amount, net_amount, 
          payment_method, status, type, description, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          paymentId, req.user.id, orderId, amount, feeAmount, netAmount,
          method, 'pending', 'withdrawal', `Withdrawal request via ${method}`,
          new Date(), new Date()
        ]
      );
    });
    
    // Get full user data for email
    const fullUser = await db.getOne(
      'SELECT * FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Get created withdrawal
    const withdrawal = await db.getOne(
      'SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY requested_at DESC LIMIT 1',
      [req.user.id]
    );
    
    // Send withdrawal notification email
    try {
      await emailService.sendWithdrawalNotification(fullUser, withdrawal);
    } catch (emailError) {
      console.error('Failed to send withdrawal notification email:', emailError);
    }
    
    return res.status(201).json({
      success: true,
      message: 'Withdrawal request submitted successfully! It will be processed within 24 hours.',
      withdrawal: {
        id: withdrawal.id,
        amount,
        feeAmount,
        netAmount,
        method,
        status: 'pending'
      }
    });
  } catch (error) {
    console.error('Create withdrawal error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get user's withdrawals
router.get('/withdrawals', verifyToken, async (req, res) => {
  try {
    const withdrawals = await db.query(
      `SELECT * FROM withdrawal_requests
      WHERE user_id = ?
      ORDER BY requested_at DESC`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      withdrawals
    });
  } catch (error) {
    console.error('Get withdrawals error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
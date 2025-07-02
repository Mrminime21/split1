import express from 'express';
import { v4 as uuidv4 } from 'uuid';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';
import emailService from '../config/email.js';

const router = express.Router();

// Get user's investments
router.get('/', verifyToken, async (req, res) => {
  try {
    const investments = await db.query(
      `SELECT * FROM investments
      WHERE user_id = ?
      ORDER BY created_at DESC`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      investments
    });
  } catch (error) {
    console.error('Get investments error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get investment by ID
router.get('/:id', verifyToken, async (req, res) => {
  try {
    const investment = await db.getOne(
      `SELECT * FROM investments
      WHERE id = ? AND user_id = ?`,
      [req.params.id, req.user.id]
    );
    
    if (!investment) {
      return res.status(404).json({
        success: false,
        message: 'Investment not found'
      });
    }
    
    // Get investment earnings
    const earnings = await db.query(
      `SELECT * FROM investment_earnings
      WHERE investment_id = ?
      ORDER BY earning_date DESC`,
      [req.params.id]
    );
    
    return res.status(200).json({
      success: true,
      investment,
      earnings
    });
  } catch (error) {
    console.error('Get investment error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Create new investment
router.post('/', verifyToken, async (req, res) => {
  try {
    const { planType, amount } = req.body;
    
    // Validate input
    if (!planType || !amount) {
      return res.status(400).json({
        success: false,
        message: 'Please provide plan type and amount'
      });
    }
    
    // Check if plan type is valid
    const plans = {
      '3months': { duration: 90, rate: 0.27, min: 500 },
      '6months': { duration: 180, rate: 0.40, min: 1000 },
      '12months': { duration: 365, rate: 0.60, min: 2000 }
    };
    
    if (!plans[planType]) {
      return res.status(400).json({
        success: false,
        message: 'Invalid investment plan'
      });
    }
    
    const plan = plans[planType];
    
    // Check if amount is valid
    if (amount < plan.min) {
      return res.status(400).json({
        success: false,
        message: `Minimum investment for this plan is $${plan.min}`
      });
    }
    
    // Get user balance
    const user = await db.getOne(
      'SELECT balance FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Check if user has enough balance
    if (user.balance < amount) {
      return res.status(400).json({
        success: false,
        message: 'Insufficient balance'
      });
    }
    
    // Start transaction
    await db.transaction(async (connection) => {
      // Create investment
      const investmentId = uuidv4();
      const startDate = new Date();
      const endDate = new Date();
      endDate.setDate(endDate.getDate() + plan.duration);
      
      const investmentData = {
        id: investmentId,
        user_id: req.user.id,
        plan_name: `${planType.charAt(0).toUpperCase() + planType.slice(1)} Investment Plan`,
        plan_duration: plan.duration,
        investment_amount: amount,
        daily_rate: plan.rate,
        expected_daily_profit: (amount * plan.rate) / 100,
        total_earned: 0,
        status: 'active',
        start_date: startDate,
        end_date: endDate,
        actual_start_date: startDate,
        created_at: new Date(),
        updated_at: new Date()
      };
      
      await connection.execute(
        `INSERT INTO investments (
          id, user_id, plan_name, plan_duration, investment_amount, 
          daily_rate, expected_daily_profit, total_earned, status, 
          start_date, end_date, actual_start_date, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          investmentData.id, investmentData.user_id, investmentData.plan_name,
          investmentData.plan_duration, investmentData.investment_amount,
          investmentData.daily_rate, investmentData.expected_daily_profit,
          investmentData.total_earned, investmentData.status,
          investmentData.start_date, investmentData.end_date,
          investmentData.actual_start_date, investmentData.created_at, investmentData.updated_at
        ]
      );
      
      // Update user balance
      await connection.execute(
        'UPDATE users SET balance = balance - ?, total_invested = total_invested + ?, updated_at = ? WHERE id = ?',
        [amount, amount, new Date(), req.user.id]
      );
      
      // Create payment record
      const paymentId = uuidv4();
      await connection.execute(
        `INSERT INTO payments (
          id, user_id, amount, payment_method, status, type, description, processed_at, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          paymentId, req.user.id, amount, 'balance', 'completed', 'investment',
          `Investment in ${investmentData.plan_name}`, new Date(), new Date(), new Date()
        ]
      );
      
      // Update investment with payment ID
      await connection.execute(
        'UPDATE investments SET payment_id = ? WHERE id = ?',
        [paymentId, investmentId]
      );
    });
    
    // Get full user data for email
    const fullUser = await db.getOne(
      'SELECT * FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Get created investment
    const investment = await db.getOne(
      'SELECT * FROM investments WHERE id = ?',
      [investmentId]
    );
    
    // Send investment confirmation email
    try {
      await emailService.sendInvestmentConfirmation(fullUser, investment);
    } catch (emailError) {
      console.error('Failed to send investment confirmation email:', emailError);
    }
    
    return res.status(201).json({
      success: true,
      message: 'Investment created successfully! Daily profits will start tomorrow.',
      investment
    });
  } catch (error) {
    console.error('Create investment error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
import express from 'express';
import { v4 as uuidv4 } from 'uuid';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';
import emailService from '../config/email.js';

const router = express.Router();

// Get user's rentals
router.get('/', verifyToken, async (req, res) => {
  try {
    const rentals = await db.query(
      `SELECT r.*, d.name as device_name, d.location, d.model
      FROM rentals r
      JOIN devices d ON r.device_id = d.id
      WHERE r.user_id = ?
      ORDER BY r.created_at DESC`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      rentals
    });
  } catch (error) {
    console.error('Get rentals error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get rental by ID
router.get('/:id', verifyToken, async (req, res) => {
  try {
    const rental = await db.getOne(
      `SELECT r.*, d.name as device_name, d.location, d.model
      FROM rentals r
      JOIN devices d ON r.device_id = d.id
      WHERE r.id = ? AND r.user_id = ?`,
      [req.params.id, req.user.id]
    );
    
    if (!rental) {
      return res.status(404).json({
        success: false,
        message: 'Rental not found'
      });
    }
    
    // Get rental earnings
    const earnings = await db.query(
      `SELECT * FROM rental_earnings
      WHERE rental_id = ?
      ORDER BY earning_date DESC`,
      [req.params.id]
    );
    
    return res.status(200).json({
      success: true,
      rental,
      earnings
    });
  } catch (error) {
    console.error('Get rental error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Create new rental
router.post('/', verifyToken, async (req, res) => {
  try {
    const { deviceId, planType, duration } = req.body;
    
    // Validate input
    if (!deviceId || !planType || !duration) {
      return res.status(400).json({
        success: false,
        message: 'Please provide device ID, plan type, and duration'
      });
    }
    
    // Check if duration is valid
    if (duration < 30 || duration > 365) {
      return res.status(400).json({
        success: false,
        message: 'Duration must be between 30 and 365 days'
      });
    }
    
    // Check if plan type is valid
    const validPlanTypes = ['basic', 'standard', 'premium'];
    if (!validPlanTypes.includes(planType)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid plan type'
      });
    }
    
    // Get device
    const device = await db.getOne(
      'SELECT * FROM devices WHERE id = ? AND status = ?',
      [deviceId, 'available']
    );
    
    if (!device) {
      return res.status(404).json({
        success: false,
        message: 'Device not available'
      });
    }
    
    // Get user balance
    const user = await db.getOne(
      'SELECT balance FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Calculate costs based on plan type
    const plans = {
      'basic': { rate: 5.0, costPerDay: 2.0 },
      'standard': { rate: 8.0, costPerDay: 5.0 },
      'premium': { rate: 12.0, costPerDay: 10.0 }
    };
    
    const plan = plans[planType];
    const totalCost = plan.costPerDay * duration;
    const expectedDailyProfit = (totalCost * plan.rate) / 100 / duration;
    
    // Check if user has enough balance
    if (user.balance < totalCost) {
      return res.status(400).json({
        success: false,
        message: 'Insufficient balance'
      });
    }
    
    // Start transaction
    await db.transaction(async (connection) => {
      // Create rental
      const rentalId = uuidv4();
      const startDate = new Date();
      const endDate = new Date();
      endDate.setDate(endDate.getDate() + duration);
      
      const rentalData = {
        id: rentalId,
        user_id: req.user.id,
        device_id: deviceId,
        plan_type: planType,
        plan_name: `${planType.charAt(0).toUpperCase() + planType.slice(1)} Rental Plan`,
        rental_duration: duration,
        daily_profit_rate: plan.rate,
        total_cost: totalCost,
        expected_daily_profit: expectedDailyProfit,
        actual_total_profit: 0,
        status: 'active',
        start_date: startDate,
        end_date: endDate,
        actual_start_date: startDate,
        created_at: new Date(),
        updated_at: new Date()
      };
      
      await connection.execute(
        `INSERT INTO rentals (
          id, user_id, device_id, plan_type, plan_name, rental_duration, 
          daily_profit_rate, total_cost, expected_daily_profit, actual_total_profit, 
          status, start_date, end_date, actual_start_date, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          rentalData.id, rentalData.user_id, rentalData.device_id, 
          rentalData.plan_type, rentalData.plan_name, rentalData.rental_duration,
          rentalData.daily_profit_rate, rentalData.total_cost, rentalData.expected_daily_profit,
          rentalData.actual_total_profit, rentalData.status, rentalData.start_date,
          rentalData.end_date, rentalData.actual_start_date, rentalData.created_at, rentalData.updated_at
        ]
      );
      
      // Update device status
      await connection.execute(
        'UPDATE devices SET status = ?, updated_at = ? WHERE id = ?',
        ['rented', new Date(), deviceId]
      );
      
      // Update user balance
      await connection.execute(
        'UPDATE users SET balance = balance - ?, total_invested = total_invested + ?, updated_at = ? WHERE id = ?',
        [totalCost, totalCost, new Date(), req.user.id]
      );
      
      // Create payment record
      const paymentId = uuidv4();
      await connection.execute(
        `INSERT INTO payments (
          id, user_id, amount, payment_method, status, type, description, processed_at, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          paymentId, req.user.id, totalCost, 'balance', 'completed', 'rental',
          `Device rental: ${device.name}`, new Date(), new Date(), new Date()
        ]
      );
      
      // Update rental with payment ID
      await connection.execute(
        'UPDATE rentals SET payment_id = ? WHERE id = ?',
        [paymentId, rentalId]
      );
    });
    
    // Get full user data for email
    const fullUser = await db.getOne(
      'SELECT * FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Get created rental
    const rental = await db.getOne(
      'SELECT * FROM rentals WHERE id = ?',
      [rentalId]
    );
    
    // Send rental activation email
    try {
      await emailService.sendRentalActivation(fullUser, rental, device);
    } catch (emailError) {
      console.error('Failed to send rental activation email:', emailError);
    }
    
    return res.status(201).json({
      success: true,
      message: 'Device rental activated successfully! Daily profits will start tomorrow.',
      rental: {
        ...rental,
        device_name: device.name,
        location: device.location
      }
    });
  } catch (error) {
    console.error('Create rental error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
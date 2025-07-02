import express from 'express';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
import db from '../config/database.js';
import { verifyAdmin, authorize } from '../middleware/auth.js';

dotenv.config();
const router = express.Router();

// Admin login
router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    
    // Validate input
    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: 'Please provide username and password'
      });
    }
    
    // Find admin
    const admin = await db.getOne(
      'SELECT * FROM admin_users WHERE username = ? AND status = ?',
      [username, 'active']
    );
    
    if (!admin) {
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password'
      });
    }
    
    // Verify password
    const isPasswordValid = await bcrypt.compare(password, admin.password_hash);
    
    if (!isPasswordValid) {
      // Increment login attempts
      await db.update(
        'admin_users',
        { 
          login_attempts: admin.login_attempts + 1,
          updated_at: new Date()
        },
        'id = ?',
        [admin.id]
      );
      
      // Lock account after 5 failed attempts
      if (admin.login_attempts >= 4) {
        const lockUntil = new Date();
        lockUntil.setMinutes(lockUntil.getMinutes() + 30); // Lock for 30 minutes
        
        await db.update(
          'admin_users',
          { 
            locked_until: lockUntil,
            updated_at: new Date()
          },
          'id = ?',
          [admin.id]
        );
      }
      
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password'
      });
    }
    
    // Check if account is locked
    if (admin.locked_until && new Date(admin.locked_until) > new Date()) {
      return res.status(403).json({
        success: false,
        message: 'Account is locked. Please try again later.'
      });
    }
    
    // Reset login attempts
    await db.update(
      'admin_users',
      { 
        login_attempts: 0,
        locked_until: null,
        last_login: new Date(),
        ip_address: req.ip,
        updated_at: new Date()
      },
      'id = ?',
      [admin.id]
    );
    
    // Generate JWT token
    const token = jwt.sign(
      { id: admin.id, role: admin.role },
      process.env.JWT_SECRET,
      { expiresIn: '24h' }
    );
    
    return res.status(200).json({
      success: true,
      message: 'Login successful',
      token,
      admin: {
        id: admin.id,
        username: admin.username,
        email: admin.email,
        role: admin.role
      }
    });
  } catch (error) {
    console.error('Admin login error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get admin dashboard stats
router.get('/dashboard', verifyAdmin, async (req, res) => {
  try {
    const stats = {
      totalUsers: 0,
      activeDevices: 0,
      totalInvestments: 0,
      pendingWithdrawals: 0
    };
    
    // Get total users
    const totalUsers = await db.getOne(
      'SELECT COUNT(*) as count FROM users'
    );
    stats.totalUsers = totalUsers ? totalUsers.count : 0;
    
    // Get active devices
    const activeDevices = await db.getOne(
      "SELECT COUNT(*) as count FROM devices WHERE status = 'available'"
    );
    stats.activeDevices = activeDevices ? activeDevices.count : 0;
    
    // Get total investments
    const totalInvestments = await db.getOne(
      "SELECT COALESCE(SUM(investment_amount), 0) as total FROM investments WHERE status = 'active'"
    );
    stats.totalInvestments = totalInvestments ? totalInvestments.total : 0;
    
    // Get pending withdrawals
    const pendingWithdrawals = await db.getOne(
      "SELECT COUNT(*) as count FROM withdrawal_requests WHERE status = 'pending'"
    );
    stats.pendingWithdrawals = pendingWithdrawals ? pendingWithdrawals.count : 0;
    
    // Get recent users
    const recentUsers = await db.query(
      'SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5'
    );
    
    // Get recent withdrawals
    const recentWithdrawals = await db.query(
      `SELECT wr.*, u.username 
      FROM withdrawal_requests wr 
      JOIN users u ON wr.user_id = u.id 
      WHERE wr.status = 'pending' 
      ORDER BY wr.requested_at DESC 
      LIMIT 5`
    );
    
    return res.status(200).json({
      success: true,
      stats,
      recentUsers,
      recentWithdrawals
    });
  } catch (error) {
    console.error('Admin dashboard error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get all users (with pagination)
router.get('/users', verifyAdmin, async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 10;
    const offset = (page - 1) * limit;
    
    // Get users
    const users = await db.query(
      `SELECT id, username, email, balance, total_earnings, total_invested, 
      total_withdrawn, status, created_at 
      FROM users 
      ORDER BY created_at DESC 
      LIMIT ? OFFSET ?`,
      [limit, offset]
    );
    
    // Get total count
    const totalCount = await db.getOne(
      'SELECT COUNT(*) as count FROM users'
    );
    
    return res.status(200).json({
      success: true,
      users,
      pagination: {
        page,
        limit,
        totalCount: totalCount ? totalCount.count : 0,
        totalPages: Math.ceil((totalCount ? totalCount.count : 0) / limit)
      }
    });
  } catch (error) {
    console.error('Get users error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get user by ID
router.get('/users/:id', verifyAdmin, async (req, res) => {
  try {
    const user = await db.getOne(
      `SELECT id, username, email, referral_code, telegram_id, telegram_username,
      balance, total_earnings, total_invested, total_withdrawn, 
      referral_earnings, rental_earnings, investment_earnings,
      phone, country, timezone, language, status, email_verified,
      telegram_verified, kyc_status, last_login, created_at
      FROM users WHERE id = ?`,
      [req.params.id]
    );
    
    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    // Get user's rentals
    const rentals = await db.query(
      `SELECT r.*, d.name as device_name
      FROM rentals r
      JOIN devices d ON r.device_id = d.id
      WHERE r.user_id = ?
      ORDER BY r.created_at DESC`,
      [req.params.id]
    );
    
    // Get user's investments
    const investments = await db.query(
      `SELECT * FROM investments
      WHERE user_id = ?
      ORDER BY created_at DESC`,
      [req.params.id]
    );
    
    // Get user's payments
    const payments = await db.query(
      `SELECT * FROM payments
      WHERE user_id = ?
      ORDER BY created_at DESC
      LIMIT 20`,
      [req.params.id]
    );
    
    // Get user's withdrawals
    const withdrawals = await db.query(
      `SELECT * FROM withdrawal_requests
      WHERE user_id = ?
      ORDER BY requested_at DESC`,
      [req.params.id]
    );
    
    // Get user's referrals
    const referrals = await db.query(
      `SELECT r.*, u.username, u.email, u.created_at as join_date
      FROM referrals r
      JOIN users u ON r.referred_id = u.id
      WHERE r.referrer_id = ?
      ORDER BY r.level ASC, u.created_at DESC`,
      [req.params.id]
    );
    
    return res.status(200).json({
      success: true,
      user,
      rentals,
      investments,
      payments,
      withdrawals,
      referrals
    });
  } catch (error) {
    console.error('Get user error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Update user
router.put('/users/:id', verifyAdmin, async (req, res) => {
  try {
    const { username, email, status, balance } = req.body;
    
    // Check if user exists
    const user = await db.getOne(
      'SELECT id FROM users WHERE id = ?',
      [req.params.id]
    );
    
    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    // Update user data
    const updateData = {};
    
    if (username) updateData.username = username;
    if (email) updateData.email = email;
    if (status) updateData.status = status;
    if (balance !== undefined) updateData.balance = parseFloat(balance);
    
    updateData.updated_at = new Date();
    
    await db.update(
      'users',
      updateData,
      'id = ?',
      [req.params.id]
    );
    
    return res.status(200).json({
      success: true,
      message: 'User updated successfully'
    });
  } catch (error) {
    console.error('Update user error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get all devices
router.get('/devices', verifyAdmin, async (req, res) => {
  try {
    const devices = await db.query(
      'SELECT * FROM devices ORDER BY name'
    );
    
    return res.status(200).json({
      success: true,
      devices
    });
  } catch (error) {
    console.error('Get devices error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Create device
router.post('/devices', verifyAdmin, async (req, res) => {
  try {
    const { deviceId, name, model, location, dailyRate } = req.body;
    
    // Validate input
    if (!deviceId || !name || !location) {
      return res.status(400).json({
        success: false,
        message: 'Please provide device ID, name, and location'
      });
    }
    
    // Check if device ID already exists
    const existingDevice = await db.getOne(
      'SELECT id FROM devices WHERE device_id = ?',
      [deviceId]
    );
    
    if (existingDevice) {
      return res.status(400).json({
        success: false,
        message: 'Device ID already exists'
      });
    }
    
    // Create device
    const id = uuidv4();
    const deviceData = {
      id,
      device_id: deviceId,
      name,
      model: model || 'Starlink Standard',
      location,
      status: 'available',
      daily_rate: dailyRate || 15.00,
      created_at: new Date(),
      updated_at: new Date()
    };
    
    await db.insert('devices', deviceData);
    
    return res.status(201).json({
      success: true,
      message: 'Device created successfully',
      device: deviceData
    });
  } catch (error) {
    console.error('Create device error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Update device
router.put('/devices/:id', verifyAdmin, async (req, res) => {
  try {
    const { name, model, location, status, dailyRate } = req.body;
    
    // Check if device exists
    const device = await db.getOne(
      'SELECT id FROM devices WHERE id = ?',
      [req.params.id]
    );
    
    if (!device) {
      return res.status(404).json({
        success: false,
        message: 'Device not found'
      });
    }
    
    // Update device data
    const updateData = {};
    
    if (name) updateData.name = name;
    if (model) updateData.model = model;
    if (location) updateData.location = location;
    if (status) updateData.status = status;
    if (dailyRate) updateData.daily_rate = parseFloat(dailyRate);
    
    updateData.updated_at = new Date();
    
    await db.update(
      'devices',
      updateData,
      'id = ?',
      [req.params.id]
    );
    
    return res.status(200).json({
      success: true,
      message: 'Device updated successfully'
    });
  } catch (error) {
    console.error('Update device error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get all withdrawals (with pagination)
router.get('/withdrawals', verifyAdmin, async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 10;
    const offset = (page - 1) * limit;
    const status = req.query.status || 'pending';
    
    // Get withdrawals
    const withdrawals = await db.query(
      `SELECT wr.*, u.username, u.email
      FROM withdrawal_requests wr
      JOIN users u ON wr.user_id = u.id
      WHERE wr.status = ?
      ORDER BY wr.requested_at DESC
      LIMIT ? OFFSET ?`,
      [status, limit, offset]
    );
    
    // Get total count
    const totalCount = await db.getOne(
      'SELECT COUNT(*) as count FROM withdrawal_requests WHERE status = ?',
      [status]
    );
    
    return res.status(200).json({
      success: true,
      withdrawals,
      pagination: {
        page,
        limit,
        totalCount: totalCount ? totalCount.count : 0,
        totalPages: Math.ceil((totalCount ? totalCount.count : 0) / limit)
      }
    });
  } catch (error) {
    console.error('Get withdrawals error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Process withdrawal
router.put('/withdrawals/:id', verifyAdmin, async (req, res) => {
  try {
    const { status, adminNotes, transactionHash } = req.body;
    
    // Validate input
    if (!status) {
      return res.status(400).json({
        success: false,
        message: 'Please provide status'
      });
    }
    
    // Check if withdrawal exists
    const withdrawal = await db.getOne(
      'SELECT * FROM withdrawal_requests WHERE id = ?',
      [req.params.id]
    );
    
    if (!withdrawal) {
      return res.status(404).json({
        success: false,
        message: 'Withdrawal not found'
      });
    }
    
    // Check if status is valid
    const validStatuses = ['approved', 'processing', 'completed', 'rejected'];
    if (!validStatuses.includes(status)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid status'
      });
    }
    
    // Update withdrawal
    const updateData = {
      status,
      admin_notes: adminNotes || withdrawal.admin_notes,
      processed_by: req.admin.id,
      processed_at: new Date(),
      updated_at: new Date()
    };
    
    if (status === 'completed') {
      updateData.completed_at = new Date();
      updateData.transaction_hash = transactionHash || null;
      
      // Update user's total withdrawn
      await db.update(
        'users',
        { 
          total_withdrawn: db.query('SELECT total_withdrawn + ? FROM users WHERE id = ?', [withdrawal.amount, withdrawal.user_id]),
          updated_at: new Date()
        },
        'id = ?',
        [withdrawal.user_id]
      );
    } else if (status === 'rejected') {
      // Return funds to user
      await db.update(
        'users',
        { 
          balance: db.query('SELECT balance + ? FROM users WHERE id = ?', [withdrawal.amount, withdrawal.user_id]),
          updated_at: new Date()
        },
        'id = ?',
        [withdrawal.user_id]
      );
    }
    
    await db.update(
      'withdrawal_requests',
      updateData,
      'id = ?',
      [req.params.id]
    );
    
    // Update related payment
    await db.update(
      'payments',
      { 
        status: status === 'completed' ? 'completed' : (status === 'rejected' ? 'cancelled' : 'processing'),
        updated_at: new Date(),
        processed_at: status === 'completed' ? new Date() : null
      },
      'user_id = ? AND type = ? AND amount = ? AND status = ?',
      [withdrawal.user_id, 'withdrawal', withdrawal.amount, 'pending']
    );
    
    return res.status(200).json({
      success: true,
      message: `Withdrawal ${status} successfully`
    });
  } catch (error) {
    console.error('Process withdrawal error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get system settings
router.get('/settings', verifyAdmin, async (req, res) => {
  try {
    const settings = await db.query(
      'SELECT * FROM system_settings ORDER BY category, setting_key'
    );
    
    // Group settings by category
    const groupedSettings = {};
    settings.forEach(setting => {
      if (!groupedSettings[setting.category]) {
        groupedSettings[setting.category] = [];
      }
      groupedSettings[setting.category].push(setting);
    });
    
    return res.status(200).json({
      success: true,
      settings: groupedSettings
    });
  } catch (error) {
    console.error('Get settings error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Update system settings
router.put('/settings', verifyAdmin, async (req, res) => {
  try {
    const { settings } = req.body;
    
    if (!settings || !Array.isArray(settings)) {
      return res.status(400).json({
        success: false,
        message: 'Please provide settings array'
      });
    }
    
    // Update settings
    for (const setting of settings) {
      if (!setting.key || setting.value === undefined) continue;
      
      const existingSetting = await db.getOne(
        'SELECT id FROM system_settings WHERE setting_key = ?',
        [setting.key]
      );
      
      if (existingSetting) {
        await db.update(
          'system_settings',
          { 
            setting_value: setting.value.toString(),
            updated_by: req.admin.id,
            updated_at: new Date()
          },
          'setting_key = ?',
          [setting.key]
        );
      } else {
        await db.insert('system_settings', {
          setting_key: setting.key,
          setting_value: setting.value.toString(),
          setting_type: setting.type || 'string',
          category: setting.category || 'general',
          description: setting.description || null,
          is_public: setting.isPublic ? 1 : 0,
          updated_by: req.admin.id,
          created_at: new Date(),
          updated_at: new Date()
        });
      }
    }
    
    return res.status(200).json({
      success: true,
      message: 'Settings updated successfully'
    });
  } catch (error) {
    console.error('Update settings error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
import express from 'express';
import bcrypt from 'bcrypt';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';

const router = express.Router();

// Get user profile
router.get('/profile', verifyToken, async (req, res) => {
  try {
    const user = await db.getOne(
      `SELECT id, username, email, referral_code, telegram_id, telegram_username,
      balance, total_earnings, total_invested, total_withdrawn, 
      referral_earnings, rental_earnings, investment_earnings,
      phone, country, timezone, language, status, email_verified,
      telegram_verified, kyc_status, last_login, created_at
      FROM users WHERE id = ?`,
      [req.user.id]
    );
    
    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    return res.status(200).json({
      success: true,
      user
    });
  } catch (error) {
    console.error('Get profile error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Update user profile
router.put('/profile', verifyToken, async (req, res) => {
  try {
    const { username, phone, country, timezone, language } = req.body;
    
    // Check if username is already taken
    if (username && username !== req.user.username) {
      const existingUser = await db.getOne(
        'SELECT id FROM users WHERE username = ? AND id != ?',
        [username, req.user.id]
      );
      
      if (existingUser) {
        return res.status(400).json({
          success: false,
          message: 'Username is already taken'
        });
      }
    }
    
    // Update user data
    const updateData = {};
    
    if (username) updateData.username = username;
    if (phone) updateData.phone = phone;
    if (country) updateData.country = country;
    if (timezone) updateData.timezone = timezone;
    if (language) updateData.language = language;
    
    updateData.updated_at = new Date();
    
    await db.update(
      'users',
      updateData,
      'id = ?',
      [req.user.id]
    );
    
    // Get updated user data
    const updatedUser = await db.getOne(
      `SELECT id, username, email, referral_code, telegram_id, telegram_username,
      balance, total_earnings, total_invested, total_withdrawn, 
      referral_earnings, rental_earnings, investment_earnings,
      phone, country, timezone, language, status, email_verified,
      telegram_verified, kyc_status, last_login, created_at
      FROM users WHERE id = ?`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      message: 'Profile updated successfully',
      user: updatedUser
    });
  } catch (error) {
    console.error('Update profile error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Change password
router.put('/change-password', verifyToken, async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;
    
    // Validate input
    if (!currentPassword || !newPassword) {
      return res.status(400).json({
        success: false,
        message: 'Please provide current password and new password'
      });
    }
    
    if (newPassword.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'New password must be at least 6 characters long'
      });
    }
    
    // Get user with password
    const user = await db.getOne(
      'SELECT password_hash FROM users WHERE id = ?',
      [req.user.id]
    );
    
    // Verify current password
    const isPasswordValid = await bcrypt.compare(currentPassword, user.password_hash);
    
    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        message: 'Current password is incorrect'
      });
    }
    
    // Hash new password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(newPassword, salt);
    
    // Update password
    await db.update(
      'users',
      { password_hash: hashedPassword, updated_at: new Date() },
      'id = ?',
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      message: 'Password changed successfully'
    });
  } catch (error) {
    console.error('Change password error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Get user dashboard stats
router.get('/dashboard', verifyToken, async (req, res) => {
  try {
    // Get user stats
    const stats = {
      totalEarnings: 0,
      activeRentals: 0,
      referrals: 0,
      dailyProfit: 0
    };
    
    // Get total earnings
    const user = await db.getOne(
      'SELECT total_earnings FROM users WHERE id = ?',
      [req.user.id]
    );
    
    stats.totalEarnings = user.total_earnings || 0;
    
    // Get active rentals count
    const activeRentals = await db.getOne(
      'SELECT COUNT(*) as count FROM rentals WHERE user_id = ? AND status = ?',
      [req.user.id, 'active']
    );
    
    stats.activeRentals = activeRentals ? activeRentals.count : 0;
    
    // Get referrals count
    const referrals = await db.getOne(
      'SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ?',
      [req.user.id]
    );
    
    stats.referrals = referrals ? referrals.count : 0;
    
    // Get daily profit
    const dailyProfit = await db.getOne(
      'SELECT COALESCE(SUM(total_profit_amount), 0) as total FROM rental_earnings WHERE user_id = ? AND earning_date = CURDATE()',
      [req.user.id]
    );
    
    stats.dailyProfit = dailyProfit ? dailyProfit.total : 0;
    
    // Get recent activities
    const activities = await db.query(
      `SELECT 'rental' as type, 'Device activation' as action, d.name as device, re.created_at as time, re.total_profit_amount as profit
      FROM rental_earnings re 
      JOIN rentals r ON re.rental_id = r.id 
      JOIN devices d ON r.device_id = d.id 
      WHERE re.user_id = ? 
      UNION ALL
      SELECT 'referral' as type, 'Referral bonus' as action, CONCAT('User @', u.username) as device, ref_e.created_at as time, ref_e.commission_amount as profit
      FROM referral_earnings ref_e 
      JOIN users u ON ref_e.referred_id = u.id 
      WHERE ref_e.referrer_id = ?
      UNION ALL
      SELECT 'payment' as type, 
             CASE 
               WHEN p.type = 'deposit' THEN 'Deposit' 
               WHEN p.type = 'withdrawal' THEN 'Withdrawal' 
               ELSE p.type 
             END as action, 
             p.payment_method as device, 
             p.created_at as time, 
             CASE 
               WHEN p.type = 'deposit' THEN p.amount 
               WHEN p.type = 'withdrawal' THEN -p.amount 
               ELSE p.amount 
             END as profit
      FROM payments p
      WHERE p.user_id = ? AND p.status = 'completed'
      ORDER BY time DESC LIMIT 10`,
      [req.user.id, req.user.id, req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      stats,
      activities
    });
  } catch (error) {
    console.error('Get dashboard error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
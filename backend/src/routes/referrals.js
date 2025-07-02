import express from 'express';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';

const router = express.Router();

// Get user's referral stats and referrals
router.get('/', verifyToken, async (req, res) => {
  try {
    // Get referral statistics
    const stats = {
      totalReferrals: 0,
      level1Referrals: 0,
      level2Referrals: 0,
      level3Referrals: 0,
      totalEarnings: 0
    };
    
    // Get referral counts
    const referralCounts = await db.query(
      'SELECT level, COUNT(*) as count FROM referrals WHERE referrer_id = ? GROUP BY level',
      [req.user.id]
    );
    
    referralCounts.forEach(row => {
      if (row.level === 1) stats.level1Referrals = row.count;
      if (row.level === 2) stats.level2Referrals = row.count;
      if (row.level === 3) stats.level3Referrals = row.count;
    });
    
    stats.totalReferrals = stats.level1Referrals + stats.level2Referrals + stats.level3Referrals;
    
    // Get total referral earnings
    const user = await db.getOne(
      'SELECT referral_earnings FROM users WHERE id = ?',
      [req.user.id]
    );
    
    stats.totalEarnings = user.referral_earnings || 0;
    
    // Get referrals with user details
    const referrals = await db.query(
      `SELECT r.*, u.username, u.email, u.created_at as join_date, u.total_earnings, u.status
      FROM referrals r
      JOIN users u ON r.referred_id = u.id
      WHERE r.referrer_id = ?
      ORDER BY r.level ASC, u.created_at DESC`,
      [req.user.id]
    );
    
    // Get referral earnings
    const earnings = await db.query(
      `SELECT re.*, u.username
      FROM referral_earnings re
      JOIN users u ON re.referred_id = u.id
      WHERE re.referrer_id = ?
      ORDER BY re.created_at DESC
      LIMIT 20`,
      [req.user.id]
    );
    
    return res.status(200).json({
      success: true,
      stats,
      referrals,
      earnings
    });
  } catch (error) {
    console.error('Get referrals error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
import express from 'express';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { v4 as uuidv4 } from 'uuid';
import dotenv from 'dotenv';
import db from '../config/database.js';
import emailService from '../config/email.js';
import { verifyToken } from '../middleware/auth.js';

dotenv.config();
const router = express.Router();

// Register a new user
router.post('/register', async (req, res) => {
  try {
    const { username, email, password, referralCode } = req.body;
    
    // Validate input
    if (!username || !email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Please provide username, email and password'
      });
    }
    
    if (password.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'Password must be at least 6 characters long'
      });
    }
    
    // Check if user already exists
    const existingUser = await db.getOne(
      'SELECT id FROM users WHERE email = ? OR username = ?',
      [email, username]
    );
    
    if (existingUser) {
      return res.status(400).json({
        success: false,
        message: 'User with this email or username already exists'
      });
    }
    
    // Generate referral code
    const generatedReferralCode = generateReferralCode();
    
    // Find referrer if referral code provided
    let referrerId = null;
    if (referralCode) {
      const referrer = await db.getOne(
        'SELECT id FROM users WHERE referral_code = ?',
        [referralCode]
      );
      
      if (referrer) {
        referrerId = referrer.id;
      }
    }
    
    // Hash password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(password, salt);
    
    // Create user
    const userId = uuidv4();
    const userData = {
      id: userId,
      username,
      email,
      password_hash: hashedPassword,
      referral_code: generatedReferralCode,
      referred_by: referrerId,
      status: 'active',
      email_verified: false,
      ip_address: req.ip,
      created_at: new Date(),
      updated_at: new Date()
    };
    
    await db.insert('users', userData);
    
    // Send welcome email
    try {
      await emailService.sendWelcomeEmail({ id: userId, username, email, referral_code: generatedReferralCode }, referralCode);
    } catch (emailError) {
      console.error('Failed to send welcome email:', emailError);
    }
    
    // Generate JWT token
    const token = jwt.sign(
      { id: userId },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
    );
    
    // Get user data without password
    const user = await db.getOne(
      'SELECT id, username, email, referral_code, balance, status, created_at FROM users WHERE id = ?',
      [userId]
    );
    
    return res.status(201).json({
      success: true,
      message: 'User registered successfully',
      token,
      user
    });
  } catch (error) {
    console.error('Registration error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error during registration'
    });
  }
});

// Login user
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    
    // Validate input
    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Please provide email and password'
      });
    }
    
    // Find user
    const user = await db.getOne(
      'SELECT id, username, email, password_hash, status FROM users WHERE email = ?',
      [email]
    );
    
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }
    
    // Check if user is active
    if (user.status !== 'active') {
      return res.status(403).json({
        success: false,
        message: 'Your account is not active. Please contact support.'
      });
    }
    
    // Verify password
    const isPasswordValid = await bcrypt.compare(password, user.password_hash);
    
    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }
    
    // Generate JWT token
    const token = jwt.sign(
      { id: user.id },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
    );
    
    // Update last login
    await db.update(
      'users',
      { last_login: new Date(), ip_address: req.ip },
      'id = ?',
      [user.id]
    );
    
    // Get user data without password
    const userData = await db.getOne(
      `SELECT id, username, email, referral_code, balance, total_earnings, 
      total_invested, total_withdrawn, referral_earnings, rental_earnings, 
      investment_earnings, status, created_at 
      FROM users WHERE id = ?`,
      [user.id]
    );
    
    return res.status(200).json({
      success: true,
      message: 'Login successful',
      token,
      user: userData
    });
  } catch (error) {
    console.error('Login error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error during login'
    });
  }
});

// Get current user
router.get('/me', verifyToken, async (req, res) => {
  try {
    // Get user data without password
    const user = await db.getOne(
      `SELECT id, username, email, referral_code, balance, total_earnings, 
      total_invested, total_withdrawn, referral_earnings, rental_earnings, 
      investment_earnings, status, created_at 
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
    console.error('Get user error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

// Helper function to generate referral code
const generateReferralCode = () => {
  return Math.random().toString(36).substring(2, 12).toUpperCase();
};

export default router;
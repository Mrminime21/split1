import express from 'express';
import db from '../config/database.js';
import { verifyToken } from '../middleware/auth.js';

const router = express.Router();

// Get all available devices
router.get('/', verifyToken, async (req, res) => {
  try {
    const devices = await db.query(
      'SELECT * FROM devices WHERE status = ? ORDER BY name',
      ['available']
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

// Get device by ID
router.get('/:id', verifyToken, async (req, res) => {
  try {
    const device = await db.getOne(
      'SELECT * FROM devices WHERE id = ?',
      [req.params.id]
    );
    
    if (!device) {
      return res.status(404).json({
        success: false,
        message: 'Device not found'
      });
    }
    
    return res.status(200).json({
      success: true,
      device
    });
  } catch (error) {
    console.error('Get device error:', error);
    return res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

export default router;
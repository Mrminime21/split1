import nodemailer from 'nodemailer';
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';
import db from './database.js';

dotenv.config();

// Get current directory
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Create email transporter
const createTransporter = async () => {
  // Get SMTP settings from database
  const smtpSettings = await getEmailSettings();
  
  return nodemailer.createTransport({
    host: smtpSettings.smtp_host || process.env.SMTP_HOST,
    port: parseInt(smtpSettings.smtp_port || process.env.SMTP_PORT || '587'),
    secure: (smtpSettings.smtp_secure || 'tls') === 'ssl',
    auth: {
      user: smtpSettings.smtp_username || process.env.SMTP_USER,
      pass: smtpSettings.smtp_password || process.env.SMTP_PASS
    }
  });
};

// Get email settings from database
const getEmailSettings = async () => {
  try {
    const settings = await db.query(
      "SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'email_%'"
    );
    
    const config = {};
    settings.forEach(setting => {
      config[setting.setting_key] = setting.setting_value;
    });
    
    return config;
  } catch (error) {
    console.error('Error fetching email settings:', error);
    return {};
  }
};

// Get email template
const getTemplate = async (templateName) => {
  try {
    const templatePath = path.join(__dirname, '../../templates/email', `${templateName}.html`);
    return await fs.readFile(templatePath, 'utf8');
  } catch (error) {
    console.error(`Error loading email template ${templateName}:`, error);
    return getBasicTemplate();
  }
};

// Process template with variables
const processTemplate = (template, variables) => {
  // Add common variables
  const allVariables = {
    ...variables,
    site_name: process.env.SITE_NAME || 'Starlink Router Rent',
    site_url: process.env.FRONTEND_URL || 'http://localhost:5173',
    current_year: new Date().getFullYear(),
    support_email: process.env.SUPPORT_EMAIL || 'support@starlinkrouterrent.com',
    unsubscribe_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/unsubscribe`,
    current_date: new Date().toLocaleDateString()
  };

  // Replace variables in template
  let processedTemplate = template;
  Object.keys(allVariables).forEach(key => {
    const value = allVariables[key] !== undefined ? allVariables[key] : '';
    processedTemplate = processedTemplate.replace(new RegExp(`{{${key}}}`, 'g'), value);
  });

  return processedTemplate;
};

// Send email
const sendEmail = async (to, subject, template, variables = {}) => {
  try {
    // Check if email notifications are enabled
    const emailEnabled = await db.getOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'email_notifications_enabled'"
    );
    
    if (emailEnabled && emailEnabled.setting_value !== '1') {
      console.log('Email notifications are disabled');
      return false;
    }
    
    const transporter = await createTransporter();
    const emailSettings = await getEmailSettings();
    
    const emailTemplate = await getTemplate(template);
    const htmlContent = processTemplate(emailTemplate, variables);
    
    const mailOptions = {
      from: `"${emailSettings.email_from_name || process.env.EMAIL_FROM_NAME || 'Starlink Router Rent'}" <${emailSettings.email_from || process.env.EMAIL_FROM || 'noreply@starlinkrouterrent.com'}>`,
      to,
      subject,
      html: htmlContent
    };
    
    const info = await transporter.sendMail(mailOptions);
    console.log('Email sent:', info.messageId);
    
    // Log email to database
    await logEmail(to, subject, template, variables.user_id);
    
    return true;
  } catch (error) {
    console.error('Error sending email:', error);
    return false;
  }
};

// Log email to database
const logEmail = async (email, subject, template, userId = null) => {
  try {
    await db.insert('email_notifications', {
      user_id: userId,
      email,
      subject,
      template_name: template,
      status: 'sent',
      sent_at: new Date(),
      created_at: new Date()
    });
  } catch (error) {
    console.error('Error logging email:', error);
  }
};

// Basic email template fallback
const getBasicTemplate = () => {
  return `
  <!DOCTYPE html>
  <html>
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{{site_name}}</title>
  </head>
  <body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f3f4f6;">
      <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
          <div style="background: linear-gradient(135deg, #3b82f6, #06b6d4); padding: 20px; text-align: center;">
              <h1 style="color: white; margin: 0;">{{site_name}}</h1>
          </div>
          <div style="padding: 30px;">
              {{content}}
          </div>
          <div style="background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;">
              <p>&copy; {{current_year}} {{site_name}}. All rights reserved.</p>
          </div>
      </div>
  </body>
  </html>`;
};

// Email service functions
const emailService = {
  // Send welcome email
  sendWelcomeEmail: async (user, referralCode = null) => {
    const templateEnabled = await db.getOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'welcome_email_enabled'"
    );
    
    if (templateEnabled && templateEnabled.setting_value !== '1') {
      return false;
    }
    
    const variables = {
      user_id: user.id,
      user_name: user.username,
      user_email: user.email,
      referral_code: user.referral_code,
      login_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/login`,
      dashboard_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/dashboard`,
      referrer_bonus: referralCode ? '$10 referral bonus' : ''
    };
    
    return await sendEmail(
      user.email,
      'Welcome to Starlink Router Rent - Start Earning Today!',
      'welcome',
      variables
    );
  },
  
  // Send deposit confirmation email
  sendDepositConfirmation: async (user, payment) => {
    const templateEnabled = await db.getOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'deposit_email_enabled'"
    );
    
    if (templateEnabled && templateEnabled.setting_value !== '1') {
      return false;
    }
    
    const variables = {
      user_id: user.id,
      user_name: user.username,
      amount: payment.amount.toFixed(2),
      currency: (payment.crypto_currency || 'USD').toUpperCase(),
      transaction_id: payment.transaction_id,
      payment_method: payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1),
      status: payment.status.charAt(0).toUpperCase() + payment.status.slice(1),
      dashboard_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/dashboard`
    };
    
    return await sendEmail(
      user.email,
      `Deposit Confirmation - $${payment.amount.toFixed(2)} Received`,
      'deposit_confirmation',
      variables
    );
  },
  
  // Send withdrawal notification email
  sendWithdrawalNotification: async (user, withdrawal) => {
    const templateEnabled = await db.getOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'withdrawal_email_enabled'"
    );
    
    if (templateEnabled && templateEnabled.setting_value !== '1') {
      return false;
    }
    
    const variables = {
      user_id: user.id,
      user_name: user.username,
      amount: withdrawal.amount.toFixed(2),
      fee: withdrawal.fee_amount.toFixed(2),
      net_amount: withdrawal.net_amount.toFixed(2),
      method: withdrawal.withdrawal_method.charAt(0).toUpperCase() + withdrawal.withdrawal_method.slice(1),
      status: withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1),
      processing_time: getProcessingTime(withdrawal.withdrawal_method),
      dashboard_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/dashboard`
    };
    
    return await sendEmail(
      user.email,
      `Withdrawal Request - $${withdrawal.amount.toFixed(2)}`,
      'withdrawal_notification',
      variables
    );
  },
  
  // Send investment confirmation email
  sendInvestmentConfirmation: async (user, investment) => {
    const variables = {
      user_id: user.id,
      user_name: user.username,
      plan_name: investment.plan_name,
      investment_amount: investment.investment_amount.toFixed(2),
      daily_profit: investment.expected_daily_profit.toFixed(2),
      daily_rate: investment.daily_rate,
      duration: investment.plan_duration,
      end_date: new Date(investment.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      total_expected: (investment.expected_daily_profit * investment.plan_duration).toFixed(2),
      dashboard_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/dashboard`
    };
    
    return await sendEmail(
      user.email,
      `Investment Confirmed - ${investment.plan_name}`,
      'investment_confirmation',
      variables
    );
  },
  
  // Send rental activation email
  sendRentalActivation: async (user, rental, device) => {
    const variables = {
      user_id: user.id,
      user_name: user.username,
      device_name: device.name,
      device_location: device.location,
      plan_type: rental.plan_type.charAt(0).toUpperCase() + rental.plan_type.slice(1),
      daily_profit: rental.expected_daily_profit.toFixed(2),
      rental_duration: rental.rental_duration,
      total_cost: rental.total_cost.toFixed(2),
      end_date: new Date(rental.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      dashboard_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/dashboard`
    };
    
    return await sendEmail(
      user.email,
      `Device Rental Activated - ${device.name}`,
      'rental_activation',
      variables
    );
  },
  
  // Send referral bonus notification
  sendReferralBonus: async (user, referralEarning) => {
    const templateEnabled = await db.getOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'referral_email_enabled'"
    );
    
    if (templateEnabled && templateEnabled.setting_value !== '1') {
      return false;
    }
    
    const variables = {
      user_id: user.id,
      user_name: user.username,
      bonus_amount: referralEarning.commission_amount.toFixed(2),
      referral_level: referralEarning.level,
      commission_rate: referralEarning.commission_rate,
      referred_user: referralEarning.referred_username || 'User',
      total_referral_earnings: user.referral_earnings.toFixed(2),
      referrals_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/referrals`
    };
    
    return await sendEmail(
      user.email,
      `Referral Bonus - $${referralEarning.commission_amount.toFixed(2)} Earned`,
      'referral_bonus',
      variables
    );
  }
};

// Helper function to get processing time for withdrawal method
const getProcessingTime = (method) => {
  const times = {
    'crypto': '2-6 hours',
    'binance': '1-2 hours',
    'bank_transfer': '1-3 business days',
    'paypal': '24-48 hours'
  };
  
  return times[method] || '24-48 hours';
};

export default emailService;
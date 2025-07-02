import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Satellite, Mail, Lock, User, Eye, EyeOff, AlertCircle } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { supabase } from '../lib/supabase';
import toast from 'react-hot-toast';

const Login: React.FC = () => {
  const { signIn, signUp, loading } = useAuth();
  const navigate = useNavigate();
  const [isLogin, setIsLogin] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [showForgotPassword, setShowForgotPassword] = useState(false);
  const [resetLoading, setResetLoading] = useState(false);
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    username: '',
    referralCode: ''
  });
  const [errors, setErrors] = useState<{[key: string]: string}>({});

  const validateForm = () => {
    const newErrors: {[key: string]: string} = {};

    if (!formData.email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }

    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (!isLogin && formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters long';
    }

    if (!isLogin) {
      if (!formData.username) {
        newErrors.username = 'Username is required';
      } else if (formData.username.length < 3) {
        newErrors.username = 'Username must be at least 3 characters long';
      } else if (!/^[a-zA-Z0-9_]+$/.test(formData.username)) {
        newErrors.username = 'Username can only contain letters, numbers, and underscores';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    try {
      if (isLogin) {
        await signIn(formData.email, formData.password);
        navigate('/dashboard');
      } else {
        await signUp(formData.email, formData.password, formData.username, formData.referralCode);
        // Don't navigate immediately after signup, let user verify email first
        toast.success('Please check your email to verify your account before signing in.');
        setIsLogin(true); // Switch to login mode
        setFormData({ ...formData, password: '', username: '', referralCode: '' });
      }
    } catch (error) {
      // Error is already handled in the auth context
      console.error('Authentication error:', error);
    }
  };

  const handlePasswordReset = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.email) {
      setErrors({ email: 'Please enter your email address' });
      return;
    }

    if (!/\S+@\S+\.\S+/.test(formData.email)) {
      setErrors({ email: 'Please enter a valid email address' });
      return;
    }

    setResetLoading(true);
    setErrors({});

    try {
      const { error } = await supabase.auth.resetPasswordForEmail(formData.email, {
        redirectTo: `${window.location.origin}/reset-password`,
      });

      if (error) {
        throw error;
      }

      toast.success('Password reset email sent! Please check your inbox.');
      setShowForgotPassword(false);
    } catch (error: any) {
      console.error('Password reset error:', error);
      toast.error(error.message || 'Failed to send password reset email. Please try again.');
    } finally {
      setResetLoading(false);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors({
        ...errors,
        [name]: ''
      });
    }
  };

  const switchMode = () => {
    setIsLogin(!isLogin);
    setErrors({});
    setShowForgotPassword(false);
    setFormData({
      email: formData.email, // Keep email when switching
      password: '',
      username: '',
      referralCode: ''
    });
  };

  const toggleForgotPassword = () => {
    setShowForgotPassword(!showForgotPassword);
    setErrors({});
  };

  return (
    <div className="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full">
        <div className="glass-effect p-8 rounded-2xl backdrop-blur-sm">
          <div className="text-center mb-8">
            <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
              <Satellite className="h-8 w-8 text-white" />
            </div>
            <h2 className="text-3xl font-bold text-white">
              {showForgotPassword ? 'Reset Password' : (isLogin ? 'Welcome Back' : 'Join Starlink Router Rent')}
            </h2>
            <p className="text-gray-300 mt-2">
              {showForgotPassword 
                ? 'Enter your email to receive a password reset link'
                : (isLogin ? 'Sign in to your account' : 'Create your account and start earning')
              }
            </p>
          </div>

          {showForgotPassword ? (
            <form onSubmit={handlePasswordReset} className="space-y-6">
              <div>
                <label className="block text-gray-300 mb-2">Email Address</label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className={`input-field pl-10 ${errors.email ? 'border-red-500' : ''}`}
                    placeholder="Enter your email"
                  />
                  {errors.email && (
                    <div className="flex items-center mt-1 text-red-400 text-sm">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.email}
                    </div>
                  )}
                </div>
              </div>

              <button
                type="submit"
                disabled={resetLoading}
                className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {resetLoading ? 'Sending...' : 'Send Reset Link'}
              </button>

              <div className="text-center">
                <button
                  type="button"
                  onClick={toggleForgotPassword}
                  className="text-cyan-400 hover:text-cyan-300 font-medium"
                >
                  Back to Sign In
                </button>
              </div>
            </form>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-6">
              {!isLogin && (
                <div>
                  <label className="block text-gray-300 mb-2">Username</label>
                  <div className="relative">
                    <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                      type="text"
                      name="username"
                      value={formData.username}
                      onChange={handleInputChange}
                      className={`input-field pl-10 ${errors.username ? 'border-red-500' : ''}`}
                      placeholder="Enter your username"
                    />
                    {errors.username && (
                      <div className="flex items-center mt-1 text-red-400 text-sm">
                        <AlertCircle className="h-4 w-4 mr-1" />
                        {errors.username}
                      </div>
                    )}
                  </div>
                </div>
              )}

              <div>
                <label className="block text-gray-300 mb-2">Email Address</label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className={`input-field pl-10 ${errors.email ? 'border-red-500' : ''}`}
                    placeholder="Enter your email"
                  />
                  {errors.email && (
                    <div className="flex items-center mt-1 text-red-400 text-sm">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.email}
                    </div>
                  )}
                </div>
              </div>

              <div>
                <div className="flex justify-between items-center mb-2">
                  <label className="block text-gray-300">Password</label>
                  {isLogin && (
                    <button
                      type="button"
                      onClick={toggleForgotPassword}
                      className="text-cyan-400 hover:text-cyan-300 text-sm font-medium"
                    >
                      Forgot Password?
                    </button>
                  )}
                </div>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <input
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    value={formData.password}
                    onChange={handleInputChange}
                    className={`input-field pl-10 pr-10 ${errors.password ? 'border-red-500' : ''}`}
                    placeholder="Enter your password"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white"
                  >
                    {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                  </button>
                  {errors.password && (
                    <div className="flex items-center mt-1 text-red-400 text-sm">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.password}
                    </div>
                  )}
                </div>
              </div>

              {!isLogin && (
                <div>
                  <label className="block text-gray-300 mb-2">Referral Code (Optional)</label>
                  <input
                    type="text"
                    name="referralCode"
                    value={formData.referralCode}
                    onChange={handleInputChange}
                    className="input-field"
                    placeholder="Enter referral code"
                  />
                  <p className="text-gray-400 text-sm mt-1">Enter a referral code to earn bonus rewards</p>
                </div>
              )}

              <button
                type="submit"
                disabled={loading}
                className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? 'Processing...' : (isLogin ? 'Sign In' : 'Create Account')}
              </button>
            </form>
          )}

          {!showForgotPassword && (
            <div className="mt-6 text-center">
              <p className="text-gray-300">
                {isLogin ? "Don't have an account?" : "Already have an account?"}
                <button
                  onClick={switchMode}
                  className="text-cyan-400 hover:text-cyan-300 ml-2 font-medium"
                >
                  {isLogin ? 'Sign up' : 'Sign in'}
                </button>
              </p>
            </div>
          )}

          {isLogin && !showForgotPassword && (
            <div className="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
              <p className="text-yellow-300 text-sm">
                <strong>Note:</strong> If you just created an account, please check your email and verify your account before signing in.
              </p>
            </div>
          )}

          {!isLogin && !showForgotPassword && (
            <div className="mt-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
              <h4 className="text-white font-medium mb-2">🎁 Welcome Bonus</h4>
              <p className="text-gray-300 text-sm">
                Sign up now and get a $10 welcome bonus plus access to our 3-level referral system!
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Login;
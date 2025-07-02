import React, { useState } from 'react';
import { 
  Minus, 
  Bitcoin, 
  Building, 
  Wallet,
  Clock,
  Shield,
  AlertCircle
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';
import toast from 'react-hot-toast';

const Withdrawal: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [amount, setAmount] = useState<number>(0);
  const [method, setMethod] = useState<string>('');
  const [address, setAddress] = useState<string>('');
  const [notes, setNotes] = useState<string>('');
  const [loading, setLoading] = useState(false);

  const withdrawalMethods = [
    {
      id: 'crypto',
      name: 'Cryptocurrency',
      icon: Bitcoin,
      description: 'Bitcoin, Ethereum, USDT and more',
      processingTime: '2-6 hours',
      addressLabel: 'Cryptocurrency Wallet Address',
      addressPlaceholder: 'Enter your wallet address (BTC, ETH, USDT, etc.)'
    },
    {
      id: 'binance',
      name: 'Binance Pay',
      icon: Wallet,
      description: 'Direct to Binance account',
      processingTime: '1-2 hours',
      addressLabel: 'Binance Email/ID',
      addressPlaceholder: 'Enter your Binance account email or user ID'
    },
    {
      id: 'bank_transfer',
      name: 'Bank Transfer',
      icon: Building,
      description: 'Direct bank wire transfer',
      processingTime: '1-3 business days',
      addressLabel: 'Bank Account Details',
      addressPlaceholder: 'Bank name, account number, routing number, SWIFT code, etc.'
    }
  ];

  const calculateFees = () => {
    if (amount <= 0) return { fee: 0, net: 0 };
    
    const feePercentage = 2.0; // 2%
    const fee = Math.max((amount * feePercentage) / 100, 5.0); // Minimum $5 fee
    const net = amount - fee;
    
    return { fee, net };
  };

  const { fee, net } = calculateFees();

  const handleWithdrawal = async () => {
    if (!user) {
      toast.error('Please sign in to make a withdrawal');
      return;
    }

    if (amount < 20) {
      toast.error('Minimum withdrawal amount is $20');
      return;
    }

    if (amount > user.balance) {
      toast.error('Insufficient balance');
      return;
    }

    if (!method) {
      toast.error('Please select a withdrawal method');
      return;
    }

    if (!address.trim()) {
      toast.error('Please enter withdrawal address/details');
      return;
    }

    setLoading(true);

    try {
      // Create withdrawal request
      const { error } = await supabase
        .from('withdrawal_requests')
        .insert({
          user_id: user.id,
          amount: amount,
          fee_amount: fee,
          net_amount: net,
          withdrawal_method: method,
          withdrawal_address: address,
          user_notes: notes,
          status: 'pending'
        });

      if (error) throw error;

      // Update user balance (hold the funds)
      const { error: userError } = await supabase
        .from('users')
        .update({ balance: user.balance - amount })
        .eq('id', user.id);

      if (userError) throw userError;

      toast.success('Withdrawal request submitted successfully! It will be processed within 24 hours.');
      
      // Reset form
      setAmount(0);
      setMethod('');
      setAddress('');
      setNotes('');
    } catch (error: any) {
      console.error('Error creating withdrawal:', error);
      toast.error(error.message || 'Failed to create withdrawal request');
    } finally {
      setLoading(false);
    }
  };

  const selectedMethod = withdrawalMethods.find(m => m.id === method);

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <Minus className="h-16 w-16 text-blue-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Sign in to withdraw funds</h2>
          <p className="text-gray-300 mb-6">Withdraw your earnings to your preferred payment method</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-white mb-4">Withdraw Funds</h1>
          <p className="text-xl text-gray-300">
            Withdraw your earnings to your preferred payment method
          </p>
        </div>

        <div className="grid lg:grid-cols-2 gap-8">
          {/* Withdrawal Form */}
          <div className="stat-card">
            <h3 className="text-2xl font-bold text-white mb-6">Request Withdrawal</h3>
            
            <div className="space-y-6">
              <div>
                <label className="block text-gray-300 mb-2">Withdrawal Amount ($)</label>
                <input 
                  type="number" 
                  value={amount || ''} 
                  onChange={(e) => setAmount(parseFloat(e.target.value) || 0)}
                  min="20" 
                  max={user.balance}
                  step="1" 
                  className="input-field"
                  placeholder="Enter amount (min $20)"
                />
                <p className="text-gray-400 text-sm mt-1">
                  Available: ${user.balance.toFixed(2)}
                </p>
              </div>

              <div>
                <label className="block text-gray-300 mb-2">Withdrawal Method</label>
                <select 
                  value={method} 
                  onChange={(e) => setMethod(e.target.value)}
                  className="input-field"
                  required
                >
                  <option value="">Select method</option>
                  {withdrawalMethods.map((withdrawalMethod) => (
                    <option key={withdrawalMethod.id} value={withdrawalMethod.id}>
                      {withdrawalMethod.name} - {withdrawalMethod.processingTime}
                    </option>
                  ))}
                </select>
              </div>

              {selectedMethod && (
                <div>
                  <label className="block text-gray-300 mb-2">{selectedMethod.addressLabel}</label>
                  <textarea 
                    value={address} 
                    onChange={(e) => setAddress(e.target.value)}
                    rows={3}
                    className="input-field"
                    placeholder={selectedMethod.addressPlaceholder}
                    required
                  />
                </div>
              )}

              <div>
                <label className="block text-gray-300 mb-2">Notes (Optional)</label>
                <textarea 
                  value={notes} 
                  onChange={(e) => setNotes(e.target.value)}
                  rows={2}
                  className="input-field"
                  placeholder="Additional notes for the withdrawal"
                />
              </div>

              {amount > 0 && (
                <div className="bg-slate-700/50 p-4 rounded-lg">
                  <h4 className="text-white font-semibold mb-3">Withdrawal Summary</h4>
                  <div className="space-y-2">
                    <div className="flex justify-between">
                      <span className="text-gray-300">Withdrawal Amount:</span>
                      <span className="text-white font-semibold">${amount.toFixed(2)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-300">Processing Fee (2%):</span>
                      <span className="text-red-400">-${fee.toFixed(2)}</span>
                    </div>
                    <div className="flex justify-between border-t border-gray-600 pt-2">
                      <span className="text-gray-300">Net Amount:</span>
                      <span className="text-green-400 font-semibold text-lg">${net.toFixed(2)}</span>
                    </div>
                  </div>
                </div>
              )}

              <div className="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-lg">
                <div className="flex items-center space-x-2 mb-2">
                  <AlertCircle className="h-5 w-5 text-yellow-400" />
                  <span className="text-yellow-400 font-medium">Withdrawal Fees</span>
                </div>
                <p className="text-gray-300 text-sm">
                  A 2% fee (minimum $5) will be deducted from your withdrawal amount.
                </p>
              </div>

              <button 
                onClick={handleWithdrawal}
                disabled={amount < 20 || !method || !address.trim() || loading}
                className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? 'Processing...' : 'Submit Withdrawal Request'}
              </button>
            </div>
          </div>

          {/* Account Info */}
          <div className="space-y-6">
            <div className="stat-card">
              <h4 className="text-lg font-semibold text-white mb-4">Account Summary</h4>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-400">Available Balance:</span>
                  <span className="text-green-400 font-semibold">${user.balance.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Total Earnings:</span>
                  <span className="text-white">${user.total_earnings.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Total Withdrawn:</span>
                  <span className="text-white">${user.total_withdrawn.toFixed(2)}</span>
                </div>
              </div>
            </div>

            <div className="stat-card">
              <h4 className="text-lg font-semibold text-white mb-4">Withdrawal Information</h4>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-400">Minimum Withdrawal:</span>
                  <span className="text-white">$20</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Processing Time:</span>
                  <span className="text-white">24-48 hours</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Withdrawal Fee:</span>
                  <span className="text-white">2% (min $5)</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Daily Limit:</span>
                  <span className="text-white">$5,000</span>
                </div>
              </div>
            </div>

            <div className="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
              <div className="flex items-center space-x-2 mb-2">
                <Clock className="h-5 w-5 text-blue-400" />
                <span className="text-blue-400 font-medium">Processing Times</span>
              </div>
              <ul className="text-gray-300 text-sm space-y-1">
                <li>• Crypto: 2-6 hours</li>
                <li>• Binance Pay: 1-2 hours</li>
                <li>• Bank Transfer: 1-3 business days</li>
              </ul>
            </div>

            <div className="bg-green-500/10 border border-green-500/20 p-4 rounded-lg">
              <div className="flex items-center space-x-2 mb-2">
                <Shield className="h-5 w-5 text-green-400" />
                <span className="text-green-400 font-medium">Security Notice</span>
              </div>
              <p className="text-gray-300 text-sm">
                All withdrawals are manually reviewed for security. You'll receive email confirmation once processed.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Withdrawal;
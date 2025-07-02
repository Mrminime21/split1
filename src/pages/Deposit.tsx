import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  Plus, 
  Bitcoin, 
  CreditCard, 
  Building, 
  Wallet,
  Shield,
  Clock,
  CheckCircle
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';
import toast from 'react-hot-toast';

const Deposit: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [amount, setAmount] = useState<number>(0);
  const [method, setMethod] = useState<string>('');
  const [loading, setLoading] = useState(false);

  const paymentMethods = [
    {
      id: 'crypto',
      name: 'Cryptocurrency',
      icon: Bitcoin,
      description: 'Bitcoin, Ethereum, USDT and more via Plisio.net',
      features: ['Instant processing', 'Low fees', 'Secure'],
      color: 'from-orange-500 to-yellow-500'
    },
    {
      id: 'binance',
      name: 'Binance Pay',
      icon: Wallet,
      description: 'Direct from Binance account',
      features: ['Fast transfer', 'Low fees', 'Secure'],
      color: 'from-yellow-500 to-yellow-600'
    },
    {
      id: 'card',
      name: 'Credit/Debit Card',
      icon: CreditCard,
      description: 'Visa, Mastercard, American Express',
      features: ['Instant', 'Widely accepted', 'Secure'],
      color: 'from-blue-500 to-cyan-400'
    },
    {
      id: 'bank_transfer',
      name: 'Bank Transfer',
      icon: Building,
      description: 'Direct bank wire transfer',
      features: ['Large amounts', 'Secure', 'Reliable'],
      color: 'from-green-500 to-blue-500'
    }
  ];

  const handleDeposit = async () => {
    if (!user) {
      toast.error('Please sign in to make a deposit');
      return;
    }

    if (amount < 50) {
      toast.error('Minimum deposit amount is $50');
      return;
    }

    if (amount > 10000) {
      toast.error('Maximum deposit amount is $10,000');
      return;
    }

    if (!method) {
      toast.error('Please select a payment method');
      return;
    }

    setLoading(true);

    try {
      // Generate unique order ID
      const orderId = `DEP_${Date.now()}_${user.id}_${Math.floor(Math.random() * 10000)}`;

      // Create payment record
      const { error } = await supabase
        .from('payments')
        .insert({
          user_id: user.id,
          transaction_id: orderId,
          amount: amount,
          currency: 'USD',
          payment_method: method,
          status: 'pending',
          type: 'deposit',
          description: `Account deposit via ${method}`
        });

      if (error) throw error;

      if (method === 'crypto') {
        // For crypto payments, redirect to Plisio payment page
        toast.success('Redirecting to crypto payment gateway...');
        // In a real implementation, you would redirect to Plisio
        setTimeout(() => {
          toast.success('Payment created successfully! (Demo mode)');
        }, 2000);
      } else {
        toast.success('Deposit request created successfully!');
      }

      // Reset form
      setAmount(0);
      setMethod('');
    } catch (error: any) {
      console.error('Error creating deposit:', error);
      toast.error(error.message || 'Failed to create deposit');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <Plus className="h-16 w-16 text-green-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Sign in to deposit funds</h2>
          <p className="text-gray-300 mb-6">Add funds to your account to start renting routers and investing</p>
          <Link to="/login" className="btn-primary">
            Sign In
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-white mb-4">Deposit Funds</h1>
          <p className="text-xl text-gray-300">
            Add funds to your account to start investing and renting routers
          </p>
        </div>

        <div className="grid lg:grid-cols-2 gap-8">
          {/* Deposit Form */}
          <div className="stat-card">
            <h3 class="text-2xl font-bold text-white mb-6">Make Deposit</h3>
            
            <div className="space-y-6">
              <div>
                <label className="block text-gray-300 mb-2">Deposit Amount ($)</label>
                <input 
                  type="number" 
                  value={amount || ''} 
                  onChange={(e) => setAmount(parseFloat(e.target.value) || 0)}
                  min="50" 
                  max="10000" 
                  step="10" 
                  className="input-field"
                  placeholder="Enter amount (min $50)"
                />
                <p className="text-gray-400 text-sm mt-1">Minimum: $50 | Maximum: $10,000</p>
              </div>

              <div>
                <label className="block text-gray-300 mb-2">Payment Method</label>
                <div className="space-y-3">
                  {paymentMethods.map((paymentMethod) => (
                    <label key={paymentMethod.id} className="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                      <input 
                        type="radio" 
                        name="method" 
                        value={paymentMethod.id}
                        checked={method === paymentMethod.id}
                        onChange={(e) => setMethod(e.target.value)}
                        className="mr-3" 
                      />
                      <div className="flex items-center space-x-3 flex-1">
                        <div className={`bg-gradient-to-r ${paymentMethod.color} p-2 rounded-lg`}>
                          <paymentMethod.icon className="h-5 w-5 text-white" />
                        </div>
                        <div className="flex-1">
                          <h4 className="text-white font-medium">{paymentMethod.name}</h4>
                          <p className="text-gray-400 text-sm">{paymentMethod.description}</p>
                          <div className="flex space-x-2 mt-1">
                            {paymentMethod.features.map((feature, index) => (
                              <span key={index} className="text-green-400 text-xs">
                                ✓ {feature}
                              </span>
                            ))}
                          </div>
                        </div>
                      </div>
                    </label>
                  ))}
                </div>
              </div>

              <button 
                onClick={handleDeposit}
                disabled={amount < 50 || !method || loading}
                className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? 'Processing...' : 'Create Deposit Request'}
              </button>
            </div>
          </div>

          {/* Account Info */}
          <div className="space-y-6">
            <div className="stat-card">
              <h4 className="text-lg font-semibold text-white mb-4">Account Balance</h4>
              <div className="text-3xl font-bold text-green-400 mb-2">
                ${user.balance.toFixed(2)}
              </div>
              <p className="text-gray-400">Available for investment and rentals</p>
            </div>

            <div className="stat-card">
              <h4 className="text-lg font-semibold text-white mb-4">Deposit Information</h4>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-400">Minimum Deposit:</span>
                  <span className="text-white">$50</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Maximum Deposit:</span>
                  <span className="text-white">$10,000</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Crypto Processing:</span>
                  <span className="text-white">5-30 minutes</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Other Methods:</span>
                  <span className="text-white">1-3 business days</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Deposit Fee:</span>
                  <span className="text-green-400">Free</span>
                </div>
              </div>
            </div>

            <div className="bg-orange-500/10 border border-orange-500/20 p-4 rounded-lg">
              <div className="flex items-center space-x-2 mb-2">
                <Bitcoin className="h-5 w-5 text-orange-400" />
                <span className="text-orange-400 font-medium">Crypto Payments via Plisio.net</span>
              </div>
              <ul className="text-gray-300 text-sm space-y-1">
                <li>• Instant processing and confirmation</li>
                <li>• Support for 15+ cryptocurrencies</li>
                <li>• Real-time exchange rates</li>
                <li>• Secure and encrypted transactions</li>
              </ul>
            </div>

            <div className="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
              <div className="flex items-center space-x-2 mb-2">
                <Shield className="h-5 w-5 text-blue-400" />
                <span className="text-blue-400 font-medium">Secure Deposits</span>
              </div>
              <p className="text-gray-300 text-sm">
                All deposits are processed through secure payment gateways with bank-level encryption and fraud protection.
              </p>
            </div>
          </div>
        </div>

        {/* Processing Times */}
        <div className="mt-12 stat-card">
          <h3 className="text-2xl font-bold text-white mb-6 text-center">Processing Times</h3>
          
          <div className="grid md:grid-cols-4 gap-6">
            <div className="text-center">
              <div className="bg-orange-500/20 p-4 rounded-lg mb-3">
                <Bitcoin className="h-8 w-8 text-orange-400 mx-auto" />
              </div>
              <h4 className="text-white font-semibold mb-2">Cryptocurrency</h4>
              <p className="text-green-400 font-medium">5-30 minutes</p>
            </div>

            <div className="text-center">
              <div className="bg-yellow-500/20 p-4 rounded-lg mb-3">
                <Wallet className="h-8 w-8 text-yellow-400 mx-auto" />
              </div>
              <h4 className="text-white font-semibold mb-2">Binance Pay</h4>
              <p className="text-green-400 font-medium">1-2 hours</p>
            </div>

            <div className="text-center">
              <div className="bg-blue-500/20 p-4 rounded-lg mb-3">
                <CreditCard className="h-8 w-8 text-blue-400 mx-auto" />
              </div>
              <h4 className="text-white font-semibold mb-2">Credit Card</h4>
              <p className="text-yellow-400 font-medium">Instant</p>
            </div>

            <div className="text-center">
              <div className="bg-green-500/20 p-4 rounded-lg mb-3">
                <Building className="h-8 w-8 text-green-400 mx-auto" />
              </div>
              <h4 className="text-white font-semibold mb-2">Bank Transfer</h4>
              <p className="text-yellow-400 font-medium">1-3 days</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Deposit;
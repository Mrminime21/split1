import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  TrendingUp, 
  DollarSign, 
  Calendar, 
  CheckCircle,
  Plus,
  BarChart3
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';
import toast from 'react-hot-toast';

interface InvestmentPlan {
  id: string;
  name: string;
  duration: number;
  rate: number;
  minAmount: number;
  description: string;
  features: string[];
  popular?: boolean;
}

const Investment: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [selectedPlan, setSelectedPlan] = useState<string>('');
  const [amount, setAmount] = useState<number>(0);
  const [loading, setLoading] = useState(false);

  const plans: InvestmentPlan[] = [
    {
      id: '3months',
      name: '3 Months Plan',
      duration: 90,
      rate: 0.27,
      minAmount: 500,
      description: 'Short-term investment with steady returns',
      features: ['0.27% Daily Return', '24% Total Return', '$500 Minimum', 'Guaranteed Profits']
    },
    {
      id: '6months',
      name: '6 Months Plan',
      duration: 180,
      rate: 0.40,
      minAmount: 1000,
      description: 'Balanced investment for optimal growth',
      features: ['0.40% Daily Return', '72% Total Return', '$1,000 Minimum', 'Premium Support'],
      popular: true
    },
    {
      id: '12months',
      name: '12 Months Plan',
      duration: 365,
      rate: 0.60,
      minAmount: 2000,
      description: 'Long-term investment for maximum returns',
      features: ['0.60% Daily Return', '216% Total Return', '$2,000 Minimum', 'VIP Benefits']
    }
  ];

  const handleInvestment = async () => {
    if (!user) {
      toast.error('Please sign in to make an investment');
      return;
    }

    if (!selectedPlan || amount <= 0) {
      toast.error('Please select a plan and enter an amount');
      return;
    }

    const plan = plans.find(p => p.id === selectedPlan);
    if (!plan) return;

    if (amount < plan.minAmount) {
      toast.error(`Minimum investment for this plan is $${plan.minAmount.toLocaleString()}`);
      return;
    }

    if (amount > user.balance) {
      toast.error('Insufficient balance. Please deposit funds first.');
      return;
    }

    setLoading(true);

    try {
      const expectedDailyProfit = (amount * plan.rate) / 100;
      const endDate = new Date();
      endDate.setDate(endDate.getDate() + plan.duration);

      // Create investment record
      const { error: investmentError } = await supabase
        .from('investments')
        .insert({
          user_id: user.id,
          plan_name: plan.name,
          plan_duration: plan.duration,
          investment_amount: amount,
          daily_rate: plan.rate,
          expected_daily_profit: expectedDailyProfit,
          status: 'active',
          start_date: new Date().toISOString().split('T')[0],
          end_date: endDate.toISOString().split('T')[0],
          actual_start_date: new Date().toISOString().split('T')[0]
        });

      if (investmentError) throw investmentError;

      // Update user balance
      const { error: userError } = await supabase
        .from('users')
        .update({ 
          balance: user.balance - amount,
          total_invested: user.total_invested + amount
        })
        .eq('id', user.id);

      if (userError) throw userError;

      toast.success('Investment created successfully! Daily profits will start tomorrow.');
      
      // Reset form
      setSelectedPlan('');
      setAmount(0);
    } catch (error: any) {
      console.error('Error creating investment:', error);
      toast.error(error.message || 'Failed to create investment');
    } finally {
      setLoading(false);
    }
  };

  const calculateReturns = () => {
    if (!selectedPlan || amount <= 0) return null;

    const plan = plans.find(p => p.id === selectedPlan);
    if (!plan) return null;

    const dailyProfit = (amount * plan.rate) / 100;
    const monthlyProfit = dailyProfit * 30;
    const totalReturn = amount + (dailyProfit * plan.duration);

    return { dailyProfit, monthlyProfit, totalReturn };
  };

  const returns = calculateReturns();

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <TrendingUp className="h-16 w-16 text-purple-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Sign in to invest</h2>
          <p className="text-gray-300 mb-6">Access premium investment plans with guaranteed daily returns</p>
          <Link to="/login" className="btn-primary">
            Sign In
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-white mb-4">Investment Plans</h1>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            Invest in our Starlink router network and earn guaranteed daily profits with transparent returns
          </p>
        </div>

        {/* Investment Plans */}
        <div className="grid md:grid-cols-3 gap-8 mb-12">
          {plans.map((plan) => (
            <div key={plan.id} className={`relative stat-card card-hover ${plan.popular ? 'border-blue-500 scale-105' : ''}`}>
              {plan.popular && (
                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                  <span className="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                    Best Value
                  </span>
                </div>
              )}

              <div className="text-center mb-6">
                <h3 className="text-2xl font-bold text-white mb-2">{plan.name}</h3>
                <div className="text-3xl font-bold text-green-400 mb-2">{(plan.rate * plan.duration / 100 * 12).toFixed(0)}%</div>
                <p className="text-gray-300 text-sm">{plan.description}</p>
              </div>

              <ul className="space-y-3 mb-8">
                {plan.features.map((feature, index) => (
                  <li key={index} className="flex items-center">
                    <CheckCircle className="h-5 w-5 text-green-400 mr-3" />
                    <span className="text-gray-300">{feature}</span>
                  </li>
                ))}
              </ul>

              <button
                onClick={() => setSelectedPlan(plan.id)}
                className={`w-full py-3 rounded-lg font-semibold transition-all ${
                  selectedPlan === plan.id
                    ? 'bg-gradient-to-r from-blue-500 to-cyan-400 text-white'
                    : plan.popular
                    ? 'bg-gradient-to-r from-blue-500 to-cyan-400 text-white hover:from-blue-600 hover:to-cyan-500'
                    : 'bg-slate-700 text-white hover:bg-slate-600'
                }`}
              >
                {selectedPlan === plan.id ? 'Selected' : 'Select Plan'}
              </button>
            </div>
          ))}
        </div>

        {/* Investment Form */}
        <div className="max-w-2xl mx-auto stat-card mb-8">
          <h3 className="text-2xl font-bold text-white mb-6 text-center">Make Investment</h3>
          
          <div className="space-y-6">
            <div>
              <label className="block text-gray-300 mb-2">Selected Plan</label>
              <select 
                value={selectedPlan} 
                onChange={(e) => setSelectedPlan(e.target.value)}
                className="input-field"
                required
              >
                <option value="">Select a plan</option>
                {plans.map((plan) => (
                  <option key={plan.id} value={plan.id}>
                    {plan.name} - {plan.rate}% Daily
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-gray-300 mb-2">Investment Amount ($)</label>
              <input 
                type="number" 
                value={amount || ''} 
                onChange={(e) => setAmount(parseFloat(e.target.value) || 0)}
                min={selectedPlan ? plans.find(p => p.id === selectedPlan)?.minAmount : 500}
                step="100" 
                className="input-field"
                placeholder="Enter investment amount"
              />
              <p className="text-gray-400 text-sm mt-1">
                Your balance: ${user.balance.toFixed(2)}
                {selectedPlan && (
                  <span className="ml-2">
                    | Minimum: ${plans.find(p => p.id === selectedPlan)?.minAmount.toLocaleString()}
                  </span>
                )}
              </p>
            </div>

            {returns && (
              <div className="bg-slate-700/50 p-4 rounded-lg">
                <h4 className="text-white font-semibold mb-3">Investment Summary</h4>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-gray-300">Daily Profit:</span>
                    <span className="text-green-400 font-semibold">${returns.dailyProfit.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-300">Monthly Profit:</span>
                    <span className="text-green-400 font-semibold">${returns.monthlyProfit.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-300">Total Return:</span>
                    <span className="text-green-400 font-semibold text-xl">${returns.totalReturn.toFixed(2)}</span>
                  </div>
                </div>
              </div>
            )}

            <button 
              onClick={handleInvestment}
              disabled={!selectedPlan || amount <= 0 || loading}
              className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Creating Investment...' : 'Create Investment'}
            </button>
          </div>
        </div>

        {/* Investment Benefits */}
        <div className="grid md:grid-cols-2 gap-8 mb-8">
          <div className="stat-card">
            <h3 className="text-xl font-semibold text-white mb-4 flex items-center">
              <DollarSign className="h-5 w-5 mr-2 text-green-400" />
              Guaranteed Returns
            </h3>
            <ul className="space-y-2 text-gray-300">
              <li>• Fixed daily profit rates</li>
              <li>• Automatic profit distribution</li>
              <li>• No hidden fees or charges</li>
              <li>• Transparent profit calculation</li>
            </ul>
          </div>

          <div className="stat-card">
            <h3 className="text-xl font-semibold text-white mb-4 flex items-center">
              <BarChart3 className="h-5 w-5 mr-2 text-blue-400" />
              Investment Features
            </h3>
            <ul className="space-y-2 text-gray-300">
              <li>• Real-time profit tracking</li>
              <li>• Compound growth options</li>
              <li>• Flexible withdrawal terms</li>
              <li>• 24/7 customer support</li>
            </ul>
          </div>
        </div>

        {/* Call to Action */}
        <div className="text-center stat-card">
          <Calendar className="h-12 w-12 text-purple-400 mx-auto mb-4" />
          <h3 className="text-2xl font-bold text-white mb-4">Need More Funds?</h3>
          <p className="text-gray-300 mb-6">
            Deposit funds to start investing in our premium plans and earning guaranteed daily returns
          </p>
          <Link to="/deposit" className="btn-primary inline-flex items-center">
            <Plus className="h-5 w-5 mr-2" />
            Deposit Funds
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Investment;
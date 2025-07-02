import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Satellite, 
  DollarSign, 
  CheckCircle, 
  Globe,
  Zap,
  Shield,
  Calendar,
  TrendingUp,
  Plus
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';
import { Device } from '../lib/supabase';
import toast from 'react-hot-toast';

interface RentalPlan {
  name: string;
  price: string;
  profit: string;
  dailyRate: number;
  costPerDay: number;
  features: string[];
  popular?: boolean;
  color: string;
}

const Rental: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [devices, setDevices] = useState<Device[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedPlan, setSelectedPlan] = useState<string>('');
  const [selectedDevice, setSelectedDevice] = useState<string>('');
  const [duration, setDuration] = useState<number>(30);

  const plans: RentalPlan[] = [
    {
      name: 'Basic Plan',
      price: '$2/day',
      profit: '5%',
      dailyRate: 5.0,
      costPerDay: 2.0,
      features: ['1 Starlink Router', 'Basic Support', '30-day Minimum', 'Standard Speeds'],
      color: 'from-gray-500 to-gray-600'
    },
    {
      name: 'Standard Plan',
      price: '$5/day',
      profit: '8%',
      dailyRate: 8.0,
      costPerDay: 5.0,
      features: ['3 Starlink Routers', 'Priority Support', '30-day Minimum', 'High-Speed Internet', 'Device Monitoring'],
      popular: true,
      color: 'from-blue-500 to-cyan-400'
    },
    {
      name: 'Premium Plan',
      price: '$10/day',
      profit: '12%',
      dailyRate: 12.0,
      costPerDay: 10.0,
      features: ['6 Starlink Routers', '24/7 VIP Support', '30-day Minimum', 'Ultra-High Speeds', 'Advanced Analytics', 'Backup Routers'],
      color: 'from-purple-500 to-pink-500'
    }
  ];

  useEffect(() => {
    fetchDevices();
  }, []);

  const fetchDevices = async () => {
    try {
      const { data, error } = await supabase
        .from('devices')
        .select('*')
        .eq('status', 'available')
        .order('name');

      if (error) throw error;
      setDevices(data || []);
    } catch (error) {
      console.error('Error fetching devices:', error);
      toast.error('Failed to load available routers');
    } finally {
      setLoading(false);
    }
  };

  const handleRental = async () => {
    if (!user) {
      toast.error('Please sign in to rent a router');
      return;
    }

    if (!selectedPlan || !selectedDevice) {
      toast.error('Please select a plan and router');
      return;
    }

    const plan = plans.find(p => p.name.toLowerCase().includes(selectedPlan));
    if (!plan) return;

    const totalCost = plan.costPerDay * duration;
    const expectedDailyProfit = (totalCost * plan.dailyRate) / 100 / duration;

    if (totalCost > user.balance) {
      toast.error('Insufficient balance. Please deposit funds first.');
      return;
    }

    try {
      // Create rental record
      const { error: rentalError } = await supabase
        .from('rentals')
        .insert({
          user_id: user.id,
          device_id: selectedDevice,
          plan_type: selectedPlan,
          plan_name: plan.name,
          rental_duration: duration,
          daily_profit_rate: plan.dailyRate,
          total_cost: totalCost,
          expected_daily_profit: expectedDailyProfit,
          status: 'active',
          start_date: new Date().toISOString().split('T')[0],
          end_date: new Date(Date.now() + duration * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        });

      if (rentalError) throw rentalError;

      // Update device status
      const { error: deviceError } = await supabase
        .from('devices')
        .update({ status: 'rented' })
        .eq('id', selectedDevice);

      if (deviceError) throw deviceError;

      // Update user balance
      const { error: userError } = await supabase
        .from('users')
        .update({ 
          balance: user.balance - totalCost,
          total_invested: user.total_invested + totalCost
        })
        .eq('id', user.id);

      if (userError) throw userError;

      toast.success('Router rental activated successfully! Daily profits will start tomorrow.');
      
      // Reset form
      setSelectedPlan('');
      setSelectedDevice('');
      setDuration(30);
      
      // Refresh devices
      fetchDevices();
    } catch (error: any) {
      console.error('Error creating rental:', error);
      toast.error(error.message || 'Failed to create rental');
    }
  };

  const calculateCosts = () => {
    if (!selectedPlan) return null;

    const plan = plans.find(p => p.name.toLowerCase().includes(selectedPlan));
    if (!plan) return null;

    const totalCost = plan.costPerDay * duration;
    const dailyProfit = (totalCost * plan.dailyRate) / 100 / duration;
    const totalProfit = dailyProfit * duration;

    return { totalCost, dailyProfit, totalProfit };
  };

  const costs = calculateCosts();

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <Satellite className="h-16 w-16 text-blue-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Sign in to rent routers</h2>
          <p className="text-gray-300 mb-6">Access premium Starlink router rentals with guaranteed daily profits</p>
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
          <h1 className="text-4xl font-bold text-white mb-4">Starlink Router Rental</h1>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            Choose your rental plan and start earning daily profits from premium Starlink satellite routers
          </p>
        </div>

        {/* Router Specifications */}
        <div className="grid lg:grid-cols-4 gap-8 mb-12">
          <div className="stat-card text-center">
            <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
              <Globe className="h-6 w-6 text-white" />
            </div>
            <h3 className="text-lg font-semibold text-white mb-2">Global Coverage</h3>
            <p className="text-cyan-400 font-medium">99.9% Uptime</p>
          </div>

          <div className="stat-card text-center">
            <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
              <Zap className="h-6 w-6 text-white" />
            </div>
            <h3 className="text-lg font-semibold text-white mb-2">Speed</h3>
            <p className="text-cyan-400 font-medium">Up to 200 Mbps</p>
          </div>

          <div className="stat-card text-center">
            <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
              <Shield className="h-6 w-6 text-white" />
            </div>
            <h3 className="text-lg font-semibold text-white mb-2">Security</h3>
            <p className="text-cyan-400 font-medium">Enterprise Grade</p>
          </div>

          <div className="stat-card text-center">
            <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
              <Calendar className="h-6 w-6 text-white" />
            </div>
            <h3 className="text-lg font-semibold text-white mb-2">Activation</h3>
            <p className="text-cyan-400 font-medium">Instant Setup</p>
          </div>
        </div>

        {/* Rental Plans */}
        <div className="grid md:grid-cols-3 gap-8 mb-12">
          {plans.map((plan, index) => (
            <div key={index} className={`relative stat-card card-hover ${plan.popular ? 'border-blue-500 scale-105' : ''}`}>
              {plan.popular && (
                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                  <span className="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                    Most Popular
                  </span>
                </div>
              )}

              <div className="text-center mb-6">
                <h3 className="text-2xl font-bold text-white mb-2">{plan.name}</h3>
                <div className="flex items-end justify-center mb-2">
                  <span className="text-4xl font-bold text-white">{plan.price}</span>
                </div>
                <p className="text-cyan-400 font-medium">Daily Profit: {plan.profit}</p>
              </div>

              <ul className="space-y-3 mb-8">
                {plan.features.map((feature, featureIndex) => (
                  <li key={featureIndex} className="flex items-center">
                    <CheckCircle className="h-5 w-5 text-green-400 mr-3" />
                    <span className="text-gray-300">{feature}</span>
                  </li>
                ))}
              </ul>

              <button
                onClick={() => setSelectedPlan(plan.name.split(' ')[0].toLowerCase())}
                className={`w-full py-3 rounded-lg font-semibold transition-all ${
                  selectedPlan === plan.name.split(' ')[0].toLowerCase()
                    ? 'bg-gradient-to-r from-blue-500 to-cyan-400 text-white'
                    : 'bg-slate-700 text-white hover:bg-slate-600'
                }`}
              >
                {selectedPlan === plan.name.split(' ')[0].toLowerCase() ? 'Selected' : `Select ${plan.name.split(' ')[0]}`}
              </button>
            </div>
          ))}
        </div>

        {/* Rental Form */}
        <div className="max-w-2xl mx-auto stat-card mb-8">
          <h3 className="text-2xl font-bold text-white mb-6 text-center">Complete Your Rental</h3>
          
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
                <option value="basic">Basic Plan - $2/day (5% profit)</option>
                <option value="standard">Standard Plan - $5/day (8% profit)</option>
                <option value="premium">Premium Plan - $10/day (12% profit)</option>
              </select>
            </div>

            <div>
              <label className="block text-gray-300 mb-2">Available Router</label>
              <select 
                value={selectedDevice} 
                onChange={(e) => setSelectedDevice(e.target.value)}
                className="input-field"
                required
              >
                <option value="">Select a router</option>
                {loading ? (
                  <option disabled>Loading routers...</option>
                ) : devices.length > 0 ? (
                  devices.map((device) => (
                    <option key={device.id} value={device.id}>
                      {device.name} - {device.location}
                    </option>
                  ))
                ) : (
                  <option disabled>No routers available</option>
                )}
              </select>
            </div>

            <div>
              <label className="block text-gray-300 mb-2">Rental Duration (Days)</label>
              <input 
                type="number" 
                value={duration} 
                onChange={(e) => setDuration(parseInt(e.target.value) || 30)}
                min="30" 
                max="365" 
                className="input-field"
                placeholder="Enter duration in days"
              />
              <p className="text-gray-400 text-sm mt-1">
                Minimum: 30 days | Your balance: ${user.balance.toFixed(2)}
              </p>
            </div>

            {costs && (
              <div className="bg-slate-700/50 p-4 rounded-lg">
                <h4 className="text-white font-semibold mb-3">Rental Summary</h4>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-gray-300">Total Cost:</span>
                    <span className="text-white font-semibold">${costs.totalCost.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-300">Daily Profit:</span>
                    <span className="text-green-400 font-semibold">${costs.dailyProfit.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-300">Total Expected Profit:</span>
                    <span className="text-green-400 font-semibold text-xl">${costs.totalProfit.toFixed(2)}</span>
                  </div>
                </div>
              </div>
            )}

            <button 
              onClick={handleRental}
              disabled={!selectedPlan || !selectedDevice || loading}
              className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Start Rental
            </button>
          </div>
        </div>

        {/* Call to Action */}
        <div className="text-center stat-card">
          <TrendingUp className="h-12 w-12 text-green-400 mx-auto mb-4" />
          <h3 className="text-2xl font-bold text-white mb-4">Need More Funds?</h3>
          <p className="text-gray-300 mb-6">
            Deposit funds to start renting premium Starlink routers and earning daily profits
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

export default Rental;
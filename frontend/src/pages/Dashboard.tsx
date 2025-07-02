import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  DollarSign, 
  Satellite, 
  Users, 
  TrendingUp, 
  Plus,
  Minus,
  Activity,
  Calendar,
  ArrowUpRight,
  ArrowDownRight
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';

interface DashboardStats {
  totalEarnings: number;
  activeRentals: number;
  referrals: number;
  dailyProfit: number;
}

interface RecentActivity {
  id: string;
  type: 'rental' | 'investment' | 'referral' | 'deposit' | 'withdrawal';
  description: string;
  amount: number;
  date: string;
  status: 'completed' | 'pending' | 'failed';
}

const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [stats, setStats] = useState<DashboardStats>({
    totalEarnings: 0,
    activeRentals: 0,
    referrals: 0,
    dailyProfit: 0
  });
  const [activities, setActivities] = useState<RecentActivity[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user) {
      fetchDashboardData();
    }
  }, [user]);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);

      // Fetch active rentals count
      const { data: rentals, error: rentalsError } = await supabase
        .from('rentals')
        .select('id, expected_daily_profit')
        .eq('user_id', user?.id)
        .eq('status', 'active');

      if (rentalsError) throw rentalsError;

      // Fetch referrals count
      const { data: referralsData, error: referralsError } = await supabase
        .from('referrals')
        .select('id')
        .eq('referrer_id', user?.id);

      if (referralsError) throw referralsError;

      // Calculate daily profit from active rentals
      const dailyProfit = rentals?.reduce((sum, rental) => sum + rental.expected_daily_profit, 0) || 0;

      // Fetch recent activities (payments)
      const { data: payments, error: paymentsError } = await supabase
        .from('payments')
        .select('*')
        .eq('user_id', user?.id)
        .order('created_at', { ascending: false })
        .limit(10);

      if (paymentsError) throw paymentsError;

      // Transform payments to activities
      const recentActivities: RecentActivity[] = payments?.map(payment => ({
        id: payment.id,
        type: payment.type,
        description: getActivityDescription(payment.type, payment.amount),
        amount: payment.amount,
        date: payment.created_at,
        status: payment.status
      })) || [];

      setStats({
        totalEarnings: user?.total_earnings || 0,
        activeRentals: rentals?.length || 0,
        referrals: referralsData?.length || 0,
        dailyProfit
      });

      setActivities(recentActivities);
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  const getActivityDescription = (type: string, amount: number): string => {
    switch (type) {
      case 'deposit':
        return `Deposited $${amount.toFixed(2)}`;
      case 'withdrawal':
        return `Withdrew $${amount.toFixed(2)}`;
      case 'rental':
        return `Router rental payment $${amount.toFixed(2)}`;
      case 'investment':
        return `Investment of $${amount.toFixed(2)}`;
      case 'referral_bonus':
        return `Referral bonus $${amount.toFixed(2)}`;
      default:
        return `Transaction $${amount.toFixed(2)}`;
    }
  };

  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'deposit':
        return <ArrowDownRight className="h-4 w-4 text-green-400" />;
      case 'withdrawal':
        return <ArrowUpRight className="h-4 w-4 text-red-400" />;
      case 'rental':
        return <Satellite className="h-4 w-4 text-blue-400" />;
      case 'investment':
        return <TrendingUp className="h-4 w-4 text-purple-400" />;
      case 'referral_bonus':
        return <Users className="h-4 w-4 text-orange-400" />;
      default:
        return <Activity className="h-4 w-4 text-gray-400" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'text-green-400';
      case 'pending':
        return 'text-yellow-400';
      case 'failed':
        return 'text-red-400';
      default:
        return 'text-gray-400';
    }
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-white mb-4">Please sign in to access your dashboard</h2>
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
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-white mb-2">Welcome back, {user.username}!</h1>
          <p className="text-gray-300">Monitor your earnings and router performance</p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Total Earnings</p>
                <p className="text-2xl font-bold text-white mt-1">
                  ${stats.totalEarnings.toFixed(2)}
                </p>
                <p className="text-green-400 text-sm mt-1">+12.5%</p>
              </div>
              <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                <DollarSign className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Active Rentals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.activeRentals}</p>
                <p className="text-green-400 text-sm mt-1">+2</p>
              </div>
              <div className="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                <Satellite className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Referrals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.referrals}</p>
                <p className="text-green-400 text-sm mt-1">+5</p>
              </div>
              <div className="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                <Users className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Daily Profit</p>
                <p className="text-2xl font-bold text-white mt-1">
                  ${stats.dailyProfit.toFixed(2)}
                </p>
                <p className="text-green-400 text-sm mt-1">+8.2%</p>
              </div>
              <div className="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                <TrendingUp className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Recent Activity */}
          <div className="lg:col-span-2 stat-card">
            <h3 className="text-xl font-semibold text-white mb-4 flex items-center">
              <Activity className="h-5 w-5 mr-2" />
              Recent Activity
            </h3>
            <div className="space-y-4">
              {loading ? (
                <div className="animate-pulse space-y-4">
                  {[...Array(5)].map((_, i) => (
                    <div key={i} className="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                      <div className="flex items-center space-x-3">
                        <div className="w-8 h-8 bg-slate-600 rounded-full"></div>
                        <div className="space-y-2">
                          <div className="w-32 h-4 bg-slate-600 rounded"></div>
                          <div className="w-24 h-3 bg-slate-600 rounded"></div>
                        </div>
                      </div>
                      <div className="w-16 h-4 bg-slate-600 rounded"></div>
                    </div>
                  ))}
                </div>
              ) : activities.length > 0 ? (
                activities.map((activity) => (
                  <div key={activity.id} className="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg hover:bg-slate-700/50 transition-colors">
                    <div className="flex items-center space-x-3">
                      <div className="p-2 bg-slate-600 rounded-full">
                        {getActivityIcon(activity.type)}
                      </div>
                      <div>
                        <p className="text-white font-medium">{activity.description}</p>
                        <p className="text-gray-400 text-sm">
                          {new Date(activity.date).toLocaleDateString()} • 
                          <span className={`ml-1 ${getStatusColor(activity.status)}`}>
                            {activity.status}
                          </span>
                        </p>
                      </div>
                    </div>
                    <span className={`font-semibold ${
                      activity.type === 'deposit' || activity.type === 'referral_bonus' 
                        ? 'text-green-400' 
                        : 'text-red-400'
                    }`}>
                      {activity.type === 'deposit' || activity.type === 'referral_bonus' ? '+' : '-'}
                      ${activity.amount.toFixed(2)}
                    </span>
                  </div>
                ))
              ) : (
                <div className="text-center py-8">
                  <Activity className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <p className="text-gray-400">No recent activity</p>
                  <p className="text-gray-500 text-sm">Start renting routers to see your activity here</p>
                </div>
              )}
            </div>
          </div>

          {/* Quick Actions */}
          <div className="stat-card">
            <h3 className="text-xl font-semibold text-white mb-4">Quick Actions</h3>
            <div className="space-y-3">
              <Link to="/rental" className="w-full btn-primary flex items-center justify-center">
                <Satellite className="h-4 w-4 mr-2" />
                Rent New Router
              </Link>
              <Link to="/deposit" className="w-full btn-secondary flex items-center justify-center">
                <Plus className="h-4 w-4 mr-2" />
                Deposit Funds
              </Link>
              <Link to="/withdrawal" className="w-full btn-secondary flex items-center justify-center">
                <Minus className="h-4 w-4 mr-2" />
                Withdraw Earnings
              </Link>
              <Link to="/referrals" className="w-full btn-secondary flex items-center justify-center">
                <Users className="h-4 w-4 mr-2" />
                View Referrals
              </Link>
            </div>

            {/* Account Summary */}
            <div className="mt-6 p-4 bg-slate-700/30 rounded-lg">
              <h4 className="text-white font-medium mb-3">Account Summary</h4>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-400">Current Balance:</span>
                  <span className="text-green-400 font-semibold">${user.balance.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Total Invested:</span>
                  <span className="text-white">${user.total_invested.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-400">Total Withdrawn:</span>
                  <span className="text-white">${user.total_withdrawn.toFixed(2)}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Performance Chart Placeholder */}
        <div className="mt-8 stat-card">
          <h3 className="text-xl font-semibold text-white mb-4 flex items-center">
            <TrendingUp className="h-5 w-5 mr-2" />
            Earnings Overview
          </h3>
          <div className="h-64 bg-slate-700/30 rounded-lg flex items-center justify-center">
            <div className="text-center">
              <Calendar className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p className="text-gray-400">Earnings chart coming soon</p>
              <p className="text-gray-500 text-sm">Track your daily profits and growth over time</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
import React, { useState, useEffect } from 'react';
import { 
  Users, 
  DollarSign, 
  Share2, 
  Copy, 
  Check,
  Gift,
  TrendingUp,
  UserPlus
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useSupabase } from '../contexts/SupabaseContext';
import { Referral } from '../lib/supabase';
import toast from 'react-hot-toast';

interface ReferralStats {
  totalReferrals: number;
  level1Referrals: number;
  level2Referrals: number;
  level3Referrals: number;
  totalEarnings: number;
}

const Referrals: React.FC = () => {
  const { user } = useAuth();
  const { supabase } = useSupabase();
  const [stats, setStats] = useState<ReferralStats>({
    totalReferrals: 0,
    level1Referrals: 0,
    level2Referrals: 0,
    level3Referrals: 0,
    totalEarnings: 0
  });
  const [referrals, setReferrals] = useState<any[]>([]);
  const [copied, setCopied] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user) {
      fetchReferralData();
    }
  }, [user]);

  const fetchReferralData = async () => {
    try {
      setLoading(true);

      // Fetch referral statistics
      const { data: referralData, error: referralError } = await supabase
        .from('referrals')
        .select('level')
        .eq('referrer_id', user?.id);

      if (referralError) throw referralError;

      // Calculate stats
      const level1 = referralData?.filter(r => r.level === 1).length || 0;
      const level2 = referralData?.filter(r => r.level === 2).length || 0;
      const level3 = referralData?.filter(r => r.level === 3).length || 0;

      // Fetch detailed referral information
      const { data: detailedReferrals, error: detailedError } = await supabase
        .from('referrals')
        .select(`
          *,
          referred:users!referrals_referred_id_fkey(username, email, created_at, total_earnings, status)
        `)
        .eq('referrer_id', user?.id)
        .order('created_at', { ascending: false });

      if (detailedError) throw detailedError;

      setStats({
        totalReferrals: level1 + level2 + level3,
        level1Referrals: level1,
        level2Referrals: level2,
        level3Referrals: level3,
        totalEarnings: user?.referral_earnings || 0
      });

      setReferrals(detailedReferrals || []);
    } catch (error) {
      console.error('Error fetching referral data:', error);
      toast.error('Failed to load referral data');
    } finally {
      setLoading(false);
    }
  };

  const copyReferralCode = async () => {
    if (!user?.referral_code) return;

    try {
      await navigator.clipboard.writeText(user.referral_code);
      setCopied(true);
      toast.success('Referral code copied to clipboard!');
      setTimeout(() => setCopied(false), 2000);
    } catch (error) {
      toast.error('Failed to copy referral code');
    }
  };

  const shareToTelegram = () => {
    const link = `${window.location.origin}/login?ref=${user?.referral_code}`;
    const text = `Join Starlink Router Rent and start earning daily profits! Use my referral link: ${link}`;
    const url = `https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
  };

  const shareToWhatsApp = () => {
    const link = `${window.location.origin}/login?ref=${user?.referral_code}`;
    const text = `Join Starlink Router Rent and start earning daily profits! Use my referral link: ${link}`;
    const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <Users className="h-16 w-16 text-purple-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Sign in to access referrals</h2>
          <p className="text-gray-300 mb-6">Build your network and earn up to 15% commission</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-white mb-4">Referral System</h1>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            Build your network and earn up to 15% commission from 3 levels of referrals
          </p>
        </div>

        {/* Referral Stats */}
        <div className="grid md:grid-cols-4 gap-6 mb-8">
          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Total Referrals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.totalReferrals}</p>
              </div>
              <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                <Users className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Level 1 Referrals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.level1Referrals}</p>
              </div>
              <div className="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                <Share2 className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Level 2 Referrals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.level2Referrals}</p>
              </div>
              <div className="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                <Gift className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>

          <div className="stat-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">Level 3 Referrals</p>
                <p className="text-2xl font-bold text-white mt-1">{stats.level3Referrals}</p>
              </div>
              <div className="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                <TrendingUp className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        </div>

        <div className="grid lg:grid-cols-2 gap-8 mb-8">
          {/* Referral Code */}
          <div className="stat-card">
            <h3 className="text-2xl font-bold text-white mb-6">Your Referral Code</h3>
            
            <div className="bg-slate-700/50 p-4 rounded-lg mb-6">
              <div className="flex items-center justify-between">
                <code className="text-2xl font-mono text-cyan-400 font-bold">{user.referral_code}</code>
                <button 
                  onClick={copyReferralCode}
                  className="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all"
                >
                  {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                  <span>{copied ? 'Copied!' : 'Copy'}</span>
                </button>
              </div>
            </div>

            <div className="space-y-4">
              <h4 className="text-lg font-semibold text-white">Share Your Link</h4>
              <div className="bg-slate-700/50 p-4 rounded-lg">
                <p className="text-gray-300 text-sm mb-2">Referral Link:</p>
                <code className="text-cyan-400 text-sm break-all">
                  {window.location.origin}/login?ref={user.referral_code}
                </code>
              </div>
              
              <div className="flex space-x-3">
                <button 
                  onClick={shareToTelegram}
                  className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-all"
                >
                  Share on Telegram
                </button>
                <button 
                  onClick={shareToWhatsApp}
                  className="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-all"
                >
                  Share on WhatsApp
                </button>
              </div>
            </div>
          </div>

          {/* Commission Structure */}
          <div className="stat-card">
            <h3 className="text-2xl font-bold text-white mb-6">Commission Structure</h3>
            
            <div className="space-y-4">
              <div className="bg-slate-700/50 p-4 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-white font-semibold">Level 1</span>
                  <span className="text-2xl font-bold bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text text-transparent">7%</span>
                </div>
                <p className="text-gray-300 text-sm">Direct referrals from people you invite</p>
              </div>

              <div className="bg-slate-700/50 p-4 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-white font-semibold">Level 2</span>
                  <span className="text-2xl font-bold bg-gradient-to-r from-green-500 to-blue-500 bg-clip-text text-transparent">5%</span>
                </div>
                <p className="text-gray-300 text-sm">Referrals from your level 1 referrals</p>
              </div>

              <div className="bg-slate-700/50 p-4 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-white font-semibold">Level 3</span>
                  <span className="text-2xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent">3%</span>
                </div>
                <p className="text-gray-300 text-sm">Referrals from your level 2 referrals</p>
              </div>
            </div>

            <div className="mt-6 p-4 bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-lg border border-green-500/20">
              <div className="flex items-center space-x-2 mb-2">
                <DollarSign className="h-5 w-5 text-green-400" />
                <span className="text-white font-semibold">Total Possible Commission: 15%</span>
              </div>
              <p className="text-gray-300 text-sm">
                Maximum earning potential when you have active referrals in all 3 levels
              </p>
            </div>
          </div>
        </div>

        {/* Recent Referrals */}
        {referrals.length > 0 && (
          <div className="stat-card mb-8">
            <h3 className="text-2xl font-bold text-white mb-6">Your Referrals</h3>
            
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gray-700">
                    <th className="text-left text-gray-400 pb-3">Username</th>
                    <th className="text-left text-gray-400 pb-3">Level</th>
                    <th className="text-left text-gray-400 pb-3">Join Date</th>
                    <th className="text-left text-gray-400 pb-3">Total Earnings</th>
                    <th className="text-left text-gray-400 pb-3">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {referrals.map((referral) => (
                    <tr key={referral.id} className="border-b border-gray-800 hover:bg-slate-700/30 transition-colors">
                      <td className="py-4">
                        <div className="flex items-center space-x-2">
                          <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-full">
                            <UserPlus className="h-4 w-4 text-white" />
                          </div>
                          <span className="text-white font-medium">@{referral.referred?.username}</span>
                        </div>
                      </td>
                      <td className="py-4">
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                          referral.level === 1 ? 'bg-blue-500/20 text-blue-400' :
                          referral.level === 2 ? 'bg-green-500/20 text-green-400' : 'bg-purple-500/20 text-purple-400'
                        }`}>
                          Level {referral.level}
                        </span>
                      </td>
                      <td className="py-4 text-gray-300">
                        {new Date(referral.referred?.created_at).toLocaleDateString()}
                      </td>
                      <td className="py-4 text-green-400 font-semibold">
                        ${(referral.referred?.total_earnings || 0).toFixed(2)}
                      </td>
                      <td className="py-4">
                        <span className="px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                          {referral.referred?.status || 'Active'}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Call to Action */}
        <div className="bg-gradient-to-r from-blue-500/10 to-cyan-400/10 p-8 rounded-2xl border border-blue-500/20">
          <div className="text-center">
            <h3 className="text-2xl font-bold text-white mb-4">Boost Your Earnings</h3>
            <p className="text-gray-300 mb-6 max-w-2xl mx-auto">
              The more people you refer, the more you earn. Share your referral code with friends, 
              family, and social networks to maximize your passive income potential.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <button 
                onClick={copyReferralCode}
                className="btn-primary"
              >
                Copy Referral Code
              </button>
              <button 
                onClick={shareToTelegram}
                className="btn-secondary"
              >
                Share on Telegram
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Referrals;
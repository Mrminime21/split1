import React, { useState } from 'react';
import { 
  User, 
  Mail, 
  Calendar, 
  MapPin, 
  Edit3, 
  Save, 
  X,
  Shield,
  Wallet,
  TrendingUp
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import toast from 'react-hot-toast';

const Profile: React.FC = () => {
  const { user, updateProfile } = useAuth();
  const [editing, setEditing] = useState(false);
  const [formData, setFormData] = useState({
    username: user?.username || '',
    email: user?.email || '',
    phone: user?.phone || '',
    country: user?.country || '',
    timezone: user?.timezone || 'UTC'
  });

  const handleSave = async () => {
    try {
      await updateProfile(formData);
      setEditing(false);
    } catch (error) {
      // Error is handled in the auth context
    }
  };

  const handleCancel = () => {
    setFormData({
      username: user?.username || '',
      email: user?.email || '',
      phone: user?.phone || '',
      country: user?.country || '',
      timezone: user?.timezone || 'UTC'
    });
    setEditing(false);
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center py-8 px-4">
        <div className="text-center">
          <User className="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-4">Please sign in</h2>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-white mb-2">Profile Settings</h1>
          <p className="text-gray-300">Manage your account information and preferences</p>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Profile Card */}
          <div className="lg:col-span-1">
            <div className="stat-card text-center">
              <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-4 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                <User className="h-10 w-10 text-white" />
              </div>
              <h2 className="text-xl font-bold text-white mb-2">{user.username}</h2>
              <p className="text-gray-400 mb-4">{user.email}</p>
              
              <div className="space-y-3">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-400">Status:</span>
                  <span className="px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs">
                    {user.status}
                  </span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-400">Member Since:</span>
                  <span className="text-white">
                    {new Date(user.created_at).toLocaleDateString()}
                  </span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-400">Referral Code:</span>
                  <code className="text-cyan-400 font-mono">{user.referral_code}</code>
                </div>
              </div>
            </div>

            {/* Account Stats */}
            <div className="stat-card mt-6">
              <h3 className="text-lg font-semibold text-white mb-4">Account Stats</h3>
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <Wallet className="h-4 w-4 text-green-400" />
                    <span className="text-gray-400">Balance</span>
                  </div>
                  <span className="text-green-400 font-semibold">${user.balance.toFixed(2)}</span>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <TrendingUp className="h-4 w-4 text-blue-400" />
                    <span className="text-gray-400">Total Earnings</span>
                  </div>
                  <span className="text-white">${user.total_earnings.toFixed(2)}</span>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <TrendingUp className="h-4 w-4 text-purple-400" />
                    <span className="text-gray-400">Total Invested</span>
                  </div>
                  <span className="text-white">${user.total_invested.toFixed(2)}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Profile Form */}
          <div className="lg:col-span-2">
            <div className="stat-card">
              <div className="flex items-center justify-between mb-6">
                <h3 className="text-xl font-semibold text-white">Personal Information</h3>
                {!editing ? (
                  <button
                    onClick={() => setEditing(true)}
                    className="flex items-center space-x-2 text-cyan-400 hover:text-cyan-300"
                  >
                    <Edit3 className="h-4 w-4" />
                    <span>Edit</span>
                  </button>
                ) : (
                  <div className="flex space-x-2">
                    <button
                      onClick={handleSave}
                      className="flex items-center space-x-2 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded"
                    >
                      <Save className="h-4 w-4" />
                      <span>Save</span>
                    </button>
                    <button
                      onClick={handleCancel}
                      className="flex items-center space-x-2 bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded"
                    >
                      <X className="h-4 w-4" />
                      <span>Cancel</span>
                    </button>
                  </div>
                )}
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-gray-300 mb-2">Username</label>
                  {editing ? (
                    <input
                      type="text"
                      value={formData.username}
                      onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                      className="input-field"
                    />
                  ) : (
                    <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                      <User className="h-4 w-4 text-gray-400" />
                      <span className="text-white">{user.username}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-gray-300 mb-2">Email</label>
                  {editing ? (
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      className="input-field"
                    />
                  ) : (
                    <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                      <Mail className="h-4 w-4 text-gray-400" />
                      <span className="text-white">{user.email}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-gray-300 mb-2">Phone</label>
                  {editing ? (
                    <input
                      type="tel"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      className="input-field"
                      placeholder="Enter phone number"
                    />
                  ) : (
                    <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                      <span className="text-white">{user.phone || 'Not provided'}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-gray-300 mb-2">Country</label>
                  {editing ? (
                    <input
                      type="text"
                      value={formData.country}
                      onChange={(e) => setFormData({ ...formData, country: e.target.value })}
                      className="input-field"
                      placeholder="Enter country"
                    />
                  ) : (
                    <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                      <MapPin className="h-4 w-4 text-gray-400" />
                      <span className="text-white">{user.country || 'Not provided'}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-gray-300 mb-2">Timezone</label>
                  {editing ? (
                    <select
                      value={formData.timezone}
                      onChange={(e) => setFormData({ ...formData, timezone: e.target.value })}
                      className="input-field"
                    >
                      <option value="UTC">UTC</option>
                      <option value="America/New_York">Eastern Time</option>
                      <option value="America/Chicago">Central Time</option>
                      <option value="America/Denver">Mountain Time</option>
                      <option value="America/Los_Angeles">Pacific Time</option>
                      <option value="Europe/London">London</option>
                      <option value="Europe/Paris">Paris</option>
                      <option value="Asia/Tokyo">Tokyo</option>
                    </select>
                  ) : (
                    <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                      <Calendar className="h-4 w-4 text-gray-400" />
                      <span className="text-white">{user.timezone}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-gray-300 mb-2">Language</label>
                  <div className="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                    <span className="text-white">{user.language || 'en'}</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Security Section */}
            <div className="stat-card mt-6">
              <h3 className="text-xl font-semibold text-white mb-4 flex items-center">
                <Shield className="h-5 w-5 mr-2" />
                Security
              </h3>
              <div className="space-y-4">
                <div className="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                  <div>
                    <h4 className="text-white font-medium">Email Verification</h4>
                    <p className="text-gray-400 text-sm">Verify your email address for security</p>
                  </div>
                  <span className={`px-3 py-1 rounded-full text-sm ${
                    user.email_verified 
                      ? 'bg-green-500/20 text-green-400' 
                      : 'bg-yellow-500/20 text-yellow-400'
                  }`}>
                    {user.email_verified ? 'Verified' : 'Pending'}
                  </span>
                </div>

                <div className="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                  <div>
                    <h4 className="text-white font-medium">Two-Factor Authentication</h4>
                    <p className="text-gray-400 text-sm">Add an extra layer of security</p>
                  </div>
                  <button className="text-cyan-400 hover:text-cyan-300 text-sm">
                    Enable 2FA
                  </button>
                </div>

                <div className="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                  <div>
                    <h4 className="text-white font-medium">Change Password</h4>
                    <p className="text-gray-400 text-sm">Update your account password</p>
                  </div>
                  <button className="text-cyan-400 hover:text-cyan-300 text-sm">
                    Change
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Profile;
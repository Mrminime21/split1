import React, { ReactNode } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { 
  Satellite, 
  Home, 
  LayoutDashboard, 
  Wifi, 
  TrendingUp, 
  Users, 
  Plus, 
  Minus, 
  User,
  LogOut,
  Menu,
  X,
  Wallet
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useState } from 'react';

interface LayoutProps {
  children: ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const { user, signOut } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navigation = [
    { name: 'Home', href: '/', icon: Home },
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, requireAuth: true },
    { name: 'Rental', href: '/rental', icon: Wifi, requireAuth: true },
    { name: 'Investment', href: '/investment', icon: TrendingUp, requireAuth: true },
    { name: 'Referrals', href: '/referrals', icon: Users, requireAuth: true },
  ];

  const handleSignOut = async () => {
    await signOut();
    navigate('/');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
      {/* Header */}
      <header className="bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            {/* Logo */}
            <Link to="/" className="flex items-center space-x-3">
              <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                <Satellite className="h-8 w-8 text-white" />
              </div>
              <span className="text-2xl font-bold gradient-text">Starlink Router Rent</span>
            </Link>

            {/* Desktop Navigation */}
            <nav className="hidden md:flex space-x-8">
              {navigation.map((item) => {
                if (item.requireAuth && !user) return null;
                const isActive = location.pathname === item.href;
                return (
                  <Link
                    key={item.name}
                    to={item.href}
                    className={`flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                      isActive
                        ? 'text-cyan-400 bg-cyan-400/10'
                        : 'text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5'
                    }`}
                  >
                    <item.icon className="h-4 w-4" />
                    <span>{item.name}</span>
                  </Link>
                );
              })}
            </nav>

            {/* User Menu */}
            <div className="flex items-center space-x-4">
              {user ? (
                <>
                  {/* Balance Display */}
                  <div className="hidden sm:flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg">
                    <Wallet className="h-4 w-4 text-green-400" />
                    <span className="text-green-400 text-sm font-semibold">
                      ${user.balance.toFixed(2)}
                    </span>
                  </div>

                  {/* Quick Actions */}
                  <div className="hidden sm:flex items-center space-x-2">
                    <Link
                      to="/deposit"
                      className="flex items-center space-x-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                      <Plus className="h-4 w-4" />
                      <span>Deposit</span>
                    </Link>
                    <Link
                      to="/withdrawal"
                      className="flex items-center space-x-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                      <Minus className="h-4 w-4" />
                      <span>Withdraw</span>
                    </Link>
                  </div>

                  {/* User Dropdown */}
                  <div className="relative group">
                    <button className="flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                      <User className="h-4 w-4 text-cyan-400" />
                      <span className="text-white text-sm">{user.username}</span>
                    </button>
                    
                    <div className="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg border border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                      <div className="py-2">
                        <Link
                          to="/profile"
                          className="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white"
                        >
                          <User className="h-4 w-4 mr-3" />
                          Profile
                        </Link>
                        <button
                          onClick={handleSignOut}
                          className="flex items-center w-full px-4 py-2 text-sm text-red-400 hover:bg-slate-700"
                        >
                          <LogOut className="h-4 w-4 mr-3" />
                          Sign Out
                        </button>
                      </div>
                    </div>
                  </div>
                </>
              ) : (
                <Link
                  to="/login"
                  className="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-6 py-2 rounded-lg font-medium hover:from-blue-600 hover:to-cyan-500 transition-all"
                >
                  Sign In
                </Link>
              )}

              {/* Mobile menu button */}
              <button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className="md:hidden text-gray-400 hover:text-white"
              >
                {mobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
              </button>
            </div>
          </div>

          {/* Mobile menu */}
          {mobileMenuOpen && (
            <div className="md:hidden pb-4">
              <div className="flex flex-col space-y-2">
                {navigation.map((item) => {
                  if (item.requireAuth && !user) return null;
                  const isActive = location.pathname === item.href;
                  return (
                    <Link
                      key={item.name}
                      to={item.href}
                      onClick={() => setMobileMenuOpen(false)}
                      className={`flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                        isActive
                          ? 'text-cyan-400 bg-cyan-400/10'
                          : 'text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5'
                      }`}
                    >
                      <item.icon className="h-4 w-4" />
                      <span>{item.name}</span>
                    </Link>
                  );
                })}
                
                {user && (
                  <>
                    <div className="border-t border-gray-700 pt-2 mt-2">
                      <div className="flex items-center space-x-2 px-3 py-2">
                        <Wallet className="h-4 w-4 text-green-400" />
                        <span className="text-green-400 text-sm font-semibold">
                          Balance: ${user.balance.toFixed(2)}
                        </span>
                      </div>
                    </div>
                    <Link
                      to="/deposit"
                      onClick={() => setMobileMenuOpen(false)}
                      className="flex items-center space-x-2 text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md"
                    >
                      <Plus className="h-4 w-4" />
                      <span>Deposit</span>
                    </Link>
                    <Link
                      to="/withdrawal"
                      onClick={() => setMobileMenuOpen(false)}
                      className="flex items-center space-x-2 text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md"
                    >
                      <Minus className="h-4 w-4" />
                      <span>Withdraw</span>
                    </Link>
                  </>
                )}
              </div>
            </div>
          )}
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-1">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-slate-900/50 border-t border-blue-500/20 mt-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <div className="grid md:grid-cols-4 gap-8">
            <div>
              <div className="flex items-center space-x-3 mb-4">
                <div className="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                  <Satellite className="h-6 w-6 text-white" />
                </div>
                <span className="text-xl font-bold gradient-text">Starlink Router Rent</span>
              </div>
              <p className="text-gray-400">Premium Starlink router rental platform with guaranteed daily profits and referral rewards.</p>
            </div>
            <div>
              <h3 className="text-white font-semibold mb-4">Services</h3>
              <ul className="space-y-2 text-gray-400">
                <li><Link to="/rental" className="hover:text-cyan-400">Router Rental</Link></li>
                <li><Link to="/investment" className="hover:text-cyan-400">Investment Plans</Link></li>
                <li><Link to="/referrals" className="hover:text-cyan-400">Referral Program</Link></li>
              </ul>
            </div>
            <div>
              <h3 className="text-white font-semibold mb-4">Account</h3>
              <ul className="space-y-2 text-gray-400">
                <li><Link to="/deposit" className="hover:text-cyan-400">Deposit Funds</Link></li>
                <li><Link to="/withdrawal" className="hover:text-cyan-400">Withdraw Funds</Link></li>
                <li><Link to="/dashboard" className="hover:text-cyan-400">Dashboard</Link></li>
              </ul>
            </div>
            <div>
              <h3 className="text-white font-semibold mb-4">Support</h3>
              <ul className="space-y-2 text-gray-400">
                <li><a href="#" className="hover:text-cyan-400">Help Center</a></li>
                <li><a href="#" className="hover:text-cyan-400">Contact Us</a></li>
                <li><a href="#" className="hover:text-cyan-400">Terms of Service</a></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; 2024 Starlink Router Rent. All rights reserved. | Built with React & Supabase</p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout;
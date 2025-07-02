import React from 'react';
import { Link } from 'react-router-dom';
import { 
  Satellite, 
  DollarSign, 
  Users, 
  Bitcoin, 
  Shield, 
  Zap, 
  Globe,
  TrendingUp,
  CheckCircle,
  ArrowRight,
  LayoutDashboard
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';

const Home: React.FC = () => {
  const { user } = useAuth();

  const features = [
    {
      icon: Satellite,
      title: 'Premium Starlink Routers',
      description: 'Rent high-performance Starlink routers with guaranteed uptime and global coverage.',
      color: 'from-blue-500 to-cyan-400'
    },
    {
      icon: DollarSign,
      title: 'Daily Profits',
      description: 'Earn consistent daily returns from your router rentals with transparent profit sharing.',
      color: 'from-green-500 to-blue-500'
    },
    {
      icon: Users,
      title: '3-Level Referrals',
      description: 'Build your network and earn up to 15% commission from referral bonuses.',
      color: 'from-purple-500 to-pink-500'
    },
    {
      icon: Bitcoin,
      title: 'Crypto Payments',
      description: 'Secure payments via Plisio with full cryptocurrency support including Bitcoin, USDT, and more.',
      color: 'from-orange-500 to-yellow-500'
    }
  ];

  const plans = [
    {
      name: 'Basic Plan',
      price: '$2/day',
      profit: '5%',
      features: ['1 Starlink Router', 'Basic Support', '30-day Minimum', 'Standard Speeds']
    },
    {
      name: 'Standard Plan',
      price: '$5/day',
      profit: '8%',
      features: ['3 Starlink Routers', 'Priority Support', '30-day Minimum', 'High-Speed Internet', 'Device Monitoring'],
      popular: true
    },
    {
      name: 'Premium Plan',
      price: '$10/day',
      profit: '12%',
      features: ['6 Starlink Routers', '24/7 VIP Support', '30-day Minimum', 'Ultra-High Speeds', 'Advanced Analytics', 'Backup Routers']
    }
  ];

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative py-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-cyan-600/20 backdrop-blur-3xl"></div>
        <div className="relative max-w-7xl mx-auto text-center">
          <div className="mb-8">
            <div className="inline-flex items-center space-x-4 bg-slate-800/50 px-6 py-3 rounded-full border border-blue-500/20">
              <Satellite className="h-8 w-8 text-blue-400" />
              <span className="text-blue-400 font-medium">Premium Router Rental Platform</span>
            </div>
          </div>

          <h1 className="text-5xl md:text-7xl font-bold text-white mb-6">
            <span className="gradient-text">Starlink Router</span><br />
            <span className="gradient-text">Rent & Earn</span>
          </h1>

          <p className="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
            Rent premium Starlink routers and earn guaranteed daily profits. 
            Join our referral network and build passive income with cutting-edge satellite technology.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            {user ? (
              <Link to="/dashboard" className="btn-primary">
                <LayoutDashboard className="h-5 w-5 mr-2" />
                Go to Dashboard
              </Link>
            ) : (
              <Link to="/login" className="btn-primary">
                Start Earning Today
                <ArrowRight className="h-5 w-5 ml-2" />
              </Link>
            )}
            <Link to="/rental" className="btn-secondary">
              View Router Plans
            </Link>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
            <div className="text-center">
              <div className="text-3xl font-bold text-white mb-2">99.9%</div>
              <div className="text-gray-400">Uptime Guarantee</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-white mb-2">15%</div>
              <div className="text-gray-400">Max Referral Bonus</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-white mb-2">24/7</div>
              <div className="text-gray-400">Support Available</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-white mb-2">15+</div>
              <div className="text-gray-400">Cryptocurrencies</div>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-white mb-4">Why Choose Starlink Router Rent?</h2>
            <p className="text-xl text-gray-300">Experience the future of satellite internet rental</p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {features.map((feature, index) => (
              <div key={index} className="stat-card card-hover animate-fade-in">
                <div className={`bg-gradient-to-r ${feature.color} p-3 rounded-lg w-fit mb-4`}>
                  <feature.icon className="h-6 w-6 text-white" />
                </div>
                <h3 className="text-xl font-semibold text-white mb-2">{feature.title}</h3>
                <p className="text-gray-300">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Plans Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-slate-800/30">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-white mb-4">Choose Your Plan</h2>
            <p className="text-xl text-gray-300">Start earning with our flexible router rental plans</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            {plans.map((plan, index) => (
              <div key={index} className={`relative bg-slate-800/50 p-8 rounded-2xl border ${plan.popular ? 'border-blue-500 scale-105' : 'border-blue-500/10'} card-hover`}>
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

                <Link
                  to="/rental"
                  className={`w-full py-3 rounded-lg font-semibold transition-all block text-center ${
                    plan.popular
                      ? 'bg-gradient-to-r from-blue-500 to-cyan-400 text-white hover:from-blue-600 hover:to-cyan-500'
                      : 'bg-slate-700 text-white hover:bg-slate-600'
                  }`}
                >
                  Select {plan.name}
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Technology Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto text-center">
          <div className="mb-8">
            <div className="inline-flex items-center space-x-4 bg-green-500/10 px-6 py-3 rounded-full border border-green-500/20">
              <Zap className="h-6 w-6 text-green-400" />
              <span className="text-green-400 font-medium">Cutting-Edge Technology</span>
            </div>
          </div>
          
          <h2 className="text-4xl font-bold text-white mb-6">Built for the Future</h2>
          <p className="text-xl text-gray-300 mb-8">
            Experience seamless satellite internet with our advanced Starlink router network
          </p>
          
          <div className="grid md:grid-cols-3 gap-6 mb-8">
            <div className="stat-card">
              <Shield className="h-8 w-8 text-blue-400 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-white mb-2">Secure Infrastructure</h3>
              <p className="text-gray-300 text-sm">Bank-level security with encrypted connections</p>
            </div>
            
            <div className="stat-card">
              <Globe className="h-8 w-8 text-green-400 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-white mb-2">Global Coverage</h3>
              <p className="text-gray-300 text-sm">Worldwide satellite network coverage</p>
            </div>
            
            <div className="stat-card">
              <TrendingUp className="h-8 w-8 text-purple-400 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-white mb-2">Real-time Analytics</h3>
              <p className="text-gray-300 text-sm">Monitor performance and earnings live</p>
            </div>
          </div>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link to="/rental" className="btn-primary">
              Start Renting Now
            </Link>
            <Link to="/investment" className="text-cyan-400 px-8 py-4 rounded-lg font-semibold border border-cyan-400/20 hover:bg-cyan-400/10 transition-all">
              Learn About Investments
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;
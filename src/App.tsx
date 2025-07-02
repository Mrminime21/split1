import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import Layout from './components/Layout';
import Home from './pages/Home';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Rental from './pages/Rental';
import Investment from './pages/Investment';
import Referrals from './pages/Referrals';
import Deposit from './pages/Deposit';
import Withdrawal from './pages/Withdrawal';
import Profile from './pages/Profile';
import { AuthProvider } from './contexts/AuthContext';
import { SupabaseProvider } from './contexts/SupabaseContext';

function App() {
  return (
    <SupabaseProvider>
      <AuthProvider>
        <Router>
          <div className="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
            <Layout>
              <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/login" element={<Login />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/rental" element={<Rental />} />
                <Route path="/investment" element={<Investment />} />
                <Route path="/referrals" element={<Referrals />} />
                <Route path="/deposit" element={<Deposit />} />
                <Route path="/withdrawal" element={<Withdrawal />} />
                <Route path="/profile" element={<Profile />} />
              </Routes>
            </Layout>
            <Toaster 
              position="top-right"
              toastOptions={{
                duration: 4000,
                style: {
                  background: '#1e293b',
                  color: '#e2e8f0',
                  border: '1px solid #3b82f6',
                },
              }}
            />
          </div>
        </Router>
      </AuthProvider>
    </SupabaseProvider>
  );
}

export default App;
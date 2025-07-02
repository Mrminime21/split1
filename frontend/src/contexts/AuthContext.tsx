import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { authAPI, userAPI } from '../lib/api';
import toast from 'react-hot-toast';

interface User {
  id: string;
  username: string;
  email: string;
  referral_code: string;
  balance: number;
  total_earnings: number;
  total_invested: number;
  total_withdrawn: number;
  referral_earnings: number;
  rental_earnings: number;
  investment_earnings: number;
  status: string;
  created_at: string;
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  signIn: (email: string, password: string) => Promise<void>;
  signUp: (email: string, password: string, username: string, referralCode?: string) => Promise<void>;
  signOut: () => void;
  updateProfile: (updates: Partial<User>) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem('token');
    if (token) {
      fetchCurrentUser();
    } else {
      setLoading(false);
    }
  }, []);

  const fetchCurrentUser = async () => {
    try {
      setLoading(true);
      const response = await authAPI.getCurrentUser();
      setUser(response.data.user);
    } catch (error) {
      console.error('Failed to fetch current user:', error);
      localStorage.removeItem('token');
    } finally {
      setLoading(false);
    }
  };

  const signIn = async (email: string, password: string) => {
    try {
      setLoading(true);
      const response = await authAPI.login({ email, password });
      localStorage.setItem('token', response.data.token);
      setUser(response.data.user);
      toast.success('Successfully signed in!');
    } catch (error: any) {
      console.error('Sign in error:', error);
      const errorMessage = error.response?.data?.message || 'Failed to sign in';
      toast.error(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const signUp = async (email: string, password: string, username: string, referralCode?: string) => {
    try {
      setLoading(true);
      const response = await authAPI.register({ email, password, username, referralCode });
      localStorage.setItem('token', response.data.token);
      setUser(response.data.user);
      toast.success('Account created successfully!');
    } catch (error: any) {
      console.error('Sign up error:', error);
      const errorMessage = error.response?.data?.message || 'Failed to create account';
      toast.error(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const signOut = () => {
    localStorage.removeItem('token');
    setUser(null);
    toast.success('Successfully signed out!');
  };

  const updateProfile = async (updates: Partial<User>) => {
    try {
      setLoading(true);
      const response = await userAPI.updateProfile(updates);
      setUser({ ...user!, ...updates });
      toast.success('Profile updated successfully!');
    } catch (error: any) {
      console.error('Profile update error:', error);
      const errorMessage = error.response?.data?.message || 'Failed to update profile';
      toast.error(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const value = {
    user,
    loading,
    signIn,
    signUp,
    signOut,
    updateProfile,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
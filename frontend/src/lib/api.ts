import axios from 'axios';

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:3000/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

// Add token to requests if available
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle token expiration
    if (error.response && error.response.status === 401) {
      // Clear token and redirect to login if token is invalid or expired
      if (error.response.data.message === 'Token expired. Please login again.' ||
          error.response.data.message === 'Invalid token.') {
        localStorage.removeItem('token');
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  register: (data: { username: string; email: string; password: string; referralCode?: string }) => 
    api.post('/auth/register', data),
  
  login: (data: { email: string; password: string }) => 
    api.post('/auth/login', data),
  
  getCurrentUser: () => 
    api.get('/auth/me')
};

// User API
export const userAPI = {
  getProfile: () => 
    api.get('/users/profile'),
  
  updateProfile: (data: any) => 
    api.put('/users/profile', data),
  
  changePassword: (data: { currentPassword: string; newPassword: string }) => 
    api.put('/users/change-password', data),
  
  getDashboard: () => 
    api.get('/users/dashboard')
};

// Device API
export const deviceAPI = {
  getDevices: () => 
    api.get('/devices'),
  
  getDevice: (id: string) => 
    api.get(`/devices/${id}`)
};

// Rental API
export const rentalAPI = {
  getRentals: () => 
    api.get('/rentals'),
  
  getRental: (id: string) => 
    api.get(`/rentals/${id}`),
  
  createRental: (data: { deviceId: string; planType: string; duration: number }) => 
    api.post('/rentals', data)
};

// Investment API
export const investmentAPI = {
  getInvestments: () => 
    api.get('/investments'),
  
  getInvestment: (id: string) => 
    api.get(`/investments/${id}`),
  
  createInvestment: (data: { planType: string; amount: number }) => 
    api.post('/investments', data)
};

// Payment API
export const paymentAPI = {
  getPayments: () => 
    api.get('/payments'),
  
  createDeposit: (data: { amount: number; method: string }) => 
    api.post('/payments/deposit', data),
  
  createWithdrawal: (data: { amount: number; method: string; address: string; notes?: string }) => 
    api.post('/payments/withdraw', data),
  
  getWithdrawals: () => 
    api.get('/payments/withdrawals')
};

// Referral API
export const referralAPI = {
  getReferrals: () => 
    api.get('/referrals')
};

// Admin API
export const adminAPI = {
  login: (data: { username: string; password: string }) => 
    api.post('/admin/login', data),
  
  getDashboard: () => 
    api.get('/admin/dashboard'),
  
  getUsers: (page = 1, limit = 10) => 
    api.get(`/admin/users?page=${page}&limit=${limit}`),
  
  getUser: (id: string) => 
    api.get(`/admin/users/${id}`),
  
  updateUser: (id: string, data: any) => 
    api.put(`/admin/users/${id}`, data),
  
  getDevices: () => 
    api.get('/admin/devices'),
  
  createDevice: (data: any) => 
    api.post('/admin/devices', data),
  
  updateDevice: (id: string, data: any) => 
    api.put(`/admin/devices/${id}`, data),
  
  getWithdrawals: (page = 1, limit = 10, status = 'pending') => 
    api.get(`/admin/withdrawals?page=${page}&limit=${limit}&status=${status}`),
  
  processWithdrawal: (id: string, data: { status: string; adminNotes?: string; transactionHash?: string }) => 
    api.put(`/admin/withdrawals/${id}`, data),
  
  getSettings: () => 
    api.get('/admin/settings'),
  
  updateSettings: (data: { settings: Array<{ key: string; value: any; type?: string; category?: string; description?: string; isPublic?: boolean }> }) => 
    api.put('/admin/settings', data)
};

export default api;
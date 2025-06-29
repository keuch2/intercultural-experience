import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';

// Determine the base URL based on platform
const getBaseUrl = () => {
  // Get current window location for web to handle different Expo ports
  if (Platform.OS === 'web') {
    // Use a direct full URL to XAMPP
    return 'http://localhost/intercultural-experience/public/api';
  } else if (Platform.OS === 'ios') {
    return 'http://localhost/intercultural-experience/public/api'; // Works for iOS simulator
  } else if (Platform.OS === 'android') {
    return 'http://10.0.2.2/intercultural-experience/public/api'; // For Android emulator
  } else {
    // For physical devices, you'll need to use your computer's local IP address
    // Example: return 'http://192.168.1.100/intercultural-experience/public/api';
    return 'http://localhost/intercultural-experience/public/api';
  }
};

// Flag to control whether to use mock data in development
// Set this to false to always attempt real API connections
const USE_MOCK_DATA_IN_DEV = false;

// Create a real API client that connects to your Laravel API
const apiClient = axios.create({
  baseURL: getBaseUrl(),
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// For development, we'll keep the mock responses as a fallback
// You can remove this section once your API is fully functional
const mockResponses = {
  '/login': {
    user: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'user',
    },
    token: 'mock-auth-token-123456789',
  },
  '/register': {
    user: {
      id: 2,
      name: 'New User',
      email: 'new@example.com',
      role: 'user',
    },
    token: 'mock-auth-token-987654321',
  },
  '/user': {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    role: 'user',
  },
  '/ping': {
    message: 'API is working!',
    status: 'success',
    timestamp: new Date().toISOString(),
    source: 'mock',
  },
  '/programs': [
    {
      id: 1,
      name: 'Study Abroad - Spain',
      description: 'Semester abroad in Madrid, Spain',
      duration: '4 months',
      location: 'Madrid, Spain',
      credits: 15,
      cost: 8500,
      application_deadline: '2025-08-15',
      start_date: '2026-01-10',
      end_date: '2026-05-10',
      status: 'open',
    },
    {
      id: 2,
      name: 'Exchange Program - Japan',
      description: 'Full year exchange with Tokyo University',
      duration: '9 months',
      location: 'Tokyo, Japan',
      credits: 30,
      cost: 12000,
      application_deadline: '2025-09-30',
      start_date: '2026-04-01',
      end_date: '2026-12-20',
      status: 'open',
    },
  ],
};



// Request interceptor for adding auth token
apiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (error) {
      console.error('Error getting auth token:', error);
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling common errors
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle authentication errors
    if (error.response && error.response.status === 401) {
      // Clear token and redirect to login
      try {
        await AsyncStorage.removeItem('auth_token');
        // You might want to add navigation to login screen here
      } catch (storageError) {
        console.error('Error removing auth token:', storageError);
      }
    }
    
    // Log detailed error information for debugging
    console.error('API Error:', {
      url: error.config?.url,
      method: error.config?.method,
      status: error.response?.status,
      statusText: error.response?.statusText,
      data: error.response?.data,
      message: error.message
    });
    
    // If API server is not available or returns an error, use mock data only if configured to do so
    // Otherwise, let the real error propagate to the UI
    if (USE_MOCK_DATA_IN_DEV && (!error.response || error.response.status >= 500 || error.message === 'Network Error')) {
      // Extract the endpoint path from the full URL
      let url = error.config.url || '';
      
      // If the URL includes the base URL, remove it to get just the endpoint
      if (url.includes(apiClient.defaults.baseURL || '')) {
        url = url.replace(apiClient.defaults.baseURL || '', '');
      }
      
      // If the URL starts with a slash, use it as is
      // Otherwise, try to extract just the endpoint name
      const endpoint = url.startsWith('/') ? url : `/${url.split('/').pop()}`;
      
      console.log(`API server error or unavailable, using mock data for: ${endpoint}`);
      
      // Try to find a matching mock response
      if (mockResponses[endpoint as keyof typeof mockResponses]) {
        console.log(`Using mock data for ${endpoint}`);
        // Add a header to indicate this is mock data
        return Promise.resolve({ 
          data: mockResponses[endpoint as keyof typeof mockResponses],
          headers: { 'x-mock-response': 'true' }
        });
      }
      
      // If we don't have a specific mock for this endpoint but it's a GET request for a collection
      // return an empty array or object as appropriate
      if (error.config.method === 'get') {
        if (endpoint.includes('programs')) {
          return Promise.resolve({ 
            data: mockResponses['/programs'],
            headers: { 'x-mock-response': 'true' }
          });
        }
        
        // Default empty response for unknown endpoints
        console.log(`No mock data for ${endpoint}, returning empty response`);
        return Promise.resolve({ 
          data: endpoint.endsWith('s') ? [] : {},
          headers: { 'x-mock-response': 'true' }
        });
      }
    }
    
    return Promise.reject(error);
  }
);

export default apiClient;

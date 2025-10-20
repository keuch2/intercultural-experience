import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';
import NetInfo from '@react-native-community/netinfo';
import { OfflineManager } from '../../utils/OfflineUtils';

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
  timeout: 30000, // Aumentado a 30 segundos
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



// Request interceptor for adding auth token and handling offline requests
apiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }

      // Check network connectivity
      const netState = await NetInfo.fetch();
      const isOnline = netState.isConnected && netState.isInternetReachable !== false;

      // If offline and this is a mutating request, queue it
      if (!isOnline && config.method && ['post', 'put', 'patch', 'delete'].includes(config.method.toLowerCase())) {
        const queueId = await OfflineManager.queueRequest(
          config.method.toUpperCase() as any,
          config.url || '',
          config.data,
          config.headers as Record<string, string>
        );
        
        // Return a rejected promise with a special offline error
        const offlineError = new Error('Request queued for when connection is restored');
        (offlineError as any).isOfflineQueued = true;
        (offlineError as any).queueId = queueId;
        throw offlineError;
      }

      // For GET requests when offline, try to return cached data
      if (!isOnline && config.method?.toLowerCase() === 'get') {
        const cachedData = await OfflineManager.getCachedData(config.url || '');
        if (cachedData) {
          console.log(`Using cached data for offline GET: ${config.url}`);
          // Return cached data as a resolved response
          return Promise.reject({
            isOfflineCached: true,
            cachedData,
            config
          });
        }
      }
    } catch (error) {
      if ((error as any).isOfflineQueued || (error as any).isOfflineCached) {
        throw error;
      }
      console.error('Error in request interceptor:', error);
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling common errors and caching
apiClient.interceptors.response.use(
  async (response) => {
    // Cache successful GET responses
    if (response.config.method?.toLowerCase() === 'get' && response.data) {
      const url = response.config.url || '';
      const expirationTime = getExpirationTime(url);
      await OfflineManager.cacheData(url, response.data, expirationTime);
    }
    return response;
  },
  async (error) => {
    // Handle offline cached data
    if (error.isOfflineCached) {
      return Promise.resolve({
        data: error.cachedData,
        headers: { 'x-cached-response': 'true' },
        config: error.config,
        status: 200,
        statusText: 'OK (Cached)'
      });
    }

    // Handle offline queued requests
    if (error.isOfflineQueued) {
      return Promise.reject({
        message: 'Sin conexión. La acción se realizará cuando se restablezca la conexión.',
        isOfflineQueued: true,
        queueId: error.queueId
      });
    }
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
    
    // Log error information safely (avoid sensitive data exposure)
    const isDevelopment = __DEV__;
    
    if (isDevelopment) {
      console.error('API Error:', {
        url: error.config?.url,
        method: error.config?.method,
        status: error.response?.status,
        statusText: error.response?.statusText,
        message: error.message
      });
      
      // Only log response data in development and sanitize it
      if (error.response?.data) {
        const sanitizedData = { ...error.response.data };
        // Remove sensitive fields from logs
        delete sanitizedData.password;
        delete sanitizedData.token;
        delete sanitizedData.bank_info;
        console.error('Response data:', sanitizedData);
      }
    } else {
      // In production, only log basic error info
      console.error('API Error:', {
        status: error.response?.status,
        message: 'Network or server error occurred'
      });
    }
    
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

// Helper function to determine cache expiration time based on endpoint
function getExpirationTime(url: string): number {
  if (url.includes('/user')) {
    return 5 * 60 * 1000; // 5 minutes for user data
  }
  if (url.includes('/programs')) {
    return 10 * 60 * 1000; // 10 minutes for programs
  }
  if (url.includes('/applications')) {
    return 5 * 60 * 1000; // 5 minutes for applications
  }
  return 3 * 60 * 1000; // 3 minutes default
}

// Function to process offline queue when connection is restored
export async function processOfflineQueue(): Promise<void> {
  const retryableItems = await OfflineManager.getRetryableItems();
  
  for (const item of retryableItems) {
    try {
      const response = await apiClient({
        method: item.method.toLowerCase() as any,
        url: item.url,
        data: item.data,
        headers: item.headers,
      });
      
      console.log(`Successfully processed queued request: ${item.method} ${item.url}`);
      await OfflineManager.removeFromQueue(item.id);
    } catch (error) {
      console.error(`Failed to process queued request: ${item.method} ${item.url}`, error);
      await OfflineManager.updateRetryCount(item.id);
    }
  }
}

export default apiClient;

import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { apiClient } from '../services/api';
import InputField from '../components/InputField';

const ApiTestScreen = () => {
  const [loading, setLoading] = useState(false);
  const [response, setResponse] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  const [email, setEmail] = useState('new_student@example.com');
  const [password, setPassword] = useState('password123');
  const [name, setName] = useState('Nuevo Estudiante');

  const testPublicEndpoint = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Test the Laravel API's public endpoint using the configured apiClient
      console.log('Attempting to connect to API at:', apiClient.defaults.baseURL);
      const response = await apiClient.get('/simple-test');
      setResponse(response.data);
      console.log('Public endpoint response:', response.data);
    } catch (err: any) {
      console.error('Error testing public endpoint:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        config: {
          url: err.config?.url,
          baseURL: err.config?.baseURL,
          method: err.config?.method,
          headers: err.config?.headers
        },
        isMock: err.response?.headers?.['x-mock-response'] === 'true'
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };
  
  const testDirectAccess = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Test direct access to PHP file bypassing Laravel routes
      const directUrl = 'http://localhost/intercultural-experience/public/test.php';
      console.log('Testing direct PHP access at:', directUrl);
      const response = await axios.get(directUrl);
      setResponse(response.data);
      console.log('Direct PHP response:', response.data);
    } catch (err: any) {
      console.error('Error testing direct PHP access:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        config: {
          url: err.config?.url,
          method: err.config?.method
        }
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };
  
  const testDirectLogin = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Test direct login using the PHP test file that bypasses Laravel
      const directUrl = 'http://localhost/intercultural-experience/public/test-login.php';
      console.log('Testing direct login at:', directUrl);
      const response = await axios.post(directUrl, {
        email,
        password
      }, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });
      
      setResponse(response.data);
      console.log('Direct login response:', response.data);
      
      // If token is in the response, save it
      if (response.data.token) {
        await AsyncStorage.setItem('auth_token', response.data.token);
        console.log('Auth token saved from direct login');
      }
    } catch (err: any) {
      console.error('Error testing direct login:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        config: {
          url: err.config?.url,
          method: err.config?.method,
          headers: err.config?.headers
        }
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };
  
  const testVisitUrl = async (url: string) => {
    setLoading(true);
    setResponse({message: `Opening URL in new tab: ${url}`});
    window.open(url, '_blank');
    setLoading(false);
  };

  const testLogin = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Test login with the configured apiClient
      console.log('Attempting login with:', { email, password });
      const response = await apiClient.post('/login', {
        email,
        password
      });
      setResponse(response.data);
      console.log('Login response:', response.data);
      
      // If token is in the response, save it
      if (response.data.token) {
        await AsyncStorage.setItem('auth_token', response.data.token);
        console.log('Auth token saved to AsyncStorage');
      }
    } catch (err: any) {
      console.error('Error testing login:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        isMock: err.response?.headers?.['x-mock-response'] === 'true'
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };

  const testRegister = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Test register with the configured apiClient
      console.log('Attempting register with:', { name, email, password });
      const response = await apiClient.post('/register', {
        name,
        email,
        password,
        password_confirmation: password
      });
      setResponse(response.data);
      console.log('Register response:', response.data);
      
      // If token is in the response, save it
      if (response.data.token) {
        await AsyncStorage.setItem('auth_token', response.data.token);
        console.log('Auth token saved to AsyncStorage');
      }
    } catch (err: any) {
      console.error('Error testing register:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        isMock: err.response?.headers?.['x-mock-response'] === 'true'
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };

  const testPrograms = async () => {
    setLoading(true);
    setResponse(null);
    setError(null);
    
    try {
      // Get the auth token from AsyncStorage
      const token = await AsyncStorage.getItem('auth_token');
      
      if (!token) {
        throw new Error('No auth token found. Please login first.');
      }
      
      // Set the auth header
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Test an authenticated endpoint
      console.log('Testing authenticated endpoint with token:', token);
      const response = await apiClient.get('/applications');
      setResponse(response.data);
      console.log('Programs response:', response.data);
    } catch (err: any) {
      console.error('Error testing programs:', err);
      const errorDetails = {
        message: err.message,
        status: err.response?.status,
        statusText: err.response?.statusText,
        data: err.response?.data,
        isMock: err.response?.headers?.['x-mock-response'] === 'true'
      };
      setError(JSON.stringify(errorDetails, null, 2));
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <StatusBar style="dark" />
      <View style={styles.header}>
        <Text style={styles.title}>API Connection Test</Text>
        <Text style={styles.subtitle}>Test your connection to the Laravel API</Text>
        <Text style={styles.baseUrlInfo}>Current API URL: {apiClient.defaults.baseURL}</Text>
      </View>

      <ScrollView style={styles.formContainer}>
        <Text style={styles.sectionTitle}>Test Credentials</Text>
        
        <InputField
          label="Name"
          value={name}
          onChangeText={setName}
          placeholder="Enter name"
        />
        
        <InputField
          label="Email"
          value={email}
          onChangeText={setEmail}
          placeholder="Enter email"
          keyboardType="email-address"
        />
        
        <InputField
          label="Password"
          value={password}
          onChangeText={setPassword}
          placeholder="Enter password"
          secureTextEntry
        />
        
        <View style={styles.buttonContainer}>
          <Text style={styles.sectionTitle}>API Tests</Text>
          <TouchableOpacity 
            style={styles.button} 
            onPress={testPublicEndpoint}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Connection</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={testLogin}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Login</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={testRegister}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Register</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={testPrograms}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Programs (Auth)</Text>
          </TouchableOpacity>
          
          <Text style={[styles.sectionTitle, {marginTop: 20}]}>Direct URL Tests</Text>
          <TouchableOpacity 
            style={styles.button} 
            onPress={testDirectAccess}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Direct PHP Access</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={testDirectLogin}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Test Direct PHP Login</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={() => testVisitUrl('http://localhost/intercultural-experience/public/test.php')}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Open Direct PHP URL</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.button} 
            onPress={() => testVisitUrl(apiClient.defaults.baseURL + '/simple-test')}
            disabled={loading}
          >
            <Text style={styles.buttonText}>Open API URL in Browser</Text>
          </TouchableOpacity>
        </View>
        
        {loading && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#0066cc" />
            <Text style={styles.loadingText}>Loading...</Text>
          </View>
        )}
        
        {error && (
          <View style={styles.errorContainer}>
            <Text style={styles.errorTitle}>Error:</Text>
            <Text style={styles.errorText}>{error}</Text>
          </View>
        )}
        
        {response && (
          <View style={styles.responseContainer}>
            <Text style={styles.responseTitle}>Response:</Text>
            <Text style={styles.responseText}>
              {JSON.stringify(response, null, 2)}
            </Text>
          </View>
        )}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    padding: 16,
  },
  header: {
    marginBottom: 20,
    marginTop: 40,
  },
  baseUrlInfo: {
    fontSize: 12,
    color: '#666',
    fontStyle: 'italic',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 20,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginBottom: 20,
  },
  formContainer: {
    flex: 1,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  buttonContainer: {
    marginTop: 20,
    marginBottom: 20,
  },
  button: {
    backgroundColor: '#007AFF',
    borderRadius: 8,
    padding: 12,
    alignItems: 'center',
    marginBottom: 10,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  loadingContainer: {
    padding: 20,
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
  },
  errorContainer: {
    backgroundColor: '#FFF0F0',
    borderRadius: 8,
    padding: 15,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#FFCCCC',
  },
  errorTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#CC0000',
    marginBottom: 5,
  },
  errorText: {
    color: '#333',
    fontFamily: 'monospace',
  },
  responseContainer: {
    backgroundColor: '#F0F8FF',
    borderRadius: 8,
    padding: 15,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#CCCCFF',
  },
  responseTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#0066CC',
    marginBottom: 5,
  },
  responseText: {
    color: '#333',
    fontFamily: 'monospace',
  }
});

export default ApiTestScreen;

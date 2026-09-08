import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { StatusBar } from 'expo-status-bar';
import AppNavigator from './src/navigation/AppNavigator';
import { AuthProvider } from './src/contexts/AuthContext';
import { NetworkProvider } from './src/contexts/NetworkContext';
import ErrorBoundary from './src/components/ErrorBoundary';
import OfflineQueueManager from './src/services/OfflineQueueManager';

export default function App() {
  return (
    <ErrorBoundary>
      <SafeAreaProvider>
      <StatusBar style="dark" />
      <NetworkProvider>
        <AuthProvider>
          <NavigationContainer>
            <OfflineQueueManager>
              <AppNavigator />
            </OfflineQueueManager>
          </NavigationContainer>
        </AuthProvider>
      </NetworkProvider>
      </SafeAreaProvider>
    </ErrorBoundary>
  );
}

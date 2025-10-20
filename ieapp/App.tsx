import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import AppNavigator from './src/navigation/AppNavigator';
import { AuthProvider } from './src/contexts/AuthContext';
import { NetworkProvider } from './src/contexts/NetworkContext';
import ErrorBoundary from './src/components/ErrorBoundary';
import OfflineQueueManager from './src/services/OfflineQueueManager';

export default function App() {
  return (
    <ErrorBoundary>
      <NetworkProvider>
        <AuthProvider>
          <NavigationContainer>
            <OfflineQueueManager>
              <AppNavigator />
            </OfflineQueueManager>
          </NavigationContainer>
        </AuthProvider>
      </NetworkProvider>
    </ErrorBoundary>
  );
}

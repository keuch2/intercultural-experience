import React, { useEffect, useCallback, ReactNode } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import { useNetwork } from '../contexts/NetworkContext';
import { processOfflineQueue } from './api/apiClient';
import { OfflineManager } from '../utils/OfflineUtils';

interface OfflineQueueManagerProps {
  children: ReactNode;
}

const OfflineQueueManager: React.FC<OfflineQueueManagerProps> = ({ children }) => {
  const { isOnline, connectionQuality } = useNetwork();

  const processQueue = useCallback(async () => {
    if (isOnline && connectionQuality !== 'poor') {
      try {
        await processOfflineQueue();
        console.log('Offline queue processed successfully');
      } catch (error) {
        console.error('Error processing offline queue:', error);
      }
    }
  }, [isOnline, connectionQuality]);

  // Process queue when connection is restored
  useEffect(() => {
    if (isOnline && connectionQuality !== 'poor') {
      // Add a small delay to ensure connection is stable
      const timer = setTimeout(() => {
        processQueue();
      }, 1000);

      return () => clearTimeout(timer);
    }
  }, [isOnline, connectionQuality, processQueue]);

  // Process queue when app becomes active (user returns to app)
  useEffect(() => {
    const handleAppStateChange = (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active' && isOnline && connectionQuality !== 'poor') {
        processQueue();
      }
    };

    const subscription = AppState.addEventListener('change', handleAppStateChange);

    return () => {
      subscription?.remove();
    };
  }, [isOnline, connectionQuality, processQueue]);

  // Periodic queue processing (every 30 seconds when online)
  useEffect(() => {
    if (isOnline && connectionQuality !== 'poor') {
      const interval = setInterval(() => {
        processQueue();
      }, 30000); // 30 seconds

      return () => clearInterval(interval);
    }
  }, [isOnline, connectionQuality, processQueue]);

  return <>{children}</>;
};

export default OfflineQueueManager;

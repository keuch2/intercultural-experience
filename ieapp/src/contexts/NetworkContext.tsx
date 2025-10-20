import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import NetInfo from '@react-native-community/netinfo';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface NetworkState {
  isConnected: boolean;
  isInternetReachable: boolean | null;
  type: string | null;
  isStrongConnection: boolean;
}

interface NetworkContextType extends NetworkState {
  retryConnection: () => Promise<void>;
  isOnline: boolean;
  connectionQuality: 'excellent' | 'good' | 'poor' | 'offline';
  hasBeenOffline: boolean;
}

const NetworkContext = createContext<NetworkContextType | undefined>(undefined);

export const NetworkProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [networkState, setNetworkState] = useState<NetworkState>({
    isConnected: true,
    isInternetReachable: null,
    type: null,
    isStrongConnection: true,
  });
  const [hasBeenOffline, setHasBeenOffline] = useState(false);

  useEffect(() => {
    // Subscribe to network state updates
    const unsubscribe = NetInfo.addEventListener(state => {
      const isConnected = state.isConnected ?? false;
      const isInternetReachable = state.isInternetReachable;
      const type = state.type;
      
      // Determine if connection is strong based on type and details
      let isStrongConnection = true;
      if (state.type === 'cellular' && state.details.cellularGeneration) {
        const generation = state.details.cellularGeneration;
        isStrongConnection = generation === '4g' || generation === '5g';
      } else if (state.type === 'wifi') {
        // WiFi is generally considered strong
        isStrongConnection = true;
      } else if (state.type === 'none') {
        isStrongConnection = false;
      }

      // Track if user has been offline during this session
      if (!isConnected) {
        setHasBeenOffline(true);
        // Store offline state for persistence
        AsyncStorage.setItem('has_been_offline', 'true');
      }

      setNetworkState({
        isConnected,
        isInternetReachable,
        type,
        isStrongConnection,
      });
    });

    // Check if user has been offline before
    AsyncStorage.getItem('has_been_offline').then(value => {
      if (value === 'true') {
        setHasBeenOffline(true);
      }
    });

    // Cleanup subscription on unmount
    return () => unsubscribe();
  }, []);

  const retryConnection = async () => {
    try {
      const state = await NetInfo.fetch();
      const isConnected = state.isConnected ?? false;
      const isInternetReachable = state.isInternetReachable;
      const type = state.type;
      
      let isStrongConnection = true;
      if (state.type === 'cellular' && state.details.cellularGeneration) {
        const generation = state.details.cellularGeneration;
        isStrongConnection = generation === '4g' || generation === '5g';
      } else if (state.type === 'wifi') {
        isStrongConnection = true;
      } else if (state.type === 'none') {
        isStrongConnection = false;
      }

      setNetworkState({
        isConnected,
        isInternetReachable,
        type,
        isStrongConnection,
      });
    } catch (error) {
      console.error('Error checking network state:', error);
    }
  };

  // Derive computed properties
  const isOnline = networkState.isConnected && networkState.isInternetReachable !== false;
  
  const connectionQuality: 'excellent' | 'good' | 'poor' | 'offline' = (() => {
    if (!networkState.isConnected) return 'offline';
    if (networkState.type === 'wifi') return 'excellent';
    if (networkState.type === 'cellular' && networkState.isStrongConnection) return 'good';
    if (networkState.type === 'cellular') return 'poor';
    return networkState.isConnected ? 'good' : 'offline';
  })();

  const contextValue: NetworkContextType = {
    ...networkState,
    retryConnection,
    isOnline,
    connectionQuality,
    hasBeenOffline,
  };

  return (
    <NetworkContext.Provider value={contextValue}>
      {children}
    </NetworkContext.Provider>
  );
};

export const useNetwork = (): NetworkContextType => {
  const context = useContext(NetworkContext);
  if (context === undefined) {
    throw new Error('useNetwork must be used within a NetworkProvider');
  }
  return context;
};

import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Animated } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNetwork } from '../contexts/NetworkContext';

interface NetworkStatusBannerProps {
  onRetry?: () => void;
}

const NetworkStatusBanner: React.FC<NetworkStatusBannerProps> = ({ onRetry }) => {
  const { isOnline, connectionQuality, retryConnection } = useNetwork();
  const [fadeAnim] = React.useState(new Animated.Value(0));

  React.useEffect(() => {
    if (!isOnline || connectionQuality === 'poor') {
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 300,
        useNativeDriver: true,
      }).start();
    } else {
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 300,
        useNativeDriver: true,
      }).start();
    }
  }, [isOnline, connectionQuality, fadeAnim]);

  const handleRetry = async () => {
    await retryConnection();
    if (onRetry) {
      onRetry();
    }
  };

  const getBannerConfig = () => {
    if (!isOnline) {
      return {
        backgroundColor: '#E52224',
        icon: 'wifi-outline' as const,
        text: 'Sin conexión a internet',
        showRetry: true,
      };
    }
    
    if (connectionQuality === 'poor') {
      return {
        backgroundColor: '#F8B400',
        icon: 'warning-outline' as const,
        text: 'Conexión lenta detectada',
        showRetry: false,
      };
    }

    return null;
  };

  const config = getBannerConfig();

  if (!config) {
    return null;
  }

  return (
    <Animated.View style={[styles.banner, { backgroundColor: config.backgroundColor, opacity: fadeAnim }]}>
      <View style={styles.content}>
        <Ionicons name={config.icon} size={20} color="#FFFFFF" style={styles.icon} />
        <Text style={styles.text}>{config.text}</Text>
        {config.showRetry && (
          <TouchableOpacity onPress={handleRetry} style={styles.retryButton}>
            <Text style={styles.retryText}>Reintentar</Text>
          </TouchableOpacity>
        )}
      </View>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  banner: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    elevation: 4,
    boxShadow: '0px 2px 4px rgba(0, 0, 0, 0.25)',
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  icon: {
    marginRight: 8,
  },
  text: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
    flex: 1,
    textAlign: 'center',
  },
  retryButton: {
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  retryText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: 'bold',
  },
});

export default NetworkStatusBanner;

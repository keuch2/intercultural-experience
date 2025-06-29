import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../contexts/AuthContext';

interface HeaderProps {
  showProfileButton?: boolean;
  showBackButton?: boolean;
  title?: string;
  customBackAction?: () => void;
}

const Header: React.FC<HeaderProps> = ({ 
  showProfileButton = true,
  showBackButton = false,
  title,
  customBackAction
}) => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { user } = useAuth();

  return (
    <View style={styles.header}>
      {showBackButton ? (
        <TouchableOpacity 
          style={styles.backButton}
          onPress={customBackAction || (() => navigation.goBack())}
        >
          <Text style={styles.backButtonText}>← Volver</Text>
        </TouchableOpacity>
      ) : (
        <Image 
          source={require('../../assets/images/ie-icon.png')} 
          style={styles.logo}
          resizeMode="contain"
        />
      )}
      
      {title ? (
        <Text style={styles.headerTitle}>{title}</Text>
      ) : (
        <Text style={styles.greeting}>Hola <Text style={styles.highlight}>{user?.name || 'Usuario'}</Text></Text>
      )}
      
      {showProfileButton ? (
        <TouchableOpacity onPress={() => navigation.navigate('Profile')}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{user?.name ? user.name.charAt(0).toUpperCase() : 'U'}</Text>
          </View>
        </TouchableOpacity>
      ) : (
        <View style={{ width: 40 }} />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  header: { 
    flexDirection: 'row', 
    alignItems: 'center', 
    justifyContent: 'space-between', 
    marginBottom: 20,
    paddingHorizontal: 10,
    paddingTop: 10
  },
  logo: { 
    width: 50, 
    height: 50
  },
  greeting: { 
    fontSize: 20 
  },
  highlight: { 
    color: '#E52224', 
    fontWeight: 'bold' 
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    textAlign: 'center'
  },
  backButton: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
    justifyContent: 'center'
  },
  backButtonText: {
    fontSize: 16,
    color: '#6C4AA0',
    fontWeight: '500'
  },
  avatar: { 
    width: 40, 
    height: 40, 
    borderRadius: 20, 
    backgroundColor: '#6C4AA0', 
    justifyContent: 'center', 
    alignItems: 'center' 
  },
  avatarText: { 
    color: '#fff', 
    fontWeight: 'bold', 
    fontSize: 18 
  }
});

export default Header;

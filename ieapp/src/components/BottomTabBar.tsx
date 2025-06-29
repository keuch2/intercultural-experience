import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { Ionicons } from '@expo/vector-icons';
import { RootStackParamList } from '../navigation/AppNavigator';

export type TabType = 'home' | 'programs' | 'rewards' | 'applications' | 'profile';

interface BottomTabBarProps {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
}

const BottomTabBar: React.FC<BottomTabBarProps> = ({ activeTab, setActiveTab }) => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();

  const handleTabPress = (tab: TabType) => {
    setActiveTab(tab);
    
    // Navegar a la pantalla correspondiente según la pestaña
    switch (tab) {
      case 'home':
        navigation.navigate('Home');
        break;
      case 'programs':
        navigation.navigate('Programs');
        break;
      case 'rewards':
        navigation.navigate('Rewards');
        break;
      case 'applications':
        navigation.navigate('MyApplications');
        break;
      case 'profile':
        navigation.navigate('Profile');
        break;
    }
  };

  return (
    <View style={styles.bottomNav}>
      <TouchableOpacity 
        style={styles.navItem} 
        onPress={() => handleTabPress('home')}
      >
        <Ionicons 
          name="home" 
          size={24} 
          color={activeTab === 'home' ? "#E52224" : "#777"} 
        />
        <Text style={activeTab === 'home' ? styles.activeNavText : styles.navText}>Inicio</Text>
      </TouchableOpacity>
      
      <TouchableOpacity 
        style={styles.navItem} 
        onPress={() => handleTabPress('programs')}
      >
        <Ionicons 
          name="globe-outline" 
          size={24} 
          color={activeTab === 'programs' ? "#E52224" : "#777"} 
        />
        <Text style={activeTab === 'programs' ? styles.activeNavText : styles.navText}>Programas</Text>
      </TouchableOpacity>
      
      <TouchableOpacity 
        style={styles.navItem} 
        onPress={() => handleTabPress('rewards')}
      >
        <Ionicons 
          name="gift-outline" 
          size={24} 
          color={activeTab === 'rewards' ? "#E52224" : "#777"} 
        />
        <Text style={activeTab === 'rewards' ? styles.activeNavText : styles.navText}>Recompensas</Text>
      </TouchableOpacity>
      
      <TouchableOpacity 
        style={styles.navItem} 
        onPress={() => handleTabPress('applications')}
      >
        <Ionicons 
          name="document-text-outline" 
          size={24} 
          color={activeTab === 'applications' ? "#E52224" : "#777"} 
        />
        <Text style={activeTab === 'applications' ? styles.activeNavText : styles.navText}>Solicitudes</Text>
      </TouchableOpacity>
      
      <TouchableOpacity 
        style={styles.navItem} 
        onPress={() => handleTabPress('profile')}
      >
        <Ionicons 
          name="person-outline" 
          size={24} 
          color={activeTab === 'profile' ? "#E52224" : "#777"} 
        />
        <Text style={activeTab === 'profile' ? styles.activeNavText : styles.navText}>Perfil</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  bottomNav: { 
    flexDirection: 'row', 
    justifyContent: 'space-around', 
    padding: 10, 
    paddingBottom: 15,
    borderTopWidth: 1, 
    borderColor: '#eee',
    backgroundColor: '#fff'
  },
  navItem: {
    alignItems: 'center',
    justifyContent: 'center'
  },
  activeNavText: {
    color: '#E52224',
    fontSize: 12,
    marginTop: 2,
    fontWeight: 'bold'
  },
  navText: {
    color: '#777',
    fontSize: 12,
    marginTop: 2
  },
});

export default BottomTabBar;

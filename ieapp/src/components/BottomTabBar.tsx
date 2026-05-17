import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { Ionicons } from '@expo/vector-icons';
import { RootStackParamList } from '../navigation/AppNavigator';

/**
 * V1 (Au Pair-only): tabs centrados en el flujo Au Pair.
 *  - home          → AuPairDashboard (Sprint 2). Mientras tanto cae en HomeScreen.
 *  - documents     → AuPairDocuments (Sprint 2). Por ahora cae en MyApplications.
 *  - payments      → PaymentsScreen (Sprint 3). Por ahora cae en MyApplications.
 *  - notifications → NotificationsScreen (Sprint 6). Por ahora cae en Home.
 *  - profile       → ProfileScreen.
 *
 * `applications`, `programs`, `rewards` quedan en el union para compat,
 * pero no se renderizan como tabs en V1.
 */
export type TabType = 'home' | 'documents' | 'payments' | 'notifications' | 'profile'
  // legacy values aún referenciados por código existente
  | 'programs' | 'rewards' | 'applications';

interface BottomTabBarProps {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
}

type TabConfig = {
  key: TabType;
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  iconActive: keyof typeof Ionicons.glyphMap;
  target: keyof RootStackParamList;
};

const TABS: TabConfig[] = [
  { key: 'home', label: 'Inicio', icon: 'home-outline', iconActive: 'home', target: 'AuPairDashboard' },
  { key: 'documents', label: 'Documentos', icon: 'document-text-outline', iconActive: 'document-text', target: 'AuPairDocuments' },
  { key: 'payments', label: 'Pagos', icon: 'card-outline', iconActive: 'card', target: 'Payments' },
  { key: 'notifications', label: 'Avisos', icon: 'notifications-outline', iconActive: 'notifications', target: 'Notifications' },
  { key: 'profile', label: 'Perfil', icon: 'person-outline', iconActive: 'person', target: 'Profile' },
];

const BottomTabBar: React.FC<BottomTabBarProps> = ({ activeTab, setActiveTab }) => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();

  const handlePress = (tab: TabConfig) => {
    setActiveTab(tab.key);
    navigation.navigate(tab.target as never);
  };

  return (
    <View style={styles.bottomNav}>
      {TABS.map(tab => {
        const active = activeTab === tab.key;
        return (
          <TouchableOpacity
            key={tab.key}
            style={styles.navItem}
            onPress={() => handlePress(tab)}
            accessibilityLabel={tab.label}
          >
            <Ionicons
              name={active ? tab.iconActive : tab.icon}
              size={24}
              color={active ? '#E52224' : '#777'}
            />
            <Text style={active ? styles.activeNavText : styles.navText}>{tab.label}</Text>
          </TouchableOpacity>
        );
      })}
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
    backgroundColor: '#fff',
  },
  navItem: { alignItems: 'center', justifyContent: 'center' },
  activeNavText: { color: '#E52224', fontSize: 11, marginTop: 2, fontWeight: 'bold' },
  navText: { color: '#777', fontSize: 11, marginTop: 2 },
});

export default BottomTabBar;

import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useOptionalProgram } from '../contexts/ProgramContext';
import { screensFor } from '../navigation/programFlowRegistry';
import { useTabNavigation } from '../contexts/NavigationContext';

/**
 * Menú inferior:
 *  - home          → Home general (postulación actual + programas disponibles)
 *  - programs      → catálogo de programas
 *  - process       → dashboard del programa activo (Au Pair o motor); sin postulación → catálogo
 *  - notifications → avisos
 *  - profile       → datos personales
 */
export type TabType = 'home' | 'programs' | 'process' | 'notifications' | 'profile'
  // valores legacy aún referenciados por pantallas antiguas
  | 'documents' | 'payments' | 'rewards' | 'applications';

interface BottomTabBarProps {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
}

type TabConfig = {
  key: TabType;
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  iconActive: keyof typeof Ionicons.glyphMap;
  target: string;
};

const TABS: TabConfig[] = [
  { key: 'home', label: 'Inicio', icon: 'home-outline', iconActive: 'home', target: 'Home' },
  { key: 'programs', label: 'Programas', icon: 'globe-outline', iconActive: 'globe', target: 'PublicPrograms' },
  { key: 'process', label: 'Mi proceso', icon: 'map-outline', iconActive: 'map', target: 'ProgramDashboard' },
  { key: 'notifications', label: 'Avisos', icon: 'notifications-outline', iconActive: 'notifications', target: 'Notifications' },
  { key: 'profile', label: 'Perfil', icon: 'person-outline', iconActive: 'person', target: 'Profile' },
];

const BottomTabBar: React.FC<BottomTabBarProps> = ({ activeTab, setActiveTab }) => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const program = useOptionalProgram();
  const insets = useSafeAreaInsets();
  const { unreadCount } = useTabNavigation();
  const flow = program?.flow ?? 'none';

  const targetFor = (tab: TabConfig): string => {
    if (tab.key === 'process') return flow === 'none' ? 'PublicPrograms' : screensFor(flow).home;
    return tab.target;
  };

  const handlePress = (tab: TabConfig) => {
    setActiveTab(tab.key);
    navigation.navigate(targetFor(tab) as never);
  };

  return (
    <View style={[styles.bottomNav, { paddingBottom: 10 + Math.max(insets.bottom, 5) }]}>
      {TABS.map(tab => {
        const active = activeTab === tab.key;
        return (
          <TouchableOpacity key={tab.key} style={styles.navItem} onPress={() => handlePress(tab)} accessibilityLabel={tab.label}>
            <View>
              <Ionicons name={active ? tab.iconActive : tab.icon} size={24} color={active ? '#E52224' : '#777'} />
              {tab.key === 'notifications' && unreadCount > 0 && (
                <View style={styles.badge}><Text style={styles.badgeText}>{unreadCount > 9 ? '9+' : unreadCount}</Text></View>
              )}
            </View>
            <Text style={active ? styles.activeNavText : styles.navText}>{tab.label}</Text>
          </TouchableOpacity>
        );
      })}
    </View>
  );
};

const styles = StyleSheet.create({
  bottomNav: { flexDirection: 'row', justifyContent: 'space-around', padding: 10, borderTopWidth: 1, borderColor: '#eee', backgroundColor: '#fff' },
  navItem: { alignItems: 'center', justifyContent: 'center', minWidth: 56 },
  activeNavText: { color: '#E52224', fontSize: 11, marginTop: 2, fontWeight: 'bold' },
  navText: { color: '#777', fontSize: 11, marginTop: 2 },
  badge: { position: 'absolute', top: -4, right: -10, minWidth: 16, height: 16, borderRadius: 8, backgroundColor: '#E52224', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 3 },
  badgeText: { color: '#fff', fontSize: 9, fontWeight: '800' },
});

export default BottomTabBar;

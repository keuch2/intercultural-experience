import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';

const ScreenHeader: React.FC<{ title: string; right?: React.ReactNode }> = ({ title, right }) => {
  const navigation = useNavigation<any>();
  return (
    <View style={styles.headerBar}>
      <TouchableOpacity onPress={() => (navigation.canGoBack() ? navigation.goBack() : navigation.navigate('ProgramDashboard'))}>
        <Ionicons name="arrow-back" size={24} color="#222" />
      </TouchableOpacity>
      <Text style={styles.title} numberOfLines={1}>{title}</Text>
      <View style={{ width: 24 }}>{right}</View>
    </View>
  );
};

const styles = StyleSheet.create({
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700', color: '#222', flex: 1, textAlign: 'center' },
});

export default ScreenHeader;

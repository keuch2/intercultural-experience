import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import PublicHomeScreen from '../screens/Public/PublicHomeScreen';
import PublicProgramsScreen from '../screens/Public/PublicProgramsScreen';
import PublicProgramDetailScreen from '../screens/Public/PublicProgramDetailScreen';

/**
 * Stack público (sin auth). Punto de entrada para visitantes.
 * Las pantallas usan `usePublicAuth().requestAuth()` para empujar al AuthStack
 * cuando el usuario quiere postular o iniciar sesión — el switch real lo hace
 * AppNavigator.
 */
export type PublicStackParamList = {
  PublicHome: undefined;
  PublicPrograms: undefined;
  PublicProgramDetail: { id: number };
};

const Stack = createNativeStackNavigator<PublicStackParamList>();

const PublicNavigator: React.FC = () => (
  <Stack.Navigator initialRouteName="PublicHome" screenOptions={{ headerShown: false }}>
    <Stack.Screen name="PublicHome" component={PublicHomeScreen} />
    <Stack.Screen name="PublicPrograms" component={PublicProgramsScreen} />
    <Stack.Screen name="PublicProgramDetail" component={PublicProgramDetailScreen} />
  </Stack.Navigator>
);

export default PublicNavigator;

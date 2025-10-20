import React from 'react';
import { View, StyleSheet } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import LoginScreen from '../screens/Auth/LoginScreen';
import RegisterScreen from '../screens/Auth/RegisterScreen';
import ForgotPasswordScreen from '../screens/Auth/ForgotPasswordScreen';
import ResetPasswordScreen from '../screens/Auth/ResetPasswordScreen';
import HomeScreen from '../screens/HomeScreen';
import ProgramsScreen from '../screens/ProgramsScreen';
import ProfileScreen from '../screens/ProfileScreen';
import ApiTestScreen from '../screens/ApiTestScreen';
import ProgramDetailScreen from '../screens/ProgramDetailScreen';
import ApplicationDetailScreen from '../screens/ApplicationDetailScreen';
import ApplicationConfirmScreen from '../screens/ApplicationConfirmScreen';
import MyApplicationsScreen from '../screens/MyApplicationsScreen';
import MyAssignmentsScreen from '../screens/MyAssignmentsScreen';
import AssignmentDetailScreen from '../screens/AssignmentDetailScreen';
import FormScreen from '../screens/FormScreen';
import RewardsScreen from '../screens/RewardsScreen';
import RewardDetailScreen from '../screens/RewardDetailScreen';
import MyRedemptionsScreen from '../screens/MyRedemptionsScreen';
import PointsHistoryScreen from '../screens/PointsHistoryScreen';
import { NavigationProvider } from '../contexts/NavigationContext';
import { useAuth } from '../contexts/AuthContext';
import BottomTabBar from '../components/BottomTabBar';
import WhatsAppButton from '../components/WhatsAppButton';
import { useTabNavigation } from '../contexts/NavigationContext';

export type RootStackParamList = {
  Login: undefined;
  Register: undefined;
  ForgotPassword: undefined;
  ResetPassword: { token: string };
  Home: undefined;
  Programs: undefined;
  Profile: undefined;
  ApiTest: undefined;
  ProgramDetail: { programId: number };
  ApplicationDetail: { applicationId: number };
  ApplicationConfirm: { programId: number };
  MyApplications: undefined;
  MyAssignments: undefined;
  AssignmentDetail: { assignmentId: number; assignment?: any };
  FormScreen: { formId: number; programId: number };
  Rewards: undefined;
  RewardDetail: { reward: any; pointsBalance: any };
  MyRedemptions: undefined;
  RedemptionDetail: { redemption: any };
  PointsHistory: undefined;
};

const AuthStack = createNativeStackNavigator<RootStackParamList>();
const MainStack = createNativeStackNavigator<RootStackParamList>();

// Componente contenedor para pantallas autenticadas que incluye el BottomTabBar y el botón de WhatsApp
const AuthenticatedScreenContainer: React.FC<{children: React.ReactNode}> = ({ children }) => {
  const { activeTab, setActiveTab } = useTabNavigation();
  
  return (
    <View style={styles.container}>
      {children}
      <WhatsAppButton />
      <BottomTabBar activeTab={activeTab} setActiveTab={setActiveTab} />
    </View>
  );
};

// Componentes con el BottomTabBar incluido
const HomeScreenWithNav = () => (
  <AuthenticatedScreenContainer>
    <HomeScreen />
  </AuthenticatedScreenContainer>
);

const ProgramsScreenWithNav = () => (
  <AuthenticatedScreenContainer>
    <ProgramsScreen />
  </AuthenticatedScreenContainer>
);

const ProfileScreenWithNav = () => (
  <AuthenticatedScreenContainer>
    <ProfileScreen />
  </AuthenticatedScreenContainer>
);

// No necesitamos envolver el ProgramDetailScreen con un componente personalizado
// ya que React Navigation pasará automáticamente las props necesarias
const ProgramDetailScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <ProgramDetailScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de confirmación de aplicación también usa el contenedor autenticado
const ApplicationConfirmScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <ApplicationConfirmScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de detalle de aplicación usa el contenedor autenticado
const ApplicationDetailScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <ApplicationDetailScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de mis aplicaciones usa el contenedor autenticado
const MyApplicationsScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <MyApplicationsScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de mis asignaciones usa el contenedor autenticado
const MyAssignmentsScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <MyAssignmentsScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de detalle de asignación usa el contenedor autenticado
const AssignmentDetailScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <AssignmentDetailScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de formulario usa el contenedor autenticado
const FormScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <FormScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de recompensas usa el contenedor autenticado
const RewardsScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <RewardsScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de detalle de recompensa (con WhatsApp button pero sin barra de navegación)
const RewardDetailScreenWithNav = (props: any) => (
  <View style={styles.container}>
    <RewardDetailScreen {...props} />
    <WhatsAppButton />
  </View>
);

// Pantalla de mis canjes usa el contenedor autenticado
const MyRedemptionsScreenWithNav = (props: any) => (
  <AuthenticatedScreenContainer>
    <MyRedemptionsScreen {...props} />
  </AuthenticatedScreenContainer>
);

// Pantalla de historial de puntos (con WhatsApp button pero sin barra de navegación)
const PointsHistoryScreenWithNav = (props: any) => (
  <View style={styles.container}>
    <PointsHistoryScreen {...props} />
    <WhatsAppButton />
  </View>
);

// Stack de navegación para usuarios no autenticados
const AuthStackNavigator = () => (
  <AuthStack.Navigator initialRouteName="Login" screenOptions={{ headerShown: false }}>
    <AuthStack.Screen name="Login" component={LoginScreen} />
    <AuthStack.Screen name="Register" component={RegisterScreen} />
    <AuthStack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
    <AuthStack.Screen name="ResetPassword" component={ResetPasswordScreen} />
    <AuthStack.Screen name="ApiTest" component={ApiTestScreen} />
  </AuthStack.Navigator>
);

// Stack de navegación para usuarios autenticados
const MainStackNavigator = () => (
  <MainStack.Navigator screenOptions={{ headerShown: false }}>
    <MainStack.Screen name="Home" component={HomeScreenWithNav} />
    <MainStack.Screen name="Programs" component={ProgramsScreenWithNav} />
    <MainStack.Screen name="ProgramDetail" component={ProgramDetailScreenWithNav} />
    <MainStack.Screen name="ApplicationConfirm" component={ApplicationConfirmScreenWithNav} />
    <MainStack.Screen name="ApplicationDetail" component={ApplicationDetailScreenWithNav} />
    <MainStack.Screen name="MyApplications" component={MyApplicationsScreenWithNav} />
    <MainStack.Screen name="MyAssignments" component={MyAssignmentsScreenWithNav} />
    <MainStack.Screen name="AssignmentDetail" component={AssignmentDetailScreenWithNav} />
    <MainStack.Screen name="FormScreen" component={FormScreenWithNav} />
    <MainStack.Screen name="Rewards" component={RewardsScreenWithNav} />
    <MainStack.Screen name="RewardDetail" component={RewardDetailScreenWithNav} />
    <MainStack.Screen name="MyRedemptions" component={MyRedemptionsScreenWithNav} />
    <MainStack.Screen name="PointsHistory" component={PointsHistoryScreenWithNav} />
    <MainStack.Screen name="Profile" component={ProfileScreenWithNav} />
  </MainStack.Navigator>
);

// Navegación raíz que decide qué stack mostrar según el estado de autenticación
const AppNavigator = () => {
  const { isAuthenticated } = useAuth();
  
  return (
    <NavigationProvider>
      {isAuthenticated ? <MainStackNavigator /> : <AuthStackNavigator />}
    </NavigationProvider>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    position: 'relative', // Asegurar que sea un contenedor relativo para absolute
  }
});

export default AppNavigator; 
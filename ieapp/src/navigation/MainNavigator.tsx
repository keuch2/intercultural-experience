import React from 'react';
import { View, StyleSheet } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import HomeScreen from '../screens/HomeScreen';
import ProgramsScreen from '../screens/ProgramsScreen';
import ProfileScreen from '../screens/ProfileScreen';
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
import AuPairDashboardScreen from '../screens/AuPair/AuPairDashboardScreen';
import AuPairDocumentsScreen from '../screens/AuPair/AuPairDocumentsScreen';
import AuPairDocumentUploadScreen from '../screens/AuPair/AuPairDocumentUploadScreen';
import PaymentsScreen from '../screens/Payments/PaymentsScreen';
import PaymentDetailScreen from '../screens/Payments/PaymentDetailScreen';
import PaymentRegisterScreen from '../screens/Payments/PaymentRegisterScreen';
import AuPairEnglishTestScreen from '../screens/AuPair/AuPairEnglishTestScreen';
import AuPairVisaScreen from '../screens/AuPair/AuPairVisaScreen';
import AuPairMatchesScreen from '../screens/AuPair/AuPairMatchesScreen';
import AuPairMatchDetailScreen from '../screens/AuPair/AuPairMatchDetailScreen';
import AuPairSupportScreen from '../screens/AuPair/AuPairSupportScreen';
import AuPairResourcesScreen from '../screens/AuPair/AuPairResourcesScreen';
import AuPairOnboardingScreen from '../screens/AuPair/AuPairOnboardingScreen';
import NotificationsScreen from '../screens/NotificationsScreen';
import { usePublicAuth } from '../contexts/PublicAuthContext';
import { NavigationProvider, useTabNavigation } from '../contexts/NavigationContext';
import BottomTabBar from '../components/BottomTabBar';
import WhatsAppButton from '../components/WhatsAppButton';

/**
 * Stack autenticado. Cuando AppNavigator monta este componente, el user
 * ya está logueado. Pantallas Au Pair específicas se agregan en sprints
 * posteriores (Dashboard, Documents, Payments, etc.).
 */
export type MainStackParamList = {
  Home: undefined;
  Programs: undefined;
  Profile: undefined;
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
  // Au Pair (Sprint 2)
  AuPairDashboard: undefined;
  AuPairDocuments: { stage?: 'admission' | 'application_payment1' | 'application_payment2' | 'visa' } | undefined;
  AuPairDocumentUpload: { entry: any };
  // Payments (Sprint 3)
  Payments: undefined;
  PaymentDetail: { paymentId: number };
  PaymentRegister: { applicationId: number };
  // Au Pair Sprint 4
  AuPairEnglishTest: undefined;
  AuPairVisa: undefined;
  AuPairMatches: undefined;
  AuPairMatchDetail: { id: number };
  AuPairSupport: undefined;
  AuPairResources: undefined;
  AuPairOnboarding: { programId?: number } | undefined;
  Notifications: undefined;
};

const Stack = createNativeStackNavigator<MainStackParamList>();

const AuthenticatedScreenContainer: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { activeTab, setActiveTab } = useTabNavigation();
  return (
    <View style={styles.container}>
      {children}
      <WhatsAppButton />
      <BottomTabBar activeTab={activeTab} setActiveTab={setActiveTab} />
    </View>
  );
};

const withTabs = (Component: React.ComponentType<any>) => (props: any) => (
  <AuthenticatedScreenContainer>
    <Component {...props} />
  </AuthenticatedScreenContainer>
);

const HomeWithNav = withTabs(HomeScreen);
const ProgramsWithNav = withTabs(ProgramsScreen);
const ProfileWithNav = withTabs(ProfileScreen);
const ProgramDetailWithNav = withTabs(ProgramDetailScreen);
const ApplicationConfirmWithNav = withTabs(ApplicationConfirmScreen);
const ApplicationDetailWithNav = withTabs(ApplicationDetailScreen);
const MyApplicationsWithNav = withTabs(MyApplicationsScreen);
const MyAssignmentsWithNav = withTabs(MyAssignmentsScreen);
const AssignmentDetailWithNav = withTabs(AssignmentDetailScreen);
const FormScreenWithNav = withTabs(FormScreen);
const RewardsWithNav = withTabs(RewardsScreen);
const MyRedemptionsWithNav = withTabs(MyRedemptionsScreen);
const AuPairDashboardWithNav = withTabs(AuPairDashboardScreen);
const AuPairDocumentsWithNav = withTabs(AuPairDocumentsScreen);
const PaymentsWithNav = withTabs(PaymentsScreen);
const AuPairEnglishTestWithNav = withTabs(AuPairEnglishTestScreen);
const AuPairVisaWithNav = withTabs(AuPairVisaScreen);
const AuPairMatchesWithNav = withTabs(AuPairMatchesScreen);
const AuPairSupportWithNav = withTabs(AuPairSupportScreen);
const AuPairResourcesWithNav = withTabs(AuPairResourcesScreen);
const NotificationsWithNav = withTabs(NotificationsScreen);

// Sin BottomTabBar (vista detalle "modal-like")
const NoTabsContainer: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <View style={styles.container}>
    {children}
    <WhatsAppButton />
  </View>
);

const RewardDetailNoTabs = (props: any) => (
  <NoTabsContainer><RewardDetailScreen {...props} /></NoTabsContainer>
);
const PointsHistoryNoTabs = (props: any) => (
  <NoTabsContainer><PointsHistoryScreen {...props} /></NoTabsContainer>
);

const MainNavigator: React.FC = () => {
  // Si el visitante llegó desde "Postular Au Pair" y completó login/register,
  // arrancamos en AuPairOnboarding (se le crea la Application). Sino, dashboard.
  const { authRequest } = usePublicAuth();
  const initial = authRequest?.redirectTo === 'AuPairOnboarding' && authRequest?.programId
    ? 'AuPairOnboarding'
    : 'AuPairDashboard';

  return (
  <NavigationProvider>
    <Stack.Navigator initialRouteName={initial as any} screenOptions={{ headerShown: false }}>
      <Stack.Screen name="AuPairDashboard" component={AuPairDashboardWithNav} />
      <Stack.Screen name="AuPairDocuments" component={AuPairDocumentsWithNav} />
      <Stack.Screen name="AuPairDocumentUpload" component={AuPairDocumentUploadScreen} />
      <Stack.Screen name="Payments" component={PaymentsWithNav} />
      <Stack.Screen name="PaymentDetail" component={PaymentDetailScreen} />
      <Stack.Screen name="PaymentRegister" component={PaymentRegisterScreen} />
      <Stack.Screen name="AuPairEnglishTest" component={AuPairEnglishTestWithNav} />
      <Stack.Screen name="AuPairVisa" component={AuPairVisaWithNav} />
      <Stack.Screen name="AuPairMatches" component={AuPairMatchesWithNav} />
      <Stack.Screen name="AuPairMatchDetail" component={AuPairMatchDetailScreen} />
      <Stack.Screen name="AuPairSupport" component={AuPairSupportWithNav} />
      <Stack.Screen name="AuPairResources" component={AuPairResourcesWithNav} />
      <Stack.Screen name="Home" component={HomeWithNav} />
      <Stack.Screen name="Programs" component={ProgramsWithNav} />
      <Stack.Screen name="ProgramDetail" component={ProgramDetailWithNav} />
      <Stack.Screen name="ApplicationConfirm" component={ApplicationConfirmWithNav} />
      <Stack.Screen name="ApplicationDetail" component={ApplicationDetailWithNav} />
      <Stack.Screen name="MyApplications" component={MyApplicationsWithNav} />
      <Stack.Screen name="MyAssignments" component={MyAssignmentsWithNav} />
      <Stack.Screen name="AssignmentDetail" component={AssignmentDetailWithNav} />
      <Stack.Screen name="FormScreen" component={FormScreenWithNav} />
      <Stack.Screen name="Rewards" component={RewardsWithNav} />
      <Stack.Screen name="RewardDetail" component={RewardDetailNoTabs} />
      <Stack.Screen name="MyRedemptions" component={MyRedemptionsWithNav} />
      <Stack.Screen name="PointsHistory" component={PointsHistoryNoTabs} />
      <Stack.Screen name="Profile" component={ProfileWithNav} />
      <Stack.Screen
        name="AuPairOnboarding"
        component={AuPairOnboardingScreen}
        initialParams={authRequest?.programId ? { programId: authRequest.programId } : undefined}
      />
      <Stack.Screen name="Notifications" component={NotificationsWithNav} />
    </Stack.Navigator>
  </NavigationProvider>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, position: 'relative' },
});

export default MainNavigator;

import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { PublicAuthProvider, usePublicAuth } from '../contexts/PublicAuthContext';
import PublicNavigator from './PublicNavigator';
import AuthNavigator from './AuthNavigator';
import MainNavigator from './MainNavigator';

/**
 * Tipos legacy que algunas pantallas existentes importan. Mantenidos como
 * union de los tres stacks para no romper imports actuales.
 * TODO (Sprint 6): migrar imports a {Public,Auth,Main}StackParamList.
 */
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
  AuPairDashboard: undefined;
  AuPairDocuments: { stage?: string } | undefined;
  AuPairDocumentUpload: { entry: any };
  Payments: undefined;
  PaymentDetail: { paymentId: number };
  PaymentRegister: { applicationId: number };
  AuPairEnglishTest: undefined;
  AuPairVisa: undefined;
  AuPairMatches: undefined;
  AuPairMatchDetail: { id: number };
  AuPairSupport: undefined;
  AuPairResources: undefined;
  AuPairOnboarding: { programId?: number } | undefined;
  Notifications: undefined;
};

/**
 * Selector raíz: decide qué stack montar según estado de autenticación
 * y si una pantalla pública pidió mostrar el flujo de login/registro.
 */
const RootSelector: React.FC = () => {
  const { isAuthenticated } = useAuth();
  const { authRequest } = usePublicAuth();

  if (isAuthenticated) return <MainNavigator />;
  if (authRequest !== null) return <AuthNavigator />;
  return <PublicNavigator />;
};

const AppNavigator: React.FC = () => (
  <PublicAuthProvider>
    <RootSelector />
  </PublicAuthProvider>
);

export default AppNavigator;

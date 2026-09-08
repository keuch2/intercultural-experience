import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { PublicAuthProvider, usePublicAuth } from '../contexts/PublicAuthContext';
import PublicNavigator from './PublicNavigator';
import AuthNavigator from './AuthNavigator';
import MainNavigator from './MainNavigator';
import { ProgramProvider } from '../contexts/ProgramContext';

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
  SetupPassword: { email: string; name?: string };
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
  // Motor de programas (Work & Travel y programas configurables)
  ProgramDashboard: undefined;
  ProgramDocuments: { group?: string } | undefined;
  ProgramDocumentUpload: { entry: any };
  ProgramEnglishTest: undefined;
  ProgramVisa: undefined;
  ProgramSupport: undefined;
  ProgramResources: undefined;
  ProgramOnboarding: { programId?: number } | undefined;
  JobPool: undefined;
  JobPlacement: undefined;
  // Catálogo (también dentro del stack autenticado)
  PublicPrograms: undefined;
  PublicProgramDetail: { id: number };
};

/**
 * Selector raíz: decide qué stack montar según estado de autenticación
 * y si una pantalla pública pidió mostrar el flujo de login/registro.
 */
const RootSelector: React.FC = () => {
  const { isAuthenticated } = useAuth();
  const { authRequest, exploring } = usePublicAuth();

  if (isAuthenticated) return <MainNavigator />;
  // Sin sesión la app abre en Login/Registro; el catálogo público solo se muestra
  // si el usuario tocó "Explorar programas" y no pidió volver a autenticarse.
  if (exploring && authRequest === null) return <PublicNavigator />;
  return <AuthNavigator />;
};

const AppNavigator: React.FC = () => (
  <PublicAuthProvider>
    <ProgramProvider>
      <RootSelector />
    </ProgramProvider>
  </PublicAuthProvider>
);

export default AppNavigator;

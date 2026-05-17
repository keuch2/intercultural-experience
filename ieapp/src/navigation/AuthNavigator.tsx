import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import LoginScreen from '../screens/Auth/LoginScreen';
import RegisterScreen from '../screens/Auth/RegisterScreen';
import ForgotPasswordScreen from '../screens/Auth/ForgotPasswordScreen';
import ResetPasswordScreen from '../screens/Auth/ResetPasswordScreen';
import ApiTestScreen from '../screens/ApiTestScreen';

/**
 * Stack de autenticación. Puede llegar acá desde PublicNavigator
 * (visitante hace tap en "Postular" o "Iniciar sesión") o como pantalla
 * inicial si el usuario no ha visitado el flujo público.
 *
 * Tras login/registro exitoso, el AuthContext flipea `isAuthenticated`
 * y AppNavigator monta MainNavigator automáticamente. Si había un
 * `authRequest.redirectTo`, MainNavigator lo respeta como pantalla inicial.
 */
export type AuthStackParamList = {
  Login: { programId?: number } | undefined;
  Register: { programId?: number } | undefined;
  ForgotPassword: undefined;
  ResetPassword: { token: string };
  ApiTest: undefined;
};

const Stack = createNativeStackNavigator<AuthStackParamList>();

const AuthNavigator: React.FC = () => (
  <Stack.Navigator initialRouteName="Login" screenOptions={{ headerShown: false }}>
    <Stack.Screen name="Login" component={LoginScreen} />
    <Stack.Screen name="Register" component={RegisterScreen} />
    <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
    <Stack.Screen name="ResetPassword" component={ResetPasswordScreen} />
    <Stack.Screen name="ApiTest" component={ApiTestScreen} />
  </Stack.Navigator>
);

export default AuthNavigator;

import React from 'react';
import { SafeAreaView as ContextSafeAreaView, SafeAreaViewProps, Edge } from 'react-native-safe-area-context';

/**
 * SafeAreaView de la app. El de 'react-native' no aplica insets en Android y
 * Expo 54 dibuja de borde a borde, por lo que el contenido quedaba bajo la barra
 * de estado. Por defecto solo insets superior y laterales: las pantallas con
 * BottomTabBar no deben sumar inset inferior (lo maneja la barra). Pasar
 * `edges={['top','bottom','left','right']}` en pantallas sin tab bar con pie fijo.
 */
const DEFAULT_EDGES: Edge[] = ['top', 'left', 'right'];

export const SafeAreaView: React.FC<SafeAreaViewProps> = ({ edges = DEFAULT_EDGES, ...props }) => (
  <ContextSafeAreaView edges={edges} {...props} />
);

export const FULL_EDGES: Edge[] = ['top', 'bottom', 'left', 'right'];

export default SafeAreaView;

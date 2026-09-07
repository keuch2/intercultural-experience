/**
 * Registro de flujos de la app. Decide qué pantallas usa cada tipo de programa:
 *  - aupair: pantallas específicas Au Pair (intactas).
 *  - engine: pantallas genéricas del motor (Work & Travel y programas configurables).
 * Las pantallas de módulos específicos (Pool de ofertas, Placement) las nombra el
 * backend en `mobile_screen` / `modules[*].screen` y la app navega "a ciegas".
 */
export type ProgramFlow = 'aupair' | 'engine' | 'none';

export interface FlowScreens {
  home: string;
  documents: string;
  onboarding: string;
  payments: string;
}

export const FLOW_SCREENS: Record<ProgramFlow, FlowScreens> = {
  aupair: { home: 'AuPairDashboard', documents: 'AuPairDocuments', onboarding: 'AuPairOnboarding', payments: 'Payments' },
  engine: { home: 'ProgramDashboard', documents: 'ProgramDocuments', onboarding: 'ProgramOnboarding', payments: 'Payments' },
  // Sin postulación: el dashboard genérico muestra el estado vacío (elegir programa).
  none: { home: 'ProgramDashboard', documents: 'ProgramDocuments', onboarding: 'ProgramOnboarding', payments: 'Payments' },
};

/** Pantalla móvil por módulo del catálogo (coincide con ModuleCatalog en el backend). */
export const MODULE_SCREENS: Record<string, string> = {
  english_test: 'ProgramEnglishTest',
  visa: 'ProgramVisa',
  job_pool: 'JobPool',
  placement: 'JobPlacement',
  support: 'ProgramSupport',
  resources: 'ProgramResources',
};

export const MODULE_ICONS: Record<string, string> = {
  english_test: 'school-outline',
  visa: 'airplane-outline',
  job_pool: 'briefcase-outline',
  placement: 'business-outline',
  support: 'headset-outline',
  resources: 'folder-open-outline',
};

export const screensFor = (flow: ProgramFlow): FlowScreens => FLOW_SCREENS[flow] ?? FLOW_SCREENS.none;

/** Onboarding según el programa público elegido (motor → genérico; Au Pair → propio). */
export const onboardingScreenFor = (program: { engine_enabled?: boolean; subcategory?: string | null }): string =>
  program.engine_enabled ? 'ProgramOnboarding' : 'AuPairOnboarding';

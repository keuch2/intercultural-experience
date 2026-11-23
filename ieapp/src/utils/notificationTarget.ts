import { ProgramFlow, screensFor } from '../navigation/programFlowRegistry';

/** Datos de un aviso que dicen a dónde lleva (ver NotificationCatalog / NotificationCampaign en el backend). */
export interface NotificationLink {
  target?: string;
  program_id?: number;
  url?: string;
}

export type NotificationDestination =
  | { kind: 'screen'; name: string; params?: Record<string, any> }
  | { kind: 'url'; url: string }
  | null;

const byTarget = (flow: ProgramFlow, target: string): string | null => {
  switch (target) {
    case 'process': return flow === 'none' ? null : screensFor(flow).home;
    case 'documents': return flow === 'none' ? null : screensFor(flow).documents;
    case 'payments': return 'Payments';
    case 'visa': return flow === 'aupair' ? 'AuPairVisa' : flow === 'engine' ? 'ProgramVisa' : null;
    case 'support': return flow === 'aupair' ? 'AuPairSupport' : flow === 'engine' ? 'ProgramSupport' : null;
    case 'job_pool': return flow === 'engine' ? 'JobPool' : null;
    case 'personal_info': return 'PersonalInfo';
    case 'notifications': return 'Notifications';
    default: return null;
  }
};

/** Pantalla por categoría del aviso (avisos del sistema sin destino explícito). */
const byCategory: Record<string, string> = {
  payment: 'payments',
  documents: 'documents',
  visa: 'visa',
  support: 'support',
  job_pool: 'job_pool',
  // Solicitud aprobada, avance de etapa y fechas → su proceso. Una rechazada no tiene pantalla.
  program_stage: 'process',
  program_dates: 'process',
  application: 'process',
};

/** A dónde lleva un aviso: primero su destino explícito (avisos especiales), si no, su categoría. */
export const resolveNotificationDestination = (
  flow: ProgramFlow,
  category?: string | null,
  data?: NotificationLink | null,
): NotificationDestination => {
  if (data?.target === 'url' && data.url) return { kind: 'url', url: data.url };
  if (data?.target === 'program' && data.program_id) return { kind: 'screen', name: 'PublicProgramDetail', params: { id: Number(data.program_id) } };
  const target = data?.target ?? (category ? byCategory[category] : undefined);
  const name = target ? byTarget(flow, target) : null;
  return name ? { kind: 'screen', name } : null;
};

/** Avisos que cambian el estado del proceso: hay que recargarlo antes de abrir la pantalla. */
export const refreshesProcess = (category?: string | null): boolean =>
  ['application', 'program_stage', 'program_dates'].includes(category ?? '');

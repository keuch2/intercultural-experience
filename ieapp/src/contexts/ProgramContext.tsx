import React, { createContext, useCallback, useContext, useEffect, useMemo, useState, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useAuth } from './AuthContext';
import { programEngineService, auPairService, programService } from '../services/api';
import { ProgramEnvelope } from '../types/programEngine';
import { AuPairProcess } from '../types/aupair';
import { UserApplication } from '../types/applications';
import { ProgramFlow } from '../navigation/programFlowRegistry';

const SELECTED_KEY = 'selected_application_id';
const TERMINAL_STATUSES = ['rejected', 'cancelled', 'withdrawn', 'completed'];

/** Flujo que corresponde a una postulación según su programa. */
export const flowForApplication = (app?: UserApplication | null): ProgramFlow => {
  if (!app?.program) return 'none';
  if (app.program.engine_enabled) return 'engine';
  if (app.program.subcategory === 'Au Pair') return 'aupair';
  return 'none';
};

export const isApplicationActive = (app: UserApplication): boolean =>
  !TERMINAL_STATUSES.includes(app.status) && !app.completed_at;

interface ProgramCtx {
  flow: ProgramFlow;
  loading: boolean;
  applications: UserApplication[];
  selectedApplication: UserApplication | null;
  envelope: ProgramEnvelope | null;
  auPairProcess: AuPairProcess | null;
  slug: string | null;
  applicationId: number | null;
  refresh: () => Promise<void>;
  selectApplication: (id: number) => Promise<void>;
  setEnvelope: (env: ProgramEnvelope | null) => void;
}

const Ctx = createContext<ProgramCtx | undefined>(undefined);

/**
 * Estado del participante: sus postulaciones (actuales y anteriores), cuál está
 * seleccionada como "Mi proceso" y el envelope/proceso de esa postulación.
 * Selección: la guardada por el usuario si sigue existiendo; si no, la postulación
 * activa más reciente; si no hay activas, la más reciente.
 */
export const ProgramProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const { isAuthenticated } = useAuth();
  const [applications, setApplications] = useState<UserApplication[]>([]);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [flow, setFlow] = useState<ProgramFlow>('none');
  const [envelope, setEnvelope] = useState<ProgramEnvelope | null>(null);
  const [auPairProcess, setAuPairProcess] = useState<AuPairProcess | null>(null);
  const [loading, setLoading] = useState(true);

  const loadProcessFor = useCallback(async (app: UserApplication | null) => {
    const f = flowForApplication(app);
    if (f === 'engine' && app?.program?.slug) {
      const env = await programEngineService.getProcess(app.program.slug);
      setEnvelope(env); setAuPairProcess(null); setFlow(env ? 'engine' : 'none');
      return;
    }
    if (f === 'aupair') {
      const proc = await auPairService.getProcess();
      setAuPairProcess(proc); setEnvelope(null); setFlow(proc ? 'aupair' : 'none');
      return;
    }
    setEnvelope(null); setAuPairProcess(null); setFlow('none');
  }, []);

  const pickSelected = (apps: UserApplication[], storedId: number | null): UserApplication | null => {
    if (apps.length === 0) return null;
    const stored = storedId ? apps.find(a => a.id === storedId) : undefined;
    if (stored) return stored;
    const sorted = [...apps].sort((a, b) => (b.applied_at || b.created_at || '').localeCompare(a.applied_at || a.created_at || ''));
    return sorted.find(isApplicationActive) ?? sorted[0];
  };

  const refresh = useCallback(async () => {
    if (!isAuthenticated) {
      setApplications([]); setSelectedId(null); setFlow('none'); setEnvelope(null); setAuPairProcess(null); setLoading(false);
      return;
    }
    setLoading(true);
    try {
      const res = await programService.getUserApplications();
      const apps: UserApplication[] = (res?.data?.data ?? res?.data ?? []) as UserApplication[];
      setApplications(apps);
      let storedId: number | null = null;
      try { const raw = await AsyncStorage.getItem(SELECTED_KEY); storedId = raw ? Number(raw) : null; } catch {}
      const chosen = pickSelected(apps, storedId);
      setSelectedId(chosen?.id ?? null);
      await loadProcessFor(chosen);
    } catch {
      // Sin red u otro error: se conserva el estado resuelto previamente.
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated, loadProcessFor]);

  const selectApplication = useCallback(async (id: number) => {
    const app = applications.find(a => a.id === id);
    if (!app) return;
    setSelectedId(id);
    try { await AsyncStorage.setItem(SELECTED_KEY, String(id)); } catch {}
    setLoading(true);
    try { await loadProcessFor(app); } finally { setLoading(false); }
  }, [applications, loadProcessFor]);

  useEffect(() => { refresh(); }, [refresh]);

  const selectedApplication = useMemo(() => applications.find(a => a.id === selectedId) ?? null, [applications, selectedId]);

  const value = useMemo<ProgramCtx>(() => ({
    flow,
    loading,
    applications,
    selectedApplication,
    envelope,
    auPairProcess,
    slug: envelope?.program?.slug ?? selectedApplication?.program?.slug ?? null,
    applicationId: envelope?.application_id ?? auPairProcess?.application_id ?? selectedApplication?.id ?? null,
    refresh,
    selectApplication,
    setEnvelope,
  }), [flow, loading, applications, selectedApplication, envelope, auPairProcess, refresh, selectApplication]);

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
};

export const useProgram = (): ProgramCtx => {
  const v = useContext(Ctx);
  if (!v) throw new Error('useProgram must be used within ProgramProvider');
  return v;
};

/** Variante tolerante para componentes que pueden montarse fuera del provider. */
export const useOptionalProgram = (): ProgramCtx | undefined => useContext(Ctx);

import React, { createContext, useCallback, useContext, useEffect, useMemo, useState, ReactNode } from 'react';
import { useAuth } from './AuthContext';
import { programEngineService, auPairService } from '../services/api';
import { ProgramEnvelope } from '../types/programEngine';
import { ProgramFlow } from '../navigation/programFlowRegistry';

/**
 * Resuelve el flujo del usuario autenticado:
 *  1. GET /me/process → programa del motor (Work & Travel, etc.) → flow 'engine'
 *  2. Si no hay, GET /au-pair/process → flow 'aupair'
 *  3. Si no hay ninguno → 'none'
 * Expone el envelope del motor (etapas, módulos, próxima acción) y el applicationId
 * para las pantallas compartidas (Pagos).
 */
interface ProgramCtx {
  flow: ProgramFlow;
  loading: boolean;
  envelope: ProgramEnvelope | null;
  slug: string | null;
  applicationId: number | null;
  refresh: () => Promise<void>;
  setEnvelope: (env: ProgramEnvelope | null) => void;
}

const Ctx = createContext<ProgramCtx | undefined>(undefined);

export const ProgramProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const { isAuthenticated } = useAuth();
  const [flow, setFlow] = useState<ProgramFlow>('none');
  const [envelope, setEnvelope] = useState<ProgramEnvelope | null>(null);
  const [auPairApplicationId, setAuPairApplicationId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    if (!isAuthenticated) {
      setFlow('none'); setEnvelope(null); setAuPairApplicationId(null); setLoading(false);
      return;
    }
    setLoading(true);
    try {
      const env = await programEngineService.getMyProcess();
      if (env) {
        setEnvelope(env); setFlow('engine'); setAuPairApplicationId(null);
        return;
      }
      const auPair = await auPairService.getProcess();
      if (auPair) {
        setEnvelope(null); setFlow('aupair'); setAuPairApplicationId(auPair.application_id);
        return;
      }
      setEnvelope(null); setFlow('none'); setAuPairApplicationId(null);
    } catch {
      // Sin red u otro error: no cambiamos el flujo ya resuelto.
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated]);

  useEffect(() => { refresh(); }, [refresh]);

  const value = useMemo<ProgramCtx>(() => ({
    flow,
    loading,
    envelope,
    slug: envelope?.program?.slug ?? null,
    applicationId: envelope?.application_id ?? auPairApplicationId,
    refresh,
    setEnvelope,
  }), [flow, loading, envelope, auPairApplicationId, refresh]);

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
};

export const useProgram = (): ProgramCtx => {
  const v = useContext(Ctx);
  if (!v) throw new Error('useProgram must be used within ProgramProvider');
  return v;
};

/** Variante tolerante para componentes que pueden montarse fuera del provider. */
export const useOptionalProgram = (): ProgramCtx | undefined => useContext(Ctx);

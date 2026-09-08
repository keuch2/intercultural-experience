import React, { createContext, useCallback, useContext, useMemo, useState, ReactNode } from 'react';

/**
 * Coordina el arranque de la app sin sesión:
 *  - Por defecto la app abre en el AuthStack (Login / Registro).
 *  - `startExploring()` muestra el catálogo público (enlace "Explorar programas" del Login).
 *  - `requestAuth(params)` vuelve al AuthStack guardando la intención (programa a postular)
 *    para retomarla tras iniciar sesión (MainNavigator lee `authRequest`).
 */
export type AuthRequest = {
  redirectTo?: string;
  programId?: number;
} | null;

interface PublicAuthCtx {
  authRequest: AuthRequest;
  exploring: boolean;
  requestAuth: (params?: { redirectTo?: string; programId?: number }) => void;
  clearAuthRequest: () => void;
  startExploring: () => void;
  stopExploring: () => void;
}

const Ctx = createContext<PublicAuthCtx | undefined>(undefined);

export const PublicAuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [authRequest, setAuthRequest] = useState<AuthRequest>(null);
  const [exploring, setExploring] = useState(false);

  const requestAuth = useCallback((params?: { redirectTo?: string; programId?: number }) => {
    setAuthRequest(params ?? {});
    setExploring(false);
  }, []);

  const clearAuthRequest = useCallback(() => setAuthRequest(null), []);
  const startExploring = useCallback(() => { setAuthRequest(null); setExploring(true); }, []);
  const stopExploring = useCallback(() => setExploring(false), []);

  const value = useMemo<PublicAuthCtx>(() => ({
    authRequest, exploring, requestAuth, clearAuthRequest, startExploring, stopExploring,
  }), [authRequest, exploring, requestAuth, clearAuthRequest, startExploring, stopExploring]);

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
};

export const usePublicAuth = (): PublicAuthCtx => {
  const v = useContext(Ctx);
  if (!v) throw new Error('usePublicAuth must be used within PublicAuthProvider');
  return v;
};

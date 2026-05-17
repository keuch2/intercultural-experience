import React, { createContext, useCallback, useContext, useMemo, useState, ReactNode } from 'react';

/**
 * Contexto liviano para permitir que pantallas del PublicStack
 * disparen el cambio al AuthStack (login/registro) sin acoplarse al AppNavigator.
 *
 * AppNavigator escucha `authRequest` y, cuando cambia, monta AuthNavigator
 * en lugar de PublicNavigator. Los params (programId, redirectTo) se guardan
 * para retomar el flujo tras autenticarse.
 */
export type AuthRequest = {
  redirectTo?: string;
  programId?: number;
} | null;

interface PublicAuthCtx {
  authRequest: AuthRequest;
  requestAuth: (params?: { redirectTo?: string; programId?: number }) => void;
  clearAuthRequest: () => void;
}

const Ctx = createContext<PublicAuthCtx | undefined>(undefined);

export const PublicAuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [authRequest, setAuthRequest] = useState<AuthRequest>(null);

  const requestAuth = useCallback((params?: { redirectTo?: string; programId?: number }) => {
    setAuthRequest(params ?? {});
  }, []);

  const clearAuthRequest = useCallback(() => setAuthRequest(null), []);

  const value = useMemo<PublicAuthCtx>(() => ({
    authRequest,
    requestAuth,
    clearAuthRequest,
  }), [authRequest, requestAuth, clearAuthRequest]);

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
};

export const usePublicAuth = (): PublicAuthCtx => {
  const v = useContext(Ctx);
  if (!v) throw new Error('usePublicAuth must be used within PublicAuthProvider');
  return v;
};

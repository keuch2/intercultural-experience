import React, { createContext, useState, useContext, ReactNode, useCallback, useRef, useEffect } from 'react';
import { AppState } from 'react-native';
import { TabType } from '../components/BottomTabBar';
import { notificationService } from '../services/api';
import { useAuth } from './AuthContext';

interface NavigationContextType {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
  /** Avisos no leídos (badge de la pestaña Avisos) */
  unreadCount: number;
  /** Refresca el contador; con throttle de 30 s salvo force */
  refreshUnread: (force?: boolean) => Promise<void>;
  setUnreadCount: (n: number) => void;
}

const NavigationContext = createContext<NavigationContextType | undefined>(undefined);

export const NavigationProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [activeTab, setActiveTab] = useState<TabType>('home');
  const [unreadCount, setUnreadCount] = useState(0);
  const { isAuthenticated } = useAuth();
  const lastFetch = useRef(0);
  const inFlight = useRef(false);

  const refreshUnread = useCallback(async (force = false) => {
    if (!isAuthenticated || inFlight.current) return;
    if (!force && Date.now() - lastFetch.current < 30000) return;
    inFlight.current = true;
    try {
      const n = await notificationService.getUnreadCount();
      setUnreadCount(Number(n) || 0);
      lastFetch.current = Date.now();
    } catch {
      // sin red: se conserva el último valor
    } finally {
      inFlight.current = false;
    }
  }, [isAuthenticated]);

  useEffect(() => {
    refreshUnread(true);
    const sub = AppState.addEventListener('change', state => { if (state === 'active') refreshUnread(true); });
    return () => sub.remove();
  }, [refreshUnread]);

  return (
    <NavigationContext.Provider value={{ activeTab, setActiveTab, unreadCount, refreshUnread, setUnreadCount }}>
      {children}
    </NavigationContext.Provider>
  );
};

export const useTabNavigation = (): NavigationContextType => {
  const context = useContext(NavigationContext);
  if (context === undefined) {
    throw new Error('useTabNavigation must be used within a NavigationProvider');
  }
  return context;
};

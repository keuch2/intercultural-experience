import React, { createContext, useState, useContext, ReactNode } from 'react';
import { TabType } from '../components/BottomTabBar';

interface NavigationContextType {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
}

const NavigationContext = createContext<NavigationContextType | undefined>(undefined);

export const NavigationProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [activeTab, setActiveTab] = useState<TabType>('home');

  return (
    <NavigationContext.Provider
      value={{
        activeTab,
        setActiveTab,
      }}
    >
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

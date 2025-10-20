import AsyncStorage from '@react-native-async-storage/async-storage';

export interface CacheEntry<T = any> {
  data: T;
  timestamp: number;
  expiresIn?: number; // milliseconds
}

export interface OfflineQueueItem {
  id: string;
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  url: string;
  data?: any;
  headers?: Record<string, string>;
  timestamp: number;
  retryCount: number;
  maxRetries: number;
}

const CACHE_PREFIX = 'CACHE_';
const QUEUE_PREFIX = 'QUEUE_';
const OFFLINE_QUEUE_KEY = 'offline_queue';

export class OfflineManager {
  /**
   * Cache data with optional expiration
   */
  static async cacheData<T>(key: string, data: T, expiresInMs?: number): Promise<void> {
    const entry: CacheEntry<T> = {
      data,
      timestamp: Date.now(),
      expiresIn: expiresInMs,
    };
    
    await AsyncStorage.setItem(`${CACHE_PREFIX}${key}`, JSON.stringify(entry));
  }

  /**
   * Get cached data if it exists and hasn't expired
   */
  static async getCachedData<T>(key: string): Promise<T | null> {
    try {
      const cached = await AsyncStorage.getItem(`${CACHE_PREFIX}${key}`);
      if (!cached) return null;

      const entry: CacheEntry<T> = JSON.parse(cached);
      
      // Check if expired
      if (entry.expiresIn && (Date.now() - entry.timestamp > entry.expiresIn)) {
        await AsyncStorage.removeItem(`${CACHE_PREFIX}${key}`);
        return null;
      }

      return entry.data;
    } catch (error) {
      console.error('Error getting cached data:', error);
      return null;
    }
  }

  /**
   * Clear specific cached data
   */
  static async clearCache(key: string): Promise<void> {
    await AsyncStorage.removeItem(`${CACHE_PREFIX}${key}`);
  }

  /**
   * Clear all cached data
   */
  static async clearAllCache(): Promise<void> {
    try {
      const keys = await AsyncStorage.getAllKeys();
      const cacheKeys = keys.filter(key => key.startsWith(CACHE_PREFIX));
      await AsyncStorage.multiRemove(cacheKeys);
    } catch (error) {
      console.error('Error clearing cache:', error);
    }
  }

  /**
   * Add request to offline queue
   */
  static async queueRequest(
    method: OfflineQueueItem['method'],
    url: string,
    data?: any,
    headers?: Record<string, string>,
    maxRetries: number = 3
  ): Promise<string> {
    const id = `${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    
    const queueItem: OfflineQueueItem = {
      id,
      method,
      url,
      data,
      headers,
      timestamp: Date.now(),
      retryCount: 0,
      maxRetries,
    };

    const queue = await this.getOfflineQueue();
    queue.push(queueItem);
    await this.saveOfflineQueue(queue);
    
    return id;
  }

  /**
   * Get all queued requests
   */
  static async getOfflineQueue(): Promise<OfflineQueueItem[]> {
    try {
      const queueData = await AsyncStorage.getItem(OFFLINE_QUEUE_KEY);
      return queueData ? JSON.parse(queueData) : [];
    } catch (error) {
      console.error('Error getting offline queue:', error);
      return [];
    }
  }

  /**
   * Save offline queue
   */
  private static async saveOfflineQueue(queue: OfflineQueueItem[]): Promise<void> {
    await AsyncStorage.setItem(OFFLINE_QUEUE_KEY, JSON.stringify(queue));
  }

  /**
   * Remove item from queue
   */
  static async removeFromQueue(id: string): Promise<void> {
    const queue = await this.getOfflineQueue();
    const filteredQueue = queue.filter(item => item.id !== id);
    await this.saveOfflineQueue(filteredQueue);
  }

  /**
   * Update retry count for queue item
   */
  static async updateRetryCount(id: string): Promise<void> {
    const queue = await this.getOfflineQueue();
    const item = queue.find(item => item.id === id);
    if (item) {
      item.retryCount += 1;
      await this.saveOfflineQueue(queue);
    }
  }

  /**
   * Clear entire offline queue
   */
  static async clearOfflineQueue(): Promise<void> {
    await AsyncStorage.removeItem(OFFLINE_QUEUE_KEY);
  }

  /**
   * Get queue items that should be retried
   */
  static async getRetryableItems(): Promise<OfflineQueueItem[]> {
    const queue = await this.getOfflineQueue();
    return queue.filter(item => item.retryCount < item.maxRetries);
  }

  /**
   * Get failed queue items (exceeded max retries)
   */
  static async getFailedItems(): Promise<OfflineQueueItem[]> {
    const queue = await this.getOfflineQueue();
    return queue.filter(item => item.retryCount >= item.maxRetries);
  }

  /**
   * Cache user profile data
   */
  static async cacheUserProfile(profile: any): Promise<void> {
    await this.cacheData('user_profile', profile, 5 * 60 * 1000); // 5 minutes
  }

  /**
   * Get cached user profile
   */
  static async getCachedUserProfile(): Promise<any | null> {
    return await this.getCachedData('user_profile');
  }

  /**
   * Cache programs list
   */
  static async cachePrograms(programs: any[]): Promise<void> {
    await this.cacheData('programs_list', programs, 10 * 60 * 1000); // 10 minutes
  }

  /**
   * Get cached programs
   */
  static async getCachedPrograms(): Promise<any[] | null> {
    return await this.getCachedData('programs_list');
  }

  /**
   * Cache applications list
   */
  static async cacheApplications(applications: any[]): Promise<void> {
    await this.cacheData('user_applications', applications, 5 * 60 * 1000); // 5 minutes
  }

  /**
   * Get cached applications
   */
  static async getCachedApplications(): Promise<any[] | null> {
    return await this.getCachedData('user_applications');
  }

  /**
   * Save form draft offline
   */
  static async saveFormDraft(formId: number, formData: any): Promise<void> {
    await this.cacheData(`form_draft_${formId}`, {
      formData,
      lastSaved: Date.now(),
    });
  }

  /**
   * Get form draft
   */
  static async getFormDraft(formId: number): Promise<any | null> {
    const draft = await this.getCachedData(`form_draft_${formId}`);
    return draft && typeof draft === 'object' && 'formData' in draft ? draft.formData : null;
  }

  /**
   * Clear form draft
   */
  static async clearFormDraft(formId: number): Promise<void> {
    await this.clearCache(`form_draft_${formId}`);
  }
}

/**
 * Hook for easy cache management
 */
export const useOfflineCache = () => {
  return {
    cacheData: OfflineManager.cacheData,
    getCachedData: OfflineManager.getCachedData,
    clearCache: OfflineManager.clearCache,
    queueRequest: OfflineManager.queueRequest,
    getOfflineQueue: OfflineManager.getOfflineQueue,
    clearOfflineQueue: OfflineManager.clearOfflineQueue,
  };
};

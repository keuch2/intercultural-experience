import apiClient from './apiClient';

/** Ciudad del catálogo (por ahora solo Paraguay). */
export interface City {
  id: number;
  name: string;
  department: string;
  /** "Luque — Central" (Asunción sin departamento). */
  label: string;
}

let cache: City[] | null = null;

/** Catálogo público (GET /cities). Se pide una vez por sesión de la app. */
export const cityService = {
  async list(): Promise<City[]> {
    if (cache) return cache;
    const response = await apiClient.get('/cities');
    cache = (response.data?.data ?? []) as City[];
    return cache;
  },
};

export default cityService;

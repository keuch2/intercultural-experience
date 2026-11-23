import apiClient from './apiClient';

/** Banner del Inicio de la app, gestionado en el admin (GET /public/banners y /banners). */
export interface AppBanner {
  id: number;
  type: 'image' | 'text';
  title: string | null;
  subtitle: string | null;
  badge: string | null;
  image_url: string | null;
  background_color: string;
  button_label: string | null;
  link: { type: 'program'; program_id: number } | { type: 'url'; url: string } | null;
}

export const bannerService = {
  /** Inicio sin sesión. */
  async publicList(): Promise<AppBanner[]> {
    const response = await apiClient.get('/public/banners');
    return (response.data?.data ?? []) as AppBanner[];
  },
  /** Inicio del participante (filtrado por sus postulaciones). */
  async list(): Promise<AppBanner[]> {
    const response = await apiClient.get('/banners');
    return (response.data?.data ?? []) as AppBanner[];
  },
};

export default bannerService;

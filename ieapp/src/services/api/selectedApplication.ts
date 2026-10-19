/**
 * Postulación elegida como "Mi proceso". apiClient la envía como X-Application-Id
 * y el backend resuelve el proceso de esa postulación (no siempre la más reciente),
 * para poder ver una participación anterior tras volver a postular.
 */
let selectedApplicationId: number | null = null;

export const setSelectedApplicationId = (id: number | null): void => {
  selectedApplicationId = id;
};

export const getSelectedApplicationId = (): number | null => selectedApplicationId;

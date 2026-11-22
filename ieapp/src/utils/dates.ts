// Fechas de nacimiento: se editan como DD/MM/AAAA y se guardan como AAAA-MM-DD.
// Se toman las partes del texto (sin new Date(string)) para no correr un día por la zona horaria.

export const isoToDisplayDate = (value?: string | null): string => {
  const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(value || '');
  return m ? `${m[3]}/${m[2]}/${m[1]}` : '';
};

/** DD/MM/AAAA → AAAA-MM-DD, o null si la fecha no existe o es futura. */
export const displayToIsoDate = (value: string): string | null => {
  const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value.trim());
  if (!m) return null;
  const [, dd, mm, yyyy] = m;
  const date = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
  const valid = date.getFullYear() === Number(yyyy) && date.getMonth() === Number(mm) - 1
    && date.getDate() === Number(dd) && date < new Date() && Number(yyyy) > 1900;
  return valid ? `${yyyy}-${mm}-${dd}` : null;
};

/** Inserta las barras mientras se escribe: 13061971 → 13/06/1971 */
export const maskDate = (value: string): string => {
  const digits = value.replace(/\D/g, '').slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
};

/** Edad en años desde AAAA-MM-DD. */
export const ageFromIso = (value?: string | null): number | null => {
  const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(value || '');
  if (!m) return null;
  const today = new Date();
  const [y, mo, d] = [Number(m[1]), Number(m[2]), Number(m[3])];
  const hadBirthday = today.getMonth() + 1 > mo || (today.getMonth() + 1 === mo && today.getDate() >= d);
  const age = today.getFullYear() - y - (hadBirthday ? 0 : 1);
  return age >= 0 ? age : null;
};

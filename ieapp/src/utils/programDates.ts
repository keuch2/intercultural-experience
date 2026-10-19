/**
 * Texto para la fecha de inicio del programa en los dashboards:
 * "Inicio del programa: 01/06/2026 · faltan 84 días" o "comenzó hace 12 días".
 * Pasada la fecha de fin (si está cargada): "Finalizó el 16/10/2026".
 */
export interface ProgramStartInfo {
  label: string;
  daysLeft: number;
  dateLabel: string;
}

const pad = (n: number) => String(n).padStart(2, '0');

export const formatDateEs = (iso: string): string => {
  const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
  return y && m && d ? `${pad(d)}/${pad(m)}/${y}` : iso;
};

const toUtc = (iso: string): number | null => {
  const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
  return y && m && d ? Date.UTC(y, m - 1, d) : null;
};

export const describeProgramStart = (
  iso: string | null | undefined,
  endIso?: string | null,
  today: Date = new Date(),
): ProgramStartInfo | null => {
  if (!iso) return null;
  const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
  if (!y || !m || !d) return null;
  const start = Date.UTC(y, m - 1, d);
  const now = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());
  const daysLeft = Math.round((start - now) / 86400000);
  const dateLabel = formatDateEs(iso);
  const end = endIso ? toUtc(endIso) : null;
  if (end !== null && end < now) {
    return { label: `Finalizó el ${formatDateEs(endIso!)}`, daysLeft, dateLabel };
  }
  let when: string;
  if (daysLeft > 1) when = `faltan ${daysLeft} días`;
  else if (daysLeft === 1) when = 'es mañana';
  else if (daysLeft === 0) when = 'es hoy';
  else if (daysLeft === -1) when = 'comenzó ayer';
  else when = `comenzó hace ${-daysLeft} días`;
  return { label: `Inicio del programa: ${dateLabel} · ${when}`, daysLeft, dateLabel };
};

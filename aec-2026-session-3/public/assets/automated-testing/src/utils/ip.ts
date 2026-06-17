/**
 * Extracts and normalises the client IP from standard proxy headers.
 * Returns null if no IP can be determined (e.g. direct connection without proxy).
 *
 * For local development, set the OVERRIDE_IP env var instead of relying on headers.
 */
export function getRequestIp(headers: Headers): string | null {
  const forwarded = headers.get("x-forwarded-for");
  if (!forwarded) return null;

  const ip = forwarded.split(",")[0].trim();

  // Normalise IPv4-mapped IPv6 addresses (e.g. ::ffff:192.168.1.1)
  return ip.startsWith("::ffff:") ? ip.slice(7) : ip;
}

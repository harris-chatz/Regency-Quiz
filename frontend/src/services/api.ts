/**
 * Tiny fetch wrapper for the Laravel API.
 *
 * Base URL precedence:
 *   1. VITE_API_BASE_URL (from .env or docker-compose env)
 *   2. http://localhost:8080/api (sensible local default)
 */

const BASE_URL: string =
  import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080/api'

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public payload?: unknown,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

export async function apiFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  const url = `${BASE_URL}${path.startsWith('/') ? path : '/' + path}`

  const res = await fetch(url, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(init.headers ?? {}),
    },
    credentials: 'include',
    ...init,
  })

  const isJson = res.headers.get('content-type')?.includes('application/json')
  const body = isJson ? await res.json() : await res.text()

  if (!res.ok) {
    const message =
      (isJson && typeof body === 'object' && body !== null && 'message' in body
        ? String((body as { message: unknown }).message)
        : null) ?? `Request failed with ${res.status}`
    throw new ApiError(message, res.status, body)
  }

  return body as T
}

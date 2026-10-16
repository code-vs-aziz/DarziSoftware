import { loadConfig } from './config'
import {
  countPending,
  ensureDeviceId,
  getMeta,
  pendingOps,
  setMeta,
  updateOp,
  upsertCustomers,
  upsertMeasurements,
  upsertOrders,
} from './db'

export type ConnStatus = 'checking' | 'online' | 'offline'

async function fetchJson(url: string, options: RequestInit = {}) {
  const res = await fetch(url, options)
  const json = await res.json().catch(() => ({}))
  if (!res.ok) {
    const err = new Error((json && (json.error || json.message)) || `http_${res.status}`)
    ;(err as Error & { status?: number }).status = res.status
    throw err
  }
  return json
}

export async function checkHealth(): Promise<ConnStatus> {
  if (!navigator.onLine) return 'offline'
  const { healthUrl } = loadConfig()
  try {
    const ctrl = new AbortController()
    const t = window.setTimeout(() => ctrl.abort(), 6000)
    const json = await fetchJson(healthUrl, {
      signal: ctrl.signal,
      cache: 'no-store',
      headers: { Accept: 'application/json' },
    })
    window.clearTimeout(t)
    return json?.ok ? 'online' : 'offline'
  } catch {
    return 'offline'
  }
}

async function authHeaders(token: string | null, deviceId: string) {
  const h: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Device-Id': deviceId,
  }
  if (token) h.Authorization = `Bearer ${token}`
  return h
}

/** Issue token requires classic ERP session cookie (open PHP login in same browser once). */
export async function issueToken() {
  const { apiBase } = loadConfig()
  const deviceId = await ensureDeviceId()
  const json = await fetchJson(`${apiBase}/issue_token`, {
    method: 'POST',
    credentials: 'include',
    headers: await authHeaders(null, deviceId),
    body: JSON.stringify({ device_id: deviceId, label: 'erp-spa' }),
  })
  if (!json.ok || !json.token) throw new Error(json.error || 'issue_token_failed')
  await setMeta('sync_token', json.token)
  await setMeta('sync_token_expires', json.expires_at || '')
  await setMeta('sync_user', json.user_name || '')
  return json
}

async function ensureAuth() {
  const token = await getMeta<string | null>('sync_token', null)
  const expires = await getMeta<string | null>('sync_token_expires', null)
  const deviceId = await ensureDeviceId()
  const valid =
    !!token && (!expires || new Date(expires).getTime() > Date.now() + 60_000)
  if (valid) return { token: token!, deviceId }
  const issued = await issueToken()
  return { token: issued.token as string, deviceId }
}

export async function syncNow() {
  const status = await checkHealth()
  if (status !== 'online') throw new Error('offline')

  const auth = await ensureAuth()
  const { apiBase } = loadConfig()
  const ops = await pendingOps()

  if (ops.length) {
    const rank: Record<string, number> = {
      customer: 1,
      measurement: 2,
      order: 3,
      cash: 4,
      delivery: 5,
      transfer: 6,
    }
    ops.sort((a, b) => {
      const ra = rank[a.entity_type] || 9
      const rb = rank[b.entity_type] || 9
      if (ra !== rb) return ra - rb
      return a.created_at.localeCompare(b.created_at)
    })
    const batch = ops.slice(0, 50).map((op) => ({
      op_id: op.op_id,
      entity_type: op.entity_type,
      entity_client_id: op.entity_client_id,
      op_type: op.op_type,
      payload: op.payload,
      idempotency_key: op.idempotency_key,
    }))
    const json = await fetchJson(`${apiBase}/push`, {
      method: 'POST',
      credentials: 'include',
      headers: await authHeaders(auth.token, auth.deviceId),
      body: JSON.stringify({ device_id: auth.deviceId, ops: batch }),
    })
    for (const res of json.results || []) {
      if (!res.op_id) continue
      if (res.ok || res.duplicate) {
        await updateOp(res.op_id, {
          status: 'synced',
          last_attempt_at: new Date().toISOString(),
          last_error: '',
        })
      } else if (res.conflict) {
        await updateOp(res.op_id, {
          status: 'conflict',
          last_attempt_at: new Date().toISOString(),
          last_error: 'conflict',
        })
      } else {
        await updateOp(res.op_id, {
          status: 'pending',
          retry_count: ((await pendingOps()).find((o) => o.op_id === res.op_id)?.retry_count || 0) + 1,
          last_attempt_at: new Date().toISOString(),
          last_error: res.error || 'push_failed',
        })
      }
    }
  }

  const pull = await fetchJson(`${apiBase}/bootstrap`, {
    method: 'GET',
    credentials: 'include',
    headers: await authHeaders(auth.token, auth.deviceId),
  })
  await upsertCustomers(pull.customers || [])
  await upsertMeasurements(pull.measurements || [])
  await upsertOrders(pull.orders || [])
  await setMeta('masters', pull.masters || {})
  await setMeta('last_sync_at', pull.server_time || new Date().toISOString())
  return { pending: await countPending(), at: new Date().toISOString() }
}

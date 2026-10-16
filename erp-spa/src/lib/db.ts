import { openDB, type DBSchema, type IDBPDatabase } from 'idb'

export type CustomerRow = {
  local_id: string
  client_uuid: string
  server_id: string
  man_napno: string
  name: string
  Phone: string
  city: string
  Address: string
  cnic: string
  remarks: string
  sync_status: string
  provisional_no?: string
  updated_at: string
}

export type OrderRow = {
  local_id: string
  client_uuid: string
  server_id: string
  provisional_no: string
  naap_no: string
  customer_name: string
  payload: Record<string, unknown>
  sync_status: string
  updated_at: string
}

export type QueueOp = {
  op_id: string
  entity_type: string
  entity_client_id: string
  op_type: string
  payload: Record<string, unknown>
  created_at: string
  status: 'pending' | 'synced' | 'conflict' | string
  retry_count: number
  last_attempt_at: string | null
  last_error: string
  idempotency_key: string
}

interface DarziSpaDb extends DBSchema {
  meta: { key: string; value: { key: string; value: unknown } }
  customers: { key: string; value: CustomerRow; indexes: { sync_status: string } }
  measurements: { key: string; value: Record<string, unknown> & { local_id: string } }
  orders: { key: string; value: OrderRow; indexes: { sync_status: string } }
  sync_queue: {
    key: string
    value: QueueOp
    indexes: { status: string; created_at: string }
  }
}

let dbPromise: Promise<IDBPDatabase<DarziSpaDb>> | null = null

export function uuid() {
  if (crypto.randomUUID) return crypto.randomUUID()
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0
    const v = c === 'x' ? r : (r & 0x3) | 0x8
    return v.toString(16)
  })
}

export function getDb() {
  if (!dbPromise) {
    dbPromise = openDB<DarziSpaDb>('darzi_spa_erp', 1, {
      upgrade(db) {
        if (!db.objectStoreNames.contains('meta')) {
          db.createObjectStore('meta', { keyPath: 'key' })
        }
        if (!db.objectStoreNames.contains('customers')) {
          const s = db.createObjectStore('customers', { keyPath: 'local_id' })
          s.createIndex('sync_status', 'sync_status')
        }
        if (!db.objectStoreNames.contains('measurements')) {
          db.createObjectStore('measurements', { keyPath: 'local_id' })
        }
        if (!db.objectStoreNames.contains('orders')) {
          const s = db.createObjectStore('orders', { keyPath: 'local_id' })
          s.createIndex('sync_status', 'sync_status')
        }
        if (!db.objectStoreNames.contains('sync_queue')) {
          const s = db.createObjectStore('sync_queue', { keyPath: 'op_id' })
          s.createIndex('status', 'status')
          s.createIndex('created_at', 'created_at')
        }
      },
    })
  }
  return dbPromise
}

export async function getMeta<T>(key: string, fallback: T): Promise<T> {
  const db = await getDb()
  const row = await db.get('meta', key)
  return row && 'value' in row ? (row.value as T) : fallback
}

export async function setMeta(key: string, value: unknown) {
  const db = await getDb()
  await db.put('meta', { key, value })
}

export async function ensureDeviceId() {
  const existing = await getMeta<string | null>('device_id', null)
  if (existing) return existing
  const id = uuid()
  await setMeta('device_id', id)
  return id
}

async function nextSeq(prefix: string) {
  const key = `seq_${prefix}`
  const n = (await getMeta<number>(key, 0)) + 1
  await setMeta(key, n)
  return `OFF-${prefix}-${String(n).padStart(4, '0')}`
}

export async function enqueue(op: Omit<QueueOp, 'op_id' | 'created_at' | 'status' | 'retry_count' | 'last_attempt_at' | 'last_error'> & Partial<QueueOp>) {
  const db = await getDb()
  const row: QueueOp = {
    op_id: op.op_id || uuid(),
    entity_type: op.entity_type,
    entity_client_id: op.entity_client_id || '',
    op_type: op.op_type,
    payload: op.payload || {},
    created_at: op.created_at || new Date().toISOString(),
    status: 'pending',
    retry_count: 0,
    last_attempt_at: null,
    last_error: '',
    idempotency_key: op.idempotency_key || uuid(),
  }
  await db.put('sync_queue', row)
  return row
}

export async function pendingOps() {
  const db = await getDb()
  const rows = await db.getAllFromIndex('sync_queue', 'status', 'pending')
  return rows.sort((a, b) => a.created_at.localeCompare(b.created_at))
}

export async function countPending() {
  return (await pendingOps()).length
}

export async function saveCustomer(input: {
  name: string
  Phone?: string
  city?: string
  Address?: string
  man_napno?: string
  cnic?: string
  remarks?: string
}) {
  const db = await getDb()
  const clientUuid = uuid()
  const provisional = await nextSeq('C')
  const record: CustomerRow = {
    local_id: clientUuid,
    client_uuid: clientUuid,
    server_id: '',
    man_napno: input.man_napno?.trim() || provisional,
    name: input.name,
    Phone: input.Phone || '',
    city: input.city || '',
    Address: input.Address || '',
    cnic: input.cnic || '',
    remarks: input.remarks || '',
    sync_status: 'pending',
    provisional_no: provisional,
    updated_at: new Date().toISOString(),
  }
  await db.put('customers', record)
  await enqueue({
    entity_type: 'customer',
    entity_client_id: clientUuid,
    op_type: 'create',
    payload: {
      name: record.name,
      city: record.city,
      Phone: record.Phone,
      Address: record.Address,
      cnic: record.cnic,
      man_napno: record.man_napno,
      remarks: record.remarks,
    },
    idempotency_key: `${clientUuid}:customer.create`,
  })
  return record
}

export async function saveOrder(payload: Record<string, unknown>, customerName = '') {
  const db = await getDb()
  const clientUuid = uuid()
  const provisional = await nextSeq('O')
  const record: OrderRow = {
    local_id: clientUuid,
    client_uuid: clientUuid,
    server_id: '',
    provisional_no: provisional,
    naap_no: String(payload.Naapno || payload.naap_no || ''),
    customer_name: customerName,
    payload,
    sync_status: 'pending',
    updated_at: new Date().toISOString(),
  }
  await db.put('orders', record)
  await enqueue({
    entity_type: 'order',
    entity_client_id: clientUuid,
    op_type: 'create',
    payload,
    idempotency_key: `${clientUuid}:order.create`,
  })
  return record
}

export async function saveCash(payload: Record<string, unknown>) {
  const clientUuid = uuid()
  const provisional = await nextSeq('CASH')
  await enqueue({
    entity_type: 'cash',
    entity_client_id: clientUuid,
    op_type: 'create',
    payload,
    idempotency_key: `${clientUuid}:cash.create`,
  })
  return { provisional_no: provisional, client_uuid: clientUuid }
}

export async function saveDelivery(payload: Record<string, unknown>) {
  const clientUuid = uuid()
  const provisional = await nextSeq('DEL')
  await enqueue({
    entity_type: 'delivery',
    entity_client_id: clientUuid,
    op_type: 'create',
    payload,
    idempotency_key: `${clientUuid}:delivery.create`,
  })
  return { provisional_no: provisional, client_uuid: clientUuid }
}

export async function saveTransfer(payload: Record<string, unknown>) {
  const clientUuid = uuid()
  const provisional = await nextSeq('TR')
  await enqueue({
    entity_type: 'transfer',
    entity_client_id: clientUuid,
    op_type: 'process',
    payload,
    idempotency_key: `${clientUuid}:transfer.process`,
  })
  return { provisional_no: provisional, client_uuid: clientUuid }
}

export async function listCustomers() {
  const db = await getDb()
  const rows = await db.getAll('customers')
  return rows.sort((a, b) => b.updated_at.localeCompare(a.updated_at))
}

export async function listOrders() {
  const db = await getDb()
  const rows = await db.getAll('orders')
  return rows.sort((a, b) => b.updated_at.localeCompare(a.updated_at))
}

export async function listMeasurements() {
  const db = await getDb()
  return db.getAll('measurements')
}

export async function upsertCustomers(rows: Array<Record<string, unknown>>) {
  const db = await getDb()
  const tx = db.transaction('customers', 'readwrite')
  for (const r of rows) {
    const serverId = String(r.server_id || '')
    const clientUuid = String(r.client_uuid || '') || null
    const local_id = clientUuid || `srv:${serverId}`
    await tx.store.put({
      local_id,
      client_uuid: clientUuid || '',
      server_id: serverId,
      man_napno: String(r.man_napno || ''),
      name: String(r.name || ''),
      Phone: String(r.Phone || ''),
      city: String(r.city || ''),
      Address: String(r.Address || ''),
      cnic: String(r.cnic || ''),
      remarks: String(r.remarks || ''),
      sync_status: 'synced',
      updated_at: String(r.updated_at || new Date().toISOString()),
    })
  }
  await tx.done
}

export async function upsertMeasurements(rows: Array<Record<string, unknown>>) {
  const db = await getDb()
  const tx = db.transaction('measurements', 'readwrite')
  for (const r of rows) {
    const serverId = String(r.server_id || '')
    const clientUuid = String(r.client_uuid || '') || null
    const local_id = clientUuid || `srv:${serverId}`
    await tx.store.put({ ...r, local_id, server_id: serverId })
  }
  await tx.done
}

export async function upsertOrders(rows: Array<Record<string, unknown>>) {
  const db = await getDb()
  const tx = db.transaction('orders', 'readwrite')
  for (const r of rows) {
    const serverId = String(r.server_id || '')
    const clientUuid = String(r.client_uuid || '') || null
    const local_id = clientUuid || `srv:${serverId}`
    await tx.store.put({
      local_id,
      client_uuid: clientUuid || '',
      server_id: serverId,
      provisional_no: String(r.provisional_no || serverId),
      naap_no: String(r.naap_no || ''),
      customer_name: String(r.customer_name || ''),
      payload: (r.payload as Record<string, unknown>) || r,
      sync_status: 'synced',
      updated_at: String(r.updated_at || new Date().toISOString()),
    })
  }
  await tx.done
}

export async function updateOp(opId: string, patch: Partial<QueueOp>) {
  const db = await getDb()
  const row = await db.get('sync_queue', opId)
  if (!row) return null
  const next = { ...row, ...patch }
  await db.put('sync_queue', next)
  return next
}

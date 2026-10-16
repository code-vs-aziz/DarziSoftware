import { type FormEvent, useEffect, useState } from 'react'
import {
  listCustomers,
  listMeasurements,
  listOrders,
  saveOrder,
  type CustomerRow,
  type OrderRow,
} from '../lib/db'

type Props = { onSaved: () => void; toast: (m: string) => void }

export function Orders({ onSaved, toast }: Props) {
  const [customers, setCustomers] = useState<CustomerRow[]>([])
  const [measurements, setMeasurements] = useState<Array<Record<string, unknown>>>([])
  const [orders, setOrders] = useState<OrderRow[]>([])

  async function refresh() {
    setCustomers(await listCustomers())
    setMeasurements(await listMeasurements())
    setOrders(await listOrders())
  }

  useEffect(() => {
    void refresh()
  }, [])

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    const qty = String(fd.get('qty') || '1')
    const rate = String(fd.get('rate') || '0')
    const total = parseFloat(rate) * parseFloat(qty || '1')
    const custId = String(fd.get('customer_id') || '')
    const cust = customers.find((c) => c.server_id === custId || c.client_uuid === custId)
    const payload = {
      Naapno: String(fd.get('naap_no') || ''),
      naap_no: String(fd.get('naap_no') || ''),
      customer_code: custId,
      Advance: String(fd.get('advance') || '0'),
      Discount: String(fd.get('discount') || '0'),
      retrun_Date: String(fd.get('return_date') || ''),
      Quantity: qty,
      total_amount: total,
      total,
      desigtype: [String(fd.get('suit_type') || 'Kurta')],
      suit: [String(fd.get('suit') || '1')],
      rate: [rate],
      itotal: [String(total)],
      Remarks: [String(fd.get('remarks') || '')],
    }
    const rec = await saveOrder(payload, cust?.name || '')
    toast(`Order saved: ${rec.provisional_no}`)
    e.currentTarget.reset()
    await refresh()
    onSaved()
  }

  return (
    <section className="card">
      <h2>Orders</h2>
      <p className="lead">Measurement list Sync ke baad aati hai (classic ERP bootstrap).</p>
      <form onSubmit={onSubmit}>
        <div className="grid">
          <div className="field full">
            <label>Customer</label>
            <select name="customer_id" required>
              <option value="">Select…</option>
              {customers.map((c) => (
                <option key={c.local_id} value={c.server_id || c.client_uuid}>
                  {c.name} ({c.man_napno || c.provisional_no})
                </option>
              ))}
            </select>
          </div>
          <div className="field full">
            <label>Measurement / Naap</label>
            <select name="naap_no" required>
              <option value="">Select…</option>
              {measurements.map((m) => {
                const id = String(m.server_id || '')
                return (
                  <option key={String(m.local_id)} value={id}>
                    Naap #{id}
                    {m.customer_code ? ` · cust ${String(m.customer_code)}` : ''}
                  </option>
                )
              })}
            </select>
          </div>
          <div className="field">
            <label>Suit type</label>
            <input name="suit_type" defaultValue="Kurta" />
          </div>
          <div className="field">
            <label>Suit</label>
            <input name="suit" defaultValue="1" />
          </div>
          <div className="field">
            <label>Qty</label>
            <input name="qty" type="number" min={1} defaultValue={1} />
          </div>
          <div className="field">
            <label>Rate</label>
            <input name="rate" type="number" min={0} step="0.01" defaultValue={0} />
          </div>
          <div className="field">
            <label>Advance</label>
            <input name="advance" type="number" min={0} step="0.01" defaultValue={0} />
          </div>
          <div className="field">
            <label>Discount</label>
            <input name="discount" type="number" min={0} step="0.01" defaultValue={0} />
          </div>
          <div className="field">
            <label>Return date</label>
            <input name="return_date" type="date" />
          </div>
          <div className="field full">
            <label>Remarks</label>
            <input name="remarks" />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn" type="submit">
            Save order offline
          </button>
        </div>
      </form>
      <div className="list">
        {orders.map((o) => (
          <div className="row" key={o.local_id}>
            <strong>
              {o.provisional_no || o.server_id}
              {o.sync_status === 'pending' ? <span className="badge">pending</span> : null}
            </strong>
            <div className="muted">
              {o.customer_name} · naap {o.naap_no}
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}

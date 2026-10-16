import { type FormEvent, useEffect, useState } from 'react'
import { listCustomers, saveDelivery, type CustomerRow } from '../lib/db'

type Props = { onSaved: () => void; toast: (m: string) => void }

export function Delivery({ onSaved, toast }: Props) {
  const [customers, setCustomers] = useState<CustomerRow[]>([])

  useEffect(() => {
    void listCustomers().then(setCustomers)
  }, [])

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    const rec = await saveDelivery({
      order_id: String(fd.get('order_id') || ''),
      customer_code: String(fd.get('customer_id') || ''),
      Balance: String(fd.get('amount') || '0'),
      total_Amount: String(fd.get('total') || fd.get('amount') || '0'),
      Advance: String(fd.get('advance') || '0'),
      Discount: String(fd.get('discount') || '0'),
      paid_Date: String(fd.get('paid_date') || new Date().toISOString().slice(0, 10)),
      order_sr: [String(fd.get('order_sr') || '1')],
      returndel: ['yes'],
    })
    toast(`Delivery queued: ${rec.provisional_no}`)
    e.currentTarget.reset()
    onSaved()
  }

  return (
    <section className="card">
      <h2>Delivery</h2>
      <p className="lead">Order deliver + paid amount queue.</p>
      <form onSubmit={onSubmit}>
        <div className="grid">
          <div className="field">
            <label>Order ID</label>
            <input name="order_id" required />
          </div>
          <div className="field">
            <label>Order SR</label>
            <input name="order_sr" defaultValue="1" required />
          </div>
          <div className="field full">
            <label>Customer</label>
            <select name="customer_id" required>
              <option value="">Select…</option>
              {customers.map((c) => (
                <option key={c.local_id} value={c.server_id || c.client_uuid}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>
          <div className="field">
            <label>Paid now</label>
            <input name="amount" type="number" min={0} step="0.01" defaultValue={0} />
          </div>
          <div className="field">
            <label>Total</label>
            <input name="total" type="number" min={0} step="0.01" />
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
            <label>Paid date</label>
            <input name="paid_date" type="date" />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn" type="submit">
            Queue delivery
          </button>
        </div>
      </form>
    </section>
  )
}

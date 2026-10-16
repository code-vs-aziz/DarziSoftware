import { type FormEvent, useEffect, useState } from 'react'
import { listCustomers, saveCash, type CustomerRow } from '../lib/db'

type Props = { onSaved: () => void; toast: (m: string) => void }

export function Cash({ onSaved, toast }: Props) {
  const [customers, setCustomers] = useState<CustomerRow[]>([])

  useEffect(() => {
    void listCustomers().then(setCustomers)
  }, [])

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    const rec = await saveCash({
      type: String(fd.get('type') || '2'),
      Customer: String(fd.get('customer_id') || ''),
      Employee: String(fd.get('employee') || ''),
      paid: String(fd.get('amount') || '0'),
      amount: String(fd.get('amount') || '0'),
      Remarks: String(fd.get('remarks') || ''),
      order_number: String(fd.get('order_no') || ''),
    })
    toast(`Cash queued: ${rec.provisional_no}`)
    e.currentTarget.reset()
    onSaved()
  }

  return (
    <section className="card">
      <h2>Cash / Payment</h2>
      <p className="lead">Receipt / payment offline queue.</p>
      <form onSubmit={onSubmit}>
        <div className="grid">
          <div className="field">
            <label>Type</label>
            <select name="type" defaultValue="2">
              <option value="2">Customer receipt (2)</option>
              <option value="1">Payment / expense (1)</option>
              <option value="7">Salary (7)</option>
            </select>
          </div>
          <div className="field">
            <label>Amount</label>
            <input name="amount" type="number" min={0} step="0.01" required />
          </div>
          <div className="field full">
            <label>Customer</label>
            <select name="customer_id">
              <option value="">Optional…</option>
              {customers.map((c) => (
                <option key={c.local_id} value={c.server_id || c.client_uuid}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>
          <div className="field">
            <label>Employee</label>
            <input name="employee" />
          </div>
          <div className="field">
            <label>Order no</label>
            <input name="order_no" />
          </div>
          <div className="field full">
            <label>Remarks</label>
            <input name="remarks" />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn" type="submit">
            Save cash offline
          </button>
        </div>
      </form>
    </section>
  )
}

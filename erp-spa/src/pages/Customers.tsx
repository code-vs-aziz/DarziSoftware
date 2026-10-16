import { type FormEvent, useEffect, useState } from 'react'
import { listCustomers, saveCustomer, type CustomerRow } from '../lib/db'

type Props = { onSaved: () => void; toast: (m: string) => void }

export function Customers({ onSaved, toast }: Props) {
  const [rows, setRows] = useState<CustomerRow[]>([])

  async function refresh() {
    setRows(await listCustomers())
  }

  useEffect(() => {
    void refresh()
  }, [])

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    const rec = await saveCustomer({
      name: String(fd.get('name') || ''),
      Phone: String(fd.get('phone') || ''),
      city: String(fd.get('city') || ''),
      Address: String(fd.get('address') || ''),
      man_napno: String(fd.get('man_napno') || ''),
      cnic: String(fd.get('cnic') || ''),
      remarks: String(fd.get('remarks') || ''),
    })
    toast(`Customer saved: ${rec.provisional_no}`)
    e.currentTarget.reset()
    await refresh()
    onSaved()
  }

  return (
    <section className="card">
      <h2>Customers</h2>
      <p className="lead">Offline create → Sync later to classic ERP DB.</p>
      <form onSubmit={onSubmit}>
        <div className="grid">
          <div className="field">
            <label>Name</label>
            <input name="name" required />
          </div>
          <div className="field">
            <label>Phone</label>
            <input name="phone" />
          </div>
          <div className="field">
            <label>City</label>
            <input name="city" />
          </div>
          <div className="field">
            <label>Man napno</label>
            <input name="man_napno" />
          </div>
          <div className="field full">
            <label>Address</label>
            <input name="address" />
          </div>
          <div className="field">
            <label>CNIC</label>
            <input name="cnic" />
          </div>
          <div className="field full">
            <label>Remarks</label>
            <textarea name="remarks" rows={2} />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn" type="submit">
            Save offline
          </button>
        </div>
      </form>
      <div className="list">
        {rows.length === 0 && <p className="muted">No customers yet.</p>}
        {rows.map((c) => (
          <div className="row" key={c.local_id}>
            <strong>
              {c.name || '—'}
              {c.sync_status === 'pending' ? <span className="badge">pending</span> : null}
            </strong>
            <div className="muted">
              {c.man_napno || c.provisional_no} · {c.Phone}
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}

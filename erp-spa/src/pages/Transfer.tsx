import { type FormEvent } from 'react'
import { saveTransfer } from '../lib/db'

type Props = { onSaved: () => void; toast: (m: string) => void }

export function Transfer({ onSaved, toast }: Props) {
  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    const rec = await saveTransfer({
      transfer_trnno: String(fd.get('trnno') || ''),
      dept: String(fd.get('dept') || 'HO'),
      sr: String(fd.get('sr') || ''),
      order_id: String(fd.get('order_id') || ''),
      EMPLOYEE: String(fd.get('employee') || ''),
      Rate: String(fd.get('rate') || ''),
      RDATE: String(fd.get('rdate') || new Date().toISOString().slice(0, 10)),
    })
    toast(`Transfer queued: ${rec.provisional_no}`)
    e.currentTarget.reset()
    onSaved()
  }

  return (
    <section className="card">
      <h2>Transfer</h2>
      <p className="lead">HO / CUT / HO2 / STIT stage move — IDs classic list se.</p>
      <form onSubmit={onSubmit}>
        <div className="grid">
          <div className="field">
            <label>Transfer trnno</label>
            <input name="trnno" required />
          </div>
          <div className="field">
            <label>From dept</label>
            <select name="dept" defaultValue="HO">
              <option value="HO">HO → CUT</option>
              <option value="CUT">CUT → HO2</option>
              <option value="HO2">HO2 → STIT</option>
              <option value="STIT">STIT complete</option>
            </select>
          </div>
          <div className="field">
            <label>Order ID</label>
            <input name="order_id" required />
          </div>
          <div className="field">
            <label>SR</label>
            <input name="sr" required />
          </div>
          <div className="field">
            <label>Employee</label>
            <input name="employee" />
          </div>
          <div className="field">
            <label>Rate</label>
            <input name="rate" type="number" min={0} step="0.01" />
          </div>
          <div className="field">
            <label>Date</label>
            <input name="rdate" type="date" />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn" type="submit">
            Queue transfer
          </button>
        </div>
      </form>
    </section>
  )
}

import { useEffect, useState } from 'react'
import { pendingOps, type QueueOp } from '../lib/db'

export function Queue() {
  const [ops, setOps] = useState<QueueOp[]>([])

  useEffect(() => {
    void pendingOps().then(setOps)
    const t = window.setInterval(() => {
      void pendingOps().then(setOps)
    }, 2500)
    return () => window.clearInterval(t)
  }, [])

  return (
    <section className="card">
      <h2>Pending sync queue</h2>
      <p className="lead">Is device pe safe — Sync Now se classic MariaDB mein jayega.</p>
      <div className="list">
        {ops.length === 0 && <p className="muted">Queue empty.</p>}
        {ops.map((op) => (
          <div className="row" key={op.op_id}>
            <strong>
              {op.entity_type}.{op.op_type}
            </strong>
            <div className="muted">
              {op.created_at}
              {op.last_error ? ` · ${op.last_error}` : ''}
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}

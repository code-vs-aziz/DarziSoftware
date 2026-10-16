import { NavLink, Outlet } from 'react-router-dom'
import type { ConnStatus } from '../lib/sync'

type Props = {
  status: ConnStatus
  pending: number
  message: string
  onSync: () => void
  classicUrl: string
}

export function Layout({ status, pending, message, onSync, classicUrl }: Props) {
  const statusClass =
    status === 'online' ? 'online' : status === 'checking' ? 'checking' : 'offline'

  return (
    <div className="shell">
      <header className="top">
        <div className="brand">
          <h1>Darzi Baba · Offline SPA</h1>
          <p>Alag project — classic PHP ERP safe rehta hai</p>
        </div>
        <div className="actions">
          <a className="btn ghost" href={classicUrl} target="_blank" rel="noreferrer">
            Classic ERP
          </a>
          <button type="button" className="btn warn" onClick={onSync}>
            Sync Now{pending ? ` (${pending})` : ''}
          </button>
        </div>
      </header>

      <div className={`status ${statusClass}`}>{message}</div>

      <nav className="nav">
        <NavLink to="/" end>
          Home
        </NavLink>
        <NavLink to="/customers">Customers</NavLink>
        <NavLink to="/orders">Orders</NavLink>
        <NavLink to="/cash">Cash</NavLink>
        <NavLink to="/delivery">Delivery</NavLink>
        <NavLink to="/transfer">Transfer</NavLink>
        <NavLink to="/queue">Queue</NavLink>
      </nav>

      <Outlet />
    </div>
  )
}

import { useCallback, useEffect, useState } from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'
import { Layout } from './components/Layout'
import { loadConfig } from './lib/config'
import { countPending } from './lib/db'
import { checkHealth, syncNow, type ConnStatus } from './lib/sync'
import { Cash } from './pages/Cash'
import { Customers } from './pages/Customers'
import { Delivery } from './pages/Delivery'
import { Home } from './pages/Home'
import { Orders } from './pages/Orders'
import { Queue } from './pages/Queue'
import { Transfer } from './pages/Transfer'

export default function App() {
  const cfg = loadConfig()
  const [status, setStatus] = useState<ConnStatus>('checking')
  const [pending, setPending] = useState(0)
  const [message, setMessage] = useState('Checking…')
  const [toast, setToast] = useState('')

  const refreshPending = useCallback(async () => {
    setPending(await countPending())
  }, [])

  const refreshStatus = useCallback(async () => {
    const st = await checkHealth()
    setStatus(st)
    if (st === 'online') {
      setMessage('Online — Sync Now se pending bhejo / cache refresh')
    } else {
      setMessage('Offline — SPA local pe chal rahi hai (PHP server ki zaroorat nahi)')
    }
  }, [])

  useEffect(() => {
    void refreshStatus()
    void refreshPending()
    const onOnline = () => void refreshStatus()
    const onOffline = () => {
      setStatus('offline')
      setMessage('Offline — SPA local pe chal rahi hai')
    }
    window.addEventListener('online', onOnline)
    window.addEventListener('offline', onOffline)
    const t = window.setInterval(() => void refreshStatus(), 20000)
    return () => {
      window.removeEventListener('online', onOnline)
      window.removeEventListener('offline', onOffline)
      window.clearInterval(t)
    }
  }, [refreshPending, refreshStatus])

  useEffect(() => {
    if (!toast) return
    const t = window.setTimeout(() => setToast(''), 3200)
    return () => window.clearTimeout(t)
  }, [toast])

  async function handleSync() {
    try {
      setMessage('Syncing…')
      const res = await syncNow()
      setPending(res.pending)
      setStatus('online')
      setMessage(`Synced · pending ${res.pending}`)
      setToast('Sync complete')
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'sync_failed'
      setToast(
        msg === 'offline'
          ? 'Server offline — local save continue'
          : `Sync failed: ${msg} (classic ERP login same browser mein chahiye)`,
      )
      await refreshStatus()
      await refreshPending()
    }
  }

  return (
    <>
      <Routes>
        <Route
          element={
            <Layout
              status={status}
              pending={pending}
              message={message}
              onSync={() => void handleSync()}
              classicUrl={cfg.classicErpUrl}
            />
          }
        >
          <Route index element={<Home pending={pending} status={status} />} />
          <Route
            path="customers"
            element={
              <Customers
                onSaved={() => void refreshPending()}
                toast={setToast}
              />
            }
          />
          <Route
            path="orders"
            element={<Orders onSaved={() => void refreshPending()} toast={setToast} />}
          />
          <Route
            path="cash"
            element={<Cash onSaved={() => void refreshPending()} toast={setToast} />}
          />
          <Route
            path="delivery"
            element={<Delivery onSaved={() => void refreshPending()} toast={setToast} />}
          />
          <Route
            path="transfer"
            element={<Transfer onSaved={() => void refreshPending()} toast={setToast} />}
          />
          <Route path="queue" element={<Queue />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
      {toast ? <div className="toast">{toast}</div> : null}
    </>
  )
}

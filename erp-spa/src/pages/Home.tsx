type Props = { pending: number; status: string }

export function Home({ pending, status }: Props) {
  return (
    <section className="card">
      <h2>Offline-first staff ERP (SPA)</h2>
      <p className="lead">
        Yeh alag React PWA hai. Classic PHP UI ko touch nahi karti. Server band ho to bhi yahan
        customer / order / cash / delivery / transfer save ho kar queue mein rehte hain.
      </p>
      <div className="stats">
        <div className="stat">
          <strong>{pending}</strong>
          <span>Pending sync</span>
        </div>
        <div className="stat">
          <strong>{status}</strong>
          <span>Connection</span>
        </div>
        <div className="stat">
          <strong>PWA</strong>
          <span>Install as app</span>
        </div>
      </div>
      <p className="note">
        <strong>Setup:</strong> pehle classic ERP mein login rakho (same browser), phir yahan{' '}
        <em>Sync Now</em> dabao — token + customers/measurements cache ho jayenge. Uske baad PHP
        band karke bhi yeh SPA chalegi.
      </p>
    </section>
  )
}

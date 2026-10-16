const STORAGE_KEY = 'darzi_spa_cfg'

export type SpaConfig = {
  apiBase: string
  healthUrl: string
  classicErpUrl: string
}

const defaults: SpaConfig = {
  apiBase: '/index.php/api/erp_sync',
  healthUrl: '/index.php/api/erp_sync/health',
  classicErpUrl: 'http://127.0.0.1:8080/index.php/Welcome',
}

export function loadConfig(): SpaConfig {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return { ...defaults }
    return { ...defaults, ...JSON.parse(raw) }
  } catch {
    return { ...defaults }
  }
}

export function saveConfig(partial: Partial<SpaConfig>) {
  const next = { ...loadConfig(), ...partial }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(next))
  return next
}

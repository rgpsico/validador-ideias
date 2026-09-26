import axios from 'axios'

// Same-origin: nginx proxies /api/* to PHP-FPM, so no separate api.* subdomain is needed.
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

export default api

import { ref, computed } from 'vue'
import { useApi, fetchCsrfCookie } from '../composables/useApi'

const USER_KEY = 'linkdrop_user'

const user = ref((() => { try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null') } catch { return null } })())

export function useAuth() {
  const { post, upload, loading, error } = useApi()

  const isAuthenticated = computed(() => user.value !== null)

  function setUser(u) {
    user.value = u
    if (u) {
      localStorage.setItem(USER_KEY, JSON.stringify(u))
    } else {
      localStorage.removeItem(USER_KEY)
    }
  }

  async function init() {
    try {
      const res = await fetch('/api/me', {
        credentials: 'include',
        headers: { Accept: 'application/json' },
      })
      if (res.ok) {
        setUser(await res.json())
      } else {
        setUser(null)
      }
    } catch {
      setUser(null)
    }
  }

  async function login(credentials) {
    await fetchCsrfCookie()
    const data = await post('/login', credentials)
    setUser(data.user)
    return data
  }

  async function register(payload) {
    await fetchCsrfCookie()
    const data = await post('/register', payload)
    setUser(data.user)
    return data
  }

  async function logout() {
    try { await post('/logout') } finally { setUser(null) }
  }

  async function updateAvatar(file) {
    const fd = new FormData()
    fd.append('avatar', file)
    const data = await upload('/profile/avatar', fd)
    setUser({ ...user.value, avatar: data.avatar })
    return data.avatar
  }

  return { user, isAuthenticated, loading, error, init, login, register, logout, updateAvatar }
}

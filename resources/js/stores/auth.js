import { ref, computed } from 'vue'
import { useApi } from '../composables/useApi'

const TOKEN_KEY = 'linkdrop_token'
const USER_KEY  = 'linkdrop_user'

const token = ref(localStorage.getItem(TOKEN_KEY))
const user  = ref(JSON.parse(localStorage.getItem(USER_KEY) || 'null'))

export function useAuth() {
  const { post, loading, error } = useApi()

  const isAuthenticated = computed(() => !!token.value)

  function persist(t, u) {
    token.value = t
    user.value  = u
    localStorage.setItem(TOKEN_KEY, t)
    localStorage.setItem(USER_KEY, JSON.stringify(u))
  }

  function clear() {
    token.value = null
    user.value  = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  async function login(credentials) {
    const data = await post('/login', credentials)
    persist(data.token, data.user)
    return data
  }

  async function register(payload) {
    const data = await post('/register', payload)
    persist(data.token, data.user)
    return data
  }

  async function logout() {
    try { await post('/logout') } finally { clear() }
  }

  return { user, token, isAuthenticated, loading, error, login, register, logout }
}

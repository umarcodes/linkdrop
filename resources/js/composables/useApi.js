import { ref } from 'vue'

const BASE_URL = import.meta.env.VITE_API_URL || '/api'

function getToken() {
  return localStorage.getItem('linkdrop_token')
}

function buildHeaders() {
  const headers = { 'Content-Type': 'application/json', Accept: 'application/json' }
  const token = getToken()
  if (token) headers['Authorization'] = `Bearer ${token}`
  return headers
}

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  async function request(method, path, body = null) {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${BASE_URL}${path}`, {
        method,
        headers: buildHeaders(),
        body: body ? JSON.stringify(body) : null,
      })
      const data = await res.json()
      if (!res.ok) {
        error.value = data.message || data.errors || 'An error occurred'
        throw error.value
      }
      return data
    } catch (e) {
      if (!error.value) error.value = e?.message || 'Network error'
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    get:   (path)         => request('GET', path),
    post:  (path, body)   => request('POST', path, body),
    put:   (path, body)   => request('PUT', path, body),
    patch: (path, body)   => request('PATCH', path, body),
    del:   (path)         => request('DELETE', path),
  }
}

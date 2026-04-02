<template>
  <div class="auth-bg">
    <div class="orb orb-1" />
    <div class="orb orb-2" />

    <div class="card">
      <div class="logo">
        <span class="logo-text">LinkDrop</span>
      </div>
      <h1>Welcome back</h1>
      <p class="subtitle">Sign in to your account</p>

      <form @submit.prevent="handleLogin">
        <div class="field">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="you@example.com" required />
        </div>
        <div class="field">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="••••••••" required />
        </div>

        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>

        <button type="submit" :disabled="loading" class="btn-primary">
          <span v-if="loading" class="spinner" />
          <span v-else>Sign in</span>
        </button>
      </form>

      <p class="footer-link">
        Don't have an account? <RouterLink to="/app/register">Create one</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'

const router = useRouter()
const { login, loading, error } = useAuth()

const form = ref({ email: '', password: '' })
const errorMsg = computed(() => {
  if (!error.value) return ''
  if (typeof error.value === 'string') return error.value
  return Object.values(error.value).flat().join(' ')
})

async function handleLogin() {
  try {
    await login(form.value)
    router.push({ name: 'dashboard' })
  } catch {}
}
</script>

<style scoped>
.auth-bg {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  pointer-events: none;
}

.orb-1 {
  width: 400px; height: 400px;
  background: #7c6af7;
  top: -100px; left: -100px;
}

.orb-2 {
  width: 300px; height: 300px;
  background: #e96af5;
  bottom: -80px; right: -80px;
}

.card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 420px;
  position: relative;
  z-index: 1;
}

.logo {
  text-align: center;
  margin-bottom: 24px;
}

.logo-text {
  font-size: 1.6rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

h1 {
  font-size: 1.5rem;
  font-weight: 600;
  text-align: center;
  color: #e8e8f0;
}

.subtitle {
  text-align: center;
  color: #666;
  font-size: 0.9rem;
  margin-top: 4px;
  margin-bottom: 28px;
}

.field {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  color: #a0a0b0;
  margin-bottom: 6px;
}

input {
  width: 100%;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 10px;
  padding: 12px 14px;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 0.95rem;
  transition: border-color 0.2s;
  outline: none;
}

input:focus {
  border-color: #7c6af7;
}

.error-box {
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid rgba(248, 113, 113, 0.3);
  border-radius: 10px;
  padding: 10px 14px;
  color: #f87171;
  font-size: 0.85rem;
  margin-bottom: 16px;
}

.btn-primary {
  width: 100%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  border: none;
  border-radius: 10px;
  padding: 13px;
  color: white;
  font-family: inherit;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
}

.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary:hover:not(:disabled) { opacity: 0.9; }

.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.footer-link {
  text-align: center;
  margin-top: 20px;
  font-size: 0.875rem;
  color: #666;
}

.footer-link a {
  color: #7c6af7;
  text-decoration: none;
  font-weight: 500;
}

.footer-link a:hover { text-decoration: underline; }
</style>

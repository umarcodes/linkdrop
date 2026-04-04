<template>
  <div class="auth-bg">
    <div class="orb orb-1" />
    <div class="orb orb-2" />

    <div class="card">
      <div class="logo">
        <span class="logo-text">LinkDrop</span>
      </div>

      <!-- Waitlist view: shown when registration is closed and no invite code -->
      <template v-if="registrationClosed && !hasInvite">
        <h1>Join the waitlist</h1>
        <p class="subtitle">We'll let you know when a spot opens up</p>

        <div v-if="waitlistSuccess" class="success-box">
          You're on the list! We'll email you when your invite is ready.
        </div>

        <form v-else @submit.prevent="handleWaitlist">
          <div class="field">
            <label>Email</label>
            <input v-model="waitlistEmail" type="email" placeholder="you@example.com" required />
          </div>

          <div v-if="waitlistError" class="error-box">{{ waitlistError }}</div>

          <button type="submit" :disabled="waitlistLoading" class="btn-primary">
            <span v-if="waitlistLoading" class="spinner" />
            <span v-else>Join waitlist</span>
          </button>
        </form>

        <p class="footer-link">
          Already have an account? <RouterLink to="/app/login">Sign in</RouterLink>
        </p>
      </template>

      <!-- Normal registration form -->
      <template v-else>
        <h1>Create account</h1>
        <p class="subtitle">Start sharing your links</p>

        <div v-if="hasInvite" class="invite-banner">
          You have an invite — fill in your details below.
        </div>

        <form @submit.prevent="handleRegister">
          <div class="grid-2">
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" placeholder="Jane Doe" required />
            </div>
            <div class="field">
              <label>Username</label>
              <div class="input-prefix-wrap">
                <span class="prefix">@</span>
                <input
                  v-model="form.username"
                  type="text"
                  placeholder="janedoe"
                  class="prefixed"
                  required
                />
              </div>
              <span class="field-hint">letters, numbers, _ and - only</span>
            </div>
          </div>

          <div v-if="form.username" class="url-preview">
            <span class="url-preview-label">Your link:</span>
            <span class="url-pill">{{ host }}/{{ form.username }}</span>
          </div>

          <div class="field">
            <label>Email</label>
            <input v-model="form.email" type="email" placeholder="you@example.com" required />
          </div>
          <div class="field">
            <label>Password</label>
            <input v-model="form.password" type="password" placeholder="Min 8 characters" required />
          </div>
          <div class="field">
            <label>Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required />
          </div>

          <input v-model="form.invite_code" type="hidden" />

          <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>

          <button type="submit" :disabled="loading" class="btn-primary">
            <span v-if="loading" class="spinner" />
            <span v-else>Create account</span>
          </button>
        </form>

        <p class="footer-link">
          Already have an account? <RouterLink to="/app/login">Sign in</RouterLink>
        </p>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const { register, loading, error } = useAuth()

const form = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  invite_code: '',
})

const registrationClosed = ref(false)
const hasInvite = ref(false)

const waitlistEmail = ref('')
const waitlistLoading = ref(false)
const waitlistError = ref('')
const waitlistSuccess = ref(false)

onMounted(() => {
  if (route.query.invite) {
    form.value.invite_code = route.query.invite
    hasInvite.value = true
  }
})

const host = window.location.host

const errorMsg = computed(() => {
  if (!error.value) return ''
  if (error.value === 'Registration is currently closed.') return ''
  if (typeof error.value === 'string') return error.value
  return Object.values(error.value).flat().join(' ')
})

async function handleRegister() {
  try {
    await register(form.value)
    router.push({ name: 'dashboard' })
  } catch {
    if (error.value === 'Registration is currently closed.') {
      registrationClosed.value = true
    }
  }
}

async function handleWaitlist() {
  waitlistLoading.value = true
  waitlistError.value = ''
  try {
    const res = await fetch('/api/waitlist', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: waitlistEmail.value }),
    })
    if (!res.ok) {
      const data = await res.json()
      waitlistError.value = data.message || 'Something went wrong.'
    } else {
      waitlistSuccess.value = true
    }
  } catch {
    waitlistError.value = 'Network error. Please try again.'
  } finally {
    waitlistLoading.value = false
  }
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
  padding: 24px;
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
  top: -100px; right: -100px;
}

.orb-2 {
  width: 300px; height: 300px;
  background: #e96af5;
  bottom: -80px; left: -80px;
}

.card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 480px;
  position: relative;
  z-index: 1;
}

.logo { text-align: center; margin-bottom: 24px; }

.logo-text {
  font-size: 1.6rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

h1 { font-size: 1.5rem; font-weight: 600; text-align: center; color: #e8e8f0; }

.subtitle {
  text-align: center;
  color: #666;
  font-size: 0.9rem;
  margin-top: 4px;
  margin-bottom: 28px;
}

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.field { margin-bottom: 16px; }

label { display: block; font-size: 0.85rem; font-weight: 500; color: #a0a0b0; margin-bottom: 6px; }
.field-hint { display: block; font-weight: 400; font-size: 0.78rem; color: #555; margin-top: 4px; }

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

input:focus { border-color: #7c6af7; }

.input-prefix-wrap {
  display: flex;
  align-items: center;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 10px;
  overflow: hidden;
  transition: border-color 0.2s;
}

.input-prefix-wrap:focus-within { border-color: #7c6af7; }

.prefix {
  padding: 12px 0 12px 14px;
  color: #666;
  font-size: 0.95rem;
  user-select: none;
}

input.prefixed {
  border: none;
  border-radius: 0;
  padding-left: 4px;
  background: transparent;
}

input.prefixed:focus { border-color: transparent; }

.url-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  margin-top: -8px;
}

.url-preview-label { font-size: 0.78rem; color: #666; }

.url-pill {
  font-size: 0.78rem;
  background: rgba(124, 106, 247, 0.15);
  border: 1px solid rgba(124, 106, 247, 0.3);
  color: #7c6af7;
  padding: 2px 10px;
  border-radius: 20px;
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

.success-box {
  background: rgba(52, 211, 153, 0.1);
  border: 1px solid rgba(52, 211, 153, 0.3);
  border-radius: 10px;
  padding: 12px 14px;
  color: #34d399;
  font-size: 0.9rem;
  margin-bottom: 16px;
  text-align: center;
}

.invite-banner {
  background: rgba(124, 106, 247, 0.1);
  border: 1px solid rgba(124, 106, 247, 0.3);
  border-radius: 10px;
  padding: 10px 14px;
  color: #a090f5;
  font-size: 0.85rem;
  margin-bottom: 20px;
  text-align: center;
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

.footer-link { text-align: center; margin-top: 20px; font-size: 0.875rem; color: #666; }
.footer-link a { color: #7c6af7; text-decoration: none; font-weight: 500; }
.footer-link a:hover { text-decoration: underline; }
</style>

<template>
  <div class="auth-bg">
    <div class="orb orb-1" />
    <div class="orb orb-2" />
    <div class="card">
      <div class="logo"><span class="logo-text">LinkDrop</span></div>
      <h1>Reset password</h1>
      <p class="subtitle">Enter your new password below</p>

      <div v-if="done" class="success-box">
        Password reset! <RouterLink to="/app/login">Sign in →</RouterLink>
      </div>

      <form v-else @submit.prevent="handleSubmit">
        <div class="field">
          <label>New Password</label>
          <input v-model="form.password" type="password" placeholder="Min 8 characters" required />
        </div>
        <div class="field">
          <label>Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required />
        </div>
        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>
        <button type="submit" :disabled="loading" class="btn-primary">
          <span v-if="loading" class="spinner" />
          <span v-else>Reset password</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '../composables/useApi'

const route = useRoute()
const { post, loading } = useApi()

const form     = ref({ password: '', password_confirmation: '' })
const done     = ref(false)
const errorMsg = ref('')

async function handleSubmit() {
  errorMsg.value = ''
  try {
    await post('/reset-password', {
      email: route.query.email,
      token: route.query.token,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    })
    done.value = true
  } catch (e) {
    errorMsg.value = typeof e === 'string' ? e : 'Invalid or expired reset link.'
  }
}
</script>

<style scoped>
.auth-bg {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden; padding: 24px;
}
.orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
.orb-1 { width: 400px; height: 400px; background: #7c6af7; top: -100px; right: -100px; }
.orb-2 { width: 300px; height: 300px; background: #e96af5; bottom: -80px; left: -80px; }

.card {
  background: #111118; border: 1px solid #1e1e2e; border-radius: 20px;
  padding: 40px; width: 100%; max-width: 400px; position: relative; z-index: 1;
}
.logo { text-align: center; margin-bottom: 24px; }
.logo-text {
  font-size: 1.6rem; font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
h1 { font-size: 1.5rem; font-weight: 600; text-align: center; color: #e8e8f0; }
.subtitle { text-align: center; color: #666; font-size: 0.9rem; margin-top: 4px; margin-bottom: 28px; }
.field { margin-bottom: 16px; }
label { display: block; font-size: 0.85rem; font-weight: 500; color: #a0a0b0; margin-bottom: 6px; }
input {
  width: 100%; background: #0a0a0f; border: 1px solid #1e1e2e; border-radius: 10px;
  padding: 12px 14px; color: #e8e8f0; font-family: inherit; font-size: 0.95rem;
  transition: border-color 0.2s; outline: none;
}
input:focus { border-color: #7c6af7; }
.error-box {
  background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3);
  border-radius: 10px; padding: 10px 14px; color: #f87171; font-size: 0.85rem; margin-bottom: 16px;
}
.success-box {
  background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.3);
  border-radius: 10px; padding: 14px; color: #34d399; font-size: 0.9rem; text-align: center;
}
.success-box a { color: #34d399; font-weight: 600; }
.btn-primary {
  width: 100%; background: linear-gradient(135deg, #7c6af7, #e96af5);
  border: none; border-radius: 10px; padding: 13px; color: white;
  font-family: inherit; font-size: 0.95rem; font-weight: 600; cursor: pointer;
  transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; margin-top: 8px;
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary:hover:not(:disabled) { opacity: 0.9; }
.spinner {
  width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

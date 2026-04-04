<template>
  <div class="auth-bg">
    <div class="orb orb-1" />
    <div class="orb orb-2" />
    <div class="card">
      <div class="logo"><span class="logo-text">LinkDrop</span></div>
      <h1>Verify email</h1>

      <div v-if="verifying" class="subtitle">Verifying…</div>

      <div v-else-if="done" class="success-box">
        ✓ Email verified! <RouterLink to="/app/dashboard">Go to dashboard →</RouterLink>
      </div>

      <div v-else-if="errorMsg" class="error-box">
        {{ errorMsg }}<br />
        <RouterLink to="/app/dashboard">Back to dashboard</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '../composables/useApi'

const route = useRoute()
const { post } = useApi()

const verifying = ref(true)
const done = ref(false)
const errorMsg = ref('')

onMounted(async () => {
  try {
    await post('/verify-email', {
      token: route.query.token,
      email: route.query.email,
    })
    done.value = true
  } catch (e) {
    errorMsg.value = typeof e === 'string' ? e : 'Invalid or expired verification link.'
  } finally {
    verifying.value = false
  }
})
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
  padding: 40px; width: 100%; max-width: 400px; position: relative; z-index: 1; text-align: center;
}
.logo { text-align: center; margin-bottom: 24px; }
.logo-text {
  font-size: 1.6rem; font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
h1 { font-size: 1.5rem; font-weight: 600; color: #e8e8f0; margin-bottom: 16px; }
.subtitle { color: #666; font-size: 0.9rem; }
.error-box {
  background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3);
  border-radius: 10px; padding: 14px; color: #f87171; font-size: 0.9rem; line-height: 1.6;
}
.error-box a { color: #f87171; }
.success-box {
  background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.3);
  border-radius: 10px; padding: 14px; color: #34d399; font-size: 0.9rem; line-height: 1.6;
}
.success-box a { color: #34d399; font-weight: 600; }
</style>

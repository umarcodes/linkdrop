<template>
  <div class="profile-bg">
    <div class="orb orb-1" />
    <div class="orb orb-2" />

    <div v-if="loading" class="loading-wrap">
      <div class="spinner-lg" />
    </div>

    <div v-else-if="notFound" class="not-found">
      <div class="not-found-code">404</div>
      <div class="not-found-msg">Profile not found</div>
      <p class="not-found-sub">This username doesn't exist or may have been removed.</p>
      <a href="/" class="not-found-home">← Go to LinkDrop</a>
    </div>

    <div v-else-if="profile" class="profile-card">
      <div class="avatar-wrap">
        <img v-if="profile.avatar" :src="profile.avatar" class="avatar avatar-img" :alt="profile.name" />
        <div v-else class="avatar">{{ initial }}</div>
      </div>

      <h1 class="profile-name">{{ profile.name }}</h1>
      <p class="profile-handle">@{{ profile.username }}</p>
      <p v-if="profile.bio" class="profile-bio">{{ profile.bio }}</p>

      <div class="profile-badges">
        <span v-if="profile.badge_verified" class="badge badge-verified" title="Verified account">✓ Verified</span>
        <span v-if="profile.badge_available_for_hire" class="badge badge-hire" title="Available for hire">💼 Available for hire</span>
      </div>

      <div class="links-list">
        <template v-for="link in profile.links" :key="link.id">
          <div v-if="link.is_header" class="link-section-header">{{ link.title }}</div>
          <!-- Embeddable player -->
          <div v-else-if="embedUrl(link.url)" class="link-embed">
            <div class="embed-title">{{ link.title }}</div>
            <iframe
              :src="embedUrl(link.url)"
              class="embed-frame"
              :class="{ 'embed-frame-tall': isYouTube(link.url) }"
              frameborder="0"
              allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
              allowfullscreen
              loading="lazy"
            />
          </div>
          <!-- Regular link -->
          <a
            v-else
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            class="link-item"
            :class="{ 'is-copy': isCopyLink(link.url) }"
            @click.prevent="handleLinkClick(link)"
          >
            <img v-if="link.og_image" :src="link.og_image" class="link-og-thumb" :alt="link.title" />
            <span v-else class="link-icon">{{ link.icon || detectSocialIcon(link.url) || '🔗' }}</span>
            <span class="link-title">{{ link.title }}</span>
            <span class="link-arrow">{{ copiedLinkId === link.id ? '✓' : (link.is_password_protected ? '🔒' : (isCopyLink(link.url) ? '⎘' : '→')) }}</span>
          </a>
        </template>
      </div>

      <div class="profile-actions">
        <button class="btn-share" @click="shareProfile" title="Share profile">
          {{ copied ? '✓ Copied!' : '↑ Share' }}
        </button>
        <a :href="qrUrl" target="_blank" class="btn-qr" title="Download QR code">⬛ QR Code</a>
      </div>

      <div class="made-with">
        Made with <span class="brand">LinkDrop</span>
      </div>
    </div>

    <!-- Password Modal -->
    <div v-if="passwordModal.link" class="pw-overlay" @click.self="closePasswordModal">
      <div class="pw-modal">
        <div class="pw-title">🔒 This link is password protected</div>
        <p class="pw-desc">Enter the password to continue.</p>
        <form @submit.prevent="submitPassword">
          <input
            v-model="passwordModal.value"
            type="password"
            class="pw-input"
            placeholder="Password"
            autofocus
          />
          <div v-if="passwordModal.error" class="pw-error">{{ passwordModal.error }}</div>
          <div class="pw-actions">
            <button type="submit" class="btn-primary" :disabled="passwordModal.loading">
              <span v-if="passwordModal.loading" class="spinner" />
              <span v-else>Unlock</span>
            </button>
            <button type="button" class="btn-pw-cancel" @click="closePasswordModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '../composables/useApi'
import { detectSocialIcon } from '../composables/useSocialIcon'

const route = useRoute()
const { get, post } = useApi()

const profile  = ref(null)
const loading  = ref(true)
const notFound = ref(false)
const copied      = ref(false)
const copiedLinkId = ref(null)

const passwordModal = ref({ link: null, value: '', error: '', loading: false })

function closePasswordModal() {
  passwordModal.value = { link: null, value: '', error: '', loading: false }
}

async function submitPassword() {
  const link = passwordModal.value.link
  passwordModal.value.loading = true
  passwordModal.value.error = ''
  try {
    const res = await post(`/p/${route.params.username}/verify/${link.id}`, {
      password: passwordModal.value.value,
    })
    closePasswordModal()
    window.open(res.url, '_blank', 'noopener,noreferrer')
    try { await post(`/p/${route.params.username}/click/${link.id}`) } catch {}
  } catch {
    passwordModal.value.error = 'Incorrect password.'
    passwordModal.value.loading = false
  }
}

const profileUrl = computed(() => window.location.href)
const qrUrl = computed(() =>
  `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(profileUrl.value)}`
)

async function shareProfile() {
  if (navigator.share) {
    await navigator.share({ title: profile.value?.name, url: profileUrl.value })
  } else {
    await navigator.clipboard.writeText(profileUrl.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}

const initial = computed(() => profile.value?.name?.[0]?.toUpperCase() || '?')

async function fetchProfile() {
  loading.value = true
  try {
    profile.value = await get(`/p/${route.params.username}`)
    applyProfileMeta(profile.value)
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
}

function isCopyLink(url) {
  return /^(mailto:|tel:|sms:)/i.test(url) || /^copy:/i.test(url)
}

function isYouTube(url) {
  return /youtube\.com\/watch|youtu\.be\//i.test(url)
}

function embedUrl(url) {
  if (!url) { return null }

  // YouTube
  const ytFull = url.match(/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]+)/)
  if (ytFull) { return `https://www.youtube.com/embed/${ytFull[1]}` }
  const ytShort = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/)
  if (ytShort) { return `https://www.youtube.com/embed/${ytShort[1]}` }

  // Spotify track / album / playlist
  const spotify = url.match(/open\.spotify\.com\/(track|album|playlist|episode)\/([a-zA-Z0-9]+)/)
  if (spotify) { return `https://open.spotify.com/embed/${spotify[1]}/${spotify[2]}` }

  // SoundCloud — use oEmbed iframe via SoundCloud's widget
  if (/soundcloud\.com\//i.test(url)) {
    return `https://w.soundcloud.com/player/?url=${encodeURIComponent(url)}&color=%237c6af7&auto_play=false&show_artwork=true`
  }

  return null
}

async function handleLinkClick(link) {
  if (link.is_password_protected) {
    passwordModal.value = { link, value: '', error: '', loading: false }
    return
  }
  if (isCopyLink(link.url)) {
    const text = link.url.replace(/^copy:/i, '')
    await navigator.clipboard.writeText(text)
    copiedLinkId.value = link.id
    setTimeout(() => { copiedLinkId.value = null }, 2000)
  } else {
    window.open(link.url, '_blank', 'noopener,noreferrer')
  }
  try {
    await post(`/p/${route.params.username}/click/${link.id}`)
  } catch {}
}

function setMeta(name, content) {
  let el = document.querySelector(`meta[name="${name}"]`) || document.querySelector(`meta[property="${name}"]`)
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute(name.startsWith('og:') ? 'property' : 'name', name)
    document.head.appendChild(el)
  }
  el.setAttribute('content', content)
}

function applyProfileMeta(p) {
  const title = `${p.name} — LinkDrop`
  const desc  = p.bio || `Check out ${p.name}'s links on LinkDrop.`
  const url   = window.location.href
  const image = p.avatar || ''

  document.title = title
  setMeta('description', desc)
  setMeta('og:title', title)
  setMeta('og:description', desc)
  setMeta('og:url', url)
  setMeta('og:type', 'profile')
  if (image) { setMeta('og:image', image) }
  setMeta('twitter:card', 'summary')
  setMeta('twitter:title', title)
  setMeta('twitter:description', desc)
  if (image) { setMeta('twitter:image', image) }

  if (p.theme) {
    const root = document.documentElement
    if (p.theme.accent) { root.style.setProperty('--p-accent', p.theme.accent) }
    if (p.theme.bg)     { root.style.setProperty('--p-bg', p.theme.bg) }
    if (p.theme.card)   { root.style.setProperty('--p-card', p.theme.card) }
    if (p.theme.text)   { root.style.setProperty('--p-text', p.theme.text) }
  }
}

onMounted(fetchProfile)
</script>

<style scoped>
:root {
  --p-accent: #7c6af7;
  --p-bg: transparent;
  --p-card: #111118;
  --p-text: #e8e8f0;
}

.profile-bg {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  padding: 40px 16px;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.12;
  pointer-events: none;
}

.orb-1 { width: 500px; height: 500px; background: #7c6af7; top: -150px; left: -150px; }
.orb-2 { width: 400px; height: 400px; background: #e96af5; bottom: -100px; right: -100px; }

.loading-wrap {
  display: flex; align-items: center; justify-content: center;
  height: 200px;
}

.spinner-lg {
  width: 40px; height: 40px;
  border: 3px solid #1e1e2e;
  border-top-color: #7c6af7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.not-found {
  text-align: center; z-index: 1;
}

.not-found-code {
  font-size: 5rem; font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}

.not-found-msg { color: #666; font-size: 1.1rem; margin-top: 8px; }
.not-found-sub { color: #444; font-size: 0.875rem; margin-top: 8px; }
.not-found-home {
  display: inline-block;
  margin-top: 20px;
  color: #7c6af7;
  font-size: 0.9rem;
  text-decoration: none;
  border: 1px solid rgba(124,106,247,0.3);
  padding: 8px 18px;
  border-radius: 10px;
  transition: background 0.2s;
}
.not-found-home:hover { background: rgba(124,106,247,0.1); }

.profile-card {
  width: 100%;
  max-width: 480px;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.avatar-wrap {
  margin-bottom: 16px;
  position: relative;
}

.avatar-wrap::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  filter: blur(12px);
  opacity: 0.6;
}

.avatar {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; font-weight: 700;
  position: relative;
}

.avatar-img { object-fit: cover; background: none; }

.profile-name { font-size: 1.5rem; font-weight: 700; color: #e8e8f0; margin-bottom: 4px; }
.profile-handle { color: #666; font-size: 0.9rem; margin-bottom: 6px; }
.profile-badges { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-bottom: 12px; }
.badge { font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
.badge-verified { background: rgba(124,106,247,0.15); color: #7c6af7; border: 1px solid rgba(124,106,247,0.3); }
.badge-hire { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
.profile-bio { color: #a0a0b0; font-size: 0.9rem; text-align: center; max-width: 320px; margin-bottom: 24px; line-height: 1.5; }

.links-list { width: 100%; display: flex; flex-direction: column; gap: 10px; margin-bottom: 32px; }

.link-embed {
  background: var(--p-card, #111118);
  border: 1px solid #1e1e2e;
  border-radius: 14px;
  overflow: hidden;
}

.embed-title {
  font-size: 0.8rem;
  font-weight: 500;
  color: #888;
  padding: 10px 14px 6px;
}

.embed-frame {
  width: 100%;
  height: 80px;
  display: block;
  border: none;
}

.embed-frame-tall {
  height: 220px;
}

.link-section-header {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #666;
  padding: 8px 4px 2px;
  margin-top: 8px;
}

.link-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--p-card, #111118);
  border: 1px solid #1e1e2e;
  border-radius: 14px;
  padding: 16px 18px;
  text-decoration: none;
  color: var(--p-text, #e8e8f0);
  transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
}

.link-item:hover {
  border-color: var(--p-accent, #7c6af7);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(124,106,247,0.15);
}

.link-icon { font-size: 1.3rem; flex-shrink: 0; }
.link-og-thumb { width: 36px; height: 36px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
.link-title { flex: 1; font-weight: 500; font-size: 0.95rem; }
.link-arrow { color: #666; font-size: 1rem; transition: transform 0.15s; }
.link-item:hover .link-arrow { transform: translateX(4px); color: #7c6af7; }
.link-item.is-copy .link-arrow { font-size: 1.1rem; }

.profile-actions { display: flex; gap: 10px; margin-bottom: 24px; }

.btn-share, .btn-qr {
  padding: 8px 18px;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
  border: 1px solid rgba(124,106,247,0.3);
  background: rgba(124,106,247,0.1);
  color: #7c6af7;
  font-family: inherit;
}
.btn-share:hover, .btn-qr:hover { background: rgba(124,106,247,0.2); }

.made-with { font-size: 0.78rem; color: #444; }
.brand { color: #7c6af7; font-weight: 600; }

.pw-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.pw-modal {
  background: #111118;
  border: 1px solid #2a2a3a;
  border-radius: 16px;
  padding: 32px;
  width: 340px;
  max-width: 90vw;
}
.pw-title { font-weight: 600; font-size: 1rem; margin-bottom: 8px; }
.pw-desc { font-size: 0.85rem; color: #666; margin-bottom: 20px; }
.pw-input {
  width: 100%;
  padding: 10px 14px;
  background: #0d0d15;
  border: 1px solid #2a2a3a;
  border-radius: 8px;
  color: #e8e8f0;
  font-size: 0.9rem;
  box-sizing: border-box;
  outline: none;
  margin-bottom: 12px;
}
.pw-input:focus { border-color: #7c6af7; }
.pw-error { color: #f87171; font-size: 0.82rem; margin-bottom: 12px; }
.pw-actions { display: flex; gap: 8px; }
.btn-pw-cancel {
  background: transparent;
  border: 1px solid #2a2a3a;
  border-radius: 8px;
  padding: 10px 20px;
  color: #666;
  font-size: 0.85rem;
  cursor: pointer;
}
.btn-pw-cancel:hover { border-color: #444; color: #a0a0b0; }
</style>

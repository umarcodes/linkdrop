<template>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span class="logo-text">LinkDrop</span>
      </div>

      <nav class="sidebar-nav">
        <button
          :class="['nav-btn', activeTab === 'links' && 'active']"
          @click="activeTab = 'links'"
        >
          <span class="nav-icon">🔗</span> Links
        </button>
        <button
          :class="['nav-btn', activeTab === 'analytics' && 'active']"
          @click="activeTab = 'analytics'"
        >
          <span class="nav-icon">📊</span> Analytics
        </button>
      </nav>

      <div class="sidebar-user">
        <button class="user-avatar-btn" title="Change avatar" aria-label="Change avatar" @click="$refs.avatarInput.click()">
          <img v-if="user?.avatar" :src="user.avatar" class="user-avatar-img" alt="Your avatar" />
          <div v-else class="user-avatar">{{ userInitial }}</div>
          <div class="avatar-overlay">📷</div>
        </button>
        <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden-input" @change="handleAvatarUpload" />
        <div class="user-info">
          <div class="user-name">{{ user?.name }}</div>
          <div class="user-handle">@{{ user?.username }}</div>
        </div>
        <button
          class="logout-btn"
          title="Log out"
          aria-label="Log out"
          @click="confirmLogout = true"
        >↩</button>
      </div>
    </aside>

    <!-- Main Panel -->
    <main class="main">

      <!-- Links Tab -->
      <div v-if="activeTab === 'links'" class="panel">
        <div class="panel-header">
          <h2>My Links</h2>
          <button class="btn-add" @click="showAddForm = !showAddForm">
            {{ showAddForm ? '✕ Cancel' : '+ Add Link' }}
          </button>
        </div>

        <Transition name="slide">
          <form v-if="showAddForm" class="add-form" @submit.prevent="handleAddLink">
            <div class="form-row">
              <div class="field">
                <label>Title</label>
                <input v-model="newLink.title" placeholder="My Website" required />
              </div>
              <div class="field">
                <label>Icon <span class="field-hint">(emoji)</span></label>
                <input v-model="newLink.icon" placeholder="🌐" maxlength="10" />
              </div>
            </div>
            <div class="field">
              <label>URL</label>
              <input v-model="newLink.url" type="url" placeholder="example.com" required />
            </div>
            <div v-if="addError" class="error-box">{{ addError }}</div>
            <button type="submit" :disabled="addLoading" class="btn-primary">
              <span v-if="addLoading" class="spinner" />
              <span v-else>Add Link</span>
            </button>
          </form>
        </Transition>

        <div v-if="linksLoading" class="loading-state">Loading links…</div>

        <TransitionGroup name="list" tag="div" class="links-list">
          <div v-for="link in links" :key="link.id" class="link-card">

            <!-- Edit mode -->
            <form v-if="editingId === link.id" class="edit-form" @submit.prevent="saveEdit(link)">
              <div class="edit-row">
                <input v-model="editForm.icon" class="edit-icon-input" placeholder="🔗" maxlength="10" title="Icon emoji" aria-label="Link icon" />
                <input v-model="editForm.title" class="edit-title-input" placeholder="Title" required aria-label="Link title" />
              </div>
              <input v-model="editForm.url" type="url" placeholder="example.com" required class="edit-url-input" aria-label="Link URL" />
              <div class="edit-actions">
                <button type="submit" :disabled="editLoading" class="btn-save">
                  <span v-if="editLoading" class="spinner spinner-sm" />
                  <span v-else>Save</span>
                </button>
                <button type="button" class="btn-cancel-edit" @click="cancelEdit">Cancel</button>
              </div>
            </form>

            <!-- View mode -->
            <template v-else>
              <div class="link-icon">{{ link.icon || '🔗' }}</div>
              <div class="link-info">
                <div class="link-title">{{ link.title }}</div>
                <div class="link-url">{{ link.url }}</div>
              </div>
              <div class="link-actions">
                <label class="toggle" :title="link.is_active ? 'Deactivate link' : 'Activate link'" :aria-label="link.is_active ? 'Deactivate link' : 'Activate link'">
                  <input type="checkbox" :checked="link.is_active" @change="toggleActive(link)" />
                  <span class="toggle-slider" />
                </label>
                <button
                  class="btn-icon"
                  title="Edit link"
                  aria-label="Edit link"
                  @click="startEdit(link)"
                >✏️</button>
                <button
                  v-if="deletingId !== link.id"
                  class="btn-icon btn-delete"
                  title="Delete link"
                  aria-label="Delete link"
                  @click="deletingId = link.id"
                >🗑</button>
                <template v-else>
                  <button class="btn-confirm-delete" @click="deleteLink(link.id)" title="Confirm delete">Yes, delete</button>
                  <button class="btn-cancel-delete" @click="deletingId = null" title="Cancel">Cancel</button>
                </template>
              </div>
            </template>

          </div>
        </TransitionGroup>

        <div v-if="!linksLoading && links.length === 0" class="empty-state">
          No links yet. Add your first link above!
        </div>
      </div>

      <!-- Analytics Tab -->
      <div v-if="activeTab === 'analytics'" class="panel">
        <div class="panel-header">
          <h2>Analytics</h2>
          <button class="btn-add" @click="fetchAnalytics" title="Refresh analytics">↻ Refresh</button>
        </div>

        <div v-if="analyticsLoading" class="loading-state">Loading analytics…</div>

        <template v-else>
          <div class="stat-grid">
            <div class="stat-card">
              <div class="stat-value">{{ analytics.total_clicks ?? 0 }}</div>
              <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ activeLinks.length }}</div>
              <div class="stat-label">Active Links</div>
            </div>
          </div>

          <h3 class="section-title">Clicks per Link</h3>
          <div v-if="(analytics.per_link || []).length === 0" class="empty-state small">No click data yet.</div>
          <div v-else class="bar-chart">
            <div v-for="item in analytics.per_link" :key="item.id" class="bar-row">
              <div class="bar-label" :title="item.title">{{ item.title }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: barWidth(item.clicks_count) }" />
              </div>
              <div class="bar-count">{{ item.clicks_count }}</div>
            </div>
          </div>

          <h3 class="section-title">Last 7 Days</h3>
          <div v-if="(analytics.daily_clicks || []).length === 0" class="empty-state small">No data for the last 7 days.</div>
          <div v-else class="bar-chart">
            <div v-for="day in analytics.daily_clicks" :key="day.date" class="bar-row">
              <div class="bar-label">{{ formatDate(day.date) }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: dayBarWidth(day.clicks) }" />
              </div>
              <div class="bar-count">{{ day.clicks }}</div>
            </div>
          </div>
        </template>
      </div>
    </main>

    <!-- Phone Preview -->
    <aside class="preview-panel">
      <div class="preview-title">Live Preview</div>
      <a :href="`/${user?.username}`" target="_blank" class="phone-link" title="Open your public profile">
        <div class="phone">
          <div class="phone-screen">
            <img v-if="user?.avatar" :src="user.avatar" class="preview-avatar preview-avatar-img" alt="" />
            <div v-else class="preview-avatar">{{ userInitial }}</div>
            <div class="preview-name">{{ user?.name }}</div>
            <div class="preview-handle">@{{ user?.username }}</div>
            <div class="preview-links">
              <div v-for="link in activeLinks" :key="link.id" class="preview-link">
                <span>{{ link.icon || '🔗' }}</span>
                <span class="preview-link-title">{{ link.title }}</span>
              </div>
              <div v-if="activeLinks.length === 0" class="preview-empty">
                No active links
              </div>
            </div>
          </div>
        </div>
      </a>
    </aside>
  </div>

  <!-- Logout confirmation modal -->
  <Transition name="fade">
    <div v-if="confirmLogout" class="modal-backdrop" @click.self="confirmLogout = false">
      <div class="modal">
        <h3>Log out?</h3>
        <p>You'll need to sign in again to manage your links.</p>
        <div class="modal-actions">
          <button class="btn-modal-cancel" @click="confirmLogout = false">Stay</button>
          <button class="btn-modal-confirm" @click="handleLogout">Log out</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { useApi } from '../composables/useApi'
import { useToast } from '../composables/useToast'

const router = useRouter()
const { user, logout, updateAvatar } = useAuth()
const { post, put, del, loading: addLoading, error: addErr } = useApi()
const { get: getLinks, loading: linksLoading } = useApi()
const { get: getAnalytics, loading: analyticsLoading } = useApi()
const { put: putEdit, loading: editLoading } = useApi()
const toast = useToast()

const activeTab    = ref('links')
const showAddForm  = ref(false)
const confirmLogout = ref(false)
const links        = ref([])
const analytics    = ref({})
const addError     = ref('')
const deletingId   = ref(null)
const editingId    = ref(null)
const editForm     = ref({ title: '', url: '', icon: '' })

const newLink = ref({ title: '', url: '', icon: '' })

const userInitial = computed(() => user.value?.name?.[0]?.toUpperCase() || '?')
const activeLinks = computed(() => links.value.filter(l => l.is_active))

const maxClicks = computed(() => {
  const counts = (analytics.value.per_link || []).map(l => l.clicks_count)
  return counts.length ? Math.max(...counts) : 1
})
const maxDay = computed(() => {
  const counts = (analytics.value.daily_clicks || []).map(d => d.clicks)
  return counts.length ? Math.max(...counts) : 1
})

function barWidth(count) { return `${Math.round((count / maxClicks.value) * 100)}%` }
function dayBarWidth(count) { return `${Math.round((count / maxDay.value) * 100)}%` }

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { month: 'short', day: 'numeric' })
}

function startEdit(link) {
  editingId.value = link.id
  editForm.value = { title: link.title, url: link.url, icon: link.icon || '' }
}

function cancelEdit() {
  editingId.value = null
}

async function saveEdit(link) {
  try {
    const updated = await putEdit(`/links/${link.id}`, editForm.value)
    Object.assign(link, updated)
    editingId.value = null
    toast.success('Link updated')
  } catch {
    toast.error('Failed to update link')
  }
}

async function fetchLinks() {
  links.value = await getLinks('/links')
}

async function fetchAnalytics() {
  analytics.value = await getAnalytics('/analytics')
}

async function handleAddLink() {
  addError.value = ''
  try {
    const link = await post('/links', newLink.value)
    links.value.push(link)
    newLink.value = { title: '', url: '', icon: '' }
    showAddForm.value = false
    toast.success('Link added')
  } catch (e) {
    addError.value = typeof addErr.value === 'string'
      ? addErr.value
      : Object.values(addErr.value || {}).flat().join(' ')
  }
}

async function toggleActive(link) {
  try {
    const updated = await put(`/links/${link.id}`, { is_active: !link.is_active })
    Object.assign(link, updated)
    toast.success(link.is_active ? 'Link activated' : 'Link deactivated')
  } catch {
    toast.error('Failed to update link')
  }
}

async function deleteLink(id) {
  try {
    await del(`/links/${id}`)
    links.value = links.value.filter(l => l.id !== id)
    deletingId.value = null
    toast.success('Link deleted')
  } catch {
    toast.error('Failed to delete link')
    deletingId.value = null
  }
}

async function handleAvatarUpload(e) {
  const file = e.target.files[0]
  if (!file) { return }
  try {
    await updateAvatar(file)
    toast.success('Avatar updated')
  } catch {
    toast.error('Failed to upload avatar')
  }
  e.target.value = ''
}

async function handleLogout() {
  confirmLogout.value = false
  await logout()
  router.push({ name: 'login' })
}

onMounted(() => {
  fetchLinks()
  fetchAnalytics()
})
</script>

<style scoped>
.dashboard {
  display: grid;
  grid-template-columns: 220px 1fr 280px;
  min-height: 100vh;
}

/* Sidebar */
.sidebar {
  background: #111118;
  border-right: 1px solid #1e1e2e;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
}

.sidebar-logo { padding: 0 8px 28px; }

.logo-text {
  font-size: 1.3rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 4px; }

.nav-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: none;
  border-radius: 10px;
  background: transparent;
  color: #666;
  font-family: inherit;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  text-align: left;
}
.nav-btn:hover { background: #1e1e2e; color: #e8e8f0; }
.nav-btn.active { background: rgba(124,106,247,0.15); color: #7c6af7; }

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 8px;
  border-top: 1px solid #1e1e2e;
}

.user-avatar-btn {
  position: relative;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  flex-shrink: 0;
  border-radius: 50%;
}

.user-avatar-btn:hover .avatar-overlay { opacity: 1; }

.avatar-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.user-avatar {
  width: 34px; height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.85rem;
}

.user-avatar-img {
  width: 34px; height: 34px;
  border-radius: 50%;
  object-fit: cover;
}

.hidden-input { display: none; }

.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 0.85rem; font-weight: 600; color: #e8e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-handle { font-size: 0.75rem; color: #666; }

.logout-btn {
  background: none; border: none; color: #666;
  cursor: pointer; font-size: 1rem; padding: 4px;
  transition: color 0.2s; flex-shrink: 0;
}
.logout-btn:hover { color: #f87171; }

/* Main Panel */
.main { padding: 32px; overflow-y: auto; }

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

h2 { font-size: 1.3rem; font-weight: 600; }

.btn-add {
  background: rgba(124,106,247,0.15);
  border: 1px solid rgba(124,106,247,0.3);
  border-radius: 10px;
  padding: 8px 16px;
  color: #7c6af7;
  font-family: inherit;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-add:hover { background: rgba(124,106,247,0.25); }

/* Add Form */
.add-form {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { margin-bottom: 14px; }

label { display: block; font-size: 0.8rem; font-weight: 500; color: #a0a0b0; margin-bottom: 5px; }
.field-hint { font-weight: 400; color: #555; }

input {
  width: 100%;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 8px;
  padding: 10px 12px;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}
input:focus { border-color: #7c6af7; }

.error-box {
  background: rgba(248,113,113,0.1);
  border: 1px solid rgba(248,113,113,0.3);
  border-radius: 8px;
  padding: 8px 12px;
  color: #f87171;
  font-size: 0.83rem;
  margin-bottom: 12px;
}

.btn-primary {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  border: none; border-radius: 8px;
  padding: 10px 20px;
  color: white; font-family: inherit; font-size: 0.9rem; font-weight: 600;
  cursor: pointer; transition: opacity 0.2s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary:hover:not(:disabled) { opacity: 0.9; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
.spinner-sm { width: 12px; height: 12px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Links List */
.links-list { display: flex; flex-direction: column; gap: 10px; }

.link-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: border-color 0.2s;
}
.link-card:hover { border-color: #2e2e3e; }

.link-icon { font-size: 1.4rem; flex-shrink: 0; }
.link-info { flex: 1; min-width: 0; }
.link-title { font-weight: 500; font-size: 0.95rem; }
.link-url { font-size: 0.78rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.link-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.toggle { position: relative; display: inline-block; width: 40px; height: 22px; cursor: pointer; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
  position: absolute; inset: 0;
  background: #1e1e2e; border-radius: 22px;
  transition: background 0.2s;
}
.toggle-slider::before {
  content: '';
  position: absolute;
  width: 16px; height: 16px;
  left: 3px; top: 3px;
  background: white; border-radius: 50%;
  transition: transform 0.2s;
}
.toggle input:checked + .toggle-slider { background: #7c6af7; }
.toggle input:checked + .toggle-slider::before { transform: translateX(18px); }

.btn-icon {
  background: none; border: none;
  cursor: pointer; font-size: 1rem;
  opacity: 0.5; transition: opacity 0.2s;
  padding: 4px;
}
.btn-icon:hover { opacity: 1; }
.btn-delete:hover { opacity: 1; }

.btn-confirm-delete {
  background: rgba(248,113,113,0.15);
  border: 1px solid rgba(248,113,113,0.3);
  border-radius: 6px;
  padding: 4px 10px;
  color: #f87171;
  font-family: inherit;
  font-size: 0.78rem;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-confirm-delete:hover { background: rgba(248,113,113,0.25); }

.btn-cancel-delete {
  background: none;
  border: 1px solid #2e2e3e;
  border-radius: 6px;
  padding: 4px 10px;
  color: #666;
  font-family: inherit;
  font-size: 0.78rem;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
  white-space: nowrap;
}
.btn-cancel-delete:hover { border-color: #666; color: #e8e8f0; }

/* Edit Form */
.edit-form {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.edit-row { display: flex; gap: 8px; }

.edit-icon-input {
  width: 56px;
  flex-shrink: 0;
  padding: 8px 10px;
  text-align: center;
  border-radius: 8px;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
}
.edit-icon-input:focus { border-color: #7c6af7; }

.edit-title-input, .edit-url-input {
  padding: 8px 12px;
  border-radius: 8px;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
  width: 100%;
}
.edit-title-input:focus, .edit-url-input:focus { border-color: #7c6af7; }

.edit-actions { display: flex; gap: 8px; }

.btn-save {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  border: none; border-radius: 7px;
  padding: 7px 16px;
  color: white; font-family: inherit; font-size: 0.85rem; font-weight: 600;
  cursor: pointer; transition: opacity 0.2s;
  display: flex; align-items: center; gap: 6px;
}
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-save:hover:not(:disabled) { opacity: 0.9; }

.btn-cancel-edit {
  background: none;
  border: 1px solid #2e2e3e;
  border-radius: 7px;
  padding: 7px 14px;
  color: #666;
  font-family: inherit;
  font-size: 0.85rem;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
.btn-cancel-edit:hover { border-color: #666; color: #e8e8f0; }

.loading-state, .empty-state {
  text-align: center; color: #666; padding: 48px 0; font-size: 0.9rem;
}
.empty-state.small { padding: 16px 0; }

/* Analytics */
.stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }

.stat-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 16px;
  padding: 20px 24px;
}

.stat-value { font-size: 2rem; font-weight: 700; color: #e8e8f0; }
.stat-label { font-size: 0.8rem; color: #666; margin-top: 4px; }

.section-title { font-size: 0.9rem; font-weight: 600; color: #a0a0b0; margin-bottom: 12px; }

.bar-chart { display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
.bar-row { display: flex; align-items: center; gap: 10px; }
.bar-label { font-size: 0.82rem; color: #a0a0b0; width: 120px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bar-track { flex: 1; background: #1e1e2e; border-radius: 4px; height: 8px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(135deg, #7c6af7, #e96af5); border-radius: 4px; transition: width 0.5s ease; }
.bar-count { font-size: 0.82rem; color: #666; width: 28px; text-align: right; flex-shrink: 0; }

/* Phone Preview */
.preview-panel {
  background: #0d0d14;
  border-left: 1px solid #1e1e2e;
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.preview-title { font-size: 0.78rem; color: #666; font-weight: 500; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.08em; }

.phone-link { display: block; text-decoration: none; border-radius: 32px; transition: transform 0.2s, box-shadow 0.2s; }
.phone-link:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(124, 106, 247, 0.2); }

.phone {
  width: 200px;
  background: #111118;
  border: 2px solid #2e2e3e;
  border-radius: 32px;
  padding: 20px 16px;
  box-shadow: 0 0 0 4px #0a0a0f, 0 20px 60px rgba(0,0,0,0.5);
}

.phone-screen { display: flex; flex-direction: column; align-items: center; }

.preview-avatar {
  width: 48px; height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1.1rem;
  margin-bottom: 8px;
}

.preview-avatar-img {
  object-fit: cover;
  background: none;
}

.preview-name { font-size: 0.85rem; font-weight: 600; color: #e8e8f0; }
.preview-handle { font-size: 0.72rem; color: #666; margin-bottom: 14px; }

.preview-links { width: 100%; display: flex; flex-direction: column; gap: 6px; }

.preview-link {
  background: #1e1e2e;
  border-radius: 8px;
  padding: 7px 10px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
}

.preview-link-title { color: #e8e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.preview-empty { font-size: 0.75rem; color: #666; text-align: center; padding: 12px 0; }

/* Logout modal */
.modal-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}

.modal {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 20px;
  padding: 32px;
  width: 100%;
  max-width: 360px;
}

.modal h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; }
.modal p { color: #666; font-size: 0.9rem; margin-bottom: 24px; }

.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

.btn-modal-cancel {
  background: none;
  border: 1px solid #2e2e3e;
  border-radius: 10px;
  padding: 9px 20px;
  color: #666;
  font-family: inherit;
  font-size: 0.9rem;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
.btn-modal-cancel:hover { border-color: #666; color: #e8e8f0; }

.btn-modal-confirm {
  background: rgba(248,113,113,0.15);
  border: 1px solid rgba(248,113,113,0.3);
  border-radius: 10px;
  padding: 9px 20px;
  color: #f87171;
  font-family: inherit;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-modal-confirm:hover { background: rgba(248,113,113,0.25); }

/* Transitions */
.slide-enter-active, .slide-leave-active { transition: all 0.25s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-10px); }

.list-enter-active, .list-leave-active { transition: all 0.25s ease; }
.list-enter-from { opacity: 0; transform: translateX(-16px); }
.list-leave-to { opacity: 0; transform: translateX(16px); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

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
        <button
          :class="['nav-btn', activeTab === 'profile' && 'active']"
          @click="activeTab = 'profile'"
        >
          <span class="nav-icon">👤</span> Profile
        </button>
        <button
          v-if="user?.is_admin"
          :class="['nav-btn', activeTab === 'admin' && 'active']"
          @click="activeTab = 'admin'; fetchAdminData()"
        >
          <span class="nav-icon">🛡</span> Admin
        </button>
        <a href="/app/guide" class="nav-btn">
          <span class="nav-icon">📖</span> Guide
        </a>
      </nav>

      <div class="sidebar-user">
        <button class="user-avatar-btn" title="Change avatar" aria-label="Change avatar" @click="$refs.avatarInput.click()">
          <img v-if="user?.avatar" :src="user.avatar" class="user-avatar-img" alt="Your avatar" />
          <div v-else class="user-avatar">{{ userInitial }}</div>
          <div class="avatar-overlay">📷</div>
        </button>
        <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden-input" @change="handleAvatarUpload" />
        <div class="user-info">
          <div class="user-name">{{ user?.name }} <span v-if="user?.plan === 'pro'" class="plan-badge">PRO</span></div>
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

      <!-- Email verification banner -->
      <div v-if="user && !user.email_verified_at" class="verify-banner">
        <span>📧 Please verify your email address to unlock all features.</span>
        <button class="verify-link" :disabled="sendingVerification" @click="sendVerificationEmail">
          {{ verificationSent ? 'Email sent!' : (sendingVerification ? 'Sending…' : 'Resend verification email') }}
        </button>
      </div>

      <!-- Links Tab -->
      <div v-if="activeTab === 'links'" class="panel">
        <div class="panel-header">
          <h2>My Links <span class="link-limit-badge">{{ links.length }}/{{ user?.plan === 'pro' || user?.is_admin ? '∞' : 10 }}</span></h2>
          <div style="display:flex;gap:8px">
            <button class="btn-add" @click="addingHeader = !addingHeader; showAddForm = false; showTipJarForm = false">
              {{ addingHeader ? '✕ Cancel' : '+ Section' }}
            </button>
            <button class="btn-add" @click="showTipJarForm = !showTipJarForm; showAddForm = false; addingHeader = false">
              {{ showTipJarForm ? '✕ Cancel' : '💰 Tip Jar' }}
            </button>
            <button class="btn-add" @click="showFileForm = !showFileForm; showAddForm = false; addingHeader = false; showTipJarForm = false">
              {{ showFileForm ? '✕ Cancel' : '📄 File' }}
            </button>
            <button class="btn-add" @click="showAddForm = !showAddForm; addingHeader = false; showTipJarForm = false; showFileForm = false">
              {{ showAddForm ? '✕ Cancel' : '+ Add Link' }}
            </button>
          </div>
        </div>

        <Transition name="slide">
          <form v-if="addingHeader" class="add-form" @submit.prevent="handleAddHeader">
            <div class="field">
              <label>Section Title</label>
              <input v-model="newHeaderTitle" placeholder="Social Media" required />
            </div>
            <button type="submit" :disabled="addLoading" class="btn-primary">
              <span v-if="addLoading" class="spinner" />
              <span v-else>Add Section</span>
            </button>
          </form>
        </Transition>

        <Transition name="slide">
          <form v-if="showTipJarForm" class="add-form" @submit.prevent="handleAddTipJar">
            <div class="field">
              <label>Tip Jar Title</label>
              <input v-model="tipJarForm.title" placeholder="Support my work ☕" required />
            </div>
            <div v-if="addError" class="error-box">{{ addError }}</div>
            <button type="submit" :disabled="addLoading" class="btn-primary">
              <span v-if="addLoading" class="spinner" />
              <span v-else>Add Tip Jar</span>
            </button>
          </form>
        </Transition>

        <Transition name="slide">
          <form v-if="showFileForm" class="add-form" @submit.prevent="handleAddFileLink">
            <div class="field">
              <label>Title</label>
              <input v-model="fileForm.title" placeholder="My PDF Document" required />
            </div>
            <div class="field">
              <label>File <span class="field-hint">(PDF, images, docs, zip — max 20 MB)</span></label>
              <input type="file" ref="fileInputRef" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif,.webp,.mp3,.mp4,.mov" required class="file-input" />
            </div>
            <div v-if="addError" class="error-box">{{ addError }}</div>
            <button type="submit" :disabled="fileUploading" class="btn-primary">
              <span v-if="fileUploading" class="spinner" />
              <span v-else>Upload &amp; Add</span>
            </button>
          </form>
        </Transition>

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
              <div class="url-with-fetch">
                <input v-model="newLink.url" type="text" placeholder="example.com" required @input="autoFillIcon" />
                <button type="button" class="btn-fetch-og" :disabled="ogFetching || !newLink.url" @click="fetchOgForNew">
                  <span v-if="ogFetching" class="spinner spinner-sm" />
                  <span v-else>Fetch Preview</span>
                </button>
              </div>
            </div>
            <div v-if="newLink.og_image" class="og-preview">
              <img :src="newLink.og_image" class="og-preview-img" alt="Preview" />
              <button type="button" class="og-remove" @click="newLink.og_image = ''">✕</button>
            </div>
            <div v-if="addError" class="error-box">{{ addError }}</div>
            <button type="submit" :disabled="addLoading" class="btn-primary">
              <span v-if="addLoading" class="spinner" />
              <span v-else>Add Link</span>
            </button>
          </form>
        </Transition>

        <div v-if="linksLoading" class="loading-state">Loading links…</div>

        <div class="links-list">
          <div
            v-for="link in links"
            :key="link.id"
            class="link-card"
            :class="{ 'drag-over': dragOverId === link.id }"
            draggable="true"
            @dragstart="onDragStart(link)"
            @dragover.prevent="onDragOver(link)"
            @drop.prevent="onDrop"
            @dragend="onDragEnd"
          >

            <!-- Edit mode -->
            <form v-if="editingId === link.id" class="edit-form" @submit.prevent="saveEdit(link)">
              <div class="edit-row">
                <input v-if="!link.is_header" v-model="editForm.icon" class="edit-icon-input" placeholder="🔗" maxlength="10" title="Icon emoji" aria-label="Link icon" />
                <input v-model="editForm.title" class="edit-title-input" placeholder="Title" required aria-label="Link title" />
              </div>
              <template v-if="!link.is_header">
                <input v-model="editForm.url" type="text" placeholder="example.com" required class="edit-url-input" aria-label="Link URL" />
                <div class="schedule-row">
                  <div class="schedule-field">
                    <label>Show from</label>
                    <input v-model="editForm.starts_at" type="datetime-local" class="edit-url-input" aria-label="Start date" />
                  </div>
                  <div class="schedule-field">
                    <label>Show until</label>
                    <input v-model="editForm.ends_at" type="datetime-local" class="edit-url-input" aria-label="End date" />
                  </div>
                </div>
                <input v-model="editForm.password" type="text" placeholder="Password protect (leave blank for none)" class="edit-url-input" aria-label="Link password" />
                <input v-model.number="editForm.max_clicks" type="number" min="1" placeholder="Max clicks (leave blank for unlimited)" class="edit-url-input" aria-label="Max clicks" />
                <div class="og-edit-row">
                  <input v-model="editForm.og_image" type="text" placeholder="Preview image URL (or use Fetch)" class="edit-url-input" aria-label="OG image URL" />
                  <button type="button" class="btn-fetch-og-sm" :disabled="ogFetching" @click="fetchOgForEdit">
                    <span v-if="ogFetching" class="spinner spinner-sm" />
                    <span v-else>Fetch</span>
                  </button>
                </div>
                <img v-if="editForm.og_image" :src="editForm.og_image" class="og-preview-img" alt="Preview" style="max-height:60px;border-radius:6px;margin-top:4px;" />
                <details class="utm-details">
                  <summary class="utm-summary">UTM / Affiliate tracking</summary>
                  <div class="utm-grid">
                    <input v-model="editForm.utm_params.source" placeholder="utm_source (e.g. newsletter)" class="edit-url-input" />
                    <input v-model="editForm.utm_params.medium" placeholder="utm_medium (e.g. email)" class="edit-url-input" />
                    <input v-model="editForm.utm_params.campaign" placeholder="utm_campaign (e.g. spring-sale)" class="edit-url-input" />
                    <input v-model="editForm.utm_params.term" placeholder="utm_term (optional)" class="edit-url-input" />
                    <input v-model="editForm.utm_params.content" placeholder="utm_content (optional)" class="edit-url-input" />
                  </div>
                </details>
              </template>
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
              <div class="drag-handle" title="Drag to reorder" aria-label="Drag to reorder">⠿</div>
              <template v-if="link.is_header">
                <div class="link-info" style="flex:1">
                  <div class="link-title" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:#666">— {{ link.title }} —</div>
                </div>
              </template>
              <template v-else-if="link.type === 'tip_jar'">
                <div class="link-icon">💰</div>
                <div class="link-info">
                  <div class="link-title">{{ link.title }}</div>
                  <div class="link-url" style="color:#a090f5;">Tip Jar widget</div>
                </div>
              </template>
              <template v-else-if="link.type === 'file'">
                <div class="link-icon">📄</div>
                <div class="link-info">
                  <div class="link-title">{{ link.title }}</div>
                  <div class="link-url">{{ link.file_path ? 'File uploaded' : 'No file yet' }}</div>
                </div>
                <label class="btn-icon btn-upload" :title="'Upload file for ' + link.title" :for="'file-upload-' + link.id" style="cursor:pointer;">📤
                  <input
                    :id="'file-upload-' + link.id"
                    type="file"
                    class="hidden-file-input"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif,.webp,.mp3,.mp4,.mov"
                    @change="handleFileUpload(link, $event)"
                  />
                </label>
              </template>
              <template v-else>
                <img v-if="link.og_image" :src="link.og_image" class="link-og-thumb" :alt="link.title" />
                <div v-else class="link-icon">{{ link.icon || '🔗' }}</div>
                <div class="link-info">
                  <div class="link-title">{{ link.title }} <span v-if="link.is_password_protected" title="Password protected" style="font-size:0.75em;opacity:0.6">🔒</span></div>
                  <div class="link-url">{{ link.url }}</div>
                </div>
              </template>
              <div class="link-actions">
                <template v-if="!link.is_header">
                  <label class="toggle" :title="link.is_active ? 'Deactivate link' : 'Activate link'" :aria-label="link.is_active ? 'Deactivate link' : 'Activate link'">
                    <input type="checkbox" :checked="link.is_active" @change="toggleActive(link)" />
                    <span class="toggle-slider" />
                  </label>
                  <button
                    class="btn-icon"
                    :title="link.is_pinned ? 'Unpin link' : 'Pin link'"
                    :aria-label="link.is_pinned ? 'Unpin link' : 'Pin link'"
                    :class="{ 'btn-pinned': link.is_pinned }"
                    @click="togglePin(link)"
                  >📌</button>
                </template>
                <button
                  class="btn-icon"
                  title="Edit"
                  aria-label="Edit"
                  @click="startEdit(link)"
                >✏️</button>
                <button
                  v-if="deletingId !== link.id"
                  class="btn-icon btn-delete"
                  title="Delete"
                  aria-label="Delete"
                  @click="deletingId = link.id"
                >🗑</button>
                <template v-else>
                  <button class="btn-confirm-delete" @click="deleteLink(link.id)" title="Confirm delete">Yes, delete</button>
                  <button class="btn-cancel-delete" @click="deletingId = null" title="Cancel">Cancel</button>
                </template>
              </div>
            </template>

          </div>
        </div>

        <div v-if="!linksLoading && links.length === 0" class="empty-state">
          No links yet. Add your first link above!
        </div>
      </div>

      <!-- Analytics Tab -->
      <div v-if="activeTab === 'analytics'" class="panel">
        <div class="panel-header">
          <h2>Analytics</h2>
          <div style="display:flex;gap:8px;align-items:center">
            <select v-model="analyticsDays" class="days-select" @change="fetchAnalytics">
              <option :value="7">Last 7 days</option>
              <option :value="14">Last 14 days</option>
              <option :value="30">Last 30 days</option>
              <option :value="90">Last 90 days</option>
              <option :value="365">Last year</option>
            </select>
            <button class="btn-add" @click="fetchAnalytics" title="Refresh analytics">↻ Refresh</button>
            <button class="btn-add" title="Export CSV" @click="downloadCsv">↓ Export CSV</button>
          </div>
        </div>

        <div v-if="analyticsLoading" class="loading-state">Loading analytics…</div>

        <template v-else>
          <div class="stat-grid">
            <div class="stat-card">
              <div class="stat-value">{{ analytics.total_views ?? 0 }}</div>
              <div class="stat-label">Profile Views</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ analytics.total_clicks ?? 0 }}</div>
              <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ activeLinks.length }}</div>
              <div class="stat-label">Active Links</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ overallCtr }}%</div>
              <div class="stat-label">Overall CTR</div>
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
              <div class="bar-ctr" :title="`CTR: ${ctr(item.clicks_count)}%`">{{ ctr(item.clicks_count) }}%</div>
            </div>
          </div>

          <h3 class="section-title">Device Breakdown</h3>
          <div v-if="totalDeviceClicks === 0" class="empty-state small">No click data yet.</div>
          <div v-else class="bar-chart">
            <div v-for="(count, device) in analytics.devices" :key="device" class="bar-row">
              <div class="bar-label">{{ device }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: deviceBarWidth(count) }" />
              </div>
              <div class="bar-count">{{ count }}</div>
            </div>
          </div>

          <h3 class="section-title">Browser Breakdown</h3>
          <div v-if="totalBrowserClicks === 0" class="empty-state small">No browser data yet.</div>
          <div v-else class="bar-chart">
            <div v-for="(count, browser) in analytics.browsers" :key="browser" class="bar-row">
              <div class="bar-label">{{ browser }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: browserBarWidth(count) }" />
              </div>
              <div class="bar-count">{{ count }}</div>
            </div>
          </div>

          <h3 class="section-title">Top Countries</h3>
          <div v-if="(analytics.countries || []).length === 0" class="empty-state small">No location data yet.</div>
          <div v-else class="bar-chart">
            <div v-for="row in analytics.countries" :key="row.country" class="bar-row">
              <div class="bar-label">{{ row.country }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: countryBarWidth(row.count) }" />
              </div>
              <div class="bar-count">{{ row.count }}</div>
            </div>
          </div>

          <h3 class="section-title">Top Referrers</h3>
          <div v-if="(analytics.referrers || []).length === 0" class="empty-state small">No referrer data yet.</div>
          <div v-else class="bar-chart">
            <div v-for="ref in analytics.referrers" :key="ref.referrer" class="bar-row">
              <div class="bar-label" :title="ref.referrer">{{ ref.referrer }}</div>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: referrerBarWidth(ref.count) }" />
              </div>
              <div class="bar-count">{{ ref.count }}</div>
            </div>
          </div>

          <h3 class="section-title">Last {{ analyticsDays }} Days</h3>
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

          <h3 class="section-title">Peak Hours</h3>
          <div v-if="(analytics.peak_hours || []).length === 0" class="empty-state small">No data yet.</div>
          <div v-else class="peak-hours-grid">
            <div
              v-for="h in peakHoursGrid"
              :key="h.hour"
              class="peak-hour-cell"
              :style="{ opacity: h.opacity }"
              :title="`${h.label}: ${h.clicks} clicks`"
            >
              <div class="peak-hour-bar" :style="{ height: h.barHeight }" />
              <div class="peak-hour-label">{{ h.label }}</div>
            </div>
          </div>
        </template>
      </div>

      <!-- Profile Tab -->
      <div v-if="activeTab === 'profile'" class="panel">
        <div class="panel-header">
          <h2>Profile</h2>
        </div>
        <form class="profile-form" @submit.prevent="saveProfile">
          <div class="field">
            <label>Name</label>
            <input v-model="profileForm.name" type="text" placeholder="Your name" required />
          </div>
          <div class="field">
            <label>Bio</label>
            <textarea v-model="profileForm.bio" placeholder="Tell visitors a bit about yourself…" rows="3" class="bio-input" />
          </div>

          <div class="field">
            <label>Badges</label>
            <label class="badge-toggle">
              <input type="checkbox" v-model="profileForm.badge_available_for_hire" />
              <span>Available for hire</span>
            </label>
          </div>

          <div class="field">
            <label>Custom Domain <span class="field-hint">Pro only — point your domain's DNS to this server first</span></label>
            <input v-model="profileForm.custom_domain" type="text" placeholder="yourname.com" />
          </div>

          <div class="field">
            <label>Theme</label>
            <div class="theme-presets">
              <button
                v-for="preset in themePresets"
                :key="preset.name"
                type="button"
                class="theme-dot"
                :style="{ background: preset.accent }"
                :title="preset.name"
                :class="{ 'theme-dot-active': profileForm.theme?.accent === preset.accent }"
                @click="profileForm.theme = { ...preset }"
              />
              <button type="button" class="theme-dot theme-dot-custom" title="Custom colours" @click="showCustomTheme = !showCustomTheme">+</button>
            </div>
            <div v-if="showCustomTheme" class="custom-theme-row">
              <div class="color-field">
                <label>Accent</label>
                <input v-model="profileForm.theme.accent" type="color" />
              </div>
              <div class="color-field">
                <label>Card</label>
                <input v-model="profileForm.theme.card" type="color" />
              </div>
              <div class="color-field">
                <label>Text</label>
                <input v-model="profileForm.theme.text" type="color" />
              </div>
            </div>
          </div>

          <div v-if="profileError" class="error-box">{{ profileError }}</div>
          <button type="submit" :disabled="profileLoading" class="btn-primary">
            <span v-if="profileLoading" class="spinner" />
            <span v-else>Save changes</span>
          </button>
        </form>

        <div class="api-key-section">
          <h3 class="section-title" style="margin-bottom:8px">Public API Key</h3>
          <p style="font-size:0.82rem;color:#666;margin-bottom:12px">Use this key to access your data from external apps. Send it as the <code>X-API-Key</code> header.</p>
          <div v-if="user?.api_key" class="api-key-display">
            <code class="api-key-value">{{ user.api_key }}</code>
            <button class="btn-add" style="font-size:0.78rem" @click="revokeApiKey">Revoke</button>
          </div>
          <button v-else class="btn-add" @click="generateApiKey">Generate API key</button>
          <div style="font-size:0.78rem;color:#555;margin-top:8px" v-if="user?.api_key">
            GET /api/v1/links &nbsp;·&nbsp; GET /api/v1/analytics
          </div>
        </div>

        <div class="api-key-section">
          <h3 class="section-title" style="margin-bottom:8px">Webhooks</h3>
          <p style="font-size:0.82rem;color:#666;margin-bottom:12px">Receive an HTTP POST when a link is clicked. Optionally use a secret to verify the signature.</p>

          <div class="webhook-list">
            <div v-for="wh in webhooks" :key="wh.id" class="webhook-row">
              <div class="webhook-url">{{ wh.url }}</div>
              <div class="webhook-event">{{ wh.event }}</div>
              <button class="btn-icon btn-delete" @click="deleteWebhook(wh.id)">🗑</button>
            </div>
            <div v-if="webhooks.length === 0" class="empty-state small">No webhooks yet.</div>
          </div>

          <form class="webhook-form" @submit.prevent="addWebhook">
            <input v-model="newWebhook.url" type="text" placeholder="https://yoursite.com/webhook" required class="edit-url-input" />
            <input v-model="newWebhook.secret" type="text" placeholder="Secret (optional)" class="edit-url-input" style="margin-top:6px" />
            <button type="submit" class="btn-add" style="margin-top:8px">+ Add Webhook</button>
          </form>
        </div>

        <div class="danger-zone">
          <h3 class="danger-title">Danger Zone</h3>
          <p class="danger-desc">Permanently delete your account and all your links. This cannot be undone.</p>
          <button v-if="!showDeleteConfirm" class="btn-danger" @click="showDeleteConfirm = true">Delete account</button>
          <div v-else class="delete-confirm">
            <input v-model="deletePassword" type="password" placeholder="Enter your password to confirm" class="delete-input" />
            <div v-if="deleteError" class="error-box">{{ deleteError }}</div>
            <div class="delete-actions">
              <button class="btn-danger-confirm" :disabled="deleteLoading" @click="handleDeleteAccount">
                <span v-if="deleteLoading" class="spinner spinner-sm" />
                <span v-else>Yes, delete everything</span>
              </button>
              <button class="btn-cancel-edit" @click="showDeleteConfirm = false; deletePassword = ''">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Tab -->
      <div v-if="activeTab === 'admin'" class="panel">
        <div class="panel-header">
          <h2>Admin Dashboard</h2>
          <button class="btn-add" @click="fetchAdminData">↻ Refresh</button>
        </div>

        <div v-if="adminLoading" class="loading-state">Loading…</div>
        <template v-else>
          <div class="stat-grid" style="margin-bottom:28px">
            <div class="stat-card"><div class="stat-value">{{ adminStats.total_users ?? 0 }}</div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-value">{{ adminStats.total_links ?? 0 }}</div><div class="stat-label">Links</div></div>
            <div class="stat-card"><div class="stat-value">{{ adminStats.total_clicks ?? 0 }}</div><div class="stat-label">Clicks</div></div>
            <div class="stat-card"><div class="stat-value">{{ adminStats.total_views ?? 0 }}</div><div class="stat-label">Views</div></div>
            <div class="stat-card"><div class="stat-value">{{ adminStats.new_users_today ?? 0 }}</div><div class="stat-label">New today</div></div>
          </div>

          <h3 class="section-title">Users</h3>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Name</th><th>Username</th><th>Email</th><th>Links</th><th>Views</th><th>Plan</th><th>Verified</th><th>Admin</th><th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in adminUsers" :key="u.id">
                  <td>{{ u.name }}</td>
                  <td>@{{ u.username }}</td>
                  <td>{{ u.email }}</td>
                  <td>{{ u.links_count }}</td>
                  <td>{{ u.profile_views_count }}</td>
                  <td>
                    <select :value="u.plan" @change="adminToggle(u, 'plan', $event)" style="background:#0d0d15;border:1px solid #2a2a3a;border-radius:4px;color:#a0a0b0;font-size:0.75rem;padding:2px 6px">
                      <option value="free">Free</option>
                      <option value="pro">Pro</option>
                    </select>
                  </td>
                  <td>
                    <input type="checkbox" :checked="u.badge_verified" @change="adminToggle(u, 'badge_verified', $event)" />
                  </td>
                  <td>
                    <input type="checkbox" :checked="u.is_admin" @change="adminToggle(u, 'is_admin', $event)" />
                  </td>
                  <td>
                    <button class="btn-icon btn-delete" @click="adminDeleteUser(u)">🗑</button>
                  </td>
                </tr>
              </tbody>
            </table>
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
import { detectSocialIcon } from '../composables/useSocialIcon'

const router = useRouter()
const { user, logout, updateAvatar } = useAuth()
const { post, put, del, loading: addLoading, error: addErr } = useApi()
const { get: getLinks, loading: linksLoading } = useApi()
const { get: getAnalytics, loading: analyticsLoading } = useApi()
const { get: getWebhooks, post: postWebhook, del: delWebhook } = useApi()
const { get: getAdminStats, patch: patchAdmin, del: delAdmin } = useApi()
const { put: putEdit, loading: editLoading } = useApi()
const { patch: patchProfile, loading: profileLoading } = useApi()
const toast = useToast()

const activeTab    = ref('links')
const showAddForm  = ref(false)
const addingHeader = ref(false)
const showTipJarForm = ref(false)
const tipJarForm = ref({ title: 'Support my work ☕' })
const showFileForm = ref(false)
const fileForm = ref({ title: '' })
const fileInputRef = ref(null)
const fileUploading = ref(false)
const newHeaderTitle = ref('')
const confirmLogout = ref(false)
const links        = ref([])
const analytics    = ref({})
const analyticsDays = ref(7)
const sendingVerification = ref(false)
const verificationSent = ref(false)
const addError     = ref('')
const deletingId   = ref(null)
const editingId    = ref(null)
const editForm     = ref({ title: '', url: '', icon: '', utm_params: { source: '', medium: '', campaign: '', term: '', content: '' } })

const draggingId = ref(null)
const dragOverId = ref(null)

const profileForm = ref({ name: '', bio: '', theme: {}, badge_available_for_hire: false, custom_domain: '' })
const adminStats = ref({})
const adminUsers = ref([])
const adminLoading = ref(false)
const webhooks = ref([])
const newWebhook = ref({ url: '', secret: '' })
const showCustomTheme = ref(false)

const themePresets = [
  { name: 'Purple (default)', accent: '#7c6af7', card: '#111118', text: '#e8e8f0' },
  { name: 'Emerald', accent: '#10b981', card: '#0d1a14', text: '#d1fae5' },
  { name: 'Rose', accent: '#f43f5e', card: '#1a0d10', text: '#ffe4e6' },
  { name: 'Sky', accent: '#0ea5e9', card: '#0d1520', text: '#e0f2fe' },
  { name: 'Amber', accent: '#f59e0b', card: '#1a1500', text: '#fef3c7' },
  { name: 'Slate', accent: '#94a3b8', card: '#0f172a', text: '#e2e8f0' },
]
const profileError = ref('')
const showDeleteConfirm = ref(false)
const deletePassword = ref('')
const deleteError = ref('')
const { del: delAccount, loading: deleteLoading } = useApi()

const newLink = ref({ title: '', url: '', icon: '', og_image: '' })
const ogFetching = ref(false)

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

const totalDeviceClicks = computed(() => Object.values(analytics.value.devices || {}).reduce((a, b) => a + b, 0))
function deviceBarWidth(count) { return `${Math.round((count / (totalDeviceClicks.value || 1)) * 100)}%` }

const totalBrowserClicks = computed(() => Object.values(analytics.value.browsers || {}).reduce((a, b) => a + b, 0))
function browserBarWidth(count) { return `${Math.round((count / (totalBrowserClicks.value || 1)) * 100)}%` }

const maxCountry = computed(() => {
  const counts = (analytics.value.countries || []).map(r => r.count)
  return counts.length ? Math.max(...counts) : 1
})
function countryBarWidth(count) { return `${Math.round((count / maxCountry.value) * 100)}%` }

function barWidth(count) { return `${Math.round((count / maxClicks.value) * 100)}%` }
function dayBarWidth(count) { return `${Math.round((count / maxDay.value) * 100)}%` }
const maxReferrer = computed(() => {
  const counts = (analytics.value.referrers || []).map(r => r.count)
  return counts.length ? Math.max(...counts) : 1
})
function referrerBarWidth(count) { return `${Math.round((count / maxReferrer.value) * 100)}%` }

const peakHoursGrid = computed(() => {
  const raw = analytics.value.peak_hours || []
  const map = {}
  raw.forEach(r => { map[r.hour] = r.clicks })
  const maxClk = Math.max(...Object.values(map), 1)
  return Array.from({ length: 24 }, (_, i) => {
    const clicks = map[i] || 0
    const ratio = clicks / maxClk
    return {
      hour: i,
      clicks,
      label: i === 0 ? '12a' : i < 12 ? `${i}a` : i === 12 ? '12p' : `${i - 12}p`,
      barHeight: `${Math.max(ratio * 40, 2)}px`,
      opacity: 0.2 + ratio * 0.8,
    }
  })
})

function ctr(clicks) {
  const views = analytics.value.total_views || 0
  if (!views) { return '0.0' }
  return ((clicks / views) * 100).toFixed(1)
}
const overallCtr = computed(() => ctr(analytics.value.total_clicks || 0))

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { month: 'short', day: 'numeric' })
}

function autoFillIcon() {
  if (!newLink.value.icon) {
    const detected = detectSocialIcon(newLink.value.url)
    if (detected) { newLink.value.icon = detected }
  }
}

function startEdit(link) {
  editingId.value = link.id
  editForm.value = link.is_header
    ? { title: link.title }
    : {
        title: link.title,
        url: link.url,
        icon: link.icon || '',
        og_image: link.og_image || '',
        utm_params: link.utm_params ? { ...link.utm_params } : { source: '', medium: '', campaign: '', term: '', content: '' },
        starts_at: link.starts_at ? link.starts_at.slice(0, 16) : '',
        ends_at:   link.ends_at   ? link.ends_at.slice(0, 16)   : '',
        password: '',
        max_clicks: link.max_clicks || null,
      }
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

async function fetchOgForNew() {
  if (!newLink.value.url) { return }
  ogFetching.value = true
  try {
    const data = await post('/links/fetch-og', { url: newLink.value.url.startsWith('http') ? newLink.value.url : 'https://' + newLink.value.url })
    if (data.og_image) { newLink.value.og_image = data.og_image }
    if (data.og_title && !newLink.value.title) { newLink.value.title = data.og_title }
  } catch {} finally {
    ogFetching.value = false
  }
}

async function fetchOgForEdit() {
  if (!editForm.value.url) { return }
  ogFetching.value = true
  try {
    const data = await post('/links/fetch-og', { url: editForm.value.url })
    if (data.og_image) { editForm.value.og_image = data.og_image }
  } catch {} finally {
    ogFetching.value = false
  }
}

async function fetchLinks() {
  links.value = await getLinks('/links')
}

async function fetchAnalytics() {
  analytics.value = await getAnalytics(`/analytics?days=${analyticsDays.value}`)
}

async function downloadCsv() {
  const token = localStorage.getItem('linkdrop_token')
  const res = await fetch('/api/analytics/export', {
    headers: { Authorization: `Bearer ${token}`, Accept: 'text/csv' },
  })
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `analytics-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

async function handleAddLink() {
  addError.value = ''
  try {
    const link = await post('/links', newLink.value)
    links.value.push(link)
    newLink.value = { title: '', url: '', icon: '', og_image: '' }
    showAddForm.value = false
    toast.success('Link added')
  } catch (e) {
    addError.value = typeof addErr.value === 'string'
      ? addErr.value
      : Object.values(addErr.value || {}).flat().join(' ')
  }
}

async function handleAddHeader() {
  try {
    const link = await post('/links', { title: newHeaderTitle.value, is_header: true })
    links.value.push(link)
    newHeaderTitle.value = ''
    addingHeader.value = false
    toast.success('Section added')
  } catch (e) {
    toast.error('Failed to add section')
  }
}

async function handleAddTipJar() {
  addError.value = ''
  try {
    const link = await post('/links', { title: tipJarForm.value.title, type: 'tip_jar' })
    links.value.push(link)
    tipJarForm.value = { title: 'Support my work ☕' }
    showTipJarForm.value = false
    toast.success('Tip Jar added')
  } catch {
    addError.value = 'Failed to add Tip Jar'
  }
}

async function handleAddFileLink() {
  addError.value = ''
  const file = fileInputRef.value?.files?.[0]
  if (!file) { addError.value = 'Please select a file.'; return }

  fileUploading.value = true
  try {
    // 1. Create the link record
    const link = await post('/links', { title: fileForm.value.title, type: 'file' })
    links.value.push(link)

    // 2. Upload the file
    const token = localStorage.getItem('linkdrop_token')
    const fd = new FormData()
    fd.append('file', file)
    const res = await fetch(`/api/links/${link.id}/upload-file`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      body: fd,
    })
    const data = await res.json()
    if (!res.ok) { throw new Error(data.message || 'Upload failed') }
    Object.assign(link, data)

    fileForm.value = { title: '' }
    showFileForm.value = false
    toast.success('File link added')
  } catch (e) {
    addError.value = e.message || 'Failed to add file link'
  } finally {
    fileUploading.value = false
  }
}

async function handleFileUpload(link, event) {
  const file = event.target.files?.[0]
  if (!file) { return }
  const token = localStorage.getItem('linkdrop_token')
  const fd = new FormData()
  fd.append('file', file)
  try {
    const res = await fetch(`/api/links/${link.id}/upload-file`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      body: fd,
    })
    const data = await res.json()
    if (!res.ok) { throw new Error(data.message || 'Upload failed') }
    Object.assign(link, data)
    toast.success('File updated')
  } catch (e) {
    toast.error(e.message || 'Upload failed')
  }
}

async function togglePin(link) {
  try {
    const updated = await put(`/links/${link.id}`, { is_pinned: !link.is_pinned })
    Object.assign(link, updated)
    links.value = [...links.value].sort((a, b) => b.is_pinned - a.is_pinned || a.order - b.order)
    toast.success(link.is_pinned ? 'Link pinned' : 'Link unpinned')
  } catch {
    toast.error('Failed to update link')
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

function onDragStart(link) {
  draggingId.value = link.id
}

function onDragOver(link) {
  if (draggingId.value !== link.id) {
    dragOverId.value = link.id
  }
}

function onDrop() {
  if (!draggingId.value || !dragOverId.value || draggingId.value === dragOverId.value) { return }
  const from = links.value.findIndex(l => l.id === draggingId.value)
  const to   = links.value.findIndex(l => l.id === dragOverId.value)
  const reordered = [...links.value]
  reordered.splice(to, 0, reordered.splice(from, 1)[0])
  reordered.forEach((l, i) => { l.order = i })
  links.value = reordered
  saveOrder()
}

function onDragEnd() {
  draggingId.value = null
  dragOverId.value = null
}

async function saveOrder() {
  try {
    await post('/links/reorder', { links: links.value.map(l => ({ id: l.id, order: l.order })) })
  } catch {
    toast.error('Failed to save order')
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

async function handleDeleteAccount() {
  deleteError.value = ''
  try {
    await post('/account/delete', { password: deletePassword.value })
    logout()
    router.push({ name: 'login' })
  } catch (e) {
    deleteError.value = typeof e === 'string' ? e : 'Incorrect password.'
  }
}

async function saveProfile() {
  profileError.value = ''
  try {
    const updated = await patchProfile('/profile', profileForm.value)
    user.value = { ...user.value, ...updated }
    localStorage.setItem('linkdrop_user', JSON.stringify(user.value))
    toast.success('Profile saved')
  } catch (e) {
    profileError.value = typeof e === 'string' ? e : 'Failed to save profile'
  }
}

async function fetchAdminData() {
  adminLoading.value = true
  try {
    const [stats, usersRes] = await Promise.all([
      getAdminStats('/admin/stats'),
      getAdminStats('/admin/users'),
    ])
    adminStats.value = stats
    adminUsers.value = usersRes.data || usersRes
  } catch {
    toast.error('Failed to load admin data')
  } finally {
    adminLoading.value = false
  }
}

async function adminToggle(u, field, event) {
  const value = event.target.type === 'checkbox' ? event.target.checked : event.target.value
  try {
    await patchAdmin(`/admin/users/${u.id}`, { [field]: value })
    u[field] = value
  } catch {
    if (event.target.type === 'checkbox') { event.target.checked = !value }
    toast.error('Failed to update user')
  }
}

async function adminDeleteUser(u) {
  if (!confirm(`Delete user ${u.username}? This cannot be undone.`)) { return }
  try {
    await delAdmin(`/admin/users/${u.id}`)
    adminUsers.value = adminUsers.value.filter(x => x.id !== u.id)
    adminStats.value.total_users = (adminStats.value.total_users || 1) - 1
    toast.success('User deleted')
  } catch {
    toast.error('Failed to delete user')
  }
}

async function fetchWebhooks() {
  webhooks.value = await getWebhooks('/webhooks')
}

async function addWebhook() {
  try {
    const wh = await postWebhook('/webhooks', newWebhook.value)
    webhooks.value.push(wh)
    newWebhook.value = { url: '', secret: '' }
    toast.success('Webhook added')
  } catch {
    toast.error('Failed to add webhook')
  }
}

async function deleteWebhook(id) {
  try {
    await delWebhook(`/webhooks/${id}`)
    webhooks.value = webhooks.value.filter(w => w.id !== id)
    toast.success('Webhook deleted')
  } catch {
    toast.error('Failed to delete webhook')
  }
}

async function generateApiKey() {
  try {
    const res = await post('/api-key/generate', {})
    user.value = { ...user.value, api_key: res.api_key }
    localStorage.setItem('linkdrop_user', JSON.stringify(user.value))
    toast.success('API key generated')
  } catch {
    toast.error('Failed to generate API key')
  }
}

async function revokeApiKey() {
  try {
    await post('/api-key/revoke', {})
    user.value = { ...user.value, api_key: null }
    localStorage.setItem('linkdrop_user', JSON.stringify(user.value))
    toast.success('API key revoked')
  } catch {
    toast.error('Failed to revoke API key')
  }
}

async function sendVerificationEmail() {
  sendingVerification.value = true
  try {
    await post('/email/send-verification', {})
    verificationSent.value = true
  } catch {
    toast.error('Failed to send verification email')
  } finally {
    sendingVerification.value = false
  }
}

onMounted(() => {
  fetchLinks()
  fetchAnalytics()
  fetchWebhooks()
  profileForm.value = { name: user.value?.name || '', bio: user.value?.bio || '', theme: user.value?.theme || {}, badge_available_for_hire: user.value?.badge_available_for_hire || false, custom_domain: user.value?.custom_domain || '' }
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
.plan-badge { font-size: 0.6rem; font-weight: 700; background: linear-gradient(135deg,#7c6af7,#e96af5); color: #fff; border-radius: 4px; padding: 1px 5px; vertical-align: middle; }
.link-limit-badge { font-size: 0.7rem; font-weight: 400; color: #555; margin-left: 6px; }

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

.days-select {
  background: rgba(124,106,247,0.10);
  border: 1px solid rgba(124,106,247,0.3);
  border-radius: 10px;
  padding: 7px 12px;
  color: #c8c8e0;
  font-size: 0.82rem;
  cursor: pointer;
  outline: none;
}
.days-select:hover { background: rgba(124,106,247,0.18); }

.verify-banner {
  display: flex;
  align-items: center;
  gap: 16px;
  background: rgba(249,115,22,0.1);
  border: 1px solid rgba(249,115,22,0.3);
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 20px;
  font-size: 0.875rem;
  color: #fdba74;
  flex-wrap: wrap;
}
.verify-link {
  background: transparent;
  border: 1px solid rgba(249,115,22,0.4);
  border-radius: 8px;
  padding: 6px 14px;
  color: #fb923c;
  font-size: 0.82rem;
  cursor: pointer;
  white-space: nowrap;
}
.verify-link:hover:not(:disabled) { background: rgba(249,115,22,0.1); }
.verify-link:disabled { opacity: 0.6; cursor: default; }

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

.url-with-fetch { display: flex; gap: 8px; }
.url-with-fetch input { flex: 1; }

.btn-fetch-og {
  flex-shrink: 0;
  background: rgba(124,106,247,0.15);
  border: 1px solid rgba(124,106,247,0.3);
  border-radius: 8px;
  padding: 0 12px;
  color: #a090f5;
  font-family: inherit;
  font-size: 0.8rem;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}
.btn-fetch-og:hover:not(:disabled) { background: rgba(124,106,247,0.25); }
.btn-fetch-og:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-fetch-og-sm {
  flex-shrink: 0;
  background: rgba(124,106,247,0.15);
  border: 1px solid rgba(124,106,247,0.3);
  border-radius: 6px;
  padding: 6px 10px;
  color: #a090f5;
  font-family: inherit;
  font-size: 0.78rem;
  cursor: pointer;
  white-space: nowrap;
}
.btn-fetch-og-sm:disabled { opacity: 0.5; cursor: not-allowed; }

.og-edit-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
.og-edit-row input { flex: 1; }

.og-preview { position: relative; display: inline-block; margin-bottom: 12px; }
.og-preview-img { max-height: 80px; border-radius: 8px; display: block; }
.og-remove {
  position: absolute; top: -6px; right: -6px;
  background: #2e2e3e; border: none; border-radius: 50%;
  width: 20px; height: 20px; font-size: 0.7rem;
  color: #e8e8f0; cursor: pointer; line-height: 20px;
  display: flex; align-items: center; justify-content: center;
}

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

.drag-handle {
  font-size: 1.1rem;
  color: #333;
  cursor: grab;
  padding: 0 4px;
  flex-shrink: 0;
  user-select: none;
}
.drag-handle:active { cursor: grabbing; }

.link-card.drag-over {
  border-color: #7c6af7;
  background: rgba(124, 106, 247, 0.05);
}

.link-icon { font-size: 1.4rem; flex-shrink: 0; }
.link-og-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
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
.btn-pinned { opacity: 1; }
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

.schedule-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.schedule-field label { display: block; font-size: 0.78rem; color: #666; margin-bottom: 4px; }

.utm-details { margin-top: 8px; }
.utm-summary { font-size: 0.78rem; color: #666; cursor: pointer; user-select: none; margin-bottom: 6px; }
.utm-summary:hover { color: #a090f5; }
.utm-grid { display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }

.hidden-file-input { display: none; }
.file-input {
  width: 100%;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 8px;
  padding: 10px 12px;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 0.9rem;
  cursor: pointer;
}
.file-input:focus { border-color: #7c6af7; outline: none; }
.btn-upload { font-size: 1rem; }

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

.profile-form { max-width: 480px; }

.api-key-section {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #1e1e2e;
  max-width: 480px;
}
.api-key-display { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.webhook-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.webhook-row { display: flex; align-items: center; gap: 10px; background: #0d0d15; border: 1px solid #2a2a3a; border-radius: 8px; padding: 8px 12px; }
.webhook-url { flex: 1; font-size: 0.82rem; color: #a0a0b0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.webhook-event { font-size: 0.72rem; color: #555; white-space: nowrap; }
.webhook-form { display: flex; flex-direction: column; }

.badge-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem; color: #a0a0b0; }
.badge-toggle input { width: 16px; height: 16px; accent-color: #7c6af7; cursor: pointer; }

.api-key-value {
  background: #0d0d15;
  border: 1px solid #2a2a3a;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 0.75rem;
  color: #a0a0b0;
  word-break: break-all;
  flex: 1;
}

.theme-presets { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
.theme-dot {
  width: 32px; height: 32px; border-radius: 50%; border: 2px solid transparent;
  cursor: pointer; transition: transform 0.15s, border-color 0.15s;
}
.theme-dot:hover { transform: scale(1.15); }
.theme-dot-active { border-color: #fff !important; }
.theme-dot-custom {
  background: #2a2a3a; color: #a0a0b0; font-size: 1.1rem;
  display: flex; align-items: center; justify-content: center;
}
.custom-theme-row { display: flex; gap: 20px; margin-top: 12px; flex-wrap: wrap; }
.color-field { display: flex; flex-direction: column; gap: 4px; }
.color-field label { font-size: 0.78rem; color: #666; }
.color-field input[type=color] { width: 48px; height: 32px; border: none; border-radius: 6px; cursor: pointer; }

.danger-zone {
  margin-top: 40px;
  border-top: 1px solid #2a1010;
  padding-top: 24px;
  max-width: 480px;
}
.danger-title { font-size: 0.95rem; font-weight: 600; color: #f87171; margin-bottom: 8px; }
.danger-desc { font-size: 0.85rem; color: #666; margin-bottom: 16px; }
.btn-danger {
  background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3);
  border-radius: 8px; padding: 9px 18px; color: #f87171;
  font-family: inherit; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: background 0.2s;
}
.btn-danger:hover { background: rgba(248,113,113,0.2); }
.delete-confirm { display: flex; flex-direction: column; gap: 10px; }
.delete-input {
  background: #0a0a0f; border: 1px solid rgba(248,113,113,0.3); border-radius: 8px;
  padding: 10px 12px; color: #e8e8f0; font-family: inherit; font-size: 0.9rem;
  outline: none; transition: border-color 0.2s; width: 100%;
}
.delete-input:focus { border-color: #f87171; }
.delete-actions { display: flex; gap: 10px; }
.btn-danger-confirm {
  background: #f87171; border: none; border-radius: 8px; padding: 9px 18px;
  color: white; font-family: inherit; font-size: 0.875rem; font-weight: 600;
  cursor: pointer; transition: opacity 0.2s; display: flex; align-items: center; gap: 6px;
}
.btn-danger-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-danger-confirm:hover:not(:disabled) { opacity: 0.85; }

.bio-input {
  width: 100%;
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 8px;
  padding: 10px 12px;
  color: #e8e8f0;
  font-family: inherit;
  font-size: 0.9rem;
  outline: none;
  resize: vertical;
  transition: border-color 0.2s;
}
.bio-input:focus { border-color: #7c6af7; }

.loading-state, .empty-state {
  text-align: center; color: #666; padding: 48px 0; font-size: 0.9rem;
}
.empty-state.small { padding: 16px 0; }

/* Analytics */
.stat-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 28px; }

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
.bar-ctr { font-size: 0.72rem; color: #555; width: 40px; text-align: right; flex-shrink: 0; }

.admin-table-wrap { overflow-x: auto; }
.admin-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.admin-table th { color: #666; font-weight: 500; padding: 8px 10px; text-align: left; border-bottom: 1px solid #1e1e2e; }
.admin-table td { padding: 10px; border-bottom: 1px solid #111118; color: #a0a0b0; }
.admin-table tr:hover td { background: rgba(124,106,247,0.03); }
.admin-table input[type=checkbox] { accent-color: #7c6af7; }

.peak-hours-grid { display: flex; gap: 3px; align-items: flex-end; height: 60px; margin-bottom: 28px; }
.peak-hour-cell { display: flex; flex-direction: column; align-items: center; flex: 1; cursor: default; }
.peak-hour-bar { width: 100%; background: #7c6af7; border-radius: 2px 2px 0 0; min-height: 2px; transition: height 0.4s ease; }
.peak-hour-label { font-size: 0.55rem; color: #555; margin-top: 3px; white-space: nowrap; }

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

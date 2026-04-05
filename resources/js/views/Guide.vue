<template>
  <div class="guide">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span class="logo-text">LinkDrop</span>
      </div>

      <nav class="sidebar-nav">
        <a
          v-for="section in sections"
          :key="section.id"
          :href="`#${section.id}`"
          :class="['nav-btn', activeSection === section.id && 'active']"
          @click.prevent="scrollTo(section.id)"
        >
          <span class="nav-icon">{{ section.icon }}</span> {{ section.label }}
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="/app/dashboard" class="back-btn">← Back to Dashboard</a>
      </div>
    </aside>

    <main class="main">
      <div class="guide-header">
        <h1>User Guide</h1>
        <p>Everything you need to know about using LinkDrop.</p>
      </div>

      <!-- Profile -->
      <section id="profile" class="guide-section">
        <h2><span class="section-icon">👤</span> Setting Up Your Profile</h2>
        <p>Your profile is the public page visitors see at <code>linkdrop.app/username</code>. To update it, go to the <strong>Profile</strong> tab in the dashboard.</p>
        <div class="guide-cards">
          <div class="guide-card">
            <div class="card-title">Display Name & Bio</div>
            <p>Your name appears at the top of your profile. Add a short bio to tell visitors who you are.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Avatar</div>
            <p>Click your avatar in the bottom-left of the sidebar to upload a photo. Supported formats: JPEG, PNG, GIF, WebP.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Theme</div>
            <p>Customise your profile's accent colour, background, card colour, and text colour to match your brand.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Available for Hire Badge</div>
            <p>Enable this in your profile settings to show a badge on your public profile letting people know you're open to work.</p>
          </div>
        </div>
      </section>

      <!-- Links -->
      <section id="links" class="guide-section">
        <h2><span class="section-icon">🔗</span> Managing Links</h2>
        <p>Links are the core of your profile. You can add, reorder, edit, and delete them from the <strong>Links</strong> tab.</p>

        <h3>Link Types</h3>
        <div class="guide-cards">
          <div class="guide-card">
            <div class="card-title">Regular Link</div>
            <p>A standard URL that opens in a new tab. Supports social icon detection, OG image previews, and UTM parameters.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Section Header</div>
            <p>A non-clickable label to group your links into sections. Click <strong>+ Section</strong> to add one.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Tip Jar</div>
            <p>Displays a Ko-fi donation widget with preset amounts ($1, $3, $5, $10). Click <strong>💰 Tip Jar</strong> to add one.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">File Download</div>
            <p>Upload a file (PDF, ZIP, images, etc. up to 20 MB) that visitors can download directly. Click <strong>📄 File</strong> to add one.</p>
          </div>
        </div>

        <h3>Reordering Links</h3>
        <p>Drag and drop links using the <strong>⠿</strong> handle on the left side of each link card to change their order.</p>

        <h3>Password Protection</h3>
        <p>When editing a link, enable <strong>Password Protected</strong> and set a password. Visitors will be prompted to enter it before the link opens.</p>

        <h3>Scheduling</h3>
        <p>Set a start and/or end date on any link. The link will only appear on your public profile within that date range.</p>

        <h3>OG Image Preview</h3>
        <p>When adding a URL, click <strong>Fetch Preview</strong> to automatically pull the page's Open Graph image and display it as a thumbnail on your profile.</p>

        <h3>UTM Parameters</h3>
        <p>Expand the <strong>UTM Tracking</strong> section when editing a link to append UTM parameters (source, medium, campaign, term, content) automatically to the URL when a visitor clicks it.</p>

        <h3>Embedded Media</h3>
        <p>YouTube, Spotify, and SoundCloud links are automatically embedded as players directly on your profile — no extra setup needed.</p>

        <h3>Special URL Schemes</h3>
        <div class="guide-table-wrap">
          <table class="guide-table">
            <thead>
              <tr><th>Prefix</th><th>Example</th><th>Behaviour</th></tr>
            </thead>
            <tbody>
              <tr><td><code>mailto:</code></td><td><code>mailto:you@email.com</code></td><td>Opens the visitor's email client</td></tr>
              <tr><td><code>tel:</code></td><td><code>tel:+441234567890</code></td><td>Initiates a phone call on mobile</td></tr>
              <tr><td><code>copy:</code></td><td><code>copy:your text here</code></td><td>Copies the text to the visitor's clipboard</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Analytics -->
      <section id="analytics" class="guide-section">
        <h2><span class="section-icon">📊</span> Analytics</h2>
        <p>The <strong>Analytics</strong> tab shows you how your profile is performing. All data is collected anonymously — no cookies.</p>
        <div class="guide-cards">
          <div class="guide-card">
            <div class="card-title">Total Clicks</div>
            <p>The total number of link clicks recorded across all your links.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Clicks Per Link</div>
            <p>A breakdown of how many clicks each individual link has received.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Daily Trend</div>
            <p>A bar chart showing click volume over the last 30 days.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Devices & Browsers</div>
            <p>See whether your visitors are on desktop, mobile, or tablet, and which browsers they use.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Top Countries</div>
            <p>The top 10 countries your visitors are coming from, based on IP geolocation.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Export CSV</div>
            <p>Download a full CSV export of all your click data for use in spreadsheets or external tools.</p>
          </div>
        </div>
      </section>

      <!-- Custom Domain -->
      <section id="domain" class="guide-section">
        <h2><span class="section-icon">🌐</span> Custom Domain</h2>
        <p>Point your own domain to your LinkDrop profile instead of using the default URL.</p>
        <div class="guide-steps">
          <div class="guide-step">
            <div class="step-num">1</div>
            <div>
              <strong>Enter your domain</strong>
              <p>Go to the <strong>Profile</strong> tab and enter your domain (e.g. <code>links.yourdomain.com</code>) in the Custom Domain field.</p>
            </div>
          </div>
          <div class="guide-step">
            <div class="step-num">2</div>
            <div>
              <strong>Add a CNAME record</strong>
              <p>In your domain registrar's DNS settings, add a <code>CNAME</code> record pointing your domain to the app's hostname.</p>
            </div>
          </div>
          <div class="guide-step">
            <div class="step-num">3</div>
            <div>
              <strong>Wait for propagation</strong>
              <p>DNS changes can take up to 48 hours to propagate, though it's usually much faster.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Sharing -->
      <section id="sharing" class="guide-section">
        <h2><span class="section-icon">↑</span> Sharing Your Profile</h2>
        <p>At the bottom of your public profile you'll find two quick-share tools:</p>
        <div class="guide-cards">
          <div class="guide-card">
            <div class="card-title">↑ Share Button</div>
            <p>On supported devices this opens the native share sheet. On others it copies your profile URL to the clipboard.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">⬛ QR Code</div>
            <p>Opens a high-resolution QR code you can download and print or embed in presentations and print materials.</p>
          </div>
        </div>
      </section>

      <!-- API -->
      <section id="api" class="guide-section">
        <h2><span class="section-icon">⚡</span> API Access</h2>
        <p>LinkDrop has a public API you can use to integrate your links with external tools. Go to <strong>Profile → API</strong> to generate an API key.</p>
        <div class="guide-table-wrap">
          <table class="guide-table">
            <thead>
              <tr><th>Method</th><th>Endpoint</th><th>Description</th></tr>
            </thead>
            <tbody>
              <tr><td><span class="method get">GET</span></td><td><code>/api/v1/links</code></td><td>List all your links</td></tr>
              <tr><td><span class="method get">GET</span></td><td><code>/api/v1/analytics</code></td><td>Get your analytics data</td></tr>
            </tbody>
          </table>
        </div>
        <div class="guide-callout">
          <strong>Authentication:</strong> Pass your API key in the <code>Authorization</code> header as <code>Bearer &lt;your-key&gt;</code>.
        </div>

        <h3>Webhooks</h3>
        <p>Register a webhook URL in <strong>Profile → Webhooks</strong> to receive a POST request every time a link on your profile is clicked. The payload includes the link ID, timestamp, country, device, and browser.</p>
        <div class="guide-callout">
          Optionally set a <strong>webhook secret</strong> to verify requests — LinkDrop will sign each request with an <code>X-Webhook-Signature</code> header using HMAC-SHA256.
        </div>
      </section>

      <!-- Account -->
      <section id="account" class="guide-section">
        <h2><span class="section-icon">🔒</span> Account & Security</h2>
        <div class="guide-cards">
          <div class="guide-card">
            <div class="card-title">Email Verification</div>
            <p>Verify your email address to unlock all features. Check your inbox for a verification link, or request a new one from the banner in the dashboard.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Change Password</div>
            <p>Use the <strong>Forgot Password</strong> flow on the login page to reset your password via email.</p>
          </div>
          <div class="guide-card">
            <div class="card-title">Delete Account</div>
            <p>Go to <strong>Profile → Danger Zone</strong> to permanently delete your account and all associated data.</p>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sections = [
  { id: 'profile',   icon: '👤', label: 'Profile' },
  { id: 'links',     icon: '🔗', label: 'Links' },
  { id: 'analytics', icon: '📊', label: 'Analytics' },
  { id: 'domain',    icon: '🌐', label: 'Custom Domain' },
  { id: 'sharing',   icon: '↑',  label: 'Sharing' },
  { id: 'api',       icon: '⚡', label: 'API & Webhooks' },
  { id: 'account',   icon: '🔒', label: 'Account' },
]

const activeSection = ref('profile')

function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
}

function onScroll() {
  for (const s of [...sections].reverse()) {
    const el = document.getElementById(s.id)
    if (el && el.getBoundingClientRect().top <= 120) {
      activeSection.value = s.id
      return
    }
  }
  activeSection.value = sections[0].id
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.guide {
  display: flex;
  min-height: 100vh;
  background: #0a0a12;
  color: #e8e8f0;
  font-family: inherit;
}

/* Sidebar — matches Dashboard */
.sidebar {
  width: 220px;
  min-height: 100vh;
  background: #0d0d18;
  border-right: 1px solid #1e1e2e;
  display: flex;
  flex-direction: column;
  padding: 24px 0;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  flex-shrink: 0;
}

.sidebar-logo {
  padding: 0 20px 24px;
  border-bottom: 1px solid #1e1e2e;
  margin-bottom: 16px;
}

.logo-text {
  font-size: 1.2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 12px;
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: #888;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  text-decoration: none;
  text-align: left;
}

.nav-btn:hover { background: #1a1a2e; color: #e8e8f0; }
.nav-btn.active { background: rgba(124,106,247,0.15); color: #7c6af7; }
.nav-icon { font-size: 1rem; width: 20px; text-align: center; }

.sidebar-footer {
  padding: 16px 12px 0;
  border-top: 1px solid #1e1e2e;
  margin-top: 16px;
}

.back-btn {
  display: block;
  padding: 10px 12px;
  border-radius: 10px;
  color: #666;
  font-size: 0.85rem;
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}

.back-btn:hover { background: #1a1a2e; color: #e8e8f0; }

/* Main content */
.main {
  flex: 1;
  padding: 48px 60px;
  max-width: 860px;
}

.guide-header {
  margin-bottom: 48px;
}

.guide-header h1 {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 8px;
}

.guide-header p { color: #888; font-size: 1rem; }

/* Sections */
.guide-section {
  margin-bottom: 64px;
  scroll-margin-top: 32px;
}

.guide-section h2 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #e8e8f0;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 12px;
  border-bottom: 1px solid #1e1e2e;
}

.section-icon { font-style: normal; }

.guide-section h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #c0c0d8;
  margin: 24px 0 10px;
}

.guide-section p {
  color: #a0a0b8;
  font-size: 0.9rem;
  line-height: 1.7;
  margin-bottom: 16px;
}

code {
  background: #1a1a2e;
  border: 1px solid #2a2a3e;
  border-radius: 5px;
  padding: 2px 6px;
  font-size: 0.82rem;
  color: #a78bfa;
  font-family: monospace;
}

/* Cards */
.guide-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
  margin: 16px 0;
}

.guide-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 12px;
  padding: 16px 18px;
}

.card-title {
  font-size: 0.88rem;
  font-weight: 600;
  color: #e8e8f0;
  margin-bottom: 8px;
}

.guide-card p {
  font-size: 0.82rem;
  color: #888;
  margin: 0;
  line-height: 1.6;
}

/* Steps */
.guide-steps {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin: 16px 0;
}

.guide-step {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
  margin-top: 2px;
}

.guide-step strong { color: #e8e8f0; font-size: 0.9rem; }
.guide-step p { margin: 4px 0 0; }

/* Table */
.guide-table-wrap {
  overflow-x: auto;
  margin: 16px 0;
  border-radius: 10px;
  border: 1px solid #1e1e2e;
}

.guide-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.guide-table th {
  background: #111118;
  color: #666;
  font-weight: 600;
  text-align: left;
  padding: 10px 16px;
  border-bottom: 1px solid #1e1e2e;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.guide-table td {
  padding: 10px 16px;
  color: #a0a0b8;
  border-bottom: 1px solid #1a1a2a;
}

.guide-table tr:last-child td { border-bottom: none; }

.method {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  font-family: monospace;
}

.method.get { background: rgba(16,185,129,0.15); color: #10b981; }
.method.post { background: rgba(124,106,247,0.15); color: #7c6af7; }

/* Callout */
.guide-callout {
  background: rgba(124,106,247,0.08);
  border: 1px solid rgba(124,106,247,0.2);
  border-radius: 10px;
  padding: 14px 18px;
  font-size: 0.85rem;
  color: #a0a0b8;
  margin: 16px 0;
  line-height: 1.6;
}

.guide-callout strong { color: #e8e8f0; }

/* Responsive */
@media (max-width: 768px) {
  .sidebar { display: none; }
  .main { padding: 24px 20px; }
  .guide-cards { grid-template-columns: 1fr; }
}
</style>

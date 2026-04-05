<template>
  <div class="landing">
    <!-- Orbs -->
    <div class="orb orb-1" />
    <div class="orb orb-2" />
    <div class="orb orb-3" />

    <!-- Nav -->
    <nav class="nav">
      <span class="logo-text">LinkDrop</span>
      <div class="nav-links">
        <RouterLink to="/app/login" class="nav-link">Sign in</RouterLink>
        <RouterLink to="/app/register" class="nav-btn">Get started free</RouterLink>
      </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
      <div class="hero-badge">100% free to start</div>
      <h1 class="hero-title">
        One link for<br />
        <span class="gradient-text">everything you share</span>
      </h1>
      <p class="hero-sub">
        LinkDrop gives you a beautiful, customisable profile page that holds all your links —
        analytics, scheduling, embeds, and more included.
      </p>
      <div class="hero-cta">
        <RouterLink to="/app/register" class="btn-primary">Create your page</RouterLink>
        <a href="#features" class="btn-ghost">See features</a>
      </div>

      <!-- Mock profile card -->
      <div class="hero-preview">
        <div class="preview-card">
          <div class="preview-avatar-wrap">
            <div class="preview-avatar">JD</div>
          </div>
          <div class="preview-name">Jane Doe</div>
          <div class="preview-handle">@janedoe</div>
          <div class="preview-bio">Designer & coffee lover ☕</div>
          <div class="preview-links">
            <div class="preview-link"><span>🎨</span> My Portfolio</div>
            <div class="preview-link"><span>🐦</span> Twitter / X</div>
            <div class="preview-link"><span>📸</span> Instagram</div>
            <div class="preview-link preview-link-dim"><span>📄</span> Resume (PDF)</div>
          </div>
          <div class="preview-url">linkdrop.io/janedoe</div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="features-section">
      <div class="section-label">Features</div>
      <h2 class="section-title">Everything you need, nothing you don't</h2>

      <div class="features-grid">
        <div v-for="feature in features" :key="feature.title" class="feature-card">
          <div class="feature-icon">{{ feature.icon }}</div>
          <div class="feature-title">{{ feature.title }}</div>
          <div class="feature-desc">{{ feature.desc }}</div>
        </div>
      </div>
    </section>

    <!-- Analytics highlight -->
    <section class="highlight-section">
      <div class="highlight-content">
        <div class="section-label">Analytics</div>
        <h2 class="section-title">Know exactly who's clicking</h2>
        <p class="highlight-desc">
          Track clicks per link, see where your visitors come from, what device and browser they use,
          and which countries they're in — all in one dashboard. Export to CSV whenever you need.
        </p>
        <ul class="highlight-list">
          <li>Per-link click counts and CTR</li>
          <li>Referrer tracking (where they came from)</li>
          <li>Device & browser breakdown</li>
          <li>Country-level geolocation</li>
          <li>Peak hours chart</li>
          <li>Date range filtering</li>
          <li>CSV export</li>
        </ul>
      </div>
      <div class="highlight-visual">
        <div class="stat-card">
          <div class="stat-label">Total clicks</div>
          <div class="stat-value">2,847</div>
          <div class="stat-bar-wrap">
            <div class="stat-bar" style="width:88%" />
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Profile views</div>
          <div class="stat-value">6,102</div>
          <div class="stat-bar-wrap">
            <div class="stat-bar" style="width:100%" />
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Overall CTR</div>
          <div class="stat-value">46.7%</div>
          <div class="stat-bar-wrap">
            <div class="stat-bar" style="width:47%" />
          </div>
        </div>
        <div class="mini-bars">
          <div class="mini-bar-row">
            <span class="mini-bar-label">Mobile</span>
            <div class="mini-bar-track"><div class="mini-bar-fill" style="width:65%" /></div>
            <span class="mini-bar-val">65%</span>
          </div>
          <div class="mini-bar-row">
            <span class="mini-bar-label">Desktop</span>
            <div class="mini-bar-track"><div class="mini-bar-fill" style="width:30%" /></div>
            <span class="mini-bar-val">30%</span>
          </div>
          <div class="mini-bar-row">
            <span class="mini-bar-label">Tablet</span>
            <div class="mini-bar-track"><div class="mini-bar-fill" style="width:5%" /></div>
            <span class="mini-bar-val">5%</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Link types highlight -->
    <section class="highlight-section highlight-section-alt">
      <div class="highlight-visual">
        <div class="link-type-card">
          <span class="link-type-icon">🔗</span>
          <div>
            <div class="link-type-name">Standard link</div>
            <div class="link-type-sub">Any URL, with optional password protection</div>
          </div>
        </div>
        <div class="link-type-card">
          <span class="link-type-icon">📄</span>
          <div>
            <div class="link-type-name">File upload</div>
            <div class="link-type-sub">PDFs, images, docs — up to 20 MB</div>
          </div>
        </div>
        <div class="link-type-card">
          <span class="link-type-icon">▶</span>
          <div>
            <div class="link-type-name">Embedded player</div>
            <div class="link-type-sub">YouTube, Spotify, SoundCloud inline</div>
          </div>
        </div>
        <div class="link-type-card">
          <span class="link-type-icon">💰</span>
          <div>
            <div class="link-type-name">Tip jar</div>
            <div class="link-type-sub">Let visitors support you with one click</div>
          </div>
        </div>
        <div class="link-type-card">
          <span class="link-type-icon">⎘</span>
          <div>
            <div class="link-type-name">Copy-to-clipboard</div>
            <div class="link-type-sub">Phone numbers, emails, codes</div>
          </div>
        </div>
        <div class="link-type-card">
          <span class="link-type-icon">📅</span>
          <div>
            <div class="link-type-name">Scheduled links</div>
            <div class="link-type-sub">Show/hide links by date, or limit clicks</div>
          </div>
        </div>
      </div>
      <div class="highlight-content">
        <div class="section-label">Link types</div>
        <h2 class="section-title">Not just links</h2>
        <p class="highlight-desc">
          LinkDrop supports six different link types — from standard URLs to embedded video players,
          file downloads, and tip jars. Schedule any link to appear on a specific date, password-protect it,
          or cap it to a maximum number of clicks.
        </p>
        <RouterLink to="/app/register" class="btn-primary" style="display:inline-flex;margin-top:16px;">
          Start building your page
        </RouterLink>
      </div>
    </section>

    <!-- Developer section -->
    <section class="dev-section">
      <div class="section-label">Developer</div>
      <h2 class="section-title">Built for power users too</h2>
      <div class="dev-grid">
        <div class="dev-card">
          <div class="dev-icon">🔑</div>
          <div class="dev-title">Public API</div>
          <div class="dev-desc">Access your links and analytics programmatically with API key auth.</div>
          <div class="dev-code">GET /api/v1/links<br />X-API-Key: your_key</div>
        </div>
        <div class="dev-card">
          <div class="dev-icon">🪝</div>
          <div class="dev-title">Webhooks</div>
          <div class="dev-desc">Fire a signed HTTP POST to your endpoint every time a link is clicked.</div>
          <div class="dev-code">POST https://your-server.com<br />X-Webhook-Signature: …</div>
        </div>
        <div class="dev-card">
          <div class="dev-icon">📊</div>
          <div class="dev-title">UTM tracking</div>
          <div class="dev-desc">Attach utm_source, utm_medium, and more to any link for affiliate tracking.</div>
          <div class="dev-code">utm_source=newsletter<br />utm_medium=email</div>
        </div>
      </div>
    </section>

    <!-- Plans -->
    <section class="plans-section">
      <div class="section-label">Pricing</div>
      <h2 class="section-title">Simple pricing</h2>
      <div class="plans-grid">
        <div class="plan-card">
          <div class="plan-name">Free</div>
          <div class="plan-price">$0<span class="plan-period">/mo</span></div>
          <ul class="plan-list">
            <li>Up to 10 links</li>
            <li>All link types</li>
            <li>Analytics dashboard</li>
            <li>Custom theme</li>
            <li>QR code &amp; share button</li>
            <li>Public API &amp; webhooks</li>
          </ul>
          <RouterLink to="/app/register" class="plan-btn">Get started</RouterLink>
        </div>
        <div class="plan-card plan-card-pro">
          <div class="plan-badge">Most popular</div>
          <div class="plan-name">Pro</div>
          <div class="plan-price">$5<span class="plan-period">/mo</span></div>
          <ul class="plan-list">
            <li>Unlimited links</li>
            <li>Everything in Free</li>
            <li>Custom domain</li>
            <li>Verified badge</li>
            <li>Priority support</li>
          </ul>
          <RouterLink to="/app/register" class="plan-btn plan-btn-pro">Get started</RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
      <h2 class="cta-title">Your link, your way.</h2>
      <p class="cta-sub">Set up your LinkDrop page in under a minute.</p>
      <RouterLink to="/app/register" class="btn-primary btn-lg">Create your free page</RouterLink>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <span class="logo-text footer-logo">LinkDrop</span>
      <div class="footer-links">
        <RouterLink to="/app/login">Sign in</RouterLink>
        <RouterLink to="/app/register">Register</RouterLink>
      </div>
    </footer>
  </div>
</template>

<script setup>
const features = [
  { icon: '🎨', title: 'Custom themes', desc: 'Pick from presets or define your own accent, background, card, and text colours.' },
  { icon: '📸', title: 'Avatar & bio', desc: 'Upload a profile photo and write a short bio that shows on your public page.' },
  { icon: '📌', title: 'Pin & reorder', desc: 'Drag to reorder links, pin your most important ones to the top.' },
  { icon: '🔒', title: 'Password protection', desc: 'Lock any link behind a password — visitors enter it before the URL is revealed.' },
  { icon: '📅', title: 'Link scheduling', desc: 'Set a start and end date for any link, or cap it to a maximum number of clicks.' },
  { icon: '🖼', title: 'OG image previews', desc: 'Auto-fetch the preview image for any URL with one click.' },
  { icon: '▶', title: 'Embedded players', desc: 'YouTube, Spotify, and SoundCloud links render as inline players.' },
  { icon: '🌍', title: 'Custom domain', desc: 'Point your own domain to your LinkDrop profile on Pro.' },
  { icon: '🏷', title: 'Badges', desc: 'Show a Verified badge or "Available for hire" status on your profile.' },
  { icon: '⬛', title: 'QR code', desc: 'One-click QR code download for every profile.' },
  { icon: '🔗', title: 'Social icon detection', desc: 'Icons for Twitter, GitHub, LinkedIn, Instagram and more are auto-detected.' },
  { icon: '👤', title: 'Admin dashboard', desc: 'Manage users, issue verified badges, and set plans from the admin panel.' },
]
</script>

<style scoped>
/* ── Base ─────────────────────────────────────────────── */
.landing {
  min-height: 100vh;
  background: #08080f;
  color: #e8e8f0;
  font-family: inherit;
  position: relative;
  overflow-x: hidden;
}

.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  z-index: 0;
}
.orb-1 { width: 600px; height: 600px; background: #7c6af7; opacity: 0.07; top: -200px; right: -150px; }
.orb-2 { width: 400px; height: 400px; background: #e96af5; opacity: 0.06; bottom: 10%; left: -100px; }
.orb-3 { width: 300px; height: 300px; background: #7c6af7; opacity: 0.05; top: 50%; left: 50%; transform: translate(-50%,-50%); }

/* ── Nav ──────────────────────────────────────────────── */
.nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 40px;
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(8,8,15,0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.04);
}

.logo-text {
  font-size: 1.4rem;
  font-weight: 700;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav-links { display: flex; align-items: center; gap: 16px; }

.nav-link { color: #a0a0b0; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
.nav-link:hover { color: #e8e8f0; }

.nav-btn {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  color: white;
  border-radius: 8px;
  padding: 8px 18px;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition: opacity 0.2s;
}
.nav-btn:hover { opacity: 0.85; }

/* ── Hero ─────────────────────────────────────────────── */
.hero {
  text-align: center;
  padding: 100px 24px 80px;
  position: relative;
  z-index: 1;
}

.hero-badge {
  display: inline-block;
  background: rgba(124,106,247,0.15);
  border: 1px solid rgba(124,106,247,0.3);
  color: #a090f5;
  border-radius: 20px;
  padding: 4px 14px;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 24px;
}

.hero-title {
  font-size: clamp(2.2rem, 5vw, 3.8rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.02em;
  margin: 0 0 20px;
}

.gradient-text {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-sub {
  font-size: 1.1rem;
  color: #888;
  max-width: 520px;
  margin: 0 auto 36px;
  line-height: 1.6;
}

.hero-cta {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 64px;
}

.btn-primary {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  color: white;
  border: none;
  border-radius: 10px;
  padding: 13px 28px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: opacity 0.2s;
}
.btn-primary:hover { opacity: 0.85; }
.btn-lg { padding: 16px 36px; font-size: 1.05rem; }

.btn-ghost {
  background: transparent;
  color: #a0a0b0;
  border: 1px solid #2e2e3e;
  border-radius: 10px;
  padding: 13px 28px;
  font-size: 0.95rem;
  font-weight: 500;
  text-decoration: none;
  transition: border-color 0.2s, color 0.2s;
}
.btn-ghost:hover { border-color: #7c6af7; color: #e8e8f0; }

/* Mock profile card */
.hero-preview {
  display: flex;
  justify-content: center;
}

.preview-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 24px;
  padding: 32px 28px;
  width: 100%;
  max-width: 340px;
  text-align: center;
  box-shadow: 0 40px 80px rgba(0,0,0,0.4);
}

.preview-avatar-wrap { margin-bottom: 12px; }
.preview-avatar {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; font-weight: 700; color: white;
  margin: 0 auto;
}

.preview-name { font-weight: 700; font-size: 1.1rem; margin-bottom: 2px; }
.preview-handle { color: #666; font-size: 0.85rem; margin-bottom: 8px; }
.preview-bio { color: #888; font-size: 0.85rem; margin-bottom: 20px; }

.preview-links { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
.preview-link {
  background: #0d0d18;
  border: 1px solid #1e1e2e;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.88rem;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: left;
}
.preview-link-dim { opacity: 0.45; }

.preview-url { font-size: 0.75rem; color: #555; margin-top: 8px; }

/* ── Sections shared ──────────────────────────────────── */
section { position: relative; z-index: 1; }

.section-label {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #7c6af7;
  margin-bottom: 10px;
  text-align: center;
}

.section-title {
  font-size: clamp(1.6rem, 3vw, 2.4rem);
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0 0 40px;
  text-align: center;
}

/* ── Features grid ────────────────────────────────────── */
.features-section {
  padding: 80px 24px;
  max-width: 1100px;
  margin: 0 auto;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
}

.feature-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 16px;
  padding: 24px;
  transition: border-color 0.2s;
}
.feature-card:hover { border-color: rgba(124,106,247,0.4); }

.feature-icon { font-size: 1.6rem; margin-bottom: 12px; }
.feature-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 6px; }
.feature-desc { color: #777; font-size: 0.85rem; line-height: 1.5; }

/* ── Highlight sections ───────────────────────────────── */
.highlight-section {
  padding: 80px 40px;
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
}

.highlight-section-alt { direction: ltr; }
.highlight-section-alt .highlight-content { order: 2; }
.highlight-section-alt .highlight-visual { order: 1; }

.highlight-content .section-label,
.highlight-content .section-title { text-align: left; }

.highlight-desc { color: #888; line-height: 1.7; margin-bottom: 20px; font-size: 0.95rem; }

.highlight-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.highlight-list li {
  color: #c0c0d0;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
}
.highlight-list li::before {
  content: '✓';
  color: #7c6af7;
  font-weight: 700;
  flex-shrink: 0;
}

/* Analytics visual */
.stat-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 10px;
}
.stat-label { font-size: 0.78rem; color: #666; margin-bottom: 4px; }
.stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.stat-bar-wrap { background: #0d0d18; border-radius: 4px; height: 6px; }
.stat-bar { background: linear-gradient(90deg, #7c6af7, #e96af5); border-radius: 4px; height: 6px; }

.mini-bars {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.mini-bar-row { display: flex; align-items: center; gap: 10px; }
.mini-bar-label { font-size: 0.8rem; color: #888; width: 52px; flex-shrink: 0; }
.mini-bar-track { flex: 1; background: #0d0d18; border-radius: 4px; height: 6px; }
.mini-bar-fill { background: linear-gradient(90deg, #7c6af7, #e96af5); border-radius: 4px; height: 6px; }
.mini-bar-val { font-size: 0.78rem; color: #666; width: 32px; text-align: right; flex-shrink: 0; }

/* Link types visual */
.link-type-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 10px;
  transition: border-color 0.2s;
}
.link-type-card:hover { border-color: rgba(124,106,247,0.4); }
.link-type-icon { font-size: 1.4rem; flex-shrink: 0; }
.link-type-name { font-weight: 600; font-size: 0.9rem; }
.link-type-sub { color: #666; font-size: 0.8rem; margin-top: 2px; }

/* ── Developer ────────────────────────────────────────── */
.dev-section {
  padding: 80px 24px;
  max-width: 1100px;
  margin: 0 auto;
}

.dev-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.dev-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 16px;
  padding: 28px;
}

.dev-icon { font-size: 1.6rem; margin-bottom: 12px; }
.dev-title { font-weight: 600; font-size: 1rem; margin-bottom: 8px; }
.dev-desc { color: #777; font-size: 0.85rem; line-height: 1.5; margin-bottom: 16px; }
.dev-code {
  background: #0a0a0f;
  border: 1px solid #1e1e2e;
  border-radius: 8px;
  padding: 10px 14px;
  font-family: 'Courier New', monospace;
  font-size: 0.78rem;
  color: #a090f5;
  line-height: 1.7;
  white-space: pre;
}

/* ── Plans ────────────────────────────────────────────── */
.plans-section {
  padding: 80px 24px;
  max-width: 700px;
  margin: 0 auto;
}

.plans-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.plan-card {
  background: #111118;
  border: 1px solid #1e1e2e;
  border-radius: 20px;
  padding: 32px 28px;
  position: relative;
}

.plan-card-pro {
  border-color: rgba(124,106,247,0.5);
  background: linear-gradient(160deg, rgba(124,106,247,0.08), #111118 60%);
}

.plan-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  color: white;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 20px;
  white-space: nowrap;
}

.plan-name { font-size: 1rem; font-weight: 600; color: #a0a0b0; margin-bottom: 8px; }
.plan-price { font-size: 2.4rem; font-weight: 800; margin-bottom: 24px; }
.plan-period { font-size: 1rem; color: #666; font-weight: 400; }

.plan-list {
  list-style: none;
  padding: 0;
  margin: 0 0 28px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.plan-list li {
  font-size: 0.88rem;
  color: #c0c0d0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.plan-list li::before {
  content: '✓';
  color: #7c6af7;
  font-weight: 700;
}

.plan-btn {
  display: block;
  text-align: center;
  border: 1px solid #2e2e3e;
  color: #e8e8f0;
  border-radius: 10px;
  padding: 11px;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition: border-color 0.2s;
}
.plan-btn:hover { border-color: #7c6af7; }

.plan-btn-pro {
  background: linear-gradient(135deg, #7c6af7, #e96af5);
  border: none;
  color: white;
}
.plan-btn-pro:hover { opacity: 0.85; border: none; }

/* ── CTA ──────────────────────────────────────────────── */
.cta-section {
  text-align: center;
  padding: 100px 24px;
}

.cta-title {
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 12px;
}

.cta-sub { color: #777; font-size: 1rem; margin-bottom: 32px; }

/* ── Footer ───────────────────────────────────────────── */
.footer {
  border-top: 1px solid #1e1e2e;
  padding: 24px 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-logo { font-size: 1.1rem; }
.footer-links { display: flex; gap: 20px; }
.footer-links a { color: #555; font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
.footer-links a:hover { color: #a0a0b0; }

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 768px) {
  .nav { padding: 16px 20px; }
  .hero { padding: 60px 20px 50px; }
  .highlight-section { grid-template-columns: 1fr; gap: 40px; padding: 60px 20px; }
  .highlight-section-alt .highlight-content { order: 1; }
  .highlight-section-alt .highlight-visual { order: 2; }
  .plans-grid { grid-template-columns: 1fr; }
  .footer { flex-direction: column; gap: 16px; text-align: center; }
}
</style>

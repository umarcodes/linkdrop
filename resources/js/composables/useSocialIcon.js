const rules = [
  { pattern: /instagram\.com/i,  icon: '📸' },
  { pattern: /twitter\.com|x\.com/i, icon: '🐦' },
  { pattern: /github\.com/i,     icon: '🐙' },
  { pattern: /linkedin\.com/i,   icon: '💼' },
  { pattern: /youtube\.com|youtu\.be/i, icon: '▶️' },
  { pattern: /tiktok\.com/i,     icon: '🎵' },
  { pattern: /facebook\.com|fb\.com/i, icon: '📘' },
  { pattern: /snapchat\.com/i,   icon: '👻' },
  { pattern: /pinterest\.com/i,  icon: '📌' },
  { pattern: /twitch\.tv/i,      icon: '🎮' },
  { pattern: /spotify\.com/i,    icon: '🎧' },
  { pattern: /soundcloud\.com/i, icon: '🎶' },
  { pattern: /discord\.gg|discord\.com/i, icon: '💬' },
  { pattern: /reddit\.com/i,     icon: '🤖' },
  { pattern: /medium\.com/i,     icon: '✍️' },
  { pattern: /substack\.com/i,   icon: '📧' },
  { pattern: /patreon\.com/i,    icon: '🎁' },
  { pattern: /paypal\.com/i,     icon: '💳' },
  { pattern: /buymeacoffee\.com/i, icon: '☕' },
  { pattern: /ko-fi\.com/i,      icon: '☕' },
  { pattern: /behance\.net/i,    icon: '🎨' },
  { pattern: /dribbble\.com/i,   icon: '🏀' },
  { pattern: /figma\.com/i,      icon: '🎨' },
  { pattern: /whatsapp\.com|wa\.me/i, icon: '📱' },
  { pattern: /telegram\.me|t\.me/i, icon: '✈️' },
]

export function detectSocialIcon(url) {
  if (!url) { return null }
  for (const rule of rules) {
    if (rule.pattern.test(url)) { return rule.icon }
  }
  return null
}

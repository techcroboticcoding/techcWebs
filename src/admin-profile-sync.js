const PRODUCTION_API_BASE_SYNC = 'https://techcwebs-production.up.railway.app/api';
const LOCAL_API_BASE_SYNC = 'http://127.0.0.1:8000/api';

function isLocalFrontendSync() {
  return (
    window.location.hostname === 'localhost' ||
    window.location.hostname === '127.0.0.1' ||
    window.location.hostname === ''
  );
}

function getApiBaseSync() {
  const saved = localStorage.getItem('api_base');

  if (isLocalFrontendSync()) {
    return saved || LOCAL_API_BASE_SYNC;
  }

  if (
    saved &&
    !saved.includes('127.0.0.1') &&
    !saved.includes('localhost')
  ) {
    return saved;
  }

  localStorage.setItem('api_base', PRODUCTION_API_BASE_SYNC);
  return PRODUCTION_API_BASE_SYNC;
}

function syncFixHttps(url) {
  if (!url) return '';

  if (
    String(url).includes('127.0.0.1') ||
    String(url).includes('localhost')
  ) {
    return String(url);
  }

  return String(url).replace('http://', 'https://');
}

function syncFallbackAvatar(name) {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'Admin TECH-C')}&background=2563eb&color=fff&bold=true`;
}

function syncSetText(id, value) {
  const el = document.getElementById(id);
  if (el) el.innerText = value || '-';
}

function syncSetAvatar(url, name) {
  const avatar = document.getElementById('header_avatar');
  if (!avatar) return;

  const finalUrl = url || syncFallbackAvatar(name);

  avatar.src = finalUrl;
  avatar.alt = name || 'Admin Profile';
  avatar.classList.add('h-full', 'w-full', 'object-cover');

  avatar.onerror = function () {
    this.onerror = null;
    this.src = syncFallbackAvatar(name);
  };
}

async function syncAdminProfileHeader() {
  const API_BASE = getApiBaseSync();

  const savedName = localStorage.getItem('name') || 'Admin TECH-C';
  const savedEmail = localStorage.getItem('email') || '';
  const savedPhoto = localStorage.getItem('photo_url') || '';

  // tampilkan data localStorage dulu biar cepat
  syncSetText('header_name', savedName);
  syncSetText('dropdown_name', savedName);
  syncSetText('dropdown_email', savedEmail);

  if (savedPhoto) {
    syncSetAvatar(syncFixHttps(savedPhoto), savedName);
  } else {
    syncSetAvatar('', savedName);
  }

  try {
    const res = await fetch(`${API_BASE}/admin/profile`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'X-User-Id': localStorage.getItem('user_id') || '',
        'X-User-Email': localStorage.getItem('email') || '',
        'X-User-Role': localStorage.getItem('role') || 'admin'
      }
    });

    const raw = await res.text();

    let profile;

    try {
      profile = JSON.parse(raw);
    } catch {
      console.warn('Admin profile bukan JSON:', raw);
      return;
    }

    if (!res.ok) {
      console.warn('Gagal load admin profile:', profile);
      return;
    }

    const name = profile.name || savedName;
    const email = profile.email || savedEmail;
    const photoUrl = syncFixHttps(profile.photo_url || '');

    localStorage.setItem('name', name);
    localStorage.setItem('email', email);

    if (photoUrl) {
      localStorage.setItem('photo_url', photoUrl);
    }

    const oldUser = JSON.parse(localStorage.getItem('user') || '{}');

    localStorage.setItem('user', JSON.stringify({
      ...oldUser,
      id: profile.id || oldUser.id,
      name,
      email,
      role: profile.role || oldUser.role || 'admin',
      photo: profile.photo || oldUser.photo || null,
      photo_url: photoUrl || oldUser.photo_url || null,
    }));

    syncSetText('header_name', name);
    syncSetText('dropdown_name', name);
    syncSetText('dropdown_email', email);
    syncSetAvatar(photoUrl, name);

  } catch (err) {
    console.warn('syncAdminProfileHeader error:', err);
  }
}

window.syncAdminProfileHeader = syncAdminProfileHeader;
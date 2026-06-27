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

function syncFallbackAvatar(name) {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'Admin TECH-C')}&background=2563eb&color=fff&bold=true`;
}

function syncSetText(id, value) {
  const el = document.getElementById(id);
  if (el) el.innerText = value || '-';
}

function syncAdminPhotoUrl(raw, name) {
  const API_BASE = getApiBaseSync();

  if (!raw) {
    return syncFallbackAvatar(name);
  }

  raw = String(raw).trim();

  // Fix dari http jadi https
  raw = raw.replace('http://techcwebs-production.up.railway.app', 'https://techcwebs-production.up.railway.app');

  // Kalau masih URL lama /storage/admin-profiles, paksa ke route API file viewer
  if (raw.includes('/storage/admin-profiles/')) {
    const filename = raw.split('/storage/admin-profiles/').pop().split('?')[0];
    return `${API_BASE}/files/admin-profiles/${filename}?v=${Date.now()}`;
  }

  // Kalau path mentah admin-profiles/namafile.jpg
  if (raw.startsWith('admin-profiles/')) {
    const filename = raw.split('/').pop();
    return `${API_BASE}/files/admin-profiles/${filename}?v=${Date.now()}`;
  }

  // Kalau sudah route API benar
  if (raw.includes('/api/files/admin-profiles/')) {
    return raw.split('?')[0] + `?v=${Date.now()}`;
  }

  // Kalau URL image lain
  if (raw.startsWith('http://') || raw.startsWith('https://')) {
    return raw.split('?')[0] + `?v=${Date.now()}`;
  }

  // Fallback kalau cuma filename
  const filename = raw.split('/').pop();
  return `${API_BASE}/files/admin-profiles/${filename}?v=${Date.now()}`;
}

function syncSetAvatar(url, name) {
  const avatar = document.getElementById('header_avatar');
  if (!avatar) return;

  avatar.src = url || syncFallbackAvatar(name);
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

  syncSetText('header_name', savedName);
  syncSetText('dropdown_name', savedName);
  syncSetText('dropdown_email', savedEmail);

  if (savedPhoto) {
    syncSetAvatar(syncAdminPhotoUrl(savedPhoto, savedName), savedName);
  } else {
    syncSetAvatar(syncFallbackAvatar(savedName), savedName);
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

    const photoUrl = syncAdminPhotoUrl(
      profile.photo_url || profile.photo || savedPhoto,
      name
    );

    localStorage.setItem('name', name);
    localStorage.setItem('email', email);
    localStorage.setItem('photo_url', photoUrl);

    const oldUser = JSON.parse(localStorage.getItem('user') || '{}');

    localStorage.setItem('user', JSON.stringify({
      ...oldUser,
      id: profile.id || oldUser.id,
      name,
      email,
      role: profile.role || oldUser.role || 'admin',
      photo: profile.photo || oldUser.photo || null,
      photo_url: photoUrl,
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
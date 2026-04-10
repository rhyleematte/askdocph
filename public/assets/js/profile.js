/* ================================================================
   PROFILE PAGE – JavaScript
   NOTE: dashboard.js handles lucide init, theme + dropdown toggle.
         This file handles profile-specific features only.
================================================================ */

/* ── Helpers ────────────────────────────────────────────────── */
const $ = (sel, ctx) => (ctx || document).querySelector(sel);
const $$ = (sel, ctx) => [...(ctx || document).querySelectorAll(sel)];

let CSRF = '';

function toast(msg, type) {
    type = type || 'success';
    const el = document.getElementById('toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'toast ' + type + ' show';
    clearTimeout(el._t);
    el._t = setTimeout(function () { el.classList.remove('show'); }, 3200);
}

function apiPost(url, body, method) {
    method = method || 'POST';
    const isFormData = body instanceof FormData;
    return fetch(url, {
        method: method,
        headers: isFormData
            ? { 'X-CSRF-TOKEN': CSRF }
            : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: isFormData ? body : JSON.stringify(body),
    }).then(function (r) { return r.json(); });
}

function apiGet(url) {
    return fetch(url, {
        headers: { 'Accept': 'application/json' },
    }).then(function (r) { return r.json(); });
}

function syncSavedTab(postId, isSaved, sourcePostEl) {
    const savedTab = document.getElementById('tab-saved');
    if (!savedTab) return;

    let feed = document.getElementById('savedPostsFeed');
    const emptyState = savedTab.querySelector('.empty-state');

    if (isSaved) {
        if (!feed) {
            feed = document.createElement('div');
            feed.id = 'savedPostsFeed';
            savedTab.querySelector('.panel .prof-section-body')?.appendChild(feed);
            if (emptyState) emptyState.remove();
        }

        if (feed.querySelector(`[data-post-id="${postId}"]`)) return;

        const src = sourcePostEl || document.querySelector(`article.post[data-post-id="${postId}"]`);
        if (!src) return;

        const clone = src.cloneNode(true);

        // Remove comments section to avoid duplicate IDs and conflicts
        const comments = clone.querySelector('.comments-section');
        if (comments) comments.remove();
        clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

        feed.prepend(clone);
        if (window.lucide) lucide.createIcons({ root: clone });
        return;
    }

    if (feed) {
        const existing = feed.querySelector(`[data-post-id="${postId}"]`);
        if (existing) existing.remove();

        if (feed.querySelectorAll('.post').length === 0) {
            feed.remove();
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.innerHTML = '<i data-lucide="bookmark"></i><p>No saved posts yet. Tap the bookmark on any post to save it here.</p>';
            savedTab.querySelector('.panel .prof-section-body')?.appendChild(empty);
            if (window.lucide) lucide.createIcons({ root: empty });
        }
    }
}


/* ================================================================
   PROFILE PHOTO — upload & delete
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    // Init CSRF from meta tag
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) CSRF = csrfMeta.content;

    const photoInput = document.getElementById('photoUpload');
    const previewAvatar = document.getElementById('previewAvatar');
    const deleteBtn = document.getElementById('deletePhotoBtn');

    photoInput?.addEventListener('change', async () => {
        const file = photoInput.files[0];
        if (!file) return;

        // Local preview
        const previewUrl = URL.createObjectURL(file);
        if (previewAvatar) previewAvatar.src = previewUrl;

        const fd = new FormData();
        fd.append('photo', file);

        try {
            const res = await apiPost(window.ROUTES.updatePhoto, fd);
            if (res.ok) {
                // Update all avatar images on the page with the new URL
                $$('img').forEach(img => {
                    if (img.src === previewAvatar?.src || img.dataset.ownAvatar) return;
                });
                toast('Profile photo updated!', 'success');
            } else {
                toast(res.message ?? 'Upload failed.', 'error');
            }
        } catch {
            toast('Upload failed.', 'error');
        }
        photoInput.value = '';
    });

    deleteBtn?.addEventListener('click', async () => {
        try {
            const res = await apiPost(window.ROUTES.deletePhoto, {});
            if (res.ok) {
                const newAvatarUrl = res.avatar_url;
                if (previewAvatar) previewAvatar.src = newAvatarUrl;

                // Update all avatar images on the page for the current user
                $$('img').forEach(img => {
                    // Update header avatars or post avatars that belong to this user
                    if (img.dataset.ownAvatar || img.closest('.avatar-btn') || img.closest('.dropdown-avatar')) {
                        img.src = newAvatarUrl;
                    }
                });

                toast('Profile photo removed.', 'success');
            } else {
                toast(res.message ?? 'Error.', 'error');
            }
        } catch {
            toast('Error connecting to server.', 'error');
        }
    });

    /* ================================================================
       COVER PHOTO — upload & delete
    ================================================================ */
    const coverInput = document.getElementById('coverUpload');
    const coverDisplay = document.getElementById('coverDisplay');
    const deleteCoverBtn = document.getElementById('deleteCoverBtn');
    const coverGradientOverlay = document.getElementById('coverGradientOverlay');

    coverInput?.addEventListener('change', async () => {
        const file = coverInput.files[0];
        if (!file) return;

        // Local live preview
        const previewUrl = URL.createObjectURL(file);
        if (coverDisplay) coverDisplay.style.backgroundImage = `url('${previewUrl}')`;
        if (coverGradientOverlay) coverGradientOverlay.style.display = 'none';
        if (deleteCoverBtn) deleteCoverBtn.classList.remove('hidden');

        const fd = new FormData();
        fd.append('cover_photo', file);

        try {
            const res = await apiPost(window.ROUTES.updateCover, fd);
            if (res.ok) {
                toast('Cover photo updated!', 'success');
            } else {
                toast(res.message ?? 'Upload failed.', 'error');
            }
        } catch {
            toast('Upload failed.', 'error');
        }
        coverInput.value = '';
    });

    deleteCoverBtn?.addEventListener('click', async () => {
        try {
            const res = await apiPost(window.ROUTES.deleteCover, {});
            if (res.ok) {
                if (coverDisplay) coverDisplay.style.backgroundImage = `url('${res.cover_url}')`;
                if (coverGradientOverlay) {
                    coverGradientOverlay.style.display = 'block';
                } else {
                    // Re-instantiate gradient if missing
                    const grad = document.createElement('div');
                    grad.className = 'prof-cover-grad';
                    grad.id = 'coverGradientOverlay';
                    coverDisplay.prepend(grad);
                }
                if (deleteCoverBtn) deleteCoverBtn.classList.add('hidden');
                toast('Cover photo removed.', 'success');
            } else {
                toast(res.message ?? 'Error.', 'error');
            }
        } catch {
            toast('Error connecting to server.', 'error');
        }
    });

});

/* ================================================================
   EDIT PROFILE MODAL
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editModal');
    var editBtn = document.getElementById('editProfileBtn');
    var closeBtn = document.getElementById('closeEditModal');
    var cancelBtn = document.getElementById('cancelEditBtn');
    var editForm = document.getElementById('editProfileForm');
    var bioArea = document.getElementById('inp_bio');
    var bioCount = document.getElementById('bioCharCount');
    var feedback = document.getElementById('editFeedback');
    var saveBtn = document.getElementById('saveProfileBtn');

    function openModal() { if (editModal) editModal.classList.add('open'); }
    function closeModal() {
        if (editModal) editModal.classList.remove('open');
        clearFeedback();
    }
    function clearFeedback() {
        if (feedback) { feedback.textContent = ''; feedback.className = 'form-feedback'; }
    }

    if (editBtn) editBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeModal();
        });
    }

    // Live character counter
    if (bioArea && bioCount) {
        bioArea.addEventListener('input', function () {
            bioCount.textContent = bioArea.value.length;
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (saveBtn) saveBtn.disabled = true;
            clearFeedback();

            var data = {
                fname: (document.getElementById('inp_fname') || {}).value || '',
                mname: (document.getElementById('inp_mname') || {}).value || '',
                lname: (document.getElementById('inp_lname') || {}).value || '',
                username: (document.getElementById('inp_username') || {}).value || '',
                bio: bioArea ? bioArea.value : '',
            };

            // Trim all values
            Object.keys(data).forEach(function (k) { data[k] = data[k].trim(); });

            apiPost(window.ROUTES.updateInfo, data).then(function (res) {
                if (res.ok) {
                    // ── Use DB-confirmed values from server response ────────
                    var savedName = res.full_name || [data.fname, data.mname, data.lname].filter(Boolean).join(' ');
                    var savedUsername = res.username || data.username;
                    var savedBio = res.bio || data.bio;

                    // Update full name everywhere
                    $$('.prof-fullname').forEach(function (el) { el.textContent = savedName; });

                    // Update @handle
                    var handleEl = document.getElementById('profHandle');
                    if (handleEl) handleEl.textContent = '@' + savedUsername;
                    $$('.prof-handle').forEach(function (el) { el.textContent = '@' + savedUsername; });

                    // Update bio display
                    var bioDisplay = document.getElementById('bioDisplay');
                    if (bioDisplay) {
                        if (savedBio) {
                            bioDisplay.textContent = savedBio;
                            bioDisplay.classList.remove('muted');
                        } else {
                            bioDisplay.textContent = window.IS_OWN_PROFILE ? 'Add a short bio\u2026' : 'No bio yet.';
                            bioDisplay.classList.add('muted');
                        }
                    }

                    // Update topbar dropdown
                    var dropUser = document.querySelector('.profile-username');
                    if (dropUser) dropUser.textContent = '@' + savedUsername;
                    var dropName = document.querySelector('.profile-fullname');
                    if (dropName) dropName.textContent = savedName;

                    // Show success
                    if (feedback) {
                        feedback.textContent = res.message || 'Profile updated!';
                        feedback.classList.add('success');
                    }
                    toast(res.message || 'Profile updated!', 'success');
                    setTimeout(closeModal, 1200);
                } else {
                    var errorMsg = 'Error saving profile.';
                    if (res.errors) {
                        var msgs = [];
                        Object.keys(res.errors).forEach(function (k) {
                            res.errors[k].forEach(function (m) { msgs.push(m); });
                        });
                        errorMsg = msgs.join(' ');
                    } else if (res.message) {
                        errorMsg = res.message;
                    }
                    if (feedback) {
                        feedback.textContent = errorMsg;
                        feedback.classList.add('error');
                    }
                    toast(errorMsg, 'error');
                }

                if (saveBtn) saveBtn.disabled = false;
            }).catch(function () {
                if (feedback) {
                    feedback.textContent = 'Network error. Please try again.';
                    feedback.classList.add('error');
                }
                toast('Network error.', 'error');
                if (saveBtn) saveBtn.disabled = false;
            });
        });
    }
});


/* ================================================================
   TABS
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const tabs = $$('.tab-btn', document.getElementById('profTabs'));
    const panels = $$('.tab-content');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            const target = btn.dataset.tab;
            panels.forEach(p => {
                p.classList.toggle('hidden', p.id !== `tab-${target}`);
            });

            if (window.lucide) lucide.createIcons();

            // Optionally update URL so reloads keep you there
            const url = new URL(window.location);
            url.searchParams.set('tab', target);
            window.history.replaceState({}, '', url);
        });
    });

    // Auto-select tab from query param if present
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const targetBtn = tabs.find(b => b.dataset.tab === tabParam);
        if (targetBtn) {
            targetBtn.click();
        }
    }
});

/* ================================================================
   PROFILE FILTER DROPDOWNS (Groups / Resources: Joined vs Created)
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const dropdowns = $$('.prof-filter-dropdown');

    function closeAllMenus(except) {
        $$('.prof-filter-menu').forEach(menu => {
            if (!except || menu !== except) {
                menu.classList.remove('open');
            }
        });
    }

    document.addEventListener('click', (e) => {
        const toggle = e.target.closest('.prof-filter-toggle');
        const option = e.target.closest('.prof-filter-menu button[data-value]');

        if (toggle) {
            e.stopPropagation();
            const menu = toggle.parentElement.querySelector('.prof-filter-menu');
            const isOpen = menu.classList.contains('open');
            closeAllMenus(isOpen ? null : menu);
            if (!isOpen) {
                menu.classList.add('open');
            }
            return;
        }

        if (option) {
            e.stopPropagation();
            const dropdown = option.closest('.prof-filter-dropdown');
            const target = dropdown.dataset.target;
            const value = option.dataset.value; // 'joined' | 'created'

            const toggleBtn = dropdown.querySelector('.prof-filter-toggle');
            const labelSpan = toggleBtn?.querySelector('span');
            if (toggleBtn) toggleBtn.dataset.current = value;
            if (labelSpan) labelSpan.textContent = value === 'created' ? 'Created' : 'Joined';

            const section = document.querySelector(`.prof-section[data-section="${target}"]`);
            if (section) {
                section.setAttribute('data-current', value);
            }

            const menu = dropdown.querySelector('.prof-filter-menu');
            if (menu) menu.classList.remove('open');

            if (window.lucide) lucide.createIcons();
            return;
        }

        // Click outside: close all menus
        if (!e.target.closest('.prof-filter-dropdown')) {
            closeAllMenus();
        }
    });
});

/* ================================================================
   PROFILE SECTION SEARCH (Groups / Resources)
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const norm = (s) => (s || '').toString().trim().toLowerCase();

    function attachSearch(input, target) {
        if (!input) return;
        const section = document.querySelector(`.prof-section[data-section="${target}"]`);
        if (!section) return;

        const joinedGrid = section.querySelector(target === 'groups' ? '.prof-groups-joined' : '.prof-resources-joined');
        const createdGrid = section.querySelector(target === 'groups' ? '.prof-groups-created' : '.prof-resources-created');

        const allCards = [
            ...(joinedGrid ? Array.from(joinedGrid.querySelectorAll('.prof-card')) : []),
            ...(createdGrid ? Array.from(createdGrid.querySelectorAll('.prof-card')) : []),
        ];
        if (!allCards.length) return;

        const apply = () => {
            const q = norm(input.value);
            allCards.forEach(card => {
                const titleEl = card.querySelector('.prof-card-title');
                const descEl = card.querySelector('.prof-card-desc');
                const hay = norm((titleEl?.textContent || '') + ' ' + (descEl?.textContent || ''));
                const show = !q || hay.indexOf(q) !== -1;
                card.style.display = show ? '' : 'none';
            });
        };

        input.addEventListener('input', apply);
    }

    attachSearch(document.querySelector('.prof-section-search-input[data-prof-search="groups"]'), 'groups');
    attachSearch(document.querySelector('.prof-section-search-input[data-prof-search="resources"]'), 'resources');
});

/* ================================================================
   CREATE POST
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const postText = document.getElementById('postText');
    const postMedia = document.getElementById('postMedia');
    const submitBtn = document.getElementById('submitPostBtn');
    const previewArea = document.getElementById('mediaPreviewArea');
    const postFeedback = document.getElementById('postFeedback');
    const postsFeed = document.getElementById('postsFeed');

    if (!submitBtn) return;  // Not own profile

    let selectedFiles = [];

    postMedia?.addEventListener('change', () => {
        const newFiles = [...postMedia.files];
        selectedFiles.push(...newFiles);
        renderPreviews();
        postMedia.value = '';
    });

    function renderPreviews() {
        if (!previewArea) return;
        previewArea.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const wrap = document.createElement('div');
            wrap.className = 'preview-item';

            const url = URL.createObjectURL(file);
            if (file.type.startsWith('video')) {
                const vid = document.createElement('video');
                vid.src = url;
                wrap.appendChild(vid);
            } else {
                const img = document.createElement('img');
                img.src = url;
                wrap.appendChild(img);
            }

            const removeBtn = document.createElement('button');
            removeBtn.className = 'preview-remove';
            removeBtn.innerHTML = '×';
            removeBtn.addEventListener('click', () => {
                selectedFiles.splice(idx, 1);
                renderPreviews();
            });

            wrap.appendChild(removeBtn);
            previewArea.appendChild(wrap);
        });
    }

    /* ── Mood toggle ────────────────────────────────────────── */
    const moodBar = document.getElementById('moodBar');
    const moodToggleBtn = document.getElementById('moodToggleBtn');
    const selectedMoodDisplay = document.getElementById('selectedMoodDisplay');
    let selectedMood = '';

    if (moodToggleBtn && moodBar) {
        moodToggleBtn.addEventListener('click', () => {
            const isOpen = moodBar.style.display !== 'none';
            moodBar.style.display = isOpen ? 'none' : 'flex';
            if (hashtagRow) hashtagRow.style.display = 'none';
            if (linkRow) {
                linkRow.style.display = 'none';
                linkRow.classList.remove('open');
            }
        });

        moodBar.addEventListener('click', (e) => {
            const btn = e.target.closest('.mood-btn');
            if (!btn) return;
            selectedMood = btn.dataset.mood;
            moodBar.querySelectorAll('.mood-btn').forEach(b => {
                b.classList.toggle('active', b === btn);
            });
            if (selectedMoodDisplay) {
                selectedMoodDisplay.textContent = 'Feeling: ' + selectedMood;
                selectedMoodDisplay.style.display = 'inline-block';
            }
        });
    }

    /* ── Hashtag toggle ─────────────────────────────────────── */
    const hashtagRow = document.getElementById('hashtagRow');
    const hashtagInput = document.getElementById('hashtagInput');
    const hashtagToggleBtn = document.getElementById('hashtagToggleBtn');

    if (hashtagToggleBtn && hashtagRow) {
        hashtagToggleBtn.addEventListener('click', () => {
            const isOpen = hashtagRow.style.display !== 'none';
            hashtagRow.style.display = isOpen ? 'none' : 'flex';
            if (!isOpen && hashtagInput) hashtagInput.focus();
            if (moodBar) moodBar.style.display = 'none';
            if (linkRow) {
                linkRow.style.display = 'none';
                linkRow.classList.remove('open');
            }
        });
    }

    /* ── Link toggle & apply ────────────────────────────────── */
    const linkRow = document.getElementById('linkRow');
    const linkToggleBtn = document.getElementById('linkToggleBtn');
    const linkNameInput = document.getElementById('linkNameInput');
    const linkUrlInput = document.getElementById('linkUrlInput');
    const applyLinkBtn = document.getElementById('applyLinkBtn');

    if (linkToggleBtn && linkRow) {
        linkToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = linkRow.style.display === 'flex' || linkRow.classList.contains('open');
            
            if (!isOpen) {
                linkRow.style.display = 'flex';
                linkRow.classList.add('open');
                setTimeout(() => { if (linkNameInput) linkNameInput.focus(); }, 50);
            } else {
                linkRow.style.display = 'none';
                linkRow.classList.remove('open');
            }
        });

        document.addEventListener('click', (e) => {
            if ((linkRow.style.display === 'flex' || linkRow.classList.contains('open')) && !linkRow.contains(e.target) && !linkToggleBtn.contains(e.target)) {
                linkRow.style.display = 'none';
                linkRow.classList.remove('open');
            }
        });
    }

    if (applyLinkBtn && postText) {
        applyLinkBtn.addEventListener('click', () => {
            let name = linkNameInput ? linkNameInput.value.trim() : '';
            let url = linkUrlInput ? linkUrlInput.value.trim() : '';

            if (!name || !url) {
                toast('Please provide both Link Name and URL', 'error');
                return;
            }

            // Add protocol if missing
            if (!/^https?:\/\//i.test(url)) {
                url = 'https://' + url;
            }

            const mdLink = `[${name}](${url})`;

            // Insert at cursor
            const startPos = postText.selectionStart;
            const endPos = postText.selectionEnd;
            const currentVal = postText.value;
            
            postText.value = currentVal.substring(0, startPos) + mdLink + currentVal.substring(endPos);
            postText.focus();
            postText.selectionStart = startPos + mdLink.length;
            postText.selectionEnd = postText.selectionStart;

            // Close and clear
            if (linkNameInput) linkNameInput.value = '';
            if (linkUrlInput) linkUrlInput.value = '';
            linkRow.style.display = 'none';
            linkRow.classList.remove('open');
        });
    }

    submitBtn.addEventListener('click', async () => {
        const text = postText?.value.trim();
        if (!text && selectedFiles.length === 0) {
            if (postFeedback) postFeedback.textContent = 'Write something or attach a file.';
            return;
        }

        submitBtn.disabled = true;
        if (postFeedback) postFeedback.textContent = '';

        const fd = new FormData();
        if (text) fd.append('text_content', text);
        if (selectedMood) fd.append('mood', selectedMood);
        if (hashtagInput && hashtagInput.value.trim()) fd.append('hashtags', hashtagInput.value.trim());
        selectedFiles.forEach(f => fd.append('media[]', f));

        try {
            const res = await apiPost(window.ROUTES.storePost, fd);
            if (res.ok) {
                // Clear composer
                if (postText) postText.value = '';
                if (linkNameInput) linkNameInput.value = '';
                if (linkUrlInput) linkUrlInput.value = '';
                if (linkRow) linkRow.style.display = 'none';
                
                if (hashtagInput) hashtagInput.value = '';
                if (hashtagRow) hashtagRow.style.display = 'none';
                if (moodBar) moodBar.style.display = 'none';
                if (selectedMoodDisplay) selectedMoodDisplay.style.display = 'none';
                selectedMood = '';
                if (moodBar) moodBar.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('active'));

                selectedFiles = [];
                renderPreviews();

                // Prepend post to feed
                const postEl = buildPostEl(res.post);
                const empty = postsFeed?.querySelector('.empty-state');
                if (empty) empty.remove();
                postsFeed?.insertBefore(postEl, postsFeed.firstChild);

                // Update post count
                const badge = document.getElementById('postCountBadge');
                if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;

                if (window.lucide) lucide.createIcons();
                toast('Post shared!', 'success');
            } else {
                const msg = res.errors ? Object.values(res.errors).flat().join(' ') : (res.message ?? 'Error.');
                if (postFeedback) postFeedback.textContent = msg;
                toast(msg, 'error');
            }
        } catch {
            toast('Network error.', 'error');
        }
        submitBtn.disabled = false;
    });
});

/* ── Build post DOM element from JSON ─────────────────────────── */
function buildPostEl(post) {
    const article = document.createElement('article');
    article.className = 'panel post';
    article.dataset.postId = post.id;

    const mediaHtml = post.media && post.media.length
        ? `<div class="post-media-grid media-count-${Math.min(post.media.length, 4)}" data-media="${escapeHtml(JSON.stringify(post.media))}">
        ${post.media.map(m => m.media_type === 'video'
            ? `<video src="${m.url}" controls class="post-media-item"></video>`
            : `<img src="${m.url}" class="post-media-item" alt="Post image">`
        ).join('')}
       </div>`
        : '';

    const menuHtml = post.can_manage
        ? `<div class="post-menu-wrap">
        <button class="icon-btn post-menu-btn" type="button"><i data-lucide="more-horizontal"></i></button>
        <div class="post-menu hidden">
          <button class="post-menu-item edit-post-btn" type="button"
              data-post-id="${post.id}"
              data-text="${escapeHtml(post.text_content ?? '')}"
              data-media="${escapeHtml(JSON.stringify(post.media || []))}">
            <i data-lucide="pencil"></i> Edit
          </button>
          <button class="post-menu-item delete-post-btn danger" type="button"
              data-post-id="${post.id}">
            <i data-lucide="trash-2"></i> Delete
          </button>
        </div>
       </div>`
        : '';

    const tagsHtml = post.hashtags && post.hashtags.length
      ? `<div class="post-tags">${post.hashtags.map(t => `<span class="tag">#${escapeHtml(t)}</span>`).join('')}</div>`
      : '';

    const isVerified = post.user && post.user.role === 'doctor' && post.user.doctor_status === 'approved';
    const verifiedBadge = isVerified
      ? `<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>`
      : '';
    const profTitleHtml = (isVerified && post.user.professional_titles && post.user.professional_titles.trim())
      ? `<div class="post-prof-title">${escapeHtml(post.user.professional_titles.trim())}</div>`
      : '';

    article.innerHTML = `
    <div class="post-head">
      <div class="avatar md"><img src="${post.user.avatar_url}" alt="${escapeHtml(post.user.name)}"></div>
      <div class="post-meta">
        <div class="post-name-row">
          <div class="post-name">${escapeHtml(post.user.name)}</div>
          <span class="post-handle">@${escapeHtml(post.user.username)}</span>
          ${verifiedBadge}
        </div>
        ${profTitleHtml}
        <div class="post-sub">${post.created_at}</div>
      </div>
      ${menuHtml}
    </div>
    ${post.text_content ? `<div class="post-body post-text-content js-collapsible">${parseMarkdownLinks(escapeHtml(post.text_content))}</div>` : ''}
    ${tagsHtml}
    ${post.resource ? `
      <a href="${post.resource.url}" class="post-resource-card" style="margin-bottom:12px;">
        <div class="res-mini-thumb"><img src="${post.resource.thumbnail_url}"></div>
        <div class="res-mini-info">
          <div class="res-mini-type">${escapeHtml(post.resource.type)}</div>
          <div class="res-mini-title">${escapeHtml(post.resource.title)}</div>
          <div class="res-mini-desc">${escapeHtml(post.resource.description)}</div>
        </div>
      </a>` : ''}
    ${post.shared_post ? renderSharedPostCard(post.shared_post) : ''}
    ${mediaHtml}
    <div class="post-actions">
      <button class="post-btn like-btn ${post.is_liked ? 'liked' : ''}" type="button" data-post-id="${post.id}">
        <i data-lucide="heart" class="like-icon"></i>
        <span class="like-count">${post.like_count}</span>
      </button>
      <button class="post-btn comment-toggle-btn" type="button" data-post-id="${post.id}">
        <i data-lucide="message-square"></i>
        <span class="comment-count">${post.comment_count}</span>
      </button>
      <button class="post-btn save-btn ${post.is_saved ? 'saved' : ''} end" type="button" data-post-id="${post.id}" data-saved="${post.is_saved ? 1 : 0}" title="Save">
        <i data-lucide="bookmark"></i>
      </button>
      <button class="post-btn js-share-post" type="button" data-post-id="${post.id}" data-preview="${escapeHtml((post.text_content || '').slice(0, 80) || 'a post')}"><i data-lucide="share-2"></i></button>
    </div>
    <div class="comments-section hidden" id="comments-${post.id}">
      <div class="comment-composer">
        <div class="avatar sm"><img src="${post.user.avatar_url}" alt="You"></div>
        <div class="comment-input-wrap">
          <input type="text" class="comment-input" placeholder="Write a comment…" data-post-id="${post.id}">
          <button class="comment-send-btn" type="button" data-post-id="${post.id}"><i data-lucide="send"></i></button>
        </div>
      </div>
      <div class="comments-list" id="comments-list-${post.id}">
        ${post.comments.map(c => buildCommentHtml(c)).join('')}
      </div>
    </div>`;

    // Let shared UI scripts enhance newly-rendered posts
    try {
        document.dispatchEvent(new CustomEvent('post:rendered', { detail: { root: article } }));
    } catch (e) {}

    return article;
}

function renderSharedPostCard(sp) {
    if (!sp || !sp.user) return '';
    const profileUrl = sp.user.profile_url || `/profile/${sp.user.id}`;
    const spResource = sp.resource ? `
      <a href="${sp.resource.url}" class="post-resource-card" style="margin-top:10px;">
        <div class="res-mini-thumb"><img src="${sp.resource.thumbnail_url}"></div>
        <div class="res-mini-info">
          <div class="res-mini-type">${escapeHtml(sp.resource.type)}</div>
          <div class="res-mini-title">${escapeHtml(sp.resource.title)}</div>
          <div class="res-mini-desc">${escapeHtml(sp.resource.description)}</div>
        </div>
      </a>` : '';

    const spMediaGrid = (sp.media && sp.media.length)
      ? `<div class="post-media-grid shared-post-media-grid media-count-${Math.min(sp.media.length, 4)}" data-media="${escapeHtml(JSON.stringify(sp.media))}">
          ${sp.media.slice(0, 4).map(m => m.media_type === 'video'
            ? `<video src="${m.url}" controls class="post-media-item"></video>`
            : `<img src="${m.url}" alt="Shared media" class="post-media-item">`
          ).join('')}
          ${sp.media.length > 4 ? `<div class="media-more">+${sp.media.length - 4}</div>` : ''}
        </div>`
      : '';

    const isVerified = sp.user && sp.user.doctor_status === 'approved' && (!sp.user.role || sp.user.role === 'doctor');
    const verifiedBadge = isVerified
      ? `<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>`
      : '';
    const profTitle = (isVerified && sp.user.professional_titles && sp.user.professional_titles.trim())
      ? `<div class="post-prof-title">${escapeHtml(sp.user.professional_titles.trim())}</div>`
      : '';

    return `
      <div class="shared-post-card">
        <div class="post-head">
          <a href="${profileUrl}" class="avatar md"><img src="${sp.user.avatar_url}" alt="${escapeHtml(sp.user.name)}"></a>
          <div class="post-meta">
            <div class="post-name-row">
              <a href="${profileUrl}" class="post-name" style="color:inherit;text-decoration:none;">${escapeHtml(sp.user.name)}</a>
              <span class="post-handle">@${escapeHtml(sp.user.username)}</span>
              ${verifiedBadge}
            </div>
            ${profTitle}
            <div class="post-sub">${sp.created_at}</div>
          </div>
        </div>
        ${sp.text_content ? `<div class="post-body js-collapsible">${parseMarkdownLinks(escapeHtml(sp.text_content))}</div>` : ''}
        ${spResource}
        ${spMediaGrid}
      </div>`;
}

function buildCommentHtml(comment, isReply = false, parentId = null) {
    const canDelete = comment.can_delete;
    const repliesHtml = (!isReply && comment.replies) ? comment.replies.map(r => buildCommentHtml(r, true, comment.id)).join('') : '';
    const targetCommentId = isReply ? parentId : comment.id;
    return `
    <div class="comment-item ${isReply ? 'reply-item' : ''}" id="comment-${comment.id}">
      <div class="avatar sm"><img src="${comment.user.avatar_url}" alt="${escapeHtml(comment.user.name)}"></div>
      <div class="comment-bubble">
        <div class="comment-meta">
          <span class="comment-author">${escapeHtml(comment.user.name)}</span>
          <span class="comment-time">${comment.created_at}</span>
          ${canDelete ? `<button class="comment-delete-btn" type="button" data-comment-id="${comment.id}"><i data-lucide="x"></i></button>` : ''}
        </div>
        <p class="comment-text">${parseMarkdownLinks(escapeHtml(comment.comment_text))}</p>
        <button class="reply-toggle-btn" type="button" data-comment-id="${targetCommentId}" data-post-id="${comment.post_id ?? ''}" data-reply-to="${escapeHtml(comment.user.username)}">Reply</button>
        ${!isReply ? `
        <div class="reply-composer hidden" id="reply-composer-${comment.id}">
          <input type="text" class="comment-input reply-input" placeholder="Write a reply…" data-post-id="${comment.post_id ?? ''}" data-parent-id="${comment.id}">
          <button class="comment-send-btn reply-send-btn" type="button" data-post-id="${comment.post_id ?? ''}" data-parent-id="${comment.id}"><i data-lucide="send"></i></button>
        </div>
        <div class="replies-list" id="replies-${comment.id}">${repliesHtml}</div>
        ` : ''}
      </div>
    </div>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function parseMarkdownLinks(text) {
    if (!text) return '';
    // We match \[([^\]]+)\]\(([^)]+)\)
    return text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function(match, name, url) {
        return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">' + name + '</a>';
    });
}

/* ================================================================
   VIEW PROFILE COVER / AVATAR (lightbox)
================================================================ */
document.addEventListener('click', (e) => {
    const target = e.target.closest('[data-view-image]');
    if (!target) return;

    const src = target.dataset.fullsrc || target.getAttribute('src');
    if (!src) return;

    if (document.querySelector('.photo-lightbox')) return;

    const lb = document.createElement('div');
    lb.className = 'photo-lightbox';
    lb.style.cssText = 'position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); z-index:99999; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s;';

    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = 'position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:32px; width:48px; height:48px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:4px; transition:background 0.2s; z-index:100000;';
    closeBtn.onmouseover = () => closeBtn.style.background = 'rgba(255,255,255,0.25)';
    closeBtn.onmouseout = () => closeBtn.style.background = 'rgba(255,255,255,0.15)';
    lb.appendChild(closeBtn);

    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Preview';
    img.style.cssText = 'max-width:90vw; max-height:90vh; object-fit:contain; border-radius:8px; box-shadow:0 10px 40px rgba(0,0,0,0.5); transition:transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform:scale(0.98);';
    lb.appendChild(img);

    const closeLb = () => {
        lb.style.opacity = '0';
        img.style.transform = 'scale(0.95)';
        setTimeout(() => lb.remove(), 300);
    };

    closeBtn.onclick = closeLb;
    lb.onclick = (ev) => { if (ev.target === lb) closeLb(); };
    document.addEventListener('keydown', function escListener(ev) {
        if (ev.key === 'Escape') {
            closeLb();
            document.removeEventListener('keydown', escListener);
        }
    });

    document.body.appendChild(lb);
    requestAnimationFrame(() => { lb.style.opacity = '1'; img.style.transform = 'scale(1)'; });
});

/* ================================================================
   FOLLOW / UNFOLLOW
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const followBtn = document.getElementById('followBtn');
    if (!followBtn) return;

    followBtn.addEventListener('click', async () => {
        const userId = followBtn.dataset.userId;
        if (!userId) return;

        if (followBtn.dataset.busy === '1') return;
        followBtn.dataset.busy = '1';
        followBtn.disabled = true;
        const isFollowingNow = followBtn.dataset.following === '1';
        const action = isFollowingNow ? 'unfollow' : 'follow';
        try {
            const res = await apiPost(window.ROUTES.toggleFollow(userId), { action: action });
            if (res.ok) {
                const isFollowing = res.following !== undefined ? !!res.following : !isFollowingNow;
                followBtn.dataset.following = isFollowing ? '1' : '0';
                followBtn.classList.toggle('following', isFollowing);
                followBtn.innerHTML = `<i data-lucide="${isFollowing ? 'user-check' : 'user-plus'}"></i> ${isFollowing ? 'Following' : 'Follow'}`;
                if (window.lucide) lucide.createIcons({ root: followBtn });
                if (window.refreshNetwork) window.refreshNetwork(true);
            } else {
                toast(res.message ?? 'Error.', 'error');
            }
        } catch {
            toast('Network error.', 'error');
        }
        followBtn.dataset.busy = '0';
        followBtn.disabled = false;
    });
});

/* ================================================================
   NETWORK TAB FILTER + SEARCH + LIVE UPDATE
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const tabs = Array.from(document.querySelectorAll('.network-tab'));
    const list = document.getElementById('networkList');
    const search = document.getElementById('networkSearch');
    if (!tabs.length || !list) return;

    let active = 'following';
    let cache = null;
    let loading = false;

    tabs.forEach(btn => {
        if (!btn.dataset.baseLabel) btn.dataset.baseLabel = btn.textContent.trim();
    });

    function setTabCount(type, count) {
        const btn = tabs.find(t => t.dataset.filter === type);
        if (!btn) return;
        const base = btn.dataset.baseLabel || btn.textContent.trim();
        btn.textContent = `${base} (${count})`;
    }

    function buildRow(u, type) {
        const a = document.createElement('a');
        a.href = u.profile_url || (`/profile/${u.id}`);
        a.className = 'prof-user-row';
        a.dataset.type = type;

        const name = (u.name || '').toString();
        const uname = (u.username || '').toString();
        a.dataset.name = name.toLowerCase();
        a.dataset.username = uname.toLowerCase();

        a.innerHTML = `
      <div class="avatar sm"><img src="${u.avatar_url}" alt="${escapeHtml(name || 'User')}"></div>
      <div class="prof-user-meta">
        <div class="prof-user-name">${escapeHtml(name || uname || 'User')}</div>
        <div class="prof-user-handle">@${escapeHtml(uname)}</div>
      </div>`;
        return a;
    }

    function buildEmpty(type) {
        const div = document.createElement('div');
        div.className = 'empty-state soft';
        div.dataset.type = type;
        const icon = type === 'following' ? 'user-plus' : 'users';
        const msg = type === 'following' ? 'No following yet.' : 'No followers yet.';
        div.innerHTML = `<i data-lucide="${icon}"></i><p>${msg}</p>`;
        return div;
    }

    function renderNetwork(data) {
        const following = Array.isArray(data.following) ? data.following : [];
        const followers = Array.isArray(data.followers) ? data.followers : [];

        list.innerHTML = '';
        following.forEach(u => list.appendChild(buildRow(u, 'following')));
        if (!following.length) list.appendChild(buildEmpty('following'));

        followers.forEach(u => list.appendChild(buildRow(u, 'followers')));
        if (!followers.length) list.appendChild(buildEmpty('followers'));

        setTabCount('following', data.following_count ?? following.length);
        setTabCount('followers', data.followers_count ?? followers.length);

        if (window.lucide) lucide.createIcons({ root: list });
        apply();
    }

    function apply() {
        const q = (search?.value || '').trim().toLowerCase();
        const rows = Array.from(list.querySelectorAll('.prof-user-row, .empty-state'));
        let anyVisible = false;

        rows.forEach(row => {
            const type = row.getAttribute('data-type');
            if (type !== active) {
                row.style.display = 'none';
                return;
            }
            if (row.classList.contains('empty-state')) {
                row.style.display = q ? 'none' : '';
                if (!q) anyVisible = true;
                return;
            }

            const name = row.getAttribute('data-name') || '';
            const username = row.getAttribute('data-username') || '';
            const show = !q || name.includes(q) || username.includes(q) || ('@' + username).includes(q);
            row.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });

        // If no results and no empty-state shown, show a lightweight empty hint
        if (!anyVisible) {
            // no-op for now; keeping simple
        }
    }

    async function refreshNetwork(force) {
        if (!window.ROUTES || !window.ROUTES.profileNetwork || !window.PROFILE_USER_ID) {
            apply();
            return;
        }
        if (loading) return;
        if (!force && cache) {
            renderNetwork(cache);
            return;
        }
        loading = true;
        try {
            const res = await apiGet(window.ROUTES.profileNetwork(window.PROFILE_USER_ID));
            if (res && res.ok) {
                cache = res;
                renderNetwork(res);
            }
        } catch {
            // keep existing list on error
        }
        loading = false;
    }

    window.refreshNetwork = refreshNetwork;

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            active = btn.dataset.filter || 'following';
            apply();
            refreshNetwork(false);
        });
    });

    search?.addEventListener('input', apply);
    refreshNetwork(false);
    apply();
});

/* ================================================================
   POST INTERACTIONS — Like, 3-dot menu, Edit, Delete
================================================================ */
document.addEventListener('click', async (e) => {
    const postsFeed = document.getElementById('postsFeed');

    // ── LIKE ─────────────────────────────────────────────────────
    const likeBtn = e.target.closest('.like-btn');
    if (likeBtn) {
        const postId = likeBtn.dataset.postId;
        try {
            const res = await apiPost(window.ROUTES.toggleLike(postId), {});
            if (res.ok) {
                likeBtn.classList.toggle('liked', res.liked);
                const countEl = likeBtn.querySelector('.like-count');
                if (countEl) countEl.textContent = res.like_count;
                if (window.lucide) lucide.createIcons();
            }
        } catch { toast('Error.', 'error'); }
        return;
    }

    // ── SAVE / BOOKMARK ──────────────────────────────────────────
    const saveBtn = e.target.closest('.save-btn');
    if (saveBtn) return;

    // ── TOGGLE COMMENTS ───────────────────────────────────────────
    const commentToggle = e.target.closest('.comment-toggle-btn');
    if (commentToggle) {
        const postId = commentToggle.dataset.postId;
        const section = document.getElementById(`comments-${postId}`);
        section?.classList.toggle('hidden');
        if (window.lucide) lucide.createIcons();
        return;
    }

    // ── 3-DOT MENU TOGGLE ────────────────────────────────────────
    const menuBtn = e.target.closest('.post-menu-btn');
    if (menuBtn) {
        e.stopPropagation();
        const menu = menuBtn.nextElementSibling;
        // Close all other menus
        $$('.post-menu').forEach(m => { if (m !== menu) m.classList.add('hidden'); });
        menu?.classList.toggle('hidden');
        if (window.lucide) lucide.createIcons();
        return;
    }

    // Close all open menus on click elsewhere
    if (!e.target.closest('.post-menu-wrap')) {
        $$('.post-menu').forEach(m => m.classList.add('hidden'));
    }

    // ── EDIT POST ────────────────────────────────────────────────
    const editBtn = e.target.closest('.edit-post-btn');
    if (editBtn) {
        const postId = editBtn.dataset.postId;
        const text = editBtn.dataset.text;
        const mediaData = editBtn.dataset.media ? JSON.parse(editBtn.dataset.media) : [];
        let newFiles = [];
        let deletedMediaIds = [];

        const article = document.querySelector(`[data-post-id="${postId}"]`);
        if (!article) return;

        const textEl = article.querySelector('.post-text-content');
        const existing = article.querySelector('.post-edit-area');
        if (existing) return;  // Already editing

        // Hide menu
        editBtn.closest('.post-menu')?.classList.add('hidden');

        // Helper function to render existing media preview
        function renderEditPreviews(wrap) {
            const grid = wrap.querySelector('.edit-media-grid');
            grid.innerHTML = '';

            mediaData.forEach(m => {
                if (deletedMediaIds.includes(m.id)) return;
                const div = document.createElement('div');
                div.className = 'edit-preview-item';
                div.style.position = 'relative';
                div.style.borderRadius = '8px';
                div.style.overflow = 'hidden';
                div.style.height = '80px';
                div.style.width = '80px';
                div.style.border = '1px solid var(--border)';

                if (m.media_type === 'video') {
                    div.innerHTML = `<video src="${m.url}" style="width:100%;height:100%;object-fit:cover;"></video>`;
                } else {
                    div.innerHTML = `<img src="${m.url}" style="width:100%;height:100%;object-fit:cover;">`;
                }

                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '×';
                removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;line-height:20px;text-align:center;padding:0;font-size:14px;';
                removeBtn.onclick = () => {
                    deletedMediaIds.push(m.id);
                    renderEditPreviews(wrap);
                };
                div.appendChild(removeBtn);
                grid.appendChild(div);
            });

            newFiles.forEach((f, idx) => {
                const div = document.createElement('div');
                div.className = 'edit-preview-item';
                div.style.position = 'relative';
                div.style.borderRadius = '8px';
                div.style.overflow = 'hidden';
                div.style.height = '80px';
                div.style.width = '80px';
                div.style.border = '1px dashed var(--brand)';

                const url = URL.createObjectURL(f);
                if (f.type.startsWith('video')) {
                    div.innerHTML = `<video src="${url}" style="width:100%;height:100%;object-fit:cover;"></video>`;
                } else {
                    div.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;">`;
                }

                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '×';
                removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;line-height:20px;text-align:center;padding:0;font-size:14px;';
                removeBtn.onclick = () => {
                    newFiles.splice(idx, 1);
                    renderEditPreviews(wrap);
                };
                div.appendChild(removeBtn);
                grid.appendChild(div);
            });
        }

        // Build inline editor
        const editorWrap = document.createElement('div');
        editorWrap.className = 'post-edit-area';
        editorWrap.innerHTML = `
      <textarea class="post-edit-textarea" style="width:100%; min-height:80px; padding:10px 14px; border:1px solid var(--brand); border-radius:12px; background:var(--input-bg); color:var(--text); font-size:14px; resize:vertical; outline:none; margin-bottom:8px;">${escapeHtml(text)}</textarea>
      <div class="edit-media-grid" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;"></div>
      <div class="post-edit-actions" style="display:flex; gap:8px; justify-content:space-between; align-items:center;">
        <label class="btn-cancel" style="padding:6px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border); background:var(--chip-bg); color:var(--text); cursor:pointer; display:flex; align-items:center; gap:4px;"><i data-lucide="image" style="width:14px;height:14px;"></i> Add Photo/Video<input type="file" multiple accept="image/*,video/*" class="edit-media-input" style="display:none;"></label>
        <div style="display:flex; gap:8px;">
            <button class="btn-cancel btn-edit-cancel" type="button" style="padding:6px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border); background:var(--chip-bg); color:var(--text); cursor:pointer;">Cancel</button>
            <button class="btn-save btn-edit-save" type="button" style="padding:6px 16px; font-size:13px; border-radius:8px; border:none; background:linear-gradient(90deg, #7c3aed, #4f46e5); color:#fff; cursor:pointer;" data-post-id="${postId}">Save</button>
        </div>
      </div>`;

        if (textEl) {
            textEl.style.display = 'none';
            textEl.insertAdjacentElement('afterend', editorWrap);
        } else {
            article.querySelector('.post-head').insertAdjacentElement('afterend', editorWrap);
        }

        const oldMediaGrid = article.querySelector('.post-media-grid');
        if (oldMediaGrid) oldMediaGrid.style.display = 'none';

        if (window.lucide) lucide.createIcons({ root: editorWrap });

        editorWrap.querySelector('textarea').focus();

        const fileInput = editorWrap.querySelector('.edit-media-input');
        fileInput.addEventListener('change', (ev) => {
            const files = Array.from(ev.target.files);
            newFiles = newFiles.concat(files);
            renderEditPreviews(editorWrap);
            fileInput.value = '';
        });

        renderEditPreviews(editorWrap);

        editorWrap.querySelector('.btn-edit-cancel').addEventListener('click', () => {
            editorWrap.remove();
            if (textEl) textEl.style.display = '';
            if (oldMediaGrid) oldMediaGrid.style.display = '';
        });

        editorWrap.querySelector('.btn-edit-save').addEventListener('click', async function () {
            const newText = editorWrap.querySelector('textarea').value.trim();
            const hasExistingMedia = mediaData.filter(m => !deletedMediaIds.includes(m.id)).length > 0;

            if (!newText && newFiles.length === 0 && !hasExistingMedia) {
                toast('Post cannot be empty.', 'error');
                return;
            }

            const saveBtn = this;
            saveBtn.disabled = true;
            saveBtn.innerHTML = 'Saving...';

            const fd = new FormData();
            fd.append('_method', 'PUT');
            fd.append('text_content', newText);
            deletedMediaIds.forEach(id => fd.append('deleted_media[]', id));
            newFiles.forEach(f => fd.append('media[]', f));

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : '';

                const res = await fetch(window.ROUTES.updatePost(postId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd
                }).then(r => r.json());

                if (res.ok) {
                    editorWrap.remove();
                    toast('Post updated!', 'success');

                    const newArticle = buildPostEl(res.post);
                    article.replaceWith(newArticle);
                    if (window.lucide) lucide.createIcons({ root: newArticle });
                } else {
                    toast(res.message ?? 'Error.', 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save';
                }
            } catch (err) {
                toast('Network error.', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save';
            }
        });
        return;
    }

    // ── DELETE POST ───────────────────────────────────────────────
    const deleteBtn = e.target.closest('.delete-post-btn');
    if (deleteBtn) {
        if (!confirm('Delete this post? This cannot be undone.')) return;

        const postId = deleteBtn.dataset.postId;
        const article = document.querySelector(`[data-post-id="${postId}"]`);

        const res = await apiPost(window.ROUTES.destroyPost(postId), {}, 'DELETE');
        if (res.ok) {
            article?.remove();
            const badge = document.getElementById('postCountBadge');
            if (badge && parseInt(badge.textContent) > 0) badge.textContent = parseInt(badge.textContent) - 1;
            toast('Post deleted.', 'success');

            // Show empty state if no posts
            const feed = document.getElementById('postsFeed');
            if (feed && feed.querySelectorAll('.post').length === 0) {
                feed.innerHTML = `<div class="empty-state panel">
          <i data-lucide="file-text"></i><p>No posts yet.</p></div>`;
                if (window.lucide) lucide.createIcons();
            }
        } else {
            toast(res.message ?? 'Error.', 'error');
        }
        return;
    }

    // ── COMMENT SEND ──────────────────────────────────────────────
    const sendBtn = e.target.closest('.comment-send-btn:not(.reply-send-btn)');
    if (sendBtn) {
        const postId = sendBtn.dataset.postId;
        const input = sendBtn.closest('.comment-input-wrap')?.querySelector('.comment-input')
            ?? sendBtn.previousElementSibling;
        await sendComment(postId, null, input);
        return;
    }

    // ── REPLY SEND ────────────────────────────────────────────────
    const replySend = e.target.closest('.reply-send-btn');
    if (replySend) {
        const postId = replySend.dataset.postId;
        const parentId = replySend.dataset.parentId;
        const input = document.querySelector(`.reply-input[data-parent-id="${parentId}"]`);
        await sendComment(postId, parentId, input);
        return;
    }

    // ── REPLY TOGGLE ──────────────────────────────────────────────
    const replyToggle = e.target.closest('.reply-toggle-btn');
    if (replyToggle) {
        const commentId = replyToggle.dataset.commentId;
        const replyTo = replyToggle.dataset.replyTo;
        const composer = document.getElementById(`reply-composer-${commentId}`);
        if (composer) {
            composer.classList.remove('hidden');
            const inp = composer.querySelector('input');
            if (inp) {
                if (replyTo && replyTo !== 'undefined' && replyTo !== '') {
                    if (window.applyReplyMention) {
                        window.applyReplyMention(inp, replyTo);
                    } else {
                        inp.focus();
                        const tag = `@${replyTo} `;
                        const currentVal = inp.value;
                        if (!currentVal.startsWith(tag)) {
                            if (/^@[\w.\-]+ /.test(currentVal)) {
                                inp.value = currentVal.replace(/^@[\w.\-]+ /, tag);
                            } else {
                                inp.value = tag + currentVal;
                            }
                        }
                    }
                } else {
                    inp.focus();
                }
            }
        }
        return;
    }

    // ── DELETE COMMENT ────────────────────────────────────────────
    const delComment = e.target.closest('.comment-delete-btn');
    if (delComment) {
        if (!confirm('Delete this comment?')) return;
        const commentId = delComment.dataset.commentId;
        const res = await apiPost(window.ROUTES.destroyComment(commentId), {}, 'DELETE');
        if (res.ok) {
            const el = document.getElementById(`comment-${commentId}`);
            el?.remove();
            toast('Comment deleted.', 'success');
        } else {
            toast(res.message ?? 'Error.', 'error');
        }
        return;
    }

    // ── IMAGE LIGHTBOX ──────────────────────────────────────────
    const mediaItem = e.target.closest('.post-media-item') || e.target.closest('.media-more');
    if (mediaItem && !e.target.closest('.edit-post-btn') && !e.target.closest('.delete-post-btn')) {
        if (document.querySelector('.photo-lightbox')) return;

        const lb = document.createElement('div');
        lb.className = 'photo-lightbox';
        lb.style.cssText = 'position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); z-index:99999; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s;';

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = 'position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:32px; width:48px; height:48px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:4px; transition:background 0.2s; z-index:100000;';
        closeBtn.onmouseover = () => closeBtn.style.background = 'rgba(255,255,255,0.25)';
        closeBtn.onmouseout = () => closeBtn.style.background = 'rgba(255,255,255,0.15)';
        lb.appendChild(closeBtn);

        const mediaGrid = mediaItem.closest('.post-media-grid');
        let mediaList = mediaGrid && mediaGrid.dataset.media ? JSON.parse(mediaGrid.dataset.media) : [];
        let currentIndex = 0;
        let content;

        if (mediaList.length > 0) {
            if (mediaItem.classList.contains('media-more')) {
                currentIndex = 3;
            } else {
                const items = Array.from(mediaGrid.querySelectorAll('.post-media-item'));
                currentIndex = items.indexOf(mediaItem);
                if (currentIndex === -1) currentIndex = 0;
            }
        } else {
            const isVideo = mediaItem.tagName && mediaItem.tagName.toLowerCase() === 'video';
            let src = mediaItem.src || (mediaItem.querySelector('img, video') && mediaItem.querySelector('img, video').src);
            if (!src && mediaItem.style.backgroundImage) {
                src = mediaItem.style.backgroundImage.slice(4, -1).replace(/"/g, "");
            }
            if (src) {
                mediaList = [{ url: src, media_type: isVideo ? 'video' : 'image' }];
            } else {
                return;
            }
        }

        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '‹';
        prevBtn.style.cssText = 'position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:40px; width:56px; height:56px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:6px; transition:background 0.2s; z-index:100000;';
        prevBtn.onmouseover = () => prevBtn.style.background = 'rgba(255,255,255,0.25)';
        prevBtn.onmouseout = () => prevBtn.style.background = 'rgba(255,255,255,0.15)';
        lb.appendChild(prevBtn);

        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '›';
        nextBtn.style.cssText = 'position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:40px; width:56px; height:56px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:6px; transition:background 0.2s; z-index:100000;';
        nextBtn.onmouseover = () => nextBtn.style.background = 'rgba(255,255,255,0.25)';
        nextBtn.onmouseout = () => nextBtn.style.background = 'rgba(255,255,255,0.15)';
        lb.appendChild(nextBtn);

        const updateContent = (index) => {
            if (index < 0 || index >= mediaList.length) return;
            currentIndex = index;
            const m = mediaList[currentIndex];

            let newContent;
            if (m.media_type === 'video') {
                newContent = document.createElement('video');
                newContent.src = m.url;
                newContent.controls = true;
                newContent.autoplay = true;
            } else {
                newContent = document.createElement('img');
                newContent.src = m.url;
            }
            newContent.className = 'lightbox-media-content';
            newContent.style.cssText = 'max-width:90vw; max-height:90vh; object-fit:contain; border-radius:8px; box-shadow:0 10px 40px rgba(0,0,0,0.5); transition:transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform:scale(1);';

            if (content && content.parentNode) {
                lb.replaceChild(newContent, content);
            } else {
                lb.appendChild(newContent);
            }
            content = newContent;

            prevBtn.style.display = currentIndex > 0 ? 'flex' : 'none';
            nextBtn.style.display = currentIndex < mediaList.length - 1 ? 'flex' : 'none';
        };

        updateContent(currentIndex);
        document.body.appendChild(lb);

        requestAnimationFrame(() => {
            lb.style.opacity = '1';
            if (content) content.style.transform = 'scale(1)';
        });

        const closeLb = () => {
            lb.style.opacity = '0';
            if (content) content.style.transform = 'scale(0.95)';
            setTimeout(() => lb.remove(), 300);
        };

        closeBtn.onclick = closeLb;
        prevBtn.onclick = (e) => { e.stopPropagation(); updateContent(currentIndex - 1); };
        nextBtn.onclick = (e) => { e.stopPropagation(); updateContent(currentIndex + 1); };
        lb.onclick = (ev) => { if (ev.target === lb) closeLb(); };

        const escListener = (ev) => {
            if (ev.key === 'Escape') closeLb();
            else if (ev.key === 'ArrowLeft') updateContent(currentIndex - 1);
            else if (ev.key === 'ArrowRight') updateContent(currentIndex + 1);
        };

        let origClose = closeLb;
        closeLb = () => {
            origClose();
            document.removeEventListener('keydown', escListener);
        };
        document.addEventListener('keydown', escListener);

        return;
    }
});

/* ── Send comment/reply ───────────────────────────────────────── */
async function sendComment(postId, parentId, inputEl) {
    const text = inputEl?.value?.trim();
    if (!text) return;

    const body = { comment_text: text };
    if (parentId) body.parent_comment_id = parentId;

    const res = await apiPost(window.ROUTES.storeComment(postId), body);
    if (res.ok) {
        inputEl.value = '';

        const comment = res.comment;
        const html = buildCommentHtml(comment, !!parentId, parentId);

        if (parentId) {
            // Append to replies list
            const repliesList = document.getElementById(`replies-${parentId}`);
            if (repliesList) {
                repliesList.insertAdjacentHTML('beforeend', html);
            }
            // Hide reply composer
            document.getElementById(`reply-composer-${parentId}`)?.classList.add('hidden');
        } else {
            // Prepend to comments list
            const commentsList = document.getElementById(`comments-list-${postId}`);
            if (commentsList) {
                commentsList.insertAdjacentHTML('afterbegin', html);
            }
            // Update comment count
            const article = document.querySelector(`[data-post-id="${postId}"]`);
            const countEl = article?.querySelector('.comment-count');
            if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;
        }

        if (window.lucide) lucide.createIcons();
    } else {
        toast(res.message ?? 'Error.', 'error');
    }
}

/* ── Enter key on comment inputs ─────────────────────────────── */
document.addEventListener('keydown', async (e) => {
    if (e.key !== 'Enter') return;

    const input = e.target;
    if (input.matches('.comment-input:not(.reply-input)')) {
        const postId = input.dataset.postId;
        await sendComment(postId, null, input);
    } else if (input.matches('.reply-input')) {
        const postId = input.dataset.postId;
        const parentId = input.dataset.parentId;
        await sendComment(postId, parentId, input);
    }
});

/* ================================================================
   SCROLL TO POST FROM QUERY PARAMS
================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const postId = params.get('post_id');
    const commentId = params.get('comment_id');
    if (!postId) return;

    let done = false;
    const tryScroll = () => {
        if (done) return;
        const article = document.querySelector(`[data-post-id="${postId}"]`);
        if (!article) return;

        article.scrollIntoView({ behavior: 'smooth', block: 'center' });
        article.classList.add('post-highlight');
        setTimeout(() => article.classList.remove('post-highlight'), 3000);

        if (commentId) {
            const section = article.querySelector('.comments-section');
            section?.classList.remove('hidden');
            const commentEl = document.getElementById(`comment-${commentId}`);
            commentEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        done = true;
    };

    tryScroll();
    document.addEventListener('post:rendered', tryScroll);
});

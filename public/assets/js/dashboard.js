// public/assets/js/dashboard.js
/* ================================================================
   DASHBOARD JS – handles:
   • Theme toggle + profile dropdown
   • Composer  (text, media, mood, hashtags)  → POST /profile/posts
   • Live feed loading  → GET  /api/dashboard/feed
   • Like / unlike  → POST /profile/posts/{id}/like
   • Comments / replies  → POST /profile/posts/{id}/comments
   • Delete comment  → DELETE /profile/comments/{id}
================================================================ */

document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) lucide.createIcons();

  /* ── CSRF ────────────────────────────────────────────────── */
  var CSRF = '';
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  if (csrfMeta) CSRF = csrfMeta.content;

  /* ── Helpers ─────────────────────────────────────────────── */
  function apiPost(url, body, method) {
    method = method || 'POST';
    var isFormData = body instanceof FormData;
    return fetch(url, {
      method: method,
      headers: isFormData
        ? { 'X-CSRF-TOKEN': CSRF }
        : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: isFormData ? body : JSON.stringify(body),
    }).then(function (r) { 
        if (!r.ok || !r.headers.get('content-type')?.includes('application/json')) {
            return r.text().then(function(t) {
                console.error("API POST Error (" + url + "):", r.status, t.substring(0, 200));
                throw new Error("HTTP " + r.status);
            });
        }
        return r.json(); 
    });
  }

  function apiGet(url) {
    return fetch(url, {
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(function (r) { 
        if (!r.ok || !r.headers.get('content-type')?.includes('application/json')) {
            return r.text().then(function(t) {
                console.error("API GET Error (" + url + "):", r.status, t.substring(0, 200));
                throw new Error("HTTP " + r.status);
            });
        }
        return r.json(); 
    });
  }

  function showToast(msg, type) {
    var el = document.getElementById('dash-toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'dash-toast' + (type === 'error' ? ' error' : '') + ' show';
    clearTimeout(el._t);
    el._t = setTimeout(function () { el.classList.remove('show'); }, 3200);
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* ── Theme toggle ───────────────────────────────────────── */
  var root = document.documentElement;
  var savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') root.classList.add('theme-dark');

  var themeBtn = document.getElementById('themeToggleBtn');

  function updateThemeBtn() {
    if (!themeBtn) return;
    var isDark = root.classList.contains('theme-dark');
    var label = themeBtn.querySelector('span');
    var icon = themeBtn.querySelector('i');
    if (label) label.textContent = isDark ? 'Light mode' : 'Dark mode';
    if (icon) icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
    if (window.lucide) lucide.createIcons();
  }
  updateThemeBtn();

  if (themeBtn) {
    themeBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      root.classList.toggle('theme-dark');
      localStorage.setItem('theme', root.classList.contains('theme-dark') ? 'dark' : 'light');
      updateThemeBtn();
    });
  }

  /* ── Profile dropdown ───────────────────────────────────── */
  var toggle = document.getElementById('profileToggle');
  var menu = document.getElementById('profileDropdown');

  if (toggle && menu) {
    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = menu.classList.contains('open');
      menu.classList.toggle('open', !isOpen);
      toggle.setAttribute('aria-expanded', String(!isOpen));
      if (!isOpen && window.lucide) lucide.createIcons();
    });
    menu.addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('click', function () {
      menu.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { menu.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }
    });
  }

  /* ── Header Search ──────────────────────────────────────── */
  var searchInput = document.querySelector('.dash-search input');
  var searchWrap = document.querySelector('.dash-search');
  var searchDropdown = null;
  var searchTimer = null;

  if (searchInput && searchWrap) {
    searchDropdown = document.createElement('div');
    searchDropdown.className = 'search-dropdown';
    searchWrap.appendChild(searchDropdown);

    searchInput.addEventListener('input', function (e) {
      var query = e.target.value.trim();
      clearTimeout(searchTimer);

      if (!query) {
        searchDropdown.classList.remove('open');
        return;
      }

      searchTimer = setTimeout(function () {
        apiGet('/api/search/users?q=' + encodeURIComponent(query))
          .then(function (res) {
            if (res && (res.users || Array.isArray(res))) {
              const users = res.users || res;
              searchDropdown.innerHTML = '';
              if (users.length === 0) {
                searchDropdown.innerHTML = '<div class="search-empty">No users found.</div>';
              } else {
                users.forEach(function (u) {
                  var item = document.createElement('a');
                  item.href = u.profile_url;
                  item.className = 'search-item';
                  item.innerHTML =
                    '<img src="' + esc(u.avatar_url) + '" class="avatar" alt="User">' +
                    '<div class="search-item-info">' +
                    '<div class="search-item-name">' + esc(u.name) + '</div>' +
                    '<div class="search-item-username">@' + esc(u.username) + '</div>' +
                    '</div>';
                  searchDropdown.appendChild(item);
                });
              }
              searchDropdown.classList.add('open');
            }
          }).catch(function () {
            // fail silently
          });
      }, 300);
    });

    document.addEventListener('click', function (e) {
      if (!searchWrap.contains(e.target)) {
        searchDropdown.classList.remove('open');
      }
    });

    searchInput.addEventListener('focus', function () {
      if (searchInput.value.trim() && searchDropdown.children.length > 0) {
        searchDropdown.classList.add('open');
      }
    });
  }

  /* ================================================================
     COMPOSER
  ================================================================ */
  var postText = document.getElementById('dashPostText');
  var mediaUpload = document.getElementById('mediaUpload');
  var previewArea = document.getElementById('mediaPreviewArea');
  var hashtagRow = document.getElementById('hashtagRow');
  var hashtagInput = document.getElementById('hashtagInput');
  var hashtagToggleBtn = document.getElementById('hashtagToggleBtn');
  var moodBar = document.getElementById('moodBar');
  var moodToggleBtn = document.getElementById('moodToggleBtn');
  var selectedMoodDisplay = document.getElementById('selectedMoodDisplay');
  var shareBtn = document.getElementById('dashShareBtn');
  var composerFeedback = document.getElementById('composerFeedback');
  var feed = document.getElementById('dashFeed');
  var feedLoading = document.getElementById('feedLoading');
  var feedEmpty = document.getElementById('feedEmpty');

  var selectedFiles = [];
  var selectedMood = '';

  /* ── Mood toggle ────────────────────────────────────────── */
  if (moodToggleBtn && moodBar) {
    moodToggleBtn.addEventListener('click', function () {
      var open = moodBar.style.display !== 'none';
      moodBar.style.display = open ? 'none' : 'flex';
    });
  }

  /* ── Mood pick ──────────────────────────────────────────── */
  if (moodBar) {
    moodBar.addEventListener('click', function (e) {
      var btn = e.target.closest('.mood-btn');
      if (!btn) return;
      selectedMood = btn.dataset.mood;
      moodBar.querySelectorAll('.mood-btn').forEach(function (b) {
        b.classList.toggle('active', b === btn);
      });
      if (selectedMoodDisplay) {
        selectedMoodDisplay.textContent = 'Feeling: ' + selectedMood;
        selectedMoodDisplay.style.display = 'inline-block';
      }
    });
  }

  /* ── Hashtag toggle ─────────────────────────────────────── */
  if (hashtagToggleBtn && hashtagRow) {
    hashtagToggleBtn.addEventListener('click', function () {
      var open = hashtagRow.style.display !== 'none';
      hashtagRow.style.display = open ? 'none' : 'flex';
      if (!open && hashtagInput) hashtagInput.focus();
    });
  }

  /* ── Link toggle & apply ────────────────────────────────── */
  var linkRow = document.getElementById('linkRow');
  var linkToggleBtn = document.getElementById('linkToggleBtn');
  var linkNameInput = document.getElementById('linkNameInput');
  var linkUrlInput = document.getElementById('linkUrlInput');
  var applyLinkBtn = document.getElementById('applyLinkBtn');

  if (linkToggleBtn && linkRow) {
    linkToggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = linkRow.style.display === 'flex' || linkRow.classList.contains('open');
      
      if (hashtagRow) hashtagRow.style.display = 'none';
      if (moodBar) moodBar.style.display = 'none';

      if (!isOpen) {
        linkRow.style.display = 'flex';
        linkRow.classList.add('open');
        setTimeout(function() { if (linkNameInput) linkNameInput.focus(); }, 50);
      } else {
        linkRow.style.display = 'none';
        linkRow.classList.remove('open');
      }
    });

    document.addEventListener('click', function(e) {
      if ((linkRow.style.display === 'flex' || linkRow.classList.contains('open')) && !linkRow.contains(e.target) && !linkToggleBtn.contains(e.target)) {
        linkRow.style.display = 'none';
        linkRow.classList.remove('open');
      }
    });
  }

  if (applyLinkBtn && postText) {
    applyLinkBtn.addEventListener('click', function () {
      var name = linkNameInput ? linkNameInput.value.trim() : '';
      var url = linkUrlInput ? linkUrlInput.value.trim() : '';

      if (!name || !url) {
        showToast('Please provide both Link Name and URL', 'error');
        return;
      }

      // Add protocol if missing
      if (!/^https?:\/\//i.test(url)) {
        url = 'https://' + url;
      }

      var mdLink = '[' + name + '](' + url + ')';

      // Insert at cursor
      var startPos = postText.selectionStart;
      var endPos = postText.selectionEnd;
      var currentVal = postText.value;
      
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

  /* ── Media pick ─────────────────────────────────────────── */
  if (mediaUpload) {
    mediaUpload.addEventListener('change', function () {
      var newFiles = Array.from(mediaUpload.files);
      selectedFiles = selectedFiles.concat(newFiles);
      renderPreviews();
      mediaUpload.value = '';
    });
  }

  function renderPreviews() {
    if (!previewArea) return;
    previewArea.innerHTML = '';
    if (selectedFiles.length === 0) {
      previewArea.style.display = 'none';
      return;
    }
    previewArea.style.display = 'grid';
    selectedFiles.forEach(function (file, idx) {
      var wrap = document.createElement('div');
      wrap.className = 'preview-item';

      var url = URL.createObjectURL(file);
      if (file.type.startsWith('video')) {
        var vid = document.createElement('video');
        vid.src = url;
        vid.controls = true;
        wrap.appendChild(vid);
      } else {
        var img = document.createElement('img');
        img.src = url;
        wrap.appendChild(img);
      }

      var rem = document.createElement('button');
      rem.className = 'preview-remove';
      rem.innerHTML = '&times;';
      rem.type = 'button';
      rem.addEventListener('click', function () {
        selectedFiles.splice(idx, 1);
        renderPreviews();
      });
      wrap.appendChild(rem);
      previewArea.appendChild(wrap);
    });
    if (window.lucide) lucide.createIcons();
  }

  /* ── Share / submit post ───────────────────────────────── */
  if (shareBtn) {
    shareBtn.addEventListener('click', async function () {
      var text = postText ? postText.value.trim() : '';
      if (!text && selectedFiles.length === 0) {
        if (composerFeedback) composerFeedback.textContent = 'Write something or attach a file.';
        return;
      }
      if (composerFeedback) composerFeedback.textContent = '';
      shareBtn.disabled = true;
      shareBtn.textContent = 'Sharing…';

      var fd = new FormData();
      if (text) fd.append('text_content', text);
      if (selectedMood) fd.append('mood', selectedMood);
      if (hashtagInput && hashtagInput.value.trim()) fd.append('hashtags', hashtagInput.value.trim());

      var groupIdInput = document.getElementById('dashGroupId');
      if (groupIdInput && groupIdInput.value) {
        fd.append('group_id', groupIdInput.value);
      }

      selectedFiles.forEach(function (f) { fd.append('media[]', f); });

      try {
        var res = await apiPost(window.DASH_ROUTES.storePost, fd);
        if (res.ok) {
          if (postText) postText.value = '';
          if (hashtagInput) hashtagInput.value = '';
          if (hashtagRow) hashtagRow.style.display = 'none';
          if (linkNameInput) linkNameInput.value = '';
          if (linkUrlInput) linkUrlInput.value = '';
          if (linkRow) linkRow.style.display = 'none';
          if (moodBar) moodBar.style.display = 'none';
          if (selectedMoodDisplay) selectedMoodDisplay.style.display = 'none';
          selectedMood = '';
          moodBar && moodBar.querySelectorAll('.mood-btn').forEach(function (b) { b.classList.remove('active'); });
          selectedFiles = [];
          renderPreviews();

          var postEl = buildPostEl(res.post);
          var firstChild = feed.firstChild;
          if (firstChild && firstChild.id === 'feedLoading') {
            feed.insertBefore(postEl, firstChild);
          } else {
            feed.insertBefore(postEl, feed.firstChild);
          }
          if (feedEmpty) feedEmpty.style.display = 'none';
          if (window.lucide) lucide.createIcons();
          showToast('Post shared! 🎉');
        } else {
          var msg = res.errors
            ? Object.values(res.errors).flat().join(' ')
            : (res.message || 'Error sharing post.');
          if (composerFeedback) composerFeedback.textContent = msg;
          showToast(msg, 'error');
        }
      } catch (err) {
        showToast('Network error. Please try again.', 'error');
      }

      shareBtn.disabled = false;
      shareBtn.innerHTML = 'Share <i data-lucide="send"></i>';
      if (window.lucide) lucide.createIcons();
    });
  }

  /* ================================================================
     LOAD FEED
  ================================================================ */
  function loadFeed() {
    if (!window.DASH_ROUTES || !window.DASH_ROUTES.feed) return; // Only run on dashboard feed pages

    apiGet(window.DASH_ROUTES.feed).then(function (res) {
      if (feedLoading) feedLoading.remove();
      if (!res.ok) { showToast('Failed to load feed.', 'error'); return; }

      if (!res.posts || res.posts.length === 0) {
        if (feedEmpty) feedEmpty.style.display = 'flex';
        return;
      }

      res.posts.forEach(function (post) {
        feed.appendChild(buildPostEl(post));
      });
      if (window.lucide) lucide.createIcons();
    }).catch(function () {
      if (feedLoading) feedLoading.innerHTML = '<i data-lucide="alert-circle"></i> <span>Could not load posts.</span>';
      if (window.lucide) lucide.createIcons();
    });
  }

  // Only load feed if we are on a page that declares dashboard routes
  if (window.DASH_ROUTES) {
    loadFeed();
  }

  /* ================================================================
     BUILD POST ELEMENT
  ================================================================ */
  function buildPostEl(post) {
    var article = document.createElement('article');
    article.className = 'panel post';
    article.dataset.postId = post.id;

    /* media */
    var mediaHtml = '';
    if (post.media && post.media.length) {
      var cnt = Math.min(post.media.length, 4);
      var mediaJson = esc(JSON.stringify(post.media));
      mediaHtml = '<div class="post-media-grid media-count-' + cnt + '" data-media="' + mediaJson + '">';
      post.media.slice(0, 4).forEach(function (m) {
        if (m.media_type === 'video') {
          mediaHtml += '<video src="' + esc(m.url) + '" controls class="post-media-item"></video>';
        } else {
          mediaHtml += '<img src="' + esc(m.url) + '" alt="Post image" class="post-media-item">';
        }
      });
      if (post.media.length > 4) {
        mediaHtml += '<div class="media-more">+' + (post.media.length - 4) + '</div>';
      }
      mediaHtml += '</div>';
    }

    /* mood (match profile UI: hidden) */
    var moodHtml = '';

    /* hashtags */
    var tagsHtml = '';
    if (post.hashtags && post.hashtags.length) {
      tagsHtml = '<div class="post-tags">';
      post.hashtags.forEach(function (t) {
        tagsHtml += '<span class="tag">#' + esc(t) + '</span>';
      });
      tagsHtml += '</div>';
    }

    /* manage menu */
    var menuHtml = '';
    if (post.can_manage) {
      menuHtml = '<div class="post-menu-wrap">'
        + '<button class="icon-btn post-menu-btn" type="button"><i data-lucide="more-horizontal"></i></button>'
        + '<div class="post-menu hidden">'
        + '<button class="post-menu-item edit-post-btn" type="button" data-post-id="' + post.id + '"'
        + ' data-text="' + esc(post.text_content || '') + '"'
        + ' data-media="' + esc(JSON.stringify(post.media || [])) + '">'
        + '<i data-lucide="pencil"></i> Edit</button>'
        + '<button class="post-menu-item delete-post-btn danger" type="button" data-post-id="' + post.id + '">'
        + '<i data-lucide="trash-2"></i> Delete</button>'
        + '</div></div>';
    }

    /* verified badge + professional title (match profile UI) */
    var verifiedBadge = '';
    var profTitleHtml = '';
    if (post.user && post.user.role === 'doctor' && post.user.doctor_status === 'approved') {
      verifiedBadge = '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>';
      if (post.user.professional_titles && post.user.professional_titles.trim()) {
        profTitleHtml = '<div class="post-prof-title">' + esc(post.user.professional_titles.trim()) + '</div>';
      }
    }

    var profileUrl = (post.user.id === window.MY_ID)
      ? window.MY_PROFILE_URL
      : esc(post.user.profile_url || '/profile/' + post.user.id);

    /* resource card (for resource_share type) */
    var resourceHtml = '';
    if (post.resource) {
      resourceHtml = 
        '<a href="' + post.resource.url + '" class="post-resource-card">' +
        '<div class="res-mini-thumb"><img src="' + esc(post.resource.thumbnail_url) + '"></div>' +
        '<div class="res-mini-info">' +
        '<div class="res-mini-type">' + esc(post.resource.type) + '</div>' +
        '<div class="res-mini-title">' + esc(post.resource.title) + '</div>' +
        '<div class="res-mini-desc">' + esc(post.resource.description) + '</div>' +
        '</div></a>';
    }

    /* group card (for group_share type) */
    var groupHtml = '';
    if (post.group) {
      groupHtml = 
        '<a href="' + post.group.url + '" class="post-resource-card group-share-card">' +
        '<div class="res-mini-thumb"><img src="' + esc(post.group.cover_url) + '"></div>' +
        '<div class="res-mini-info">' +
        '<div class="res-mini-type" style="color:var(--brand);">Support Group</div>' +
        '<div class="res-mini-title">' + esc(post.group.name) + '</div>' +
        '<div class="res-mini-desc">' + esc(post.group.description) + '</div>' +
        '</div></a>';
    }

    /* shared post card (for post_share type) */
    var sharedHtml = '';
    if (post.shared_post) {
      var sp = post.shared_post;
      var spProfileUrl = (sp.user.id === window.MY_ID)
        ? window.MY_PROFILE_URL
        : esc(sp.user.profile_url || '/profile/' + sp.user.id);

      var spVerifiedBadge = '';
      var spProfTitleHtml = '';
      if (sp.user && sp.user.doctor_status === 'approved' && (!sp.user.role || sp.user.role === 'doctor')) {
        spVerifiedBadge = '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>';
        if (sp.user.professional_titles && sp.user.professional_titles.trim()) {
          spProfTitleHtml = '<div class="post-prof-title">' + esc(sp.user.professional_titles.trim()) + '</div>';
        }
      }

      var spResourceHtml = '';
      if (sp.resource) {
        spResourceHtml =
          '<a href="' + sp.resource.url + '" class="post-resource-card" style="margin-top:10px;">' +
          '<div class="res-mini-thumb"><img src="' + esc(sp.resource.thumbnail_url) + '"></div>' +
          '<div class="res-mini-info">' +
          '<div class="res-mini-type">' + esc(sp.resource.type) + '</div>' +
          '<div class="res-mini-title">' + esc(sp.resource.title) + '</div>' +
          '<div class="res-mini-desc">' + esc(sp.resource.description) + '</div>' +
          '</div></a>';
      }

      var spMediaGrid = '';
      if (sp.media && sp.media.length) {
        spMediaGrid = '<div class="post-media-grid shared-post-media-grid media-count-' + Math.min(sp.media.length, 4) + '" data-media="' + esc(JSON.stringify(sp.media)) + '">';
        sp.media.slice(0, 4).forEach(function (m) {
          if (m.media_type === 'video') {
            spMediaGrid += '<video src="' + esc(m.url) + '" controls class="post-media-item"></video>';
          } else {
            spMediaGrid += '<img src="' + esc(m.url) + '" alt="Shared media" class="post-media-item">';
          }
        });
        if (sp.media.length > 4) {
          spMediaGrid += '<div class="media-more">+' + (sp.media.length - 4) + '</div>';
        }
        spMediaGrid += '</div>';
      }

      sharedHtml =
        '<div class="shared-post-card">' +
        '<div class="post-head">' +
        '<a href="' + spProfileUrl + '" class="avatar md"><img src="' + esc(sp.user.avatar_url) + '" alt="' + esc(sp.user.name) + '"></a>' +
        '<div class="post-meta">' +
        '<div class="post-name-row">' +
        '<a href="' + spProfileUrl + '" class="post-name" style="color:inherit;text-decoration:none;">' + esc(sp.user.name) + '</a>' +
        '<span class="post-handle">@' + esc(sp.user.username) + '</span>' +
        spVerifiedBadge +
        '</div>' +
        spProfTitleHtml +
        '<div class="post-sub">' + esc(sp.created_at) + '</div>' +
        '</div></div>' +
        (sp.text_content ? '<div class="post-body js-collapsible">' + parseMarkdownLinks(esc(sp.text_content)) + '</div>' : '') +
        spResourceHtml +
        spMediaGrid +
        '</div>';
    }

    article.innerHTML =
      '<div class="post-head">'
      + '<a href="' + profileUrl + '" class="post-author-link">'
      + '<div class="avatar md"><img src="' + esc(post.user.avatar_url) + '" alt="' + esc(post.user.name) + '"></div>'
      + '</a>'
      + '<div class="post-meta">'
      + '<div class="post-name-row">'
      + '<a href="' + profileUrl + '" class="post-author-link post-name">' + esc(post.user.name) + '</a>'
      + '<span class="post-handle">@' + esc(post.user.username) + '</span>'
      + verifiedBadge
      + '</div>'
      + profTitleHtml
      + '<div class="post-sub">' + esc(post.created_at) + '</div>'
      + '</div>'
      + menuHtml
      + '</div>'
      + (post.text_content ? '<div class="post-body post-text-content js-collapsible">' + parseMarkdownLinks(esc(post.text_content)) + '</div>' : '')
      + tagsHtml
      + moodHtml
      + resourceHtml
      + groupHtml
      + sharedHtml
      + mediaHtml
      + '<div class="post-actions">'
      + '<button class="post-btn like-btn ' + (post.is_liked ? 'liked' : '') + '" type="button"'
      + ' data-post-id="' + post.id + '" data-liked="' + (post.is_liked ? '1' : '0') + '">'
      + '<i data-lucide="heart" class="like-icon"></i>'
      + '<span class="like-count">' + post.like_count + '</span>'
      + '</button>'
      + '<button class="post-btn comment-toggle-btn" type="button" data-post-id="' + post.id + '">'
      + '<i data-lucide="message-square"></i>'
      + '<span class="comment-count">' + post.comment_count + '</span>'
      + '</button>'
      + '<button class="post-btn save-btn ' + (post.is_saved ? 'saved' : '') + ' end" type="button" title="Save" data-post-id="' + post.id + '" data-saved="' + (post.is_saved ? '1' : '0') + '">'
      + '<i data-lucide="bookmark"></i>'
      + '</button>'
      + '<button class="post-btn js-share-post" type="button" data-post-id="' + post.id + '" data-preview="' + esc((post.text_content || '').slice(0, 80) || 'a post') + '"><i data-lucide="share-2"></i></button>'
      + '</div>'
      + '<div class="comments-section hidden" id="dash-comments-' + post.id + '">'
      + '<div class="comment-composer">'
      + '<div class="avatar sm"><img src="' + esc(window.MY_AVATAR) + '" alt="You"></div>'
      + '<div class="comment-input-wrap">'
      + '<input type="text" class="comment-input" placeholder="Write a comment…" data-post-id="' + post.id + '">'
      + '<button class="comment-send-btn" type="button" data-post-id="' + post.id + '"><i data-lucide="send"></i></button>'
      + '</div></div>'
      + '<div class="comments-list" id="dash-comments-list-' + post.id + '">'
      + (post.comments || []).map(function (c) { return buildCommentHtml(c); }).join('')
      + '</div>'
      + '</div>';

    // Let shared UI scripts enhance newly-rendered posts
    try {
      document.dispatchEvent(new CustomEvent('post:rendered', { detail: { root: article } }));
    } catch (e) {}

    return article;
  }

  function buildCommentHtml(c, isReply = false, parentId = null) {
    var cUrl = (c.user.id === window.MY_ID)
      ? window.MY_PROFILE_URL
      : esc(c.user.profile_url || '/profile/' + c.user.id);

    if (isReply) {
      return '<div class="comment-item reply-item" id="dash-comment-' + c.id + '">'
        + '<a href="' + cUrl + '" class="comment-avatar-link"><div class="avatar sm"><img src="' + esc(c.user.avatar_url) + '" alt="' + esc(c.user.name) + '"></div></a>'
        + '<div class="comment-bubble">'
        + '<div class="comment-meta">'
        + '<a href="' + cUrl + '" class="comment-author-link"><span class="comment-author">' + esc(c.user.name) + '</span></a>'
        + '<span class="comment-time">' + esc(c.created_at) + '</span>'
        + (c.can_delete
          ? '<button class="comment-delete-btn" type="button" data-comment-id="' + c.id + '" title="Delete"><i data-lucide="x"></i></button>'
          : '')
        + '</div>'
        + '<p class="comment-text">' + parseMarkdownLinks(esc(c.comment_text)) + '</p>'
        + '<button class="reply-toggle-btn" type="button" data-comment-id="' + parentId + '" data-post-id="' + (c.post_id || '') + '" data-reply-to="' + esc(c.user.username) + '">Reply</button>'
        + '</div></div>';
    }

    var repliesHtml = (c.replies || []).map(function (r) {
      return buildCommentHtml(r, true, c.id);
    }).join('');

    return '<div class="comment-item" id="dash-comment-' + c.id + '">'
      + '<a href="' + cUrl + '" class="comment-avatar-link"><div class="avatar sm"><img src="' + esc(c.user.avatar_url) + '" alt="' + esc(c.user.name) + '"></div></a>'
      + '<div class="comment-bubble">'
      + '<div class="comment-meta">'
      + '<a href="' + cUrl + '" class="comment-author-link"><span class="comment-author">' + esc(c.user.name) + '</span></a>'
      + '<span class="comment-time">' + esc(c.created_at) + '</span>'
      + (c.can_delete
        ? '<button class="comment-delete-btn" type="button" data-comment-id="' + c.id + '" title="Delete"><i data-lucide="x"></i></button>'
        : '')
      + '</div>'
      + '<p class="comment-text">' + parseMarkdownLinks(esc(c.comment_text)) + '</p>'
      + '<button class="reply-toggle-btn" type="button" data-comment-id="' + c.id + '" data-post-id="' + c.post_id + '" data-reply-to="' + esc(c.user.username) + '">Reply</button>'
      + '<div class="reply-composer hidden" id="dash-reply-composer-' + c.id + '">'
      + '<input type="text" class="comment-input reply-input" placeholder="Write a reply…"'
      + ' data-post-id="' + c.post_id + '" data-parent-id="' + c.id + '">'
      + '<button class="comment-send-btn reply-send-btn" type="button"'
      + ' data-post-id="' + c.post_id + '" data-parent-id="' + c.id + '"><i data-lucide="send"></i></button>'
      + '</div>'
      + '<div class="replies-list" id="dash-replies-' + c.id + '">' + repliesHtml + '</div>'
      + '</div></div>';
  }

  function parseMarkdownLinks(text) {
    if (!text) return '';
    // Use an un-escaped regex since `text` here has already been passed through `esc()`
    // We match \[([^\]]+)\]\(([^)]+)\)
    return text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function(match, name, url) {
        return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">' + name + '</a>';
    });
  }

  /* ================================================================
     INTERACTIONS (delegated)
  ================================================================ */
  document.addEventListener('click', async function (e) {
    // Only handle post interactions for dashboard feed posts to avoid
    // interfering with Profile page handlers (profile.js) that share classnames.
    var inDashFeed = !!e.target.closest('#dashFeed');

    /* ── Like ───────────────────────────────────────────────── */
    var likeBtn = e.target.closest('.like-btn, .dash-like-btn');
    if (likeBtn && inDashFeed) {
      var postId = likeBtn.dataset.postId;
      var isLiked = likeBtn.dataset.liked === '1';
      var countEl = likeBtn.querySelector('.like-count');

      // Optimistic update
      var newLiked = !isLiked;
      likeBtn.dataset.liked = newLiked ? '1' : '0';
      likeBtn.classList.toggle('liked', newLiked);
      if (countEl) countEl.textContent = parseInt(countEl.textContent || '0') + (newLiked ? 1 : -1);

      // Heart pop animation
      likeBtn.classList.remove('pop');
      void likeBtn.offsetWidth;
      likeBtn.classList.add('pop');
      setTimeout(function () { likeBtn.classList.remove('pop'); }, 400);

      try {
        var res = await apiPost(window.DASH_ROUTES.toggleLike(postId), {});
        if (res.ok) {
          likeBtn.dataset.liked = res.liked ? '1' : '0';
          likeBtn.classList.toggle('liked', res.liked);
          if (countEl) countEl.textContent = res.like_count;
          if (window.lucide) lucide.createIcons();
        }
      } catch (err) { /* keep optimistic */ }
      return;
    }

    /* ── Save / bookmark ───────────────────────────────────── */
    var saveBtn = e.target.closest('.save-btn, .dash-save-btn');
    if (saveBtn && inDashFeed) return;

    /* ── Toggle comments section ────────────────────────────── */
    var commentToggle = e.target.closest('.comment-toggle-btn, .dash-comment-toggle');
    if (commentToggle && inDashFeed) {
      var postId = commentToggle.dataset.postId;
      var section = document.getElementById('dash-comments-' + postId);
      if (section) {
        section.classList.toggle('hidden');
        if (!section.classList.contains('hidden')) {
          section.querySelector('.comment-input') && section.querySelector('.comment-input').focus();
        }
      }
      if (window.lucide) lucide.createIcons();
      return;
    }

    /* ── 3-dot menu toggle ──────────────────────────────────── */
    var menuBtn = e.target.closest('.post-menu-btn');
    if (menuBtn && inDashFeed) {
      e.stopPropagation();
      var postMenu = menuBtn.nextElementSibling;
      document.querySelectorAll('.post-menu').forEach(function (m) {
        if (m !== postMenu) m.classList.add('hidden');
      });
      postMenu && postMenu.classList.toggle('hidden');
      if (window.lucide) lucide.createIcons();
      return;
    }
    if (inDashFeed && !e.target.closest('.post-menu-wrap')) {
      document.querySelectorAll('.post-menu').forEach(function (m) { m.classList.add('hidden'); });
    }

    /* ── Delete post ────────────────────────────────────────── */
    var deletePostBtn = e.target.closest('.delete-post-btn');
    if (deletePostBtn && inDashFeed) {
      if (!confirm('Delete this post? This cannot be undone.')) return;
      var postId = deletePostBtn.dataset.postId;
      var article = document.querySelector('[data-post-id="' + postId + '"]');
      try {
        var res = await apiPost(window.DASH_ROUTES.destroyPost(postId), {}, 'DELETE');
        if (res.ok) {
          article && article.remove();
          showToast('Post deleted.');
          if (document.querySelectorAll('#dashFeed .post').length === 0 && feedEmpty) {
            feedEmpty.style.display = 'flex';
          }
        } else {
          showToast(res.message || 'Error.', 'error');
        }
      } catch (err) {
        showToast('Network error.', 'error');
      }
      return;
    }

    /* ── Edit post ──────────────────────────────────────────── */
    var editBtn = e.target.closest('.edit-post-btn');
    if (editBtn && inDashFeed) {
      var postId = editBtn.dataset.postId;
      var text = editBtn.dataset.text;
      var mediaData = editBtn.dataset.media ? JSON.parse(editBtn.dataset.media) : [];
      var newFiles = [];
      var deletedMediaIds = [];

      var article = document.querySelector('[data-post-id="' + postId + '"]');
      if (!article) return;

      var textEl = article.querySelector('.post-text-content') || article.querySelector('.post-body');
      var existing = article.querySelector('.post-edit-area');
      if (existing) return;

      var menu = editBtn.closest('.post-menu');
      if (menu) menu.classList.add('hidden');

      // Helper function to render existing media preview
      function renderEditPreviews(wrap) {
        var grid = wrap.querySelector('.edit-media-grid');
        grid.innerHTML = '';

        // Render existing that aren't deleted
        mediaData.forEach(function (m) {
          if (deletedMediaIds.includes(m.id)) return;
          var div = document.createElement('div');
          div.className = 'edit-preview-item';
          div.style.position = 'relative';
          div.style.borderRadius = '8px';
          div.style.overflow = 'hidden';
          div.style.height = '80px';
          div.style.width = '80px';
          div.style.border = '1px solid var(--border)';

          if (m.media_type === 'video') {
            div.innerHTML = '<video src="' + m.url + '" style="width:100%;height:100%;object-fit:cover;"></video>';
          } else {
            div.innerHTML = '<img src="' + m.url + '" style="width:100%;height:100%;object-fit:cover;">';
          }
          var removeBtn = document.createElement('button');
          removeBtn.innerHTML = '×';
          removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;line-height:20px;text-align:center;padding:0;font-size:14px;';
          removeBtn.onclick = function () {
            deletedMediaIds.push(m.id);
            renderEditPreviews(wrap);
          };
          div.appendChild(removeBtn);
          grid.appendChild(div);
        });

        // Render newly added files
        newFiles.forEach(function (f, idx) {
          var div = document.createElement('div');
          div.className = 'edit-preview-item';
          div.style.position = 'relative';
          div.style.borderRadius = '8px';
          div.style.overflow = 'hidden';
          div.style.height = '80px';
          div.style.width = '80px';
          div.style.border = '1px dashed var(--brand)';
          var url = URL.createObjectURL(f);

          if (f.type.startsWith('video')) {
            div.innerHTML = '<video src="' + url + '" style="width:100%;height:100%;object-fit:cover;"></video>';
          } else {
            div.innerHTML = '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;">';
          }
          var removeBtn = document.createElement('button');
          removeBtn.innerHTML = '×';
          removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;line-height:20px;text-align:center;padding:0;font-size:14px;';
          removeBtn.onclick = function () {
            newFiles.splice(idx, 1);
            renderEditPreviews(wrap);
          };
          div.appendChild(removeBtn);
          grid.appendChild(div);
        });
      }

      var editorWrap = document.createElement('div');
      editorWrap.className = 'post-edit-area';
      editorWrap.innerHTML =
        '<textarea class="post-edit-textarea" style="width:100%; min-height:80px; padding:10px 14px; border:1px solid var(--brand); border-radius:12px; background:var(--input-bg); color:var(--text); font-size:14px; resize:vertical; outline:none; margin-bottom:8px;">' + esc(text) + '</textarea>' +
        '<div class="edit-media-grid" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;"></div>' +
        '<div class="post-edit-actions" style="display:flex; gap:8px; justify-content:space-between; align-items:center;">' +
        '  <label class="btn-cancel" style="padding:6px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border); background:var(--chip-bg); color:var(--text); cursor:pointer; display:flex; align-items:center; gap:4px;"><i data-lucide="image" style="width:14px;height:14px;"></i> Add Photo/Video<input type="file" multiple accept="image/*,video/*" class="edit-media-input" style="display:none;"></label>' +
        '  <div style="display:flex; gap:8px;">' +
        '    <button class="btn-cancel btn-edit-cancel" type="button" style="padding:6px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border); background:var(--chip-bg); color:var(--text); cursor:pointer;">Cancel</button>' +
        '    <button class="btn-save btn-edit-save" type="button" style="padding:6px 16px; font-size:13px; border-radius:8px; border:none; background:linear-gradient(90deg, #7c3aed, #4f46e5); color:#fff; cursor:pointer;" data-post-id="' + postId + '">Save</button>' +
        '  </div>' +
        '</div>';

      if (textEl) {
        textEl.style.display = 'none';
        textEl.insertAdjacentElement('afterend', editorWrap);
      } else {
        article.querySelector('.post-head').insertAdjacentElement('afterend', editorWrap);
      }

      var oldMediaGrid = article.querySelector('.post-media-grid');
      if (oldMediaGrid) oldMediaGrid.style.display = 'none';

      if (window.lucide) lucide.createIcons({ root: editorWrap });

      var ta = editorWrap.querySelector('textarea');
      ta.focus();

      var fileInput = editorWrap.querySelector('.edit-media-input');
      fileInput.addEventListener('change', function (ev) {
        var files = Array.from(ev.target.files);
        newFiles = newFiles.concat(files);
        renderEditPreviews(editorWrap);
        fileInput.value = ''; // Reset
      });

      renderEditPreviews(editorWrap);

      editorWrap.querySelector('.btn-edit-cancel').addEventListener('click', function () {
        editorWrap.remove();
        if (textEl) textEl.style.display = '';
        if (oldMediaGrid) oldMediaGrid.style.display = '';
      });

      editorWrap.querySelector('.btn-edit-save').addEventListener('click', async function () {
        var newText = ta.value.trim();
        var hasExistingMedia = mediaData.filter(function (m) { return !deletedMediaIds.includes(m.id); }).length > 0;

        if (!newText && newFiles.length === 0 && !hasExistingMedia) {
          showToast('Post cannot be empty.', 'error');
          return;
        }

        var saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving...';

        var fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('text_content', newText);
        deletedMediaIds.forEach(function (id) { fd.append('deleted_media[]', id); });
        newFiles.forEach(function (f) { fd.append('media[]', f); });

        try {
          var csrfMeta = document.querySelector('meta[name="csrf-token"]');
          var csrfToken = csrfMeta ? csrfMeta.content : '';

          // Use direct fetch since apiPost passes JSON by default unless it detects FormData... 
          // wait, apiPost in dashboard.js checks 'body instanceof FormData' ? 
          // Let's just use raw fetch for safety to ensure boundaries work well with files
          var res = await fetch(window.DASH_ROUTES.updatePost(postId), {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: fd
          }).then(function (r) { return r.json(); });

          if (res.ok) {
            editorWrap.remove();
            showToast('Post updated!', 'success');

            var newArticle = buildPostEl(res.post);
            article.replaceWith(newArticle);
            if (window.lucide) lucide.createIcons({ root: newArticle });

          } else {
            showToast(res.message || 'Error.', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save';
          }
        } catch (err) {
          showToast('Network error.', 'error');
          saveBtn.disabled = false;
          saveBtn.innerHTML = 'Save';
        }
      });
      return;
    }

    /* ── Comment send ───────────────────────────────────────── */
    var sendBtn = e.target.closest('.comment-send-btn:not(.reply-send-btn)');
    if (sendBtn && inDashFeed) {
      var postId = sendBtn.dataset.postId;
      var wrap = sendBtn.closest('.comment-input-wrap');
      var input = wrap ? wrap.querySelector('.comment-input') : null;
      if (input) await submitDashComment(postId, null, input);
      return;
    }

    /* ── Reply send ─────────────────────────────────────────── */
    var replySend = e.target.closest('.reply-send-btn');
    if (replySend && inDashFeed) {
      var postId = replySend.dataset.postId;
      var parentId = replySend.dataset.parentId;
      var input = document.querySelector('.reply-input[data-parent-id="' + parentId + '"]');
      if (input) await submitDashComment(postId, parentId, input);
      return;
    }

    /* ── Reply toggle ───────────────────────────────────────── */
    var replyToggle = e.target.closest('.reply-toggle-btn');
    if (replyToggle && inDashFeed) {
      var commentId = replyToggle.dataset.commentId;
      var replyTo = replyToggle.dataset.replyTo;
      var composer = document.getElementById('dash-reply-composer-' + commentId);
      if (composer) {
        composer.classList.remove('hidden');
        var inp = composer.querySelector('input');
        if (inp) {
          if (replyTo && replyTo !== 'undefined' && replyTo !== '') {
            if (window.applyReplyMention) {
              window.applyReplyMention(inp, replyTo);
            } else {
              inp.focus();
              var tag = '@' + replyTo + ' ';
              var currentVal = inp.value;
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

    /* ── Delete comment ─────────────────────────────────────── */
    var delComment = e.target.closest('.comment-delete-btn');
    if (delComment && inDashFeed) {
      if (!confirm('Delete this comment?')) return;
      var commentId = delComment.dataset.commentId;
      try {
        var res = await apiPost(window.DASH_ROUTES.destroyComment(commentId), {}, 'DELETE');
        if (res.ok) {
          var el = document.getElementById('dash-comment-' + commentId);
          if (el) {
            el.style.transition = 'opacity 0.25s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 260);
          }
          showToast('Comment deleted.');
        } else {
          showToast(res.message || 'Error.', 'error');
        }
      } catch (err) {
        showToast('Network error.', 'error');
      }
      return;
    }

    /* ── Image Lightbox ─────────────────────────────────────── */
    var mediaItem = e.target.closest('.post-media-item') || e.target.closest('.media-more');
    if (mediaItem && inDashFeed && !e.target.closest('.edit-post-btn') && !e.target.closest('.delete-post-btn')) {
      if (document.querySelector('.photo-lightbox')) return;

      var lb = document.createElement('div');
      lb.className = 'photo-lightbox';
      lb.style.cssText = 'position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); z-index:99999; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s;';

      var closeBtn = document.createElement('button');
      closeBtn.innerHTML = '×';
      closeBtn.style.cssText = 'position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:32px; width:48px; height:48px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:4px; transition:background 0.2s; z-index:100000;';
      closeBtn.onmouseover = function () { this.style.background = 'rgba(255,255,255,0.25)'; };
      closeBtn.onmouseout = function () { this.style.background = 'rgba(255,255,255,0.15)'; };
      lb.appendChild(closeBtn);

      var mediaGrid = mediaItem.closest('.post-media-grid');
      var mediaList = mediaGrid && mediaGrid.dataset.media ? JSON.parse(mediaGrid.dataset.media) : [];
      var currentIndex = 0;
      var content;

      if (mediaList.length > 0) {
        if (mediaItem.classList.contains('media-more')) {
          currentIndex = 3;
        } else {
          var items = Array.from(mediaGrid.querySelectorAll('.post-media-item'));
          currentIndex = items.indexOf(mediaItem);
          if (currentIndex === -1) currentIndex = 0;
        }
      } else {
        var isVideo = mediaItem.tagName && mediaItem.tagName.toLowerCase() === 'video';
        var src = mediaItem.src || (mediaItem.querySelector('img, video') && mediaItem.querySelector('img, video').src);
        if (!src && mediaItem.style.backgroundImage) {
          src = mediaItem.style.backgroundImage.slice(4, -1).replace(/"/g, "");
        }
        if (src) {
          mediaList = [{ url: src, media_type: isVideo ? 'video' : 'image' }];
        } else {
          return;
        }
      }

      var prevBtn = document.createElement('button');
      prevBtn.innerHTML = '‹';
      prevBtn.style.cssText = 'position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:40px; width:56px; height:56px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:6px; transition:background 0.2s; z-index:100000;';
      prevBtn.onmouseover = function () { this.style.background = 'rgba(255,255,255,0.25)'; };
      prevBtn.onmouseout = function () { this.style.background = 'rgba(255,255,255,0.15)'; };
      lb.appendChild(prevBtn);

      var nextBtn = document.createElement('button');
      nextBtn.innerHTML = '›';
      nextBtn.style.cssText = 'position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:40px; width:56px; height:56px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; padding-bottom:6px; transition:background 0.2s; z-index:100000;';
      nextBtn.onmouseover = function () { this.style.background = 'rgba(255,255,255,0.25)'; };
      nextBtn.onmouseout = function () { this.style.background = 'rgba(255,255,255,0.15)'; };
      lb.appendChild(nextBtn);

      var updateContent = function (index) {
        if (index < 0 || index >= mediaList.length) return;
        currentIndex = index;
        var m = mediaList[currentIndex];

        var newContent;
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

      requestAnimationFrame(function () {
        lb.style.opacity = '1';
        if (content) content.style.transform = 'scale(1)';
      });

      var closeLb = function () {
        lb.style.opacity = '0';
        if (content) content.style.transform = 'scale(0.95)';
        setTimeout(function () { lb.remove(); }, 300);
      };

      closeBtn.onclick = closeLb;
      prevBtn.onclick = function (e) { e.stopPropagation(); updateContent(currentIndex - 1); };
      nextBtn.onclick = function (e) { e.stopPropagation(); updateContent(currentIndex + 1); };
      lb.onclick = function (ev) { if (ev.target === lb) closeLb(); };

      var escListener = function (ev) {
        if (ev.key === 'Escape') closeLb();
        else if (ev.key === 'ArrowLeft') updateContent(currentIndex - 1);
        else if (ev.key === 'ArrowRight') updateContent(currentIndex + 1);
      };

      var origClose = closeLb;
      closeLb = function () {
        origClose();
        document.removeEventListener('keydown', escListener);
      };
      document.addEventListener('keydown', escListener);

      return;
    }
  });

  /* ── Enter key on comment inputs ─────────────────────────── */
  document.addEventListener('keydown', async function (e) {
    if (e.key !== 'Enter') return;
    var input = e.target;
    var inDashFeed = !!(input && input.closest && input.closest('#dashFeed'));
    if (inDashFeed && input.matches && input.matches('.comment-input:not(.reply-input)')) {
      await submitDashComment(input.dataset.postId, null, input);
    } else if (inDashFeed && input.matches && input.matches('.reply-input')) {
      await submitDashComment(input.dataset.postId, input.dataset.parentId, input);
    }
  });

  /* ── Submit comment / reply via API ──────────────────────── */
  async function submitDashComment(postId, parentId, inputEl) {
    var text = inputEl && inputEl.value ? inputEl.value.trim() : '';
    if (!text) return;

    var body = { comment_text: text };
    if (parentId) body.parent_comment_id = parentId;

    try {
      var res = await apiPost(window.DASH_ROUTES.storeComment(postId), body);
      if (res.ok) {
        inputEl.value = '';
        var c = res.comment;
        var html = buildCommentHtml(c, !!parentId, parentId);

        if (parentId) {
          var repliesList = document.getElementById('dash-replies-' + parentId);
          if (repliesList) repliesList.insertAdjacentHTML('beforeend', html);
          var composer = document.getElementById('dash-reply-composer-' + parentId);
          if (composer) composer.classList.add('hidden');
        } else {
          var commentsList = document.getElementById('dash-comments-list-' + postId);
          if (commentsList) commentsList.insertAdjacentHTML('afterbegin', html);
          // Update count
          var article = document.querySelector('[data-post-id="' + postId + '"]');
          var countEl = article && article.querySelector('.comment-count');
          if (countEl) countEl.textContent = parseInt(countEl.textContent || '0') + 1;
        }
        if (window.lucide) lucide.createIcons();
      } else {
        showToast(res.message || 'Error.', 'error');
      }
    } catch (err) {
      showToast('Network error.', 'error');
    }
  }

  /* ================================================================
     SCROLL TO POST FROM QUERY PARAMS
  ================================================================ */
  (function initScrollToPost() {
    var params = new URLSearchParams(window.location.search);
    var postId = params.get('post_id');
    var commentId = params.get('comment_id');
    if (!postId) return;

    var done = false;
    function tryScroll() {
      if (done) return;
      var article = document.querySelector('article.post[data-post-id="' + postId + '"]');
      if (!article) return;

      article.scrollIntoView({ behavior: 'smooth', block: 'center' });
      article.classList.add('post-highlight');
      setTimeout(function () { article.classList.remove('post-highlight'); }, 3000);

      if (commentId) {
        var section = article.querySelector('.comments-section');
        if (section) section.classList.remove('hidden');
        var c = document.getElementById('comment-' + commentId) || document.getElementById('dash-comment-' + commentId);
        if (c) c.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      done = true;
    }

    tryScroll();
    document.addEventListener('post:rendered', tryScroll);
  })();
});

// public/assets/js/post-ui.js
// Shared UI behaviors:
// - "View more / View less" for long post text
// - Share modal with optional comment (posts + resources)

document.addEventListener('DOMContentLoaded', function () {
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var CSRF = csrfMeta ? csrfMeta.content : '';

  function apiPost(url, body, method) {
    method = method || 'POST';
    return fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF
      },
      body: JSON.stringify(body || {})
    }).then(function (r) { return r.json(); });
  }

  function toast(msg, type) {
    var dashToast = document.getElementById('dash-toast');
    var profToast = document.getElementById('toast');
    if (dashToast) {
      dashToast.textContent = msg;
      dashToast.className = 'dash-toast' + (type === 'error' ? ' error' : '') + ' show';
      clearTimeout(dashToast._t);
      dashToast._t = setTimeout(function () { dashToast.classList.remove('show'); }, 3200);
      return;
    }
    if (profToast) {
      profToast.textContent = msg;
      profToast.className = 'toast ' + (type === 'error' ? 'error' : 'success') + ' show';
      clearTimeout(profToast._t);
      profToast._t = setTimeout(function () { profToast.classList.remove('show'); }, 3200);
    }
  }

  function syncSavedTab(postId, isSaved, sourcePostEl) {
    var savedTab = document.getElementById('tab-saved');
    if (!savedTab) return;

    var feed = document.getElementById('savedPostsFeed');
    var emptyState = savedTab.querySelector('.empty-state');

    if (isSaved) {
      if (!feed) {
        feed = document.createElement('div');
        feed.id = 'savedPostsFeed';
        var body = savedTab.querySelector('.panel .prof-section-body');
        if (body) body.appendChild(feed);
        if (emptyState) emptyState.remove();
      }

      if (feed.querySelector('[data-post-id="' + postId + '"]')) return;

      var src = sourcePostEl || document.querySelector('article.post[data-post-id="' + postId + '"]');
      if (!src) return;

      var clone = src.cloneNode(true);
      var comments = clone.querySelector('.comments-section');
      if (comments) comments.remove();
      clone.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });
      feed.prepend(clone);
      if (window.lucide) lucide.createIcons({ root: clone });
      return;
    }

    if (feed) {
      var existing = feed.querySelector('[data-post-id="' + postId + '"]');
      if (existing) existing.remove();
      if (feed.querySelectorAll('.post').length === 0) {
        feed.remove();
        var empty = document.createElement('div');
        empty.className = 'empty-state';
        empty.innerHTML = '<i data-lucide="bookmark"></i><p>No saved posts yet. Tap the bookmark on any post to save it here.</p>';
        var body2 = savedTab.querySelector('.panel .prof-section-body');
        if (body2) body2.appendChild(empty);
        if (window.lucide) lucide.createIcons({ root: empty });
      }
    }
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* ================================================================
     VIEW MORE / VIEW LESS
  ================================================================ */
  function setupCollapsibleText(root) {
    root = root || document;
    var nodes = root.querySelectorAll('.js-collapsible');
    nodes.forEach(function (el) {
      if (el.dataset.vmReady === '1') return;
      el.dataset.vmReady = '1';

      // Apply collapsed class first, then measure overflow
      el.classList.add('is-collapsed');

      // Defer measurement to allow layout to settle
      requestAnimationFrame(function () {
        var overflows = el.scrollHeight > el.clientHeight + 2;
        if (!overflows) {
          el.classList.remove('is-collapsed');
          return;
        }

        var row = document.createElement('div');
        row.className = 'view-more-row';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'view-more-btn';
        btn.textContent = 'View more';
        btn.addEventListener('click', function () {
          var expanded = !el.classList.contains('is-collapsed');
          el.classList.toggle('is-collapsed', expanded);
          btn.textContent = expanded ? 'View more' : 'View less';
        });
        row.appendChild(btn);

        // Insert right after the text block
        el.insertAdjacentElement('afterend', row);
      });
    });
  }

  setupCollapsibleText(document);

  // Re-scan after dynamic feed insertions
  document.addEventListener('post:rendered', function (e) {
    setupCollapsibleText(e.detail && e.detail.root ? e.detail.root : document);
  });

  /* ================================================================
     SHARE MODAL
  ================================================================ */
  var modal = document.getElementById('shareModal');
  var closeBtn = document.getElementById('shareModalCloseBtn');
  var cancelBtn = document.getElementById('shareModalCancelBtn');
  var shareBtn = document.getElementById('shareModalShareBtn');
  var txt = document.getElementById('shareModalText');
  var feedback = document.getElementById('shareModalFeedback');
  var preview = document.getElementById('shareModalPreview');

  var current = { type: null, id: null };

  function openModal(payload) {
    if (!modal) return;
    current = payload || { type: null, id: null };
    if (txt) txt.value = '';
    if (feedback) { feedback.textContent = ''; feedback.className = 'form-feedback'; }
    if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }

    // Optional preview text
    if (payload && payload.preview_text && preview) {
      preview.style.display = 'block';
      preview.innerHTML = '<div style="font-weight:900;margin-bottom:6px;">You are sharing</div>'
        + '<div style="color:var(--muted);font-size:13px;line-height:1.4;">' + esc(payload.preview_text) + '</div>';
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    if (window.lucide) lucide.createIcons({ root: modal });
    setTimeout(function () { txt && txt.focus(); }, 50);
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    current = { type: null, id: null };
  }

  function apiJson(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF
      },
      body: JSON.stringify(body || {})
    }).then(function (r) { return r.json(); });
  }

  /* ================================================================
     SAVE / BOOKMARK (shared)
  ================================================================ */
  document.addEventListener('click', async function (e) {
    var saveBtn = e.target.closest('.save-btn');
    if (!saveBtn) return;

    var inDashFeed = !!saveBtn.closest('#dashFeed');
    var inProfile = !!document.querySelector('.prof-shell');
    var routeFn = null;

    if (inDashFeed && window.DASH_ROUTES && typeof window.DASH_ROUTES.toggleSave === 'function') {
      routeFn = window.DASH_ROUTES.toggleSave;
    } else if (window.ROUTES && typeof window.ROUTES.toggleSave === 'function') {
      routeFn = window.ROUTES.toggleSave;
    } else {
      return;
    }

    var postId = saveBtn.dataset.postId;
    if (!postId) return;

    var wasSaved = saveBtn.dataset.saved === '1';
    var nextSaved = !wasSaved;

    // Optimistic UI
    saveBtn.dataset.saved = nextSaved ? '1' : '0';
    saveBtn.classList.toggle('saved', nextSaved);
    if (window.lucide) lucide.createIcons();

    try {
      var res = await apiPost(routeFn(postId), {});
      if (res && res.ok) {
        saveBtn.classList.toggle('saved', !!res.saved);
        saveBtn.dataset.saved = res.saved ? '1' : '0';
        if (inProfile) syncSavedTab(postId, !!res.saved, saveBtn.closest('article.post'));
        if (window.lucide) lucide.createIcons();
      } else {
        saveBtn.dataset.saved = wasSaved ? '1' : '0';
        saveBtn.classList.toggle('saved', wasSaved);
        toast((res && res.message) ? res.message : 'Could not save post.', 'error');
      }
    } catch (err) {
      saveBtn.dataset.saved = wasSaved ? '1' : '0';
      saveBtn.classList.toggle('saved', wasSaved);
      toast('Could not save post.', 'error');
    }
  });

  function endpointForCurrent() {
    if (!current || !current.type || !current.id) return null;
    if (current.type === 'post') return '/profile/posts/' + current.id + '/share';
    if (current.type === 'resource') return '/resources/' + current.id + '/share';
    if (current.type === 'group') return '/groups/' + current.id + '/share';
    return null;
  }

  function bindShareTriggers() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.js-share-post, .js-share-resource, .js-share-group');
      if (!btn) return;
      e.preventDefault();

      if (btn.classList.contains('js-share-post')) {
        openModal({
          type: 'post',
          id: btn.dataset.postId,
          preview_text: btn.dataset.preview || 'a post'
        });
      } else if (btn.classList.contains('js-share-resource')) {
        openModal({
          type: 'resource',
          id: btn.dataset.resourceId,
          preview_text: btn.dataset.preview || 'a resource'
        });
      } else if (btn.classList.contains('js-share-group')) {
        openModal({
          type: 'group',
          id: btn.dataset.groupId,
          preview_text: btn.dataset.preview || 'a group'
        });
      }
    });
  }

  bindShareTriggers();

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
  });

  if (shareBtn) {
    shareBtn.addEventListener('click', async function () {
      var url = endpointForCurrent();
      if (!url) return;

      shareBtn.disabled = true;
      var oldHtml = shareBtn.innerHTML;
      shareBtn.innerHTML = 'Sharing...';

      try {
        var payload = { text_content: (txt && txt.value ? txt.value.trim() : '') };
        var res = await apiJson(url, payload);
        if (res && res.ok) {
          var detail = { type: current.type, id: current.id, response: res, payload: payload };
          closeModal();

          // Basic toast fallback (dashboard uses #dash-toast, profile uses #toast)
          var dashToast = document.getElementById('dash-toast');
          var profToast = document.getElementById('toast');
          var msg = (res && res.message) ? res.message : 'Shared!';
          if (dashToast) {
            dashToast.textContent = msg;
            dashToast.classList.add('show');
            setTimeout(function () { dashToast.classList.remove('show'); }, 3000);
          } else if (profToast) {
            profToast.textContent = msg;
            profToast.className = 'toast success show';
            setTimeout(function () { profToast.classList.remove('show'); }, 3000);
          }

          document.dispatchEvent(new CustomEvent('share:success', { detail: detail }));
        } else {
          var msg = (res && (res.message || (res.errors ? Object.values(res.errors).flat().join(' ') : null))) || 'Failed to share.';
          if (feedback) {
            feedback.textContent = msg;
            feedback.classList.add('error');
          }
        }
      } catch (err) {
        if (feedback) {
          feedback.textContent = 'Network error. Please try again.';
          feedback.classList.add('error');
        }
      }

      shareBtn.disabled = false;
      shareBtn.innerHTML = oldHtml;
      if (window.lucide) lucide.createIcons({ root: shareBtn });
    });
  }

  // Update UI for buttons after a successful share (best-effort)
  document.addEventListener('share:success', function (e) {
    var d = e.detail || {};
    if (!d.type || !d.id) return;
    if (d.type === 'resource') {
      var btn = document.querySelector('.js-share-resource[data-resource-id="' + d.id + '"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="check"></i> Shared';
        btn.style.background = '#10b981';
        btn.style.color = '#fff';
        if (window.lucide) lucide.createIcons({ root: btn });
      }
    } else if (d.type === 'group') {
      var btn = document.querySelector('.js-share-group[data-group-id="' + d.id + '"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="check"></i> Shared';
        btn.style.background = '#10b981';
        btn.style.color = '#fff';
        if (window.lucide) lucide.createIcons({ root: btn });
      }
    }
  });
});

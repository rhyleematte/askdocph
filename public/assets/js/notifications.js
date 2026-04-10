/* ================================================================
   NOTIFICATIONS DROPDOWN
================================================================ */
(function () {
  'use strict';

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function apiJson(url, body) {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var CSRF = csrfMeta ? csrfMeta.content : '';
    return fetch(url, {
      method: body ? 'POST' : 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF
      },
      body: body ? JSON.stringify(body) : null
    }).then(function (r) { 
        if (!r.ok || !r.headers.get('content-type')?.includes('application/json')) {
            return r.text().then(function(t) {
                console.error("API Error (" + url + "):", r.status, t.substring(0, 100));
                throw new Error("HTTP " + r.status);
            });
        }
        return r.json(); 
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var notifBtn = document.querySelector('button[aria-label="Notifications"]');
    if (!notifBtn) return;

    // Wrap button to anchor dropdown
    var wrap = document.createElement('div');
    wrap.className = 'notif-wrap';
    notifBtn.parentNode.insertBefore(wrap, notifBtn);
    wrap.appendChild(notifBtn);

    var dropdown = document.createElement('div');
    dropdown.className = 'notif-dropdown';
    dropdown.innerHTML =
      '<div class="notif-head">' +
        '<div class="notif-title">Notifications</div>' +
        '<button type="button" class="notif-mark-all">Mark all as read</button>' +
      '</div>' +
      '<div class="notif-list"></div>';
    wrap.appendChild(dropdown);

    var list = dropdown.querySelector('.notif-list');
    var markAllBtn = dropdown.querySelector('.notif-mark-all');

    var badge = notifBtn.querySelector('.notif-badge');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'notif-badge';
      notifBtn.appendChild(badge);
    }

    // Hide old dot if present
    var legacyDot = notifBtn.querySelector('.dot');
    if (legacyDot) legacyDot.style.display = 'none';

    var isOpen = false;
    var isLoading = false;

    function setBadge(count) {
      if (!badge) return;
      if (count > 0) {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.style.display = 'inline-flex';
      } else {
        badge.textContent = '';
        badge.style.display = 'none';
      }
    }

    function renderList(notifs) {
      list.innerHTML = '';
      if (!notifs || notifs.length === 0) {
        var empty = document.createElement('div');
        empty.className = 'notif-empty';
        empty.textContent = 'No notifications yet.';
        list.appendChild(empty);
        return;
      }

      notifs.forEach(function (n) {
        var item = document.createElement('a');
        item.className = 'notif-item' + (n.is_read ? '' : ' unread');
        item.href = n.url || '#';
        item.dataset.id = n.id;
        item.dataset.url = n.url || '';
        item.dataset.type = n.type || '';
        
        if (n.type === 'help_request' && n.data) {
            item.dataset.requestId = n.data.request_id || '';
            item.dataset.suggestedTitle = n.data.suggested_title || '';
            item.dataset.requesterName = n.actor ? (n.actor.name) : 'User';
            item.dataset.requesterAvatar = n.actor ? (n.actor.avatar_url) : '';
            item.dataset.requesterId = n.actor ? (n.actor.id) : '';
        }

        var avatar = (n.actor && n.actor.avatar_url) ? n.actor.avatar_url : '/assets/img/default.png';
        var actorName = (n.actor && n.actor.name) ? n.actor.name : 'System';

        item.innerHTML =
          '<div class="notif-avatar"><img src="' + esc(avatar) + '" alt="' + esc(actorName) + '"></div>' +
          '<div class="notif-body">' +
            '<div class="notif-message">' + esc(n.message || '') + '</div>' +
            '<div class="notif-time">' + esc(n.created_at || '') + '</div>' +
          '</div>';

        list.appendChild(item);
      });
    }

    function fetchNotifications() {
      if (isLoading) return Promise.resolve();
      isLoading = true;
      return apiJson('/api/notifications')
        .then(function (res) {
          if (res && res.ok) {
            var newCount = res.unread_count || 0;
            var oldCount = parseInt(badge.dataset.oldCount || 0);
            
            setBadge(newCount);
            
            if (newCount > oldCount) {
                badge.classList.remove('pulse');
                void badge.offsetWidth; // trigger reflow
                badge.classList.add('pulse');
            }
            badge.dataset.oldCount = newCount;
            
            renderList(res.notifications || []);
          }
        })
        .catch(function () {})
        .finally(function () { isLoading = false; });
    }

    notifBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      isOpen = !isOpen;
      dropdown.classList.toggle('open', isOpen);
      if (isOpen) {
        fetchNotifications();
      }
    });

    document.addEventListener('click', function (e) {
      if (!isOpen) return;
      if (wrap.contains(e.target)) return;
      isOpen = false;
      dropdown.classList.remove('open');
    });

    markAllBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      apiJson('/api/notifications/read-all', {}).then(function () {
        fetchNotifications();
      });
    });

    list.addEventListener('click', function (e) {
      var item = e.target.closest('.notif-item');
      if (!item) return;
      e.preventDefault();

      var id = item.dataset.id;
      var url = item.dataset.url;
      var type = item.dataset.type;

      if (type === 'help_request' && window.openMessengerWithRequest) {
          const requester = {
              id: item.dataset.requesterId,
              name: item.dataset.requesterName,
              avatar_url: item.dataset.requesterAvatar
          };
          window.openMessengerWithRequest(requester, item.dataset.requestId, item.dataset.suggestedTitle);
          isOpen = false;
          dropdown.classList.remove('open');
          
          // Mark as read without jumping
          apiJson('/api/notifications/' + id + '/read', {}).then(() => {
              fetchNotifications(); // Refresh the count and list
          });
          return;
      }

      apiJson('/api/notifications/' + id + '/read', {}).finally(function () {
        if (url && url !== '#') {
          window.location.href = url;
        }
      });
    });

    // Initial fetch + polling
    fetchNotifications();
    setInterval(fetchNotifications, 10000); // Increased frequency for "Auto Update"
  });
})();

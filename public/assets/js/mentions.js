/* ================================================================
   MENTIONS (autocomplete for @username)
   - Prioritizes people you follow
   - Falls back to followers, then global search
================================================================ */
(function () {
  'use strict';

  var LIMIT = 6;
  var following = null;
  var followers = null;
  var loadingNetwork = null;
  var searchCache = new Map();

  var activeInput = null;
  var activeRange = null;
  var activeItems = [];
  var activeIndex = -1;
  var requestSeq = 0;
  var updateTimer = null;

  var dropdown = document.createElement('div');
  dropdown.className = 'mention-suggest';
  dropdown.style.display = 'none';
  document.body.appendChild(dropdown);

  function getAuthUserId() {
    return window.AUTH_USER_ID || window.MY_ID || null;
  }

  function getNetworkUrl(id) {
    if (window.ROUTES && typeof window.ROUTES.profileNetwork === 'function') {
      return window.ROUTES.profileNetwork(id);
    }
    if (window.DASH_ROUTES && typeof window.DASH_ROUTES.profileNetwork === 'function') {
      return window.DASH_ROUTES.profileNetwork(id);
    }
    return '/api/profile/' + id + '/network';
  }

  function getSearchUrl(q) {
    return '/api/search/users?q=' + encodeURIComponent(q || '');
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function normalize(s) {
    return (s || '').toString().toLowerCase();
  }

  function matchUser(u, q) {
    if (!q) return true;
    var name = normalize(u.name);
    var uname = normalize(u.username);
    return name.indexOf(q) !== -1 || uname.indexOf(q) !== -1 || ('@' + uname).indexOf(q) !== -1;
  }

  function fetchJson(url) {
    return fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); });
  }

  function loadNetwork() {
    if (following && followers) return Promise.resolve({ following: following, followers: followers });
    if (loadingNetwork) return loadingNetwork;
    var userId = getAuthUserId();
    if (!userId) return Promise.resolve({ following: [], followers: [] });

    loadingNetwork = fetchJson(getNetworkUrl(userId))
      .then(function (res) {
        if (res && res.ok) {
          following = Array.isArray(res.following) ? res.following : [];
          followers = Array.isArray(res.followers) ? res.followers : [];
        } else {
          following = [];
          followers = [];
        }
        return { following: following, followers: followers };
      })
      .catch(function () {
        following = [];
        followers = [];
        return { following: following, followers: followers };
      })
      .finally(function () { loadingNetwork = null; });

    return loadingNetwork;
  }

  function searchUsers(q) {
    if (searchCache.has(q)) return searchCache.get(q);
    var p = fetchJson(getSearchUrl(q))
      .then(function (res) {
        if (res && res.ok && Array.isArray(res.users)) return res.users;
        return [];
      })
      .catch(function () { return []; });
    searchCache.set(q, p);
    return p;
  }

  async function getCandidates(q) {
    var lists = await loadNetwork();
    var seen = new Set();
    var results = [];

    function push(list) {
      list.forEach(function (u) {
        if (!u || results.length >= LIMIT) return;
        if (seen.has(u.id)) return;
        if (!matchUser(u, q)) return;
        seen.add(u.id);
        results.push(u);
      });
    }

    push(lists.following || []);
    push(lists.followers || []);

    if (results.length < LIMIT && q && q.length >= 2) {
      var extra = await searchUsers(q);
      extra.forEach(function (u) {
        if (!u || results.length >= LIMIT) return;
        if (seen.has(u.id)) return;
        if (!matchUser(u, q)) return;
        seen.add(u.id);
        results.push(u);
      });
    }

    return results;
  }

  function getMentionRange(input) {
    if (!input || typeof input.selectionStart !== 'number') return null;
    var value = input.value || '';
    var pos = input.selectionStart;
    var upto = value.slice(0, pos);
    var at = upto.lastIndexOf('@');
    if (at === -1) return null;
    var after = upto.slice(at + 1);
    if (/\s/.test(after)) return null;
    var prev = at > 0 ? upto[at - 1] : '';
    if (prev && !/[\s(\[{'"`]/.test(prev)) return null;
    return { start: at, end: pos, query: after };
  }

  function positionDropdown(input) {
    var rect = input.getBoundingClientRect();
    dropdown.style.left = (rect.left + window.scrollX) + 'px';
    dropdown.style.top = (rect.bottom + window.scrollY + 6) + 'px';
    dropdown.style.width = rect.width + 'px';
  }

  function renderDropdown(items) {
    dropdown.innerHTML = '';
    if (!items || items.length === 0) {
      hideDropdown();
      return;
    }
    activeItems = items;
    if (activeIndex < 0) activeIndex = 0;
    if (activeIndex >= items.length) activeIndex = items.length - 1;

    items.forEach(function (u, idx) {
      var row = document.createElement('div');
      row.className = 'mention-item' + (idx === activeIndex ? ' active' : '');
      row.innerHTML =
        '<img src="' + esc(u.avatar_url || '') + '" alt="' + esc(u.name || u.username || 'User') + '">' +
        '<div class="mention-meta">' +
        '<div class="mention-name">' + esc(u.name || u.username || 'User') + '</div>' +
        '<div class="mention-username">@' + esc(u.username || '') + '</div>' +
        '</div>';
      row.addEventListener('mousedown', function (e) {
        e.preventDefault();
        selectItem(idx);
      });
      dropdown.appendChild(row);
    });

    dropdown.style.display = 'block';
    if (activeInput) positionDropdown(activeInput);
  }

  function hideDropdown() {
    dropdown.style.display = 'none';
    activeItems = [];
    activeIndex = -1;
    activeRange = null;
  }

  function selectItem(idx) {
    var u = activeItems[idx];
    if (!u || !activeInput || !activeRange) return;

    var value = activeInput.value || '';
    var before = value.slice(0, activeRange.start);
    var after = value.slice(activeRange.end);
    var insert = '@' + (u.username || '') + ' ';

    activeInput.value = before + insert + after;
    var newPos = before.length + insert.length;
    try {
      activeInput.focus();
      activeInput.setSelectionRange(newPos, newPos);
    } catch (e) {}

    hideDropdown();
  }

  function scheduleUpdate(input) {
    if (updateTimer) clearTimeout(updateTimer);
    updateTimer = setTimeout(function () {
      updateSuggestions(input);
    }, 120);
  }

  async function updateSuggestions(input) {
    if (!input) return;
    var range = getMentionRange(input);
    if (!range) {
      hideDropdown();
      return;
    }
    activeInput = input;
    activeRange = range;
    positionDropdown(input);

    var q = normalize(range.query);
    var seq = ++requestSeq;
    var items = await getCandidates(q);
    if (seq !== requestSeq) return;
    renderDropdown(items);
  }

  function isMentionInput(el) {
    if (!el) return false;
    if (el.tagName === 'TEXTAREA') {
      return el.id === 'postText' || el.id === 'dashPostText' || (el.classList && el.classList.contains('post-edit-textarea'));
    }
    if (el.tagName === 'INPUT') {
      return el.classList && el.classList.contains('comment-input');
    }
    return false;
  }

  document.addEventListener('focusin', function (e) {
    if (isMentionInput(e.target)) {
      activeInput = e.target;
      scheduleUpdate(activeInput);
    } else {
      hideDropdown();
    }
  });

  document.addEventListener('input', function (e) {
    if (!isMentionInput(e.target)) return;
    activeInput = e.target;
    scheduleUpdate(activeInput);
  });

  document.addEventListener('keyup', function (e) {
    if (!isMentionInput(e.target)) return;
    var key = e.key || '';
    if (key === 'ArrowLeft' || key === 'ArrowRight' || key === 'Home' || key === 'End') {
      scheduleUpdate(e.target);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (!activeInput || e.target !== activeInput) return;
    if (dropdown.style.display !== 'block') return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = Math.min(activeItems.length - 1, activeIndex + 1);
      renderDropdown(activeItems);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = Math.max(0, activeIndex - 1);
      renderDropdown(activeItems);
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      if (activeIndex >= 0) {
        e.preventDefault();
        selectItem(activeIndex);
      }
    } else if (e.key === 'Escape') {
      hideDropdown();
    }
  });

  window.addEventListener('scroll', function () {
    if (dropdown.style.display === 'block' && activeInput) {
      positionDropdown(activeInput);
    }
  }, true);

  window.addEventListener('resize', function () {
    if (dropdown.style.display === 'block' && activeInput) {
      positionDropdown(activeInput);
    }
  });

  document.addEventListener('click', function (e) {
    if (dropdown.style.display !== 'block') return;
    if (dropdown.contains(e.target)) return;
    if (activeInput && e.target === activeInput) return;
    hideDropdown();
  });

  // Helper for reply buttons
  window.applyReplyMention = function (input, username) {
    if (!input || !username) return;
    var tag = '@' + username + ' ';
    var currentVal = input.value || '';
    if (currentVal.startsWith(tag)) {
      input.focus();
      return;
    }
    if (/^@[\w.\-]+ /.test(currentVal)) {
      input.value = currentVal.replace(/^@[\w.\-]+ /, tag);
    } else {
      input.value = tag + currentVal;
    }
    try {
      input.focus();
      input.setSelectionRange(tag.length, tag.length);
    } catch (e) {}
  };
})();

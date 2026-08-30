/**
 * Keep studio header cart badges in sync with studio line items.
 */
(function () {
  'use strict';

  var CFG = window.DEJOIY_STUDIO_BADGE || {};

  function updateBadges(count) {
    var n = parseInt(count, 10) || 0;
    ['dsu-hdr-cart-count', 'dsu-hdr-cart-count-mob'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      if (n > 0) {
        el.textContent = String(n);
        el.removeAttribute('hidden');
      } else {
        el.textContent = '0';
        el.setAttribute('hidden', '');
      }
    });
  }

  function refreshFromServer() {
    if (!CFG.ajax_url) return;
    var url = CFG.ajax_url + (CFG.ajax_url.indexOf('?') >= 0 ? '&' : '?') + 'action=dejoiy_studio_cart_count';
    return fetch(url, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.success && data.data && typeof data.data.count !== 'undefined') {
          updateBadges(data.data.count);
          return data.data.count;
        }
        return null;
      })
      .catch(function () { return null; });
  }

  window.dejoiyStudioSetCartBadge = updateBadges;
  window.dejoiyStudioRefreshCartBadge = refreshFromServer;

  if (typeof CFG.count !== 'undefined') {
    updateBadges(CFG.count);
  }
  refreshFromServer();

  window.addEventListener('pageshow', function (ev) {
    if (ev.persisted) {
      refreshFromServer();
    }
  });

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') {
      refreshFromServer();
    }
  });
})();

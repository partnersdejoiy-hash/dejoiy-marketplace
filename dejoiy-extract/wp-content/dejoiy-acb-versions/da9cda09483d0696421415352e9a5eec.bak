/**
 * Nexus cart — force full navigation on remove links (theme AJAX often breaks these).
 */
(function () {
  'use strict';

  function onRemoveClick(e) {
    var link = e.currentTarget;
    if (!link || !link.href || link.href.indexOf('remove_item=') === -1) {
      return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();
    window.location.href = link.href;
  }

  function bindRemoveLinks() {
    var root = document.querySelector('.dlu-cart-page, .dlu-shelf-list');
    if (!root) {
      return;
    }
    root.querySelectorAll('.dlu-shelf-remove, a.remove').forEach(function (link) {
      link.removeEventListener('click', onRemoveClick, true);
      link.addEventListener('click', onRemoveClick, true);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindRemoveLinks);
  } else {
    bindRemoveLinks();
  }
})();

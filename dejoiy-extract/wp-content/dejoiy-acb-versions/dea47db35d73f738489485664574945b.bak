/**
 * Studio cart — ensure remove links perform a full navigation (theme AJAX often breaks these).
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
    var root = document.querySelector('.dsu-cart');
    if (!root) {
      return;
    }
    root.querySelectorAll('.product-remove a.remove').forEach(function (link) {
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

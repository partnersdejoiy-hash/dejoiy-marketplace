/**
 * Studio WooCommerce — add to cart with instant badge update; buy now → studio checkout.
 */
(function ($) {
  'use strict';

  var CFG = window.DEJOIY_STUDIO_WC || {};
  var BUY_NOW_SEL = '.et-single-buy-now, .buy-now-button, .buy_now_button, .button.buy-now';

  function refreshBadge() {
    if (window.dejoiyStudioRefreshCartBadge) {
      return window.dejoiyStudioRefreshCartBadge();
    }
    return $.Deferred().resolve().promise();
  }

  function setBadgeCount(count) {
    if (window.dejoiyStudioSetCartBadge && typeof count === 'number') {
      window.dejoiyStudioSetCartBadge(count);
    }
  }

  function showNotice(msg, type) {
    var existing = document.querySelector('.dsu-atc-notice');
    if (existing) existing.remove();
    var note = document.createElement('div');
    note.className = 'dsu-atc-notice dsu-atc-notice--' + (type || 'success');
    note.setAttribute('role', 'status');
    note.textContent = msg;
    var shell = document.querySelector('.dsu-single-shell');
    if (shell) shell.insertBefore(note, shell.firstChild);
    setTimeout(function () {
      note.classList.add('dsu-atc-notice--out');
      setTimeout(function () { note.remove(); }, 400);
    }, 3200);
  }

  function ensureFlowFields($form) {
    if (!$form || !$form.length) return;
    if (!$form.find('[name="dejoiy_studio"]').length) {
      $form.append('<input type="hidden" name="dejoiy_studio" value="1" />');
    }
  }

  function isBuyNowButton(el) {
    return el && $(el).is(BUY_NOW_SEL);
  }

  function formHasUpload($form) {
    var input = $form.find('[name="dejoiy_custom_art"]')[0];
    return !!(input && input.files && input.files.length > 0);
  }

  function setBuyNowFields($form, enable) {
    $form.find('[name="woocommerce-buy-now"], [name="dejoiy_studio_buy_now"]').remove();
    if (!enable) return;
    var pid = $form.find('[name="add-to-cart"]').val();
    if (pid) {
      $form.append('<input type="hidden" name="woocommerce-buy-now" value="' + pid + '" />');
    }
    $form.append('<input type="hidden" name="dejoiy_studio_buy_now" value="1" />');
  }

  function bindForm() {
    var $form = $('form.cart');
    if (!$form.length) return;

    ensureFlowFields($form);

    $form.on('click', BUY_NOW_SEL, function (e) {
      ensureFlowFields($form);
      setBuyNowFields($form, true);
      buyNowClicked = true;
      /* Text-only: AJAX add then studio checkout. With file: native POST. */
      if (!formHasUpload($form) && CFG.wc_ajax) {
        e.preventDefault();
        $form.trigger('submit');
      }
    });

    $form.on('click', '.single_add_to_cart_button', function () {
      if (!isBuyNowButton(this)) {
        setBuyNowFields($form, false);
      }
    });

    $form.on('submit', function (e) {
      if (!$form.find('[name="add-to-cart"]').length) return;

      var submitter = e.originalEvent && e.originalEvent.submitter;
      var isBuyNow = buyNowClicked || isBuyNowButton(submitter);
      var hasUpload = formHasUpload($form);

      ensureFlowFields($form);
      if (isBuyNow) {
        setBuyNowFields($form, true);
      } else {
        setBuyNowFields($form, false);
      }

      var $btn = isBuyNow
        ? $form.find(BUY_NOW_SEL).first()
        : $form.find('.single_add_to_cart_button').not(BUY_NOW_SEL).first();
      if (!$btn.length) {
        $btn = $form.find('.single_add_to_cart_button').first();
      }

      /* File uploads must use native POST. */
      if (hasUpload) {
        $btn.addClass('loading').prop('disabled', true);
        return;
      }

      if (!CFG.wc_ajax) {
        $btn.addClass('loading').prop('disabled', true);
        return;
      }

      e.preventDefault();
      if ($btn.hasClass('loading')) return;

      $btn.addClass('loading').prop('disabled', true);

      var formData = new FormData($form[0]);
      formData.set('dejoiy_studio', '1');
      if (isBuyNow) {
        formData.set('dejoiy_studio_buy_now', '1');
        var productId = $form.find('[name="add-to-cart"]').val();
        if (productId) {
          formData.set('woocommerce-buy-now', productId);
        }
      } else {
        formData.set('dejoiy_studio_ajax', '1');
      }

      var url = CFG.wc_ajax.replace('%%endpoint%%', 'add_to_cart');

      $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.error && response.product_url) {
            showNotice(CFG.i18n_error || 'Could not add to cart.', 'error');
            refreshBadge();
            return;
          }

          if (response.fragments) {
            $.each(response.fragments, function (key, html) {
              if ($(key).length) {
                $(key).replaceWith(html);
              }
            });
            var tmp = response.fragments['#dsu-hdr-cart-count'];
            if (tmp) {
              var $t = $('<div>').html(tmp);
              setBadgeCount(parseInt($t.text(), 10) || 0);
            }
          }

          if (isBuyNow) {
            refreshBadge();
            window.location.href = CFG.checkout_url || '/checkout/?dejoiy_studio=1';
            return;
          }

          refreshBadge().always(function () {
            showNotice(CFG.i18n_added || 'Added to studio cart', 'success');
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
          });
        },
        error: function () {
          showNotice(CFG.i18n_error || 'Could not add to cart.', 'error');
          refreshBadge();
        },
        complete: function () {
          if (!isBuyNow) {
            $btn.removeClass('loading').prop('disabled', false);
          }
        }
      });
    });
  }

  $(bindForm);
})(jQuery);

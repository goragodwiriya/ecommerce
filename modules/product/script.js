// modules/product/script.js
var product_patt = /(additional|addtocart|(item)_([0-9]+))_([0-9]+)/,
  currencyChange = function() {
    window.location = replaceURL('curr', this.value);
  };

function inintProductList(id) {
  var _doAction = function() {
    var aid = 0,
      hs = product_patt.exec(this.id);
    if (hs[1] == 'addtocart') {
      if ($E('additional_' + hs[4])) {
        var aid = $E('additional_' + hs[4]).value;
        if (aid == '') {
          alert(ADDITIONAL_ITEM_EMPTY);
          return false;
        }
      }
      send(WEB_URL + 'modules/product/basket.php', 'aid=' + aid, function(xhr) {
        doProductSubmit(xhr);
        inintCart("cart_tbody", "product");
      });
      return false;
    } else if (hs[2] == 'item') {
      send(WEB_URL + 'modules/product/getprice.php', 'id=' + this.id, doFormSubmit);
    }
  };
  var _additionalChange = function() {
    send(WEB_URL + 'modules/product/getprice.php', 'id=' + this.value, doFormSubmit);
  };
  forEach($E(id).getElementsByTagName('*'), function() {
    var hs = product_patt.exec(this.id);
    if (hs) {
      if (hs[1] == 'additional') {
        $G(this).addEvent('change', _additionalChange);
      } else {
        callClick(this, _doAction);
      }
    }
  });
}
var doProductSearch = function(e) {
  var q = '';
  if ($E('widget-search-input').value != '') {
    q += '&q=' + decodeURIComponent($E('widget-search-input').value);
  }
  if ($E('category_id')) {
    q += '&category_id=' + $E('category_id').value;
    q += '&cover_id=' + $E('cover_id').value;
    q += '&author_id=' + $E('author_id').value;
  }
  if (q != '') {
    loaddoc(WEB_URL + 'index.php?module=product-list' + q);
  }
  GEvent.stop(e);
};

function inintProductView(id) {
  var patt = /^(quote|edit|delete)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/;
  var _productViewAction = function(action) {
    var temp = this;
    send(WEB_URL + 'modules/product/action.php', action, function(xhr) {
      var ds = xhr.responseText.toJSON();
      if (ds) {
        if (ds[0].action == 'quote') {
          if (editor && ds[0].detail !== '') {
            editor.value = editor.value + decodeURIComponent(ds[0].detail);
            editor.focus();
          }
        }
        if (ds[0].error) {
          alert(eval(ds[0].error));
        }
        if (ds[0].confirm) {
          if (confirm(eval(ds[0].confirm))) {
            if (ds[0].action == 'deleting') {
              _productViewAction.call(temp, 'id=' + temp.className.replace('delete-', 'deleting-'));
            } else {
              var q = 'action=' + ds[0].action;
              q += '&url=' + ds[0].url;
              q += '&topic=' + ds[0].topic;
              showModal(WEB_URL + 'modules/pm/senddelete.php', q);
            }
          }
        }
        if (ds[0].location) {
          loaddoc(ds[0].location.replace(/&amp;/g, '&'));
        }
        if (ds[0].remove && $E(ds[0].remove)) {
          $G(ds[0].remove).remove();
        }
      } else {
        alert(xhr.responseText);
      }
    });
  };
  var tag, first_item = null;
  forEach($E(id).getElementsByTagName('*'), function(item, index) {
    if (patt.exec(item.className)) {
      callClick(item, function() {
        _productViewAction.call(this, 'id=' + this.className);
      });
    } else {
      tag = item.tagName.toLowerCase();
      if (tag == 'img' && !$G(item).hasClass('nozoom')) {
        if (G_Lightbox === null) {
          G_Lightbox = new GLightbox();
        } else {
          G_Lightbox.clear();
        }
        new preload(item, function() {
          if (floatval(this.width) > floatval(item.width)) {
            G_Lightbox.add(item);
          }
        });
      } else if (tag == 'input' && product_patt.test(item.id)) {
        if (first_item == null) {
          first_item = item;
          if (item.tagName.toLowerCase() == 'input') {
            item.checked = true;
          }
        }
      }
    }
  });
}

function doProductCancle(button, id) {
  callClick(button, function() {
    if (confirm(CONFIRM_CANCLE_ORDER)) {
      var q = 'action=cancleorder&id=' + id;
      send(WEB_URL + 'modules/product/action.php', q, doFormSubmit);
    }
  });
}
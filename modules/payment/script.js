// modules/payment/script.js
function inintCart(id, module) {
  var patt = /(checkout|cart)_(delete|quantity)_([0-9]+)/;
  var _doclick = function () {
    hs = patt.exec(this.id);
    if (hs[2] == 'delete' && confirm(CONFIRM_DELETE)) {
      send(WEB_URL + 'modules/' + module + '/action.php', 'action=removecart&id=' + hs[3], doProductSubmit);
    } else if (hs[2] == 'quantity') {
      send(WEB_URL + 'modules/' + module + '/action.php', 'action=quantity&id=' + hs[3] + '&value=' + this.value, doFormSubmit);
    }
  };
  forEach($E(id).getElementsByTagName('a'), function () {
    if (patt.test(this.id)) {
      callClick(this, _doclick);
    }
  });
  forEach($E(id).getElementsByTagName('input'), function () {
    if (patt.test(this.id)) {
      if (this.onchange == null) {
        this.onchange = _doclick;
        $G(this).addEvent('keypress', numberOnly);
      }
    }
  });
  if ($E('checkout_close')) {
    callClick('checkout_close', hideModal);
  }
  var _doCheckOut = function () {
    var q = null;
    if ($E('currencyunit')) {
      q = 'curr=' + $E('currencyunit').value;
    }
    showModal(WEB_URL + 'modules/' + module + '/checkout.php', q);
  };
  if ($E('cart_checkout')) {
    callClick('cart_checkout', _doCheckOut);
  }
  if ($E('float_cart')) {
    callClick('float_cart', _doCheckOut);
  }
  inintTR(id, /cart_tr_[0-9]+/);
}
function setBasket(tr) {
  var tbody = $E('cart_tbody');
  var trs = tbody.getElementsByTagName('tr');
  for (var i = trs.length - 1; i > 0; i--) {
    tbody.removeChild(trs[i]);
  }
  if (tr != '') {
    tbody.appendChild(tr.toDOM());
    inintTR(tbody, /cart_tr_[0-9]+/);
  }
  if ($E('cart_empty')) {
    $E('cart_empty').className = tbody.getElementsByTagName('tr').length == 1 ? '' : 'hidden';
  }
}
var doProductSubmit = function (xhr) {
  var prop, val, remove = /remove([0-9]{0,})/, ds = xhr.responseText.toJSON();
  if (ds) {
    for (prop in ds[0]) {
      val = ds[0][prop];
      if (prop == 'content') {
        setBasket(decodeURIComponent(val));
      } else if (remove.test(prop)) {
        if ($E(val)) {
          var tbody = $E(val).parentNode;
          $G(val).remove();
          inintTR(tbody, /cart_tr_[0-9]+/);
        }
      } else if (prop == 'highlight') {
        window.setTimeout(function () {
          var tr = $G(ds[0]['highlight']);
          window.scrollTo(0, tr.getTop() - 100);
          tr.highlight();
        }, 1);
      } else if (prop == 'error') {
        var value = eval(val);
        if (val == 'CART_EMPTY') {
          setBasket('');
          if (modal) {
            modal.hide();
          }
        }
        alert(value);
      } else if (prop == 'alert') {
        alert(val);
      } else if ($E(prop)) {
        $G(prop).setValue(decodeURIComponent(val));
      }
    }
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
};
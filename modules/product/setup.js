// modules/product/setup.js
function doProductInint(id, action) {
  var loading = true;
  var patt = /^unlimited[\s]+(([a-z_]+)([0-9]+)?)$/;
  var _doChecked = function () {
    var hs = patt.exec(this.className);
    var input = $G(hs[1]);
    if (this.checked) {
      input.set('value', input.value);
      input.disabled = 'disabled';
      input.value = this.get('value');
    } else {
      input.disabled = '';
      input.value = input.get('value');
    }
    _doChange.call(this);
  };
  var _doChange = function () {
    if (!loading && action) {
      var patt = /^(stock|unlimited)_([0-9]+)$/;
      var hs = patt.exec(this.id);
      if (hs) {
        send(action, 'action=stock&id=' + hs[2] + '&value=' + $E('stock_' + hs[2]).value, function (xhr) {
          if (xhr.responseText != '') {
            alert(xhr.responseText);
          }
        });
      }
    }
  };
  forEach($E(id).getElementsByTagName('input'), function () {
    var c = this.className;
    if (c == 'currency' || c == 'number' || c == 'integer') {
      inputValidate(this, c);
    } else if (hs = patt.exec(c)) {
      if (this.onclick == null) {
        $G(this).set('value', this.value);
        inputValidate(hs[1], 'number');
        _doChecked.call(this);
        this.onclick = _doChecked;
        if (action) {
          $G(hs[1]).addEvent('change', _doChange);
        }
      }
    }
  });
  loading = false;
}
function inintProductCategory(id) {
  var patt = /^subbtn_([0-9]+)$/;
  var _doclick = function () {
    showModal(WEB_URL + 'modules/product/admin_subcategory.php', 'id=' + this.id.replace('subbtn_', ''), function () {
      inintTR('tbl_category');
    });
    return false;
  };
  forEach($E('tbl_category').getElementsByTagName('a'), function () {
    if (patt.test(this.id)) {
      callClick(this, _doclick);
    }
  });
  inintListCategory(id);
}
function inintProductSelect(id, mid, target) {
  var editor, c, hs;
  var patt = new RegExp('config_(' + id + ')_(delete|name)_([0-9]+)');
  function _doAction(c) {
    var q = '';
    if (this.id == 'config_' + id + '_add') {
      q = 'action=' + this.id + '&mid=' + mid;
    } else if (hs = patt.exec(this.id)) {
      if (hs[2] == 'delete' && confirm(CONFIRM_DELETE)) {
        q = 'action=' + this.id + '&mid=' + mid;
      }
    }
    if (q != '') {
      send(WEB_URL + 'modules/product/' + target + '.php', q, function (xhr) {
        var ds = xhr.responseText.toJSON();
        if (ds) {
          if (ds[0].data) {
            $G('config_' + id).appendChild(decodeURIComponent(ds[0].data).toDOM());
            _doInintProductMethod(ds[0].newId);
          } else if (ds[0].del) {
            $G(ds[0].del).remove();
          }
        } else if (xhr.responseText != '') {
          alert(xhr.responseText);
        }
      });
    }
  }
  var o = {
    editTxt: '<input type="text" size="50" class="editable" />',
    onSave: function (v) {
      var req = new GAjax({
        asynchronous: false
      });
      req.inintLoading('wait', false);
      req.send(WEB_URL + 'modules/product/' + target + '.php', 'action=' + this.id + '&value=' + encodeURIComponent(v) + '&mid=' + mid);
      req.hideLoading();
      var ds = req.responseText.toJSON();
      if (ds) {
        if (ds[0]['error']) {
          alert(eval(ds[0]['error']));
        }
        if (ds[0].edit) {
          $E(ds[0].editId).innerHTML = decodeURIComponent(ds[0].edit);
          return true;
        }
      } else if (req.responseText != '') {
        alert(req.responseText);
      }
      return false;
    }
  };
  var _dokeydown = function (e) {
    var key = GEvent.keyCode(e);
    if (key == 13 || key == 32) {
      _doAction.call(this);
    }
  };
  function _doInintProductMethod(id) {
    var hs, t, loading = true;
    forEach($G('config_' + id).getElementsByTagName('*'), function () {
      hs = patt.exec(this.id);
      if (hs) {
        if (hs[2] == 'delete') {
          this.style.cursor = 'pointer';
          $G(this).addEvent('click', _doAction);
          $G(this).addEvent('keydown', _dokeydown);
        } else if (hs[2] == 'name') {
          this.style.cursor = 'pointer';
          editor = new EditInPlace(this, o);
        }
      }
    });
    loading = false;
  }
  $G('config_' + id + '_add').addEvent('click', _doAction);
  $G('config_' + id + '_add').addEvent('keydown', _dokeydown);
  _doInintProductMethod(id);
}
function inintProductSetup(id) {
  var hs,
    patt = /^unlimited[\s]+(([a-z_]+)[0-9]+)$/,
    patt2 = /^(([a-z_]+)_([a-z]+)_)[0-9]+$/;
  function inintBGTr(id) {
    var bg = 'bg1';
    forEach($E(id).getElementsByTagName('tr'), function (item, index) {
      bg = bg == 'bg2' ? 'bg1' : 'bg2';
      item.className = bg;
      item.id = 'M_' + index;
      forEach(item.getElementsByTagName('input'), function () {
        hs = patt2.exec(this.id);
        if (hs) {
          this.id = hs[1] + index;
        }
        hs = patt.exec(this.className);
        if (hs) {
          this.className = 'unlimited ' + hs[2] + index;
        }
      });
    });
  }
  var _doImgClick = function () {
    var c = this.className;
    if (c == 'add') {
      var tbody = $E(id).parentNode.tBodies[0];
      var tr = $G(tbody.rows[0]);
      var ntr = tr.copy(false);
      tbody.appendChild(ntr);
      doProductInint(tbody);
      inintBGTr(tbody);
      inintProductSetup(ntr);
      ntr.highlight();
      var f = null;
      forEach(ntr.getElementsByTagName('input'), function () {
        if (f == null && this.type.toLowerCase() == 'text') {
          f = this;
        }
        hs = patt2.exec(this.id);
        if (hs && hs[3] == 'id') {
          this.value = 0;
        }
      });
      f.focus();
      f.select();
    } else if (c == 'delete') {
      var tr = $G(this.parentNode.parentNode);
      if (tr.parentNode.getElementsByTagName('tr').length > 1) {
        var tmp = tr.parentNode;
        tr.remove();
        inintBGTr(tmp);
      }
    }
  };
  forEach($E(id).getElementsByTagName('img'), function () {
    if (this.className == 'delete') {
      callClick(this, _doImgClick);
    }
  });
  doProductInint(id);
  inintBGTr(id);
  callClick('additional_add', _doImgClick);
}
function doProductPicDelete(e) {
  var patt = /^([0-9]+)\-([0-9]+)\-([0-9]+)$/;
  var hs = patt.exec($E(this.id.replace('productpicdelete', 'productpicid')).value);
  if (hs && confirm(CONFIRM_DELETE)) {
    send(WEB_URL + 'modules/product/admin_upload_delete.php', 'pid=' + hs[1] + '&id=' + hs[3], function (xhr) {
      var ds = xhr.responseText.toJSON();
      if (ds) {
        for (prop in ds[0]) {
          $E('productpicimg-' + ds[0][prop]).src = WEB_URL + "modules/product/img/nopicture.png";
        }
      } else if (xhr.responseText != '') {
        alert(xhr.responseText);
      }
    }, this);
  }
}
function inintProductUpload(id) {
  var ds, patt = /^productpicdelete-([0-9]+)$/;
  var _doBrowser = function () {
    $E(this.name.replace('file', 'productpicfrm')).GForm.submit(function (xhr) {
      ds = xhr.responseText.toJSON();
      if (ds) {
        var no = ds[0].index;
        if (ds[0].error) {
          alert(eval(ds[0].error));
        } else if (ds[0].result) {
          if (ds[0].result == '') {
            $G('productpicfile-' + no).hideTooltip();
          } else {
            $G('productpicfile-' + no).showTooltip(eval(ds[0].result));
          }
        }
        if (ds[0].img) {
          $E('productpicimg-' + no).src = decodeURIComponent(ds[0].img);
        }
        if (ds[0].product_id) {
          $E('productpicid-' + no).value = ds[0].product_id + '-' + no + '-' + no;
        }
      } else if (xhr.responseText != '') {
        alert(xhr.responseText);
      }
    });
  };
  forEach($G(id).getElementsByTagName('form'), function () {
    new GForm(this, WEB_URL + 'modules/product/admin_upload_file.php', this.id.replace('productpicfrm-', 'productpicwait-'), false);
  });
  forEach($G(id).getElementsByTagName('input'), function () {
    if (this.type.toLowerCase() == 'file') {
      $G(this).addEvent('change', _doBrowser);
    }
  });
  forEach($G(id).getElementsByTagName('span'), function () {
    if (patt.test(this.id)) {
      callClick(this, doProductPicDelete);
    }
  });
}
var doSubcategoryAdd = function (xhr) {
  hideModal();
  var val, ds = xhr.responseText.toJSON();
  if (ds) {
    for (prop in ds[0]) {
      val = ds[0][prop];
      if (prop == 'error') {
        alert(eval(val));
      } else if (prop == 'input') {
        el = $G(val);
        el.focus();
        el.highlight();
      } else if ($E(prop)) {
        $G(prop).setHTML(decodeURIComponent(val));
      }
    }
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
};
function checkProductNo() {
  var value = this.input.value;
  if (value.length < 3) {
    this.invalid(PRODUCT_NO_SHORT);
  } else {
    return 'action=productno&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value;
  }
}
function inintProductWrite(id, sel) {
  var a;
  function _doclick(sel) {
    forEach($E(id).getElementsByTagName('a'), function () {
      a = this.id.replace('tab_', '');
      if ($E(a)) {
        this.className = a == sel ? 'select' : '';
        $E(a).style.display = a == sel ? 'block' : 'none';
      }
    });
    $E('write_tab').value = sel;
    $E('saveasdefault').style.display = (sel == 'additional' || sel == 'options') ? '' : 'none';
  }
  forEach($E(id).getElementsByTagName('a'), function () {
    if ($E(this.id.replace('tab_', ''))) {
      if (this.onclick == null) {
        this.onclick = function () {
          _doclick(this.id.replace('tab_', ''));
          return false;
        };
      }
    }
  });
  _doclick(sel);
  if ($E('tab_upload').onclick == null) {
    $G('tab_upload').onclick = function () {
      var id = floatval($E('write_id').value);
      if (id == 0) {
        alert(PLEASE_SAVE_PRODUCT);
        return false;
      }
    };
  }
}
var doProductSubmit = function (xhr) {
  var datas = xhr.responseText.toJSON();
  if (datas) {
    if (datas[0]['tab']) {
      inintProductWrite("accordient_menu", datas[0]['tab']);
    }
    defaultSubmit(datas[0]);
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
}
function ProductChange(price, discount, net) {
  var price = $G(price);
  var discount = $G(discount);
  var net = $G(net);
  var _calc = function () {
    var _price = floatval(price.value);
    var _discount = floatval(discount.value);
    var _net = floatval(net.value);
    if (this == price || this == discount) {
      _net = _price - ((_price * _discount) / 100);
    } else {
      _discount = ((_price - _net) * 100) / _price;
    }
    price.value = CurrencyFormatted(_price);
    discount.value = CurrencyFormatted(_discount);
    net.value = CurrencyFormatted(_net);
  }
  price.addEvent('change', _calc);
  discount.addEvent('change', _calc);
  net.addEvent('change', _calc);
}
;
function inintProductView(id) {
  forEach($E(id).getElementsByTagName('input'), function () {
    var c = this.className;
    if (c == 'number' || c == 'currency') {
      inputValidate(this, c);
    }
  });
}
// js/common.js
var INVALID_EMAIL = /^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/,
  INVALID_PASSWORD = new RegExp('^[a-z0-9]{1,}$'),
  mtooltip,
  modal = null,
  loader = null,
  editor = null;
function mTooltipShow(id, action, elem) {
  if (Object.isNull(mtooltip)) {
    mtooltip = new GTooltip({
      className: 'member-tooltip',
      fade: true,
      cache: true
    });
  }
  mtooltip.showAjax(elem, action, 'id=' + id, function (xhr) {
    if (loader) {
      loader.inint(this.tooltip);
    }
  });
}
function send(target, query, callback, wait, c) {
  var req = new GAjax();
  req.inintLoading(wait || 'wait', false, c);
  req.send(target, query, function (xhr) {
    callback.call(this, xhr);
  });
}
var hideModal = function () {
  if (modal != null) {
    modal.hide();
  }
};
function showModal(src, qstr, doClose) {
  send(src, qstr, function (xhr) {
    var ds = xhr.responseText.toJSON();
    var content = '';
    if (ds) {
      if (ds[0].error) {
        alert(eval(ds[0].error));
      } else if (ds[0].content) {
        content = decodeURIComponent(ds[0].content);
      }
    } else {
      content = decodeURIComponent(xhr.responseText);
    }
    if (content != '') {
      modal = new GModal({
        onclose: doClose
      }).show(content);
      content.evalScript();
    }
  });
}
function defaultSubmit(ds) {
  var prop, val, el, tag, _alert = '';
  var hs, remove = /remove([0-9]{0,})/;
  for (prop in ds) {
    val = ds[prop];
    if (prop == 'error') {
      if (_alert == '') {
        _alert = eval(val);
        alert(_alert);
      }
    } else if (prop == 'alert') {
      if (_alert == '') {
        _alert = decodeURIComponent(val);
        alert(_alert);
      }
    } else if (prop == 'lastupdate') {
      $G('lastupdate').setHTML(val);
    } else if (prop == 'location') {
      if (val == 'close') {
        if (modal) {
          modal.hide();
        }
      } else if (val == 'reload') {
        window.location.reload();
      } else if (val == 'back') {
        window.history.go(-1);
      } else {
        loaddoc(decodeURIComponent(val));
      }
    } else if (prop == 'url') {
      window.location = decodeURIComponent(val);
    } else if (prop == 'tab') {
      inintWriteTab("accordient_menu", val);
    } else if (remove.test(prop)) {
      if ($E(val)) {
        $G(val).remove();
      }
    } else if (prop == 'input') {
      el = $G(val);
      tag = el.tagName.toLowerCase();
      el.focus();
      if (tag != 'select') {
        el.highlight();
      }
      if (tag == 'input' && el.get('type').toLowerCase() == 'text') {
        el.select();
      }
    } else if (prop == 'eval') {
      eval(decodeURIComponent(val));
    } else if ($E(prop)) {
      $G(prop).setValue(decodeURIComponent(val).replace('%', '&#37;'));
    } else if ($E(prop.replace('ret_', ''))) {
      el = $G(prop.replace('ret_', ''));
      if (val == '') {
        el.valid();
      } else if (val == 'this') {
        var t = el.title.strip_tags();
        if (t == '') {
          t = el.placeholder.strip_tags();
        }
        el.invalid(t);
        if (_alert == '') {
          _alert = t;
          alert(_alert);
          el.focus();
        }
      } else {
        el.invalid(eval(val));
      }
    }
  }
}
var doFormSubmit = function (xhr) {
  var datas = xhr.responseText.toJSON();
  if (datas) {
    defaultSubmit(datas[0]);
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
};
function inintWriteTab(id, sel) {
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
  }
  forEach($E(id).getElementsByTagName('a'), function () {
    if ($E(this.id.replace('tab_', ''))) {
      callClick(this, function () {
        _doclick(this.id.replace('tab_', ''));
        return false;
      });
    }
  });
  _doclick(sel);
}
function inintCheck(obj) {
  var chk, patt = /check_[0-9]+/;
  forEach($E(obj).getElementsByTagName('a'), function () {
    if ($G(this).hasClass('checkall')) {
      if (this.onclick == null) {
        this.title = SELECT_ALL;
        callClick(this, function () {
          this.focus();
          chk = this.hasClass('icon-check');
          forEach($E(obj).getElementsByTagName('a'), function () {
            if (patt.test(this.id)) {
              this.className = chk ? 'icon-uncheck' : 'icon-check';
              this.title = chk ? CHECK : UNCHECK;
            } else if (this.hasClass('checkall')) {
              this.className = chk ? 'checkall icon-uncheck' : 'checkall icon-check';
              this.title = chk ? SELECT_ALL : SELECT_NONE;
            }
          });
          return false;
        });
      }
    } else if (patt.test(this.id)) {
      if (this.onclick == null) {
        this.title = CHECK;
        callClick(this, function () {
          this.focus();
          chk = this.hasClass('icon-check');
          this.className = chk ? 'icon-uncheck' : 'icon-check';
          this.title = chk ? CHECK : UNCHECK;
          forEach($E(obj).getElementsByTagName('a'), function () {
            if (this.hasClass('checkall')) {
              this.className = 'checkall icon-uncheck';
              this.title = SELECT_ALL;
            }
          });
          return false;
        });
      }
    }
  });
}
function setQueryURL(key, value) {
  var a = new Array();
  var patt = new RegExp(key + '=.*');
  var ls = window.location.toString().split(/[\?\#]/);
  if (ls.length == 1) {
    window.location = ls[0] + '?' + key + '=' + value;
  } else {
    forEach(ls[1].split('&'), function (item) {
      if (!patt.test(item)) {
        a.push(item);
      }
    });
    var url = ls[0] + '?' + key + '=' + value + (a.length == 0 ? '' : '&' + a.join('&'));
    if (key == 'action' && value == 'logout') {
      window.location = url;
    } else {
      loaddoc(url);
    }
  }
}
function doAction(action, id, target, callback, customconfirm, input) {
  var hs,
    query = '',
    cs = new Array(),
    chk = /check_[0-9]+/;
  forEach($E(id).getElementsByTagName('a'), function () {
    if (chk.test(this.id) && $G(this).hasClass('icon-check')) {
      cs.push(this.id.replace('check_', ''));
    }
  });
  if (cs.length == 0) {
    alert(PLEASE_SELECT_ONE);
  } else {
    var patt = /([a-z]+)(_([0-9a-z]+))?(_(-?[0-9]+))?/;
    cs = cs.join(',');
    hs = patt.exec(action);
    if (hs) {
      if (customconfirm) {
        query = customconfirm(action, cs, hs);
      } else if (hs[1] == 'status' && confirm(REGISTER_ADMIN_CONFIRM_CHANGE_STATUS)) {
        query = 'action=status&value=' + $E('status').value + '&id=' + cs;
      } else if (hs[1] == 'ban' && confirm(REGISTER_ADMIN_CONFIRM_BAN)) {
        query = 'action=ban&value=' + $E('ban').value + '&id=' + cs;
      } else if (hs[1] == 'accept' && confirm(REGISTER_ADMIN_CONFIRM_ACCEPT_ACTIVATE)) {
        query = 'action=accept&id=' + cs;
      } else if (hs[1] == 'activate' && confirm(REGISTER_ADMIN_CONFIRM_SEND_ACTIVATE)) {
        query = 'action=activate&id=' + cs;
      } else if (hs[1] == 'sendpassword' && confirm(REGISTER_ADMIN_CONFIRM_SEND_PASSWORD)) {
        query = 'action=sendpassword&id=' + cs;
      } else if (hs[1] == 'unban' && confirm(REGISTER_ADMIN_CONFIRM_UNBAN)) {
        query = 'action=unban&id=' + cs;
      } else if (hs[1] == 'delete' && confirm(CONFIRM_DELETE_SELECTED)) {
        query = 'action=delete&id=' + cs;
      } else if (hs[1] == 'published' && confirm(CONFIRM_PUBLISHED)) {
        query = 'action=published&value=' + (hs[5] ? hs[5] : hs[3]) + '&id=' + cs;
      } else if (hs[1] == 'canreply' && confirm(CONFIRM_CAN_REPLY)) {
        query = 'action=canreply&value=' + (hs[5] ? hs[5] : hs[3]) + '&id=' + cs;
      } else if (hs[1] == 'canview' && confirm(CONFIRM_CAN_VIEW)) {
        query = 'action=canview&value=' + (hs[5] ? hs[5] : hs[3]) + '&id=' + cs;
      } else if (hs[1] == 'zone' && confirm(CONFIRM_CHANGE_ZONE)) {
        query = 'action=zone&value=' + hs[3] + '&id=' + cs;
      }
      if (query !== '' && hs[3] && hs[3] != '') {
        query += '&module=' + hs[3];
      }
    }
    if (query !== '') {
      var temp = this;
      temp.action = action;
      temp.query = query;
      send(target, query, function (xhr) {
        if (callback) {
          callback.call(temp, xhr);
        } else if (xhr.responseText !== '') {
          var datas = xhr.responseText.toJSON();
          if (datas) {
            defaultSubmit(datas[0]);
          } else {
            alert(xhr.responseText);
          }
        } else {
          alert(ACTION_COMPLETE);
          setQueryURL('action', action);
        }
      }, input);
    }
  }
}
var select_tr;
function inintTR(id, patt, selId) {
  select_tr = selId;
  function _doHighlight(hl) {
    var c2;
    var h = this.className.replace(' highlight', '').replace(' select', '');
    forEach($E(id).getElementsByTagName('tr'), function () {
      var bg = $G(this).hasClass('bg1 bg2');
      if (bg) {
        c2 = this.className.replace(' highlight', '').replace(' select', '');
        if (c2 == h && hl) {
          this.className = h + ' highlight' + (typeof select_tr == 'object' && select_tr.indexOf(this.id) > -1 ? ' select' : '');
        } else {
          this.className = c2 + (typeof select_tr == 'object' && select_tr.indexOf(this.id) > -1 ? ' select' : '');
        }
      }
    });
  }
  function _checkParent(e, tr) {
    if (e) {
      e = e.parentNode;
      while (e != document.body) {
        if (e.tagName && e.tagName.toLowerCase() == 'table') {
          return false;
        } else if (e == tr) {
          return true;
        } else {
          e = e.parentNode;
        }
      }
    }
    return false;
  }
  var a = 0;
  var old_out = '';
  var old_over = '';
  if (patt == null) {
    var old_bg = '';
    forEach($E(id).getElementsByTagName('tr'), function () {
      var bg = $G(this).hasClass('bg1 bg2');
      if (bg) {
        if (old_bg != bg) {
          old_bg = bg;
          a++;
        }
        this.className = bg + ' H_' + a + (typeof select_tr == 'object' && select_tr.indexOf(this.id) > -1 ? ' select' : '');
        if (this.onmouseover == null) {
          this.onmouseover = function () {
            if (old_over != this.id) {
              old_over = this.id;
              old_out = '';
              _doHighlight.call(this, true);
            }
          };
        }
        if (this.onmouseout == null) {
          this.onmouseout = function (e) {
            var evt = window.event || e;
            var toElement = evt.toElement || evt.relatedTarget;
            try {
              if (_checkParent(toElement, this)) {
                old_out = this.id;
              } else {
                old_out = '';
              }
              old_over = '';
              if (old_out != this.id) {
                _doHighlight.call(this, false);
              }
            } catch (e) {
            }
          };
        }
      }
    });
  } else {
    var bg = 'bg2';
    forEach($E(id).getElementsByTagName('tr'), function () {
      if (patt.exec(this.id)) {
        bg = bg == 'bg2' ? 'bg1' : 'bg2';
        a++;
        $G(this).replaceClass('select row bg1 bg2', (typeof select_tr == 'object' && select_tr.indexOf(this.id) > -1 ? 'select row ' : 'row ') + bg + ' H_' + a);
        if (this.onmouseover == null) {
          this.onmouseover = function () {
            if (old_over != this.id) {
              old_over = this.id;
              old_out = '';
              _doHighlight.call(this, true);
            }
          };
        }
        if (this.onmouseout == null) {
          this.onmouseout = function (e) {
            var evt = window.event || e;
            var toElement = evt.toElement || evt.relatedTarget;
            try {
              if (_checkParent(toElement, this)) {
                old_out = this.id;
              } else {
                old_out = '';
              }
              old_over = '';
              if (old_out != this.id) {
                _doHighlight.call(this, false);
              }
            } catch (e) {
            }
          };
        }
      }
    });
  }
}
function loaddoc(url) {
  if (loader && url != WEB_URL) {
    loader.location(url);
  } else {
    window.location = url;
  }
}
function checkEmail() {
  var value = this.input.value;
  var ids = this.input.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value == '') {
    this.invalid(this.title);
  } else if (INVALID_EMAIL.test(value)) {
    return 'action=email&value=' + encodeURIComponent(value) + id;
  } else {
    this.invalid(REGISTER_INVALID_EMAIL);
  }
}
function checkPhone() {
  var value = this.input.value;
  var ids = this.input.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value != '') {
    return 'action=phone&value=' + encodeURIComponent(value) + id;
  }
}
function checkDisplayname() {
  var value = this.input.value;
  var ids = this.input.id.split('_');
  var id = $E(ids[0] + '_id').value.toInt();
  if (value == '') {
    this.invalid(this.title);
  } else if (value.length < 2) {
    this.invalid(this.title);
  } else {
    return 'action=displayname&value=' + encodeURIComponent(value) + '&id=' + id;
  }
}
function checkPassword() {
  var ids = this.input.id.split('_');
  var id = $E(ids[0] + '_id').value.toInt();
  var Password = $E(ids[0] + '_password');
  var Repassword = $E(ids[0] + '_repassword');
  if (this.input == Password) {
    if (Password.value !== '' && !INVALID_PASSWORD.test(Password.value)) {
      Password.Validator.invalid(Password.Validator.title);
    } else if (Password.value == '' && id == 0) {
      Password.Validator.invalid(Password.Validator.title);
    } else if (Password.value == '' && id > 0) {
      Password.Validator.reset();
    } else if (Password.value.length < 4) {
      Password.Validator.invalid(Password.Validator.title);
    } else {
      Password.Validator.valid();
    }
  } else {
    if (id > 0 && Password.value == '' && Repassword.value == '') {
      Repassword.Validator.reset();
    } else if (Password.value != Repassword.value) {
      Repassword.Validator.invalid(Repassword.Validator.title);
    } else {
      Repassword.Validator.valid();
    }
  }
}
function checkAntispam() {
  var value = this.input.value;
  if (value.length > 3) {
    return 'action=antispam&value=' + value + '&antispam=' + $E('antispam').value;
  } else {
    this.invalid(this.title);
  }
}
function checkIdcard() {
  var value = this.input.value;
  var ids = this.input.id.split('_');
  var id = $E(ids[0] + '_id').value.toInt();
  var i, sum;
  if (value.length != 13) {
    this.invalid(this.title);
  } else {
    for (i = 0, sum = 0; i < 12; i++) {
      sum += parseFloat(value.charAt(i)) * (13 - i);
    }
    if ((11 - sum % 11) % 10 != parseFloat(value.charAt(12))) {
      this.invalid(IDCARD_INVALID);
    } else {
      return 'action=idcard&value=' + encodeURIComponent(value) + '&id=' + id;
    }
  }
}
function checkAlias() {
  var value = this.input.value;
  if (value == '') {
    this.invalid(this.title);
  } else if (value.length < 3) {
    this.invalid(ALIAS_SHORT);
  } else {
    return 'action=alias&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value;
  }
}
function selectChanged(src, action, callback) {
  $G(src).addEvent('change', function () {
    var temp = this;
    var req = new GAjax();
    req.inintLoading('wait', false);
    req.send(action, 'id=' + temp.id + '&value=' + temp.value, function (xhr) {
      if (xhr.responseText !== '') {
        callback.call(temp, xhr);
      }
    });
  });
}
function checkSaved(button, url, write_id, target) {
  callClick(button, function () {
    var id = floatval($E(write_id).value);
    if (id == 0) {
      alert(PLEASE_SAVE_BEFORE);
    } else if (target == '_self') {
      window.location = url.replace('&amp;', '&') + '&id=' + id;
    } else {
      window.open(url.replace('&amp;', '&') + '&id=' + id);
    }
  });
}
function inintSearch(form, input, module) {
  var _callSearch = function (e) {
    var q = $E(input);
    if (q) {
      var v = q.value.trim();
      var l = v.length;
      if (l == 0) {
        alert(SEARCH_EMPTY);
        q.focus();
      } else if (l < 2) {
        alert(SEARCH_SHORT);
        q.focus();
      } else {
        loaddoc('{WEBURL}/index.php?module=' + $E(module).value + '&q=' + encodeURIComponent(v));
      }
    }
    GEvent.stop(e);
    return false;
  };
  $G(form).addEvent('submit', _callSearch);
}
function callModal(id, src, qstr) {
  var doClick = function () {
    showModal(src, qstr);
    return false;
  };
  callClick($E(id), doClick);
}
function callAction(input, action, id, target, callback, customconfirm) {
  callClick(input, function () {
    var a;
    if (Object.isFunction(action)) {
      a = action.call();
    } else {
      a = action;
    }
    doAction(a, id, target, callback, customconfirm, this);
    return false;
  });
}
function _doCheckKey(input, e, patt) {
  var val = input.value;
  var key = GEvent.keyCode(e);
  if (!((key > 36 && key < 41) || key == 8 || key == 9 || key == 13 || GEvent.isCtrlKey(e))) {
    val = String.fromCharCode(key);
    if (val !== '' && !patt.test(val)) {
      GEvent.stop(e);
      return false;
    }
  }
  return true;
}
var numberOnly = function (e) {
  return _doCheckKey(this, e, /[0-9]/);
};
var integerOnly = function (e) {
  return _doCheckKey(this, e, /[0-9\-]/);
};
var currencyOnly = function (e) {
  return _doCheckKey(this, e, /[0-9\.]/);
};
function inputValidate(inp, typ) {
  var input = $G(inp);
  if (typ == 'currency') {
    input.addEvent('keypress', currencyOnly);
  } else if (typ == 'integer') {
    input.addEvent('keypress', integerOnly);
  } else {
    input.addEvent('keypress', numberOnly);
  }
  var _check = function () {
    var val = parseFloat(this.value);
    if (typ == 'currency') {
      if (isNaN(val)) {
        this.value = '0.00';
      } else {
        val -= 0;
        val = (Math.round(val * 100)) / 100;
        val = (val == Math.floor(val)) ? val + '.00' : ((val * 10 == Math.floor(val * 10)) ? val + '0' : val);
        this.value = val;
      }
    } else {
      this.value = isNaN(val) ? 0 : val;
    }
  };
  input.addEvent('blur', _check);
  _check.call(input);
}
function inintInput(id) {
  forEach($E(id).getElementsByTagName('input'), function () {
    var c = this.className;
    if (c == 'currency' || c == 'number' || c == 'integer') {
      inputValidate(this, c);
    }
  });
}
function setSelect(id, value) {
  forEach($E(id).getElementsByTagName('input'), function () {
    if (this.type.toLowerCase() == 'checkbox') {
      this.checked = value;
    }
  });
}
var galleryUploadResult = function (error, count) {
  if (error != "") {
    alert(error);
  }
  if (count > 0) {
    alert(UPLOAD_RESULT.replace("%s", count));
  }
  if (this.location) {
    window.location = this.location;
  } else {
    window.location.reload();
  }
};
function inintGalleryUpload(id, module) {
  var patt = /(deletep)_([0-9]+)_([0-9]+)/;
  var _galleryUploadAction = function () {
    var hs = patt.exec(this.id);
    var action = '';
    if (hs[1] == 'deletep' && confirm(CONFIRM_DELETE)) {
      action = 'action=deletep&id=' + hs[2] + '&aid=' + hs[3];
    }
    if (action != '') {
      send(WEB_URL + 'modules/' + module + '/admin_gallery_action.php', action, doFormSubmit);
    }
    return false;
  };
  forEach($E(id).getElementsByTagName('a'), function () {
    if (patt.test(this.id)) {
      callClick(this, _galleryUploadAction);
    }
  });
}
function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  } else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}
function replaceSelection(input, replaceString) {
  if (input.setSelectionRange) {
    var selectionStart = input.selectionStart;
    var selectionEnd = input.selectionEnd;
    input.value = input.value.substring(0, selectionStart) + replaceString + input.value.substring(selectionEnd);
    if (selectionStart != selectionEnd) {
      setSelectionRange(input, selectionStart, selectionStart + replaceString.length);
    } else {
      setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
    }
  } else if (document.selection) {
    var range = document.selection.createRange();
    if (range.parentElement() == input) {
      var isCollapsed = range.text == '';
      range.text = replaceString;
      if (!isCollapsed) {
        range.moveStart('character', -replaceString.length);
        range.select();
      }
    }
  }
}
function catchTab(evt) {
  if (GEvent.keyCode(evt) == 9) {
    var temp = this;
    var _focus = function () {
      temp.focus();
    };
    replaceSelection(this, String.fromCharCode(9));
    setTimeout(_focus, 0);
    GEvent.stop(evt);
  }
}
function handleFileSelect(e) {
  var input = GEvent.element(e);
  var imgDiv = $E(input.get('data-img'));
  forEach(input.files, function () {
    if (!this.type.match("image.*")) {
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      var img = document.createElement('img');
      img.src = GEvent.element(e).result;
      imgDiv.appendChild(img);
    };
    reader.readAsDataURL(this);
  });
}
function inintFilesUpload(input, div, url) {
  var doDelete = function () {
    if (confirm(CONFIRM_DELETE)) {
      send(url, 'id=' + this.id, doFormSubmit);
    }
    return false;
  };
  input = $G(input);
  input.set('data-img', div);
  input.addEvent('change', handleFileSelect);
  forEach($E(div).getElementsByTagName('span'), function () {
    callClick(this, doDelete);
  });
}
var _scrolltop = 0;
$G(window).Ready(function () {
  if ($E('change_display')) {
    forEach($E('change_display').getElementsByTagName('a'), function () {
      var f = new Array('small', 'normal', 'large');
      if (f.indexOf(this.className) > -1) {
        callClick(this, function () {
          var fontSize = floatval(Cookie.get('fontSize'));
          fontSize = fontSize == 0 ? document.body.getStyle('fontSize').toInt() : fontSize;
          if (this.className == 'small') {
            fontSize = Math.max(6, fontSize - 2);
          } else if (this.className == 'large') {
            fontSize = Math.min(24, fontSize + 2);
          } else {
            fontSize = 12;
          }
          document.body.setStyle('fontSize', fontSize + 'px');
          Cookie.set('fontSize', fontSize);
          return false;
        });
      }
    });
  }
  window.setInterval(function () {
    var c = document.viewport.getscrollTop() > 100;
    if (_scrolltop != c) {
      _scrolltop = c;
      if (c) {
        document.body.addClass('toTop');
      } else {
        document.body.removeClass('toTop');
      }
    }
  }, 500);
});
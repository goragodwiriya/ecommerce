// admin/admin.js
function inintMemberStatus(id) {
  var editor, c, hs,
    patt = /config_status_(delete|name|color|access)_([0-9]+)/;
  function _doAction(c) {
    var q = '';
    if (this.id == id + '_add') {
      q = 'action=' + this.id;
    } else if (hs = patt.exec(this.id)) {
      if (hs[1] == 'delete' && confirm(CONFIRM_DELETE_MEMBER_STATUS)) {
        q = 'action=' + this.id;
      } else if (hs[1] == 'access' && confirm(CONFIRM_CHANGE_ACCESS_STATUS)) {
        q = 'action=' + this.id + '&value=' + this.className;
      } else if (hs[1] == 'color') {
        q = 'action=' + this.id + '&value=' + encodeURIComponent(c);
      }
    }
    if (q != '') {
      send(WEB_URL + 'admin/savestatus.php', q, function (xhr) {
        var ds = xhr.responseText.toJSON();
        if (ds) {
          if (ds[0].data) {
            $G(id).appendChild(decodeURIComponent(ds[0].data).toDOM());
            _doInintStatusMethod(ds[0].newId);
            $E(ds[0].newId.replace('status', 'status_access')).focus();
          } else if (ds[0].del) {
            $G(ds[0].del).remove();
          } else if (ds[0].edit) {
            hs = patt.exec(ds[0].editId);
            if (hs[1] == 'color') {
              c = decodeURIComponent(ds[0].edit);
              $E(ds[0].editId).title = CHANGE_COLOR_STATUS + ' (' + c + ')';
              $E(ds[0].editId.replace('color', 'name')).style.color = c;
            } else if (hs[1] == 'access') {
              $E(ds[0].editId).className = ds[0].edit;
            }
          }
          if (ds[0].error) {
            alert(eval(ds[0].error));
          }
        } else if (xhr.responseText != '') {
          alert(xhr.responseText);
        }
      }, this);
    }
  }
  var o = {
    editTxt: '<input type="text" class="editable" />',
    onSave: function (v) {
      var req = new GAjax({
        asynchronous: false
      });
      req.inintLoading(this, false);
      req.send(WEB_URL + 'admin/savestatus.php', 'action=' + this.id + '&value=' + encodeURIComponent(v));
      req.hideLoading();
      var ds = req.responseText.toJSON();
      if (ds) {
        if (ds[0].edit) {
          $E(ds[0].editId).innerHTML = decodeURIComponent(ds[0].edit);
          return true;
        }
      } else {
        alert(req.responseText);
      }
      return false;
    }
  };
  function _doInintStatusMethod(id) {
    var hs, t, loading = true;
    forEach($G(id).getElementsByTagName('*'), function () {
      hs = patt.exec(this.id);
      if (hs) {
        if (hs[1] == 'delete' || hs[1] == 'access') {
          callClick(this, _doAction);
        } else if (hs[1] == 'color') {
          t = this.title;
          this.title = CHANGE_COLOR_STATUS + ' (' + t + ')';
          new GDDColor(this, function (c) {
            $E(this.input.id.replace('color', 'name')).style.color = c;
            if (!loading) {
              _doAction.call(this.input, c);
            }
          }).setColor(t);
        } else if (hs[1] == 'name') {
          editor = new EditInPlace(this, o);
        }
      }
    });
    loading = false;
  }
  callClick(id + '_add', _doAction);
  _doInintStatusMethod(id);
}
function inintTemplate(id) {
  forEach($E(id).getElementsByTagName('a'), function () {
    if (this.className == 'delete') {
      this.onclick = function () {
        return confirm(CONFIRM_REMOVE_TEMPLATE);
      };
    }
  });
}
function inintList(id, tag, patt, action, callback, onconfirm) {
  forEach($E(id).getElementsByTagName(tag), function () {
    if (patt.test(this.id)) {
      callClick(this, function () {
        if (Object.isNull(onconfirm) || onconfirm.call(this)) {
          var temp = this;
          send(action, 'data=' + this.id, function (xhr) {
            callback.call(temp, xhr);
          });
        }
        return false;
      });
    }
  });
}
var clearCache = function () {
  send(WEB_URL + 'admin/action.php', 'action=clearcache', doFormSubmit, this);
};
function getNews(div) {
  send('getnews.php', null, function (xhr) {
    $E(div).innerHTML = xhr.responseText;
  });
}
function checkCategoryName() {
  var value = this.input.value;
  if (value == '') {
    this.invalid(this.title);
  } else {
    var q = 'action=topic&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value;
    q += '&cat=' + $E('category_id').value + '&mid=' + $E('module_id').value + '&lng=' + $E('category_language').value;
    return q;
  }
}
function checkCategoryID() {
  var value = floatval(this.input.value);
  if (value == 0) {
    this.invalid(this.title);
  } else {
    var q = 'action=categoryid&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value;
    q += '&cat=' + $E('category_id').value + '&mid=' + $E('module_id').value;
    return q;
  }
}
function inintWriteCategory(m) {
  $G(window).Ready(function () {
    new GForm("setup_frm", WEB_URL + "modules/" + m + "/admin_categorywrite_save.php").onsubmit(doFormSubmit);
    new GValidator('category_id', 'keyup,change', checkCategoryID, WEB_URL + "modules/index/admin_category_check.php", null, 'setup_frm');
  });
}
function inintListCategory(module) {
  if (!module) {
    module = 'index';
  }
  var patt = /^categoryid_([0-9]+)_([0-9]+)$/;
  $G(window).Ready(function () {
    inintTR('tbl_category');
    inintCheck('tbl_category');
    callAction("btn_action", function () {
      return $E('sel_action').value
    }, 'tbl_category', WEB_URL + 'modules/' + module + '/admin_category_action.php');
    forEach($E('tbl_category').getElementsByTagName('input'), function () {
      if (patt.test(this.id)) {
        $G(this).addEvent('keypress', numberOnly);
        this.addEvent('change', function () {
          send(WEB_URL + 'modules/' + module + '/admin_category_action.php', 'module=' + this.id + '&value=' + this.value, doFormSubmit, this);
        });
      }
    });
  });
}
function confirmMailDelete() {
  return confirm(CONFIRM_DELETE);
}
function inintLanguage(id) {
  var patt = /(delete)_language_([0-9]+)/;
  var _doAction = function () {
    var hs = patt.exec(this.id);
    if (hs && hs[1] == 'delete' && confirm(CONFIRM_DELETE)) {
      send('language_action.php', 'action=deletelang&id=' + hs[2], doFormSubmit);
    }
    return false;
  };
  forEach($E(id).getElementsByTagName('a'), function () {
    if (patt.test(this.id)) {
      callClick(this, _doAction);
    }
  });
}
function inintPMTable(id, onchanged, prefix) {
  var hs,
    patt = /^unlimited[\s]+(([a-z_]+)[0-9]+)$/,
    patt2 = /^(([a-z_]+)_([a-z]+)_)[0-9]+$/;
  if (prefix == null) {
    prefix = 'M';
  }
  function inintBGTr(id) {
    var row = 0,
      bg = 'bg1';
    forEach($E(id).getElementsByTagName('tr'), function (item, index) {
      if (item.parentNode.tagName.toLowerCase() == 'tbody') {
        bg = bg == 'bg2' ? 'bg1' : 'bg2';
        item.className = bg;
        item.id = prefix + '_' + row;
        forEach(item.getElementsByTagName('input'), function () {
          if (this.id == '') {
            this.id = this.name.replace(/([\[\]_]+)/g, '_') + row;
          } else {
            hs = patt2.exec(this.id);
            if (hs) {
              this.id = hs[1] + row;
            }
          }
          hs = patt.exec(this.className);
          if (hs) {
            this.className = 'unlimited ' + hs[2] + row;
            callClick(this, _doUnlimited);
            _doUnlimited.call(this);
          }
        });
        row++;
      }
    });
  }
  var _doUnlimited = function () {
    var hs = patt.exec(this.className);
    if (hs) {
      var input = $E(hs[1]);
      if (this.checked) {
        if (input.value != -1) {
          input.setAttribute('value', input.value);
        }
        input.value = -1;
        input.disabled = true;
      } else {
        input.value = input.getAttribute('value');
        input.disabled = false;
        input.focus();
      }
    }
  };
  var _doCallClick = function () {
    var c = this.className;
    if (c == 'icon-plus') {
      var tr = $G(this.parentNode.parentNode.parentNode);
      var tbody = tr.parentNode;
      var ntr = tr.copy(false);
      tr.after(ntr);
      if (Object.isFunction(onchanged)) {
        onchanged(tbody);
      }
      inintBGTr(tbody);
      inintPMTable(ntr, onchanged, prefix);
      ntr.highlight();
      forEach(ntr.getElementsByTagName('input'), function () {
        hs = patt2.exec(this.id);
        if (hs && hs[3] == 'id') {
          this.value = 0;
        }
      });
      ntr = ntr.getElementsByTagName('input')[0];
      ntr.focus();
      ntr.select();
    } else if (c == 'icon-minus') {
      var tr = $G(this.parentNode.parentNode.parentNode);
      if (tr.parentNode.getElementsByTagName('tr').length > 1 && confirm(CONFIRM_DELETE)) {
        var tmp = tr.parentNode;
        tr.remove();
        inintBGTr(tmp);
      }
    }
  };
  forEach($E(id).getElementsByTagName('*'), function () {
    var c = this.className;
    if (c == 'icon-plus' || c == 'icon-minus') {
      callClick(this, _doCallClick);
    } else if (c == 'currency' || c == 'number' || c == 'integer') {
      inputValidate(this, c);
    }
  });
  if (Object.isFunction(onchanged)) {
    onchanged(id);
  }
  inintBGTr(id);
}
function inintLanguages(id) {
  var patt = /^(edit|delete|check)_([a-z]{2,2})$/;
  var doClick = function () {
    var hs = patt.exec(this.id);
    var q = '';
    if (hs[1] == 'check') {
      q = this.className == 'icon-uncheck' ? 'icon-check' : 'icon-uncheck';
      this.className = q;
      q = 'action=changed&lang=' + hs[2] + '&val=' + q;
    } else if (hs[1] == 'delete' && confirm(CONFIRM_DELETE)) {
      q = 'action=droplang&lang=' + hs[2];
    }
    if (q != '') {
      send('language_action.php', q, doFormSubmit, this);
    }
  };
  forEach($E(id).getElementsByTagName('span'), function () {
    if (patt.test(this.id)) {
      callClick(this, doClick);
    }
  });
}
function getUpdate() {
  send('getupdate.php', null, function (xhr) {
    if (xhr.responseText != '') {
      var div = document.createElement('aside');
      div.innerHTML = xhr.responseText;
      div.className = 'message';
      $E('skip').insertBefore(div, $E('skip').getElementsByTagName('section')[0]);
    }
  });
}
function callInstall(id, module) {
  callClick(id, function () {
    send(WEB_URL + 'admin/installing.php', 'module=' + module, function (xhr) {
      $E('install').innerHTML = xhr.responseText;
    });
  });
}
function inintModuleCategory(id, mid, module) {
  var patt = /^[a-z0-9]+$/;
  if (patt.test(module)) {
    module = WEB_URL + 'modules/' + module + '/admin_category_action.php';
  }
  function _doAction(c) {
    var q = '';
    if (this.id == id + '_add') {
      q = 'action=' + this.id + '&mid=' + mid;
    } else {
      var hs = patt.exec(this.id);
      if (hs[2] == 'delete' && confirm(CONFIRM_DELETE)) {
        q = 'action=' + this.id + '&mid=' + mid;
      }
    }
    if (q != '') {
      send(module, q, function (xhr) {
        var ds = xhr.responseText.toJSON();
        if (ds) {
          if (ds[0].data) {
            $G(id).appendChild(decodeURIComponent(ds[0].data).toDOM());
            _doInint(ds[0].newId);
          } else if (ds[0].del) {
            $G(ds[0].del).remove();
          }
        } else if (xhr.responseText != '') {
          alert(xhr.responseText);
        }
      }, this);
    }
  }
  var o = {
    editTxt: '<input type="text" class="editable" />',
    onSave: function (v) {
      var req = new GAjax({
        asynchronous: false
      });
      req.inintLoading(this, false);
      req.send(module, 'action=' + this.id + '&value=' + encodeURIComponent(v) + '&mid=' + mid);
      req.hideLoading();
      var ds = req.responseText.toJSON();
      if (ds) {
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
  function _doInint(id) {
    forEach($G(id).getElementsByTagName('*'), function () {
      var hs = patt.exec(this.id);
      if (hs) {
        if (hs[2] == 'delete') {
          callClick(this, _doAction);
        } else if (hs[2] == 'name') {
          editor = new EditInPlace(this, o);
        }
      }
    });
  }
  var hs = id.split('_');
  patt = new RegExp('config_(' + hs[hs.length - 1] + ')_(delete|name)_([0-9]+)');
  callClick(id + '_add', _doAction);
  _doInint(id);
}
var doMenuClick = function () {
  $E('wait').className = 'show';
};
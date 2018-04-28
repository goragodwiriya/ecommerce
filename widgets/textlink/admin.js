// widgets/textlink/admin.js
function inintTextlinkWrite() {
  new GForm('setup_frm', WEB_URL + 'widgets/textlink/admin_write_save.php').onsubmit(doFormSubmit);
  callClick('textlink_dateless', function () {
    $E('textlink_publish_end').disabled = this.checked ? true : false;
  });
  var _stylesChanged = function () {
    $E('textlink_template').disabled = this.value != 'custom';
    send(WEB_URL + 'widgets/textlink/admin_action.php', 'action=styles&val=' + this.value + '&id=' + $E('textlink_id').value, function (xhr) {
      $E('textlink_template').value = xhr.responseText;
      doTextlinkDemo();
    }, $E('textlink_prefix'));
  };
  $G('textlink_type').addEvent('change', _stylesChanged);
  _stylesChanged.call($E('textlink_type'));
  var doTextlinkDemo = function () {
    $E('textlink_demo').innerHTML = '{WIDGET_TEXTLINK_' + $E('textlink_type').value + $E('textlink_prefix').value + '}';
  };
  $G('textlink_prefix').addEvent('keyup', doTextlinkDemo);
  $G('textlink_prefix').addEvent('change', doTextlinkDemo);
}
function doInintTextlink(id) {
  inintCheck(id);
  inintTR(id, /user\-[0-9]+/);
  var req = new GAjax();
  function _send(src, q) {
    var _class = src.className;
    src.className = 'icon-loading';
    req.send(WEB_URL + 'widgets/textlink/admin_action.php', q, function (xhr) {
      src.className = _class;
      if (xhr.responseText != '') {
        alert(xhr.responseText);
      } else {
        inintTR(id, /user\-[0-9]+/);
      }
    });
  }
  new GSortTable(id, {
    endDrag: function () {
      var trs = new Array();
      forEach($E(id).getElementsByTagName('tr'), function () {
        if (this.id) {
          trs.push(this.id);
        }
      });
      if (trs.length > 1) {
        _send($E(this.id.replace('user-', 'move_')), 'action=move&data=' + trs.join(','));
      }
    }
  });
}
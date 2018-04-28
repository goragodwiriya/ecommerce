// modules/index/setup.js
function inintIndexPages(id, sort) {
	var as, hs, ds, patt = /(published|delete|move)_([a-z]+)_([0-9]+)/;
	var req = new GAjax();
	function _send(src, q) {
		var _class = src.className;
		src.className = 'icon-loading';
		req.send(WEB_URL + 'modules/index/admin_action.php', q, function(xhr) {
			src.className = _class;
			ds = xhr.responseText.toJSON();
			if (ds) {
				var toplv = -1;
				for (prop in ds[0]) {
					if (prop == 'delete_id') {
						$G('M_' + ds[0][prop]).remove();
					} else if (prop == 'error') {
						alert(eval(ds[0][prop]));
					} else if (prop == 'published') {
						as = decodeURIComponent(ds[0][prop]).split('|');
						var el = $E('published_' + as[0]);
						el.className = 'icon-published' + as[1];
						el.title = decodeURIComponent(as[2]);
					} else {
						as = decodeURIComponent(ds[0][prop]).split('|');
						$E(prop).innerHTML = as[0];
						$E('move_left_' + as[2]).className = (as[1] == 0 ? 'hidden' : 'icon-move_left');
						$E('move_right_' + as[2]).className = (as[1] > toplv ? 'hidden' : 'icon-move_right');
						toplv = as[1];
					}
				}
			} else if (xhr.responseText != '') {
				alert(xhr.responseText);
			}
		});
	}
	if (sort) {
		new GSortTable(id, {
			'endDrag': function() {
				var trs = new Array();
				forEach($E(id).getElementsByTagName('tr'), function() {
					if (this.id) {
						trs.push(this.id);
					}
				});
				if (trs.length > 1) {
					_send($E(this.id.replace('M_', 'move_')), 'action=move&data=' + trs.join(','));
				}
			}
		});
	}
	var _doclick = function(e) {
		GEvent.stop(e);
		hs = patt.exec(this.id);
		if (hs[1] == 'delete') {
			if (confirm(CONFIRM_DELETE)) {
				_send(this, 'action=delete&t=' + hs[2] + '&id=' + hs[3]);
			}
		} else if (hs[1] == 'move') {
			_send(this, 'action=' + hs[2] + '&id=' + hs[3]);
		} else if (hs[1] == 'published') {
			_send(this, 'action=published&t=' + hs[2] + '&id=' + hs[3]);
		}
	};
	forEach($E(id).getElementsByTagName('a'), function() {
		hs = patt.exec(this.id);
		if (hs) {
			callClick(this, _doclick);
		}
	});
}
function inintMenu() {
	var getMenus = function() {
		var t = $E('write_type').value;
		var sel = $E('write_order');
		for (var i = sel.options.length - 1; i >= 0; i--) {
			sel.removeChild(sel.options[i]);
		}
		var q = 'action=get&parent=' + $E('write_parent').value + '&id=' + floatval($E('write_id').value);
		send(WEB_URL + 'modules/index/admin_action.php', q, function(xhr) {
			var id = floatval($E('write_id').value);
			var option = sel.options[0];
			var ds = xhr.responseText.toJSON();
			if (ds) {
				for (prop in ds[0]) {
					q = prop.replace('O_', '');
					if (prop == 'parent') {
						el = $G('write_parent');
						if (ds[0][prop] == '') {
							el.addClass('valid');
							el.removeClass('invalid');
							el.hideTooltip();
						} else {
							el.addClass('invalid');
							el.removeClass('valid');
							el.showTooltip(eval(ds[0][prop]));
						}
					} else if (id > 0 && q == id) {
						if (option) {
							option.selected = 'selected';
						}
					} else if (t > 0) {
						option = document.createElement('option');
						option.value = q;
						option.innerHTML = decodeURIComponent(ds[0][prop]);
						sel.appendChild(option);
					}
				}
			} else if (xhr.responseText != '') {
				alert(xhr.responseText);
			}
		});
	};
	var menuAction = function() {
		var c = $E('write_action').value;
		forEach($E('menu_action').getElementsByTagName('div'), function() {
			if ($G(this).hasClass('action')) {
				if ($G(this).hasClass(c)) {
					this.removeClass('hidden');
				} else {
					this.addClass('hidden');
				}
			}
		});
	};
	new GForm("setup_frm", WEB_URL + "modules/index/admin_menu_save.php").onsubmit(doFormSubmit);
	$G("copy_menu").addEvent("click", doIndexCopy);
	$G('write_action').addEvent('change', menuAction);
	$G('write_parent').addEvent('change', getMenus);
	$G('write_type').addEvent('change', getMenus);
	getMenus.call(this);
	menuAction();
}
function checkIndexModule() {
	var value = this.input.value;
	var patt = /^[a-z0-9]{1,}$/;
	if (value == '') {
		this.invalid(this.title);
	} else if (!patt.test(value)) {
		this.invalid(EN_NUMBER_ONLY);
	} else {
		return 'action=module&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value + '&lng=' + $E('write_language').value;
	}
}
function checkIndexTopic() {
	var value = this.input.value;
	if (value == '') {
		this.invalid(this.title);
	} else if (value.length < 3) {
		this.invalid(TITLE_SHORT);
	} else {
		return 'action=topic&val=' + encodeURIComponent(value) + '&id=' + $E('write_id').value + '&lng=' + $E('write_language').value;
	}
}
var indexPreview = function() {
	var id = $E("write_id").value.toInt();
	if (id > 0) {
		window.open(WEB_URL + 'index.php?module=' + $E("write_owner").value + '&mid=' + id, 'preview');
	}
};
var doIndexCopy = function() {
	var lng = $E('write_language').value;
	var id = $E('write_id').value.toInt();
	if (id > 0 && lng !== '') {
		send(WEB_URL + 'modules/index/admin_copy.php', 'id=' + id + '&lng=' + lng + '&action=' + this.id, function(xhr) {
			var datas = xhr.responseText.toJSON();
			if (datas) {
				if (datas[0].error) {
					alert(eval(datas[0].error));
				}
				if (datas[0].location) {
					window.location = datas[0].location;
				}
			} else if (xhr.responseText != '') {
				alert(xhr.responseText);
			}
		});
	}
};
function inintIndexWrite() {
	new GForm("write_frm", WEB_URL + "modules/index/admin_write_save.php").onsubmit(doFormSubmit);
	$G("write_open").addEvent("click", indexPreview);
	var module = new GValidator("write_module", "keyup,change", checkIndexModule, WEB_URL + "modules/index/admin_check.php", null, "write_frm");
	var topic = new GValidator("write_topic", "keyup,change", checkIndexTopic, WEB_URL + "modules/index/admin_check.php", null, "write_frm");
	$G("write_language").addEvent("change", function() {
		if (topic.input.value != '') {
			topic.validate();
		}
		if (module.input.value != '') {
			module.validate();
		}
	});
	$G("btn_copy").addEvent("click", doIndexCopy);
}
// modules/document/script.js
var G_editor = null;
function inintDocumentEditor(frm, editor, action) {
	$G(window).Ready(function() {
		if ($E(editor)) {
			G_editor = editor;
			new GForm(frm, action, null, false).onsubmit(doFormSubmit);
		}
	});
}
function inintDocumentView(id, module) {
	$G(window).Ready(function() {
		var patt = /(quote|edit|delete|pin|lock|print|pdf)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/;
		var _viewAction = function(action) {
			var temp = this;
			send(WEB_URL + 'modules/' + module + '/action.php', action, function(xhr) {
				var ds = xhr.responseText.toJSON();
				if (ds) {
					if (ds[0].action == 'quote') {
						var editor = $E(G_editor);
						if (editor && ds[0].detail !== '') {
							editor.value = editor.value + decodeURIComponent(ds[0].detail);
							editor.focus();
						}
					} else if ((ds[0].action == 'pin' || ds[0].action == 'lock') && $E(module + '_' + ds[0].action)) {
						var a = $E(module + '_' + ds[0].action);
						a.className = a.className.replace(/(un)?(pin|lock)\s/, (ds[0].value == 0 ? 'un' : '') + '$2 ');
						a.title = ds[0].title;
					}
					if (ds[0].error) {
						alert(eval(ds[0].error));
					}
					if (ds[0].confirm) {
						if (confirm(eval(ds[0].confirm))) {
							if (ds[0].action == 'deleting') {
								_viewAction.call(temp, 'id=' + temp.className.replace('delete-', 'deleting-'));
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
			}, this);
		};
		var _doExport = function(action) {
			var hs = patt.exec(action);
			window.open(WEB_URL + 'print.php?action=' + hs[1] + '&id=' + hs[2] + '&module=' + hs[5], 'print');
		};
		$G(window).Ready(function() {
			if (G_Lightbox === null) {
				G_Lightbox = new GLightbox();
			} else {
				G_Lightbox.clear();
			}
			forEach($E(id).getElementsByTagName('*'), function(item, index) {
				if (patt.exec(item.className)) {
					callClick(item, function() {
						var hs = patt.exec(this.className);
						if (hs[1] == 'print' || hs[1] == 'pdf') {
							_doExport(this.className);
						} else {
							_viewAction.call(this, 'id=' + this.className);
						}
					});
				} else if (item.tagName.toLowerCase() == 'img' && !$G(item).hasClass('nozoom')) {
					new preload(item, function() {
						if (floatval(this.width) > floatval(item.width)) {
							G_Lightbox.add(item);
						}
					});
				}
			});
		});
	});
}
function inintDocumentMember(id) {
	forEach($E(id).getElementsByTagName('a'), function() {
		if ($G(this).hasClass('delete')) {
			callClick(this, function() {
				if (confirm(CONFIRM_DELETE_DOCUMENT)) {
					send(WEB_URL + 'modules/document/action.php', 'id=' + this.id, function(xhr) {
						var ds = xhr.responseText.toJSON();
						if (ds) {
							if (ds[0].remove && $E(ds[0].remove)) {
								$G(ds[0].remove).remove();
							}
						} else if (xhr.responseText != '') {
							alert(xhr.responseText);
						}
					});
					return false;
				}
			});
		}
	});
}
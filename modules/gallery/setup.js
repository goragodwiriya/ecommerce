// modules/gallery/setup.js
function inintGalleryUpload(id) {
	var patt = /^(preview|edit|delete)_([0-9]+)(_([0-9]+))?$/;
	if (G_Lightbox === null) {
		G_Lightbox = new GLightbox();
	} else {
		G_Lightbox.clear();
	}
	var _doDelete = function() {
		var cs = new Array();
		forEach($E(id).getElementsByTagName('a'), function() {
			if (this.className == 'icon-check') {
				var hs = patt.exec(this.id);
				cs.push(hs[2]);
			}
		});
		if (cs.length == 0) {
			alert(PLEASE_SELECT_ONE);
		} else if (confirm(CONFIRM_DELETE_SELECTED)) {
			send(WEB_URL + 'modules/gallery/admin_action.php', 'action=deletep&album=' + $E('album_id').value + '&id=' + cs.join(','), doFormSubmit, this);
		}
	};
	var _galleryUploadAction = function() {
		var hs = patt.exec(this.id);
		var action = '';
		if (hs[1] == 'delete') {
			this.className = this.className == 'icon-check' ? 'icon-uncheck' : 'icon-check';
		} else if (hs[1] == 'edit') {
			showModal(WEB_URL + 'modules/gallery/admin_action.php', 'action=edit&id=' + hs[2]);
		}
		if (action != '') {
			send(WEB_URL + 'modules/gallery/admin_action.php', action, doFormSubmit, this);
		}
		return false;
	};
	forEach($E(id).getElementsByTagName('a'), function() {
		var hs = patt.exec(this.id);
		if (hs) {
			if (hs[1] == 'preview') {
				G_Lightbox.add(this);
			} else {
				callClick(this, _galleryUploadAction);
			}
		}
	});
	callClick('btnDelete', _doDelete);
}
function firstItem(id) {
	var items = $E(id).getElementsByTagName('div');
	return items[0];
}
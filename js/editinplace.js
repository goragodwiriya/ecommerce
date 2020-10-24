/*
 EditInPlace
 design by goragod.com
 24-11-54
 */
var EditInPlace = GClass.create();
EditInPlace.prototype = {
	initialize: function(e, o) {
		this.editTxt = '<input type="text" />';
		this.className = 'editinplace';
		this.canEdit = function() {
			return true;
		};
		for (var p in o) {
			this[p] = o[p];
		}
		this.src = $G(e);
		this.src.style.cursor = 'pointer';
		this.src.tabIndex = 0;
		this.src.addClass(this.className);
		this.src.addEvent('click', this.Edit.bind(this));
		var self = this;
		this.src.addEvent('keydown', function(e) {
			var key = GEvent.keyCode(e);
			if (key == 13 || key == 32) {
				self.Edit.call(self);
				GEvent.stop(e);
				return false;
			}
			return true;
		});
	},
	Edit: function() {
		if (this.canEdit.call(this.src)) {
			var e = this.editTxt.toDOM().firstChild;
			this.src.parentNode.insertBefore(e, this.src);
			var v = this.src.value ? this.src.value : this.src.innerHTML;
			this.editor = $G(e);
			this.editor.addEvent('blur', this._saveEdit.bind(this));
			this.editor.addEvent('keypress', this._checkKey.bind(this));
			this.editor.addEvent('keydown', this._checkKey.bind(this));
			this.oldDisplay = this.src.style.display;
			this.src.style.display = 'none';
			this.editor.setValue(encodeURIComponent(v));
			this.editor.focus();
			this.editor.select();
		}
		return this;
	},
	select: function() {
		this.editor.select();
	},
	cancleEdit: function() {
		this.src.style.display = this.oldDisplay;
		this.editor.removeEvent('blur', this._saveEdit.bind(this));
		this.editor.removeEvent('keypress', this._checkKey.bind(this));
		this.editor.removeEvent('keydown', this._checkKey.bind(this));
		this.editor.remove();
		this.src.focus();
		return this;
	},
	_saveEdit: function() {
		var ret = true,
			v = this.editor.value ? this.editor.value : this.editor.innerHTML;
		if (Object.isFunction(this.onSave)) {
			ret = this.onSave.call(this.src, v);
		} else {
			this.src.setValue(v);
		}
		if (ret) {
			this.cancleEdit();
		}
	},
	_checkKey: function(e) {
		var key = GEvent.keyCode(e);
		if (key == 27) {
			this.cancleEdit();
			GEvent.stop(e);
			return false;
		} else if (key == 13) {
			if (this.editor.tagName.toLowerCase() != 'textarea') {
				this._saveEdit();
			}
			GEvent.stop(e);
			return false;
		}
		return true;
	}
};
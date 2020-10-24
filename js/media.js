/*
 GMedia swf player
 design by goragod.com
 */
var GMedia = function(name, src, width, height) {
	var c = src.toLowerCase();
	this.id = name || '';
	this.src = src;
	this.width = floatval(width);
	this.height = floatval(height);
	this.params = new Object();
	this.properties = new Object();
	if (c.indexOf('.swf') > -1) {
		this.addProperty('classid', 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000');
		this.addParam('allowscriptaccess', 'always');
		this.addParam('allowfullscreen', 'false');
		this.addParam('quality', 'high');
		this.addParam('wmode', 'transparent');
		this._getPlayer = (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) ? this._getEmbed : this._getObject;
	} else if (c.indexOf('.avi') > -1 || c.indexOf('.wmv') > -1) {
		this.addParam('type', 'application/x-mplayer2');
		this.addParam('pluginspage', 'http://www.microsoft.com/Windows/MediaPlayer/');
		this._getPlayer = this._getEmbed;
	} else {
		this._getPlayer = this._getEmbed;
	}
};
GMedia.prototype = {
	addParam: function(param, value) {
		this.params[param.toLowerCase()] = value;
		return this;
	},
	_getParams: function() {
		return this.params;
	},
	addProperty: function(prop, value) {
		this.properties[prop.toLowerCase()] = value;
		return this;
	},
	_getProperties: function() {
		return this.properties;
	},
	write: function(id) {
		if ($E(id)) {
			this.player = $G(id);
			var size = this.player.getDimensions();
			if (size.width > 0 && size.height > 0) {
				var h = this.width / this.height;
				this.width = Math.min(size.width, this.width);
				this.height = this.width / h;
			}
			this.player.innerHTML = this._getPlayer();
		}
		return this;
	},
	_getEmbed: function() {
		var a = '<embed type="application/x-shockwave-flash" id="' + this.id + '" src="' + this.src + '" width="' + this.width + '" height="' + this.height + '" ';
		var b = this._getParams();
		for (var c in b) {
			a += c + '="' + b[c] + '" ';
		}
		var d = this._getProperties();
		for (var c in d) {
			a += c + '="' + d[c] + '" ';
		}
		a += '/>';
		return a;
	},
	_getObject: function() {
		var a = '<object id="' + this.id + '" width="' + this.width + '" height="' + this.height + '" ';
		var b = this._getProperties();
		for (var c in b) {
			a += ' ' + c + '="' + b[c] + '"';
		}
		a += '>';
		a += '<param name="movie" value="' + this.src + '" />';
		var d = this._getParams();
		for (var c in d) {
			a += '<param name="' + c + '" value="' + d[c] + '" />';
		}
		a += '</object>';
		return a;
	}
};
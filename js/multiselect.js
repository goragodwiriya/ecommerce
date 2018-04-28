/*
 gMultiSelect
 ajax multi select
 design by http://www.goragod.com (Goragod Wiriya)
 5-03-56
 */
var gMultiSelect = GClass.create();
gMultiSelect.prototype = {
	initialize: function(selects, options) {
		var loading = true;
		this.selects = new Object();
		this.req = new GAjax();
		var self = this;
		var _dochanged = function() {
			var a = false;
			var temp = this;
			if (!loading && this.selectedIndex == 0) {
				loading = false;
				forEach(selects, function(item, index) {
					if (a) {
						var obj = self.selects[item];
						for (var i = obj.options.length - 1; i > 0; i--) {
							obj.removeChild(obj.options[i]);
						}
					}
					a = !a && item == temp.id ? true : a;
				});
			} else {
				var qs = new Array();
				qs.push('srcItem=' + this.id);
				for (var prop in options) {
					if (prop != 'action') {
						qs.push(prop + '=' + options[prop]);
					}
				}
				for (var sel in self.selects) {
					var select = self.selects[sel];
					qs.push(select.id + '=' + encodeURIComponent(select.value));
				}
				self.req.send(options.action, qs.join('&'), function(xhr) {
					var itemsNode = xhr.responseXML.getElementsByTagName('items')[0];
					forEach(itemsNode.childNodes, function() {
						var select = self.selects[this.tagName];
						var items = this.getElementsByTagName('*');
						self.populate(select, items, select.value);
					});
				});
			}
		};
		var l = selects.length - 1;
		forEach(selects, function(item, index) {
			var select = $G(item);
			if (index < l) {
				select.addEvent('change', _dochanged);
			}
			self.selects[item] = select;
		});
		_dochanged.call($E(selects[0]));
	},
	inintLoading: function(loading, center) {
		this.req.inintLoading(loading, center);
		return this;
	},
	populate: function(obj, items, select) {
		for (var i = obj.options.length - 1; i > 0; i--) {
			obj.removeChild(obj.options[i]);
		}
		var selectedIndex = 0;
		if (items) {
			for (var i = 0; i < items.length; i++) {
				var key = items[i].tagName.replace('a', '');
				selectedIndex = key == select ? i + 1 : selectedIndex;
				var option = document.createElement('option');
				option.innerHTML = items[i].firstChild.data;
				option.value = key;
				obj.appendChild(option);
			}
		}
		obj.selectedIndex = selectedIndex;
		obj.options[0].value = '';
	}
}
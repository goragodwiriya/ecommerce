// widgets/rss/admin.js
var doRSSSubmit = function(xhr) {
	var prop, val, el, tag;
	var datas = xhr.responseText.toJSON();
	if (datas) {
		for (prop in datas[0]) {
			val = datas[0][prop];
			if (prop == 'error') {
				alert(eval(val));
			} else if (prop == 'alert') {
				alert(decodeURIComponent(val));
			} else if (prop == 'input') {
				$G(val).highlight().focus();
			} else if (prop == 'reset') {
				rssReset();
			} else if (prop == 'content') {
				var tbody = $E('member').getElementsByTagName('tbody')[0];
				tbody.appendChild(decodeURIComponent(val).toDOM());
				inintCheck('member');
				inintTR('member', /L_[0-9]+/);
				inintList("member", "a", /edit_[0-9]+/, WEB_URL + 'widgets/rss/admin_action.php', doFormSubmit);
				rssReset();
				$G('L_' + datas[0].id).highlight();
			} else if (prop == 'topic') {
				var tr = $E('L_' + datas[0].id);
				if (tr) {
					var tds = tr.getElementsByTagName('td');
					var as = tr.getElementsByTagName('a');
					as[0].innerHTML = decodeURIComponent(datas[0].url);
					as[0].href = decodeURIComponent(datas[0].url);
					tds[1].innerHTML = decodeURIComponent(datas[0].topic);
					tds[3].innerHTML = decodeURIComponent(datas[0].index);
					tds[4].innerHTML = decodeURIComponent(datas[0].display);
					tr.highlight();
				}
				rssReset();
			} else if ($E(prop)) {
				$E(prop).value = decodeURIComponent(val);
				if (prop == 'rss_index') {
					rssIndexChanged.call($E('rss_index'));
				}
			}
		}
	} else if (xhr.responseText != '') {
		alert(xhr.responseText);
	}
};
var rssReset = function(e) {
	$E('rss_id').value = 0;
	$E('rss_topic').value = '';
	$E('rss_index').value = '';
	$E('rss_url').value = '';
	rssIndexChanged.call($E('rss_index'));
	$E('rss_url').focus();
};
var rssIndexChanged = function(e) {
	var n = this.value.toInt();
	$E('rss_index_result').innerHTML = '{WIDGET_RSS' + (n == 0 ? '' : '_' + n) + '}';
};
function doInintRSSSetup(id) {
	new GSortTable(id, {
		endDrag: function() {
			var trs = new Array();
			forEach($E(id).getElementsByTagName('tr'), function() {
				if (this.id) {
					trs.push(this.id);
				}
			});
			if (trs.length > 1) {
				send(WEB_URL + 'widgets/rss/admin_action.php', 'action=move&data=' + encodeURIComponent(trs.join(',')), function(xhr) {
					if (xhr.responseText != '') {
						alert(xhr.responseText);
					} else {
						inintTR('member', /L_[0-9]+/);
					}
				});
			}
		}
	});
}
// widgets/calendar/script.js
var inintCalendar = function(id, action) {
	var query = null;
	if (!action) {
		send(WEB_URL + 'widgets/calendar/get.php', query, function(xhr) {
			$G('widget-calendar').setHTML(xhr.responseText);
			inintCalendar('widget-calendar', true);
		});
	} else {
		var patt = /^calendar\-(([0-9]+){0,2}\-([0-9]+){0,2}\-([0-9]+){0,4})\-([0-9a-z_]+)$/;
		forEach($E(id).getElementsByTagName('a'), function(item) {
			var hs = patt.exec(item.id);
			if (hs) {
				if (hs[5] == 'back' || hs[5] == 'next' || hs[5] == 'today') {
					item.onclick = function() {
						hs = patt.exec(item.id);
						if (hs[5] == 'back') {
							query = 'module=calendar-' + hs[1] + '&n=-1';
						} else if (hs[5] == 'next') {
							query = 'module=calendar-' + hs[1] + '&n=+1';
						}
						send(WEB_URL + 'widgets/calendar/get.php', query, function(xhr) {
							$G('widget-calendar').setHTML(xhr.responseText);
							inintCalendar('widget-calendar', true);
						});
						return false;
					};
				} else {
					item.onclick = function() {
						loaddoc(this.href);
						return false;
					};
					item.onmousemove = function() {
						mTooltipShow(this.id, WEB_URL + 'widgets/calendar/tooltip.php', this);
					};
				}
			}
		});
	}
};

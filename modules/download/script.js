// modules/download/script.js
var download_time = 0;
function inintDownloadList(id) {
	var hs, patt = /download-([0-9]+)/;
	forEach($E(id).getElementsByTagName('a'), function() {
		if (patt.test(this.id)) {
			callClick(this, doDownloadClick);
		}
	});
}
var doDownloadClick = function() {
	var req = new GAjax({
		asynchronous : false
	});
	req.send(WEB_URL + 'modules/download/download.php', 'action=download&id=' + this.id);
	var ds = req.responseText.toJSON();
	if (ds) {
		if (ds[0].confirm) {
			if (confirm(eval(ds[0].confirm))) {
				req.send(WEB_URL + 'modules/download/download.php', 'action=downloading&id=' + this.id);
				ds = req.responseText.toJSON();
				if (ds[0].id) {
					this.href = decodeURIComponent(ds[0].href);
					return true;
				}
			}
		}
		if (ds[0].error) {
			alert(eval(ds[0].error));
		}
		if (ds[0].downloads) {
			$E('downloads_' + ds[0].id).innerHTML = ds[0].downloads;
		}
	} else if (req.responseText != '') {
		alert(req.responseText);
	}
	return false;
};
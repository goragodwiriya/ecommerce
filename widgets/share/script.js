// widgets/share/script.js
var share_patt = /(fb|gplus|twitter|email)_share/;
var doShare = function(e) {
	GEvent.stop(e);
	var u = this.getAttribute('data-url');
	var t = this.getAttribute('data-title');
	if (u == null || u == '') {
		u = encodeURIComponent(getCurrentURL());
	}
	if (t == null || t == '') {
		t = encodeURIComponent(window.document.title);
	}
	var hs = share_patt.exec(this.className);
	if (hs[1] == 'fb') {
		window.open('http://www.facebook.com/sharer.php?u=' + u + '&t=' + t, 'sharer', 'toolbar=0,status=0,width=626,height=436');
	} else if (hs[1] == 'email') {
		showModal(WEB_URL + 'widgets/share/email.php', 'u=' + u + '&t=' + t);
	} else if (hs[1] == 'gplus') {
		window.open('https://plus.google.com/share?url=' + u + '&t=' + t, 'sharer', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
	} else if (hs[1] == 'twitter') {
		window.open('http://www.twitter.com/share?url=' + u + '&text=' + t, 'sharer', 'toolbar=0,status=0,width=626,height=436');
	}
};
function inintShareButton(id) {
	forEach($E(id).getElementsByTagName('*'), function() {
		if (share_patt.test(this.className)) {
			callClick(this, doShare);
		}
	});
}
/*
	RSSGal
	display RSS Gallery from http://gallery.gcms.in.th
	for GCMS 4.0.0
	design by http://www.goragod.com (goragod wiriya)
	8-11-53
*/
RSSGal = GClass.create();
RSSGal.prototype = {
	initialize: function(options) {
		this.options = {
			rows: 3,
			cols: 2,
			imageWidth: 75,
			className: 'table_rss_gallery_class',
			reader: WEB_URL + 'widgets/gallery/reader.php',
			feedurl: 'http://gallery.gcms.in.th/gallery.rss',
			tags: '',
			album: 0,
			user: 0
		};
		Object.extend(this.options,options || { });
	},

	show: function(div){
		var query = 'url=' + encodeURIComponent(this.options.feedurl);
		query += '&rows=' + this.options.rows;
		query += '&cols=' + this.options.cols;
		query += '&imageWidth=' + this.options.imageWidth;
		query += '&className=' + this.options.className;
		query += '&tags=' + this.options.tags;
		query += '&album=' + this.options.album;
		query += '&user=' + this.options.user;
		var _callback = function(xhr){
			$G(div).setHTML(xhr.responseText);
		};
		new GAjax().send(this.options.reader, query, _callback);
	}
};
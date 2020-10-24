/*
 multiples upload class
 copy right @ http://www.goragod.com
 6-11-2557
 */
gUploads = GClass.create();
gUploads.prototype = {
	initialize: function(options) {
		this.options = {
			form: '',
			input: '',
			fileprogress: '',
			fileext: ['jpg', 'gif', 'png'],
			iconpath: WEB_URL + 'skin/ext/',
			onupload: emptyFunction,
			oncomplete: emptyFunction,
			customSettings: {}
		};
		Object.extend(this.options, options || { });
		this.form = $G(this.options.form);
		this.frmContainer = document.createElement('div');
		this.frmContainer.style.display = 'none';
		this.form.appendChild(this.frmContainer);
		this.index = 0;
		this.count = 0;
		var input = $G(this.options.input);
		this.prefix = input.get('id');
		this.parent = $G(input.parentNode);
		this.size = input.get('size');
		this.className = input.className;
		this.name = input.get('name');
		this.multiple = window.FormData ? true : false;
		input.multiple = this.multiple;
		this.result = $E(this.options.fileprogress);
		var temp = this;
		var _doUploadChanged = function(e) {
			var index = 0, total = 0;
			var doProgress = function(val) {
				$E('bar_' + temp.prefix + '_' + index).style.width = val + '%';
			};
			var xhr = new GAjax({
				onProgress: doProgress,
				contentType: null
			});
			function _upload(files, i) {
				if (temp.uploading && i < files.length) {
					index = i;
					if ($E('p_' + temp.prefix + '_' + index)) {
						var f = files[i];
						var data = new FormData();
						for (var name in temp.options.customSettings) {
							data.append(name, encodeURIComponent(temp.options.customSettings[name]));
						}
						data.append('file', f);
						$G('close_' + temp.prefix + '_' + index).remove();
						xhr.send(temp.form.action, data, function(xhr) {
							var ds = xhr.responseText.toJSON();
							if (ds) {
								if (ds[0].error) {
									val = eval(ds[0].error);
									$G('result_' + temp.prefix + '_' + index).addClass('invalid').innerHTML = val;
									temp.error.push(val);
								}
							} else if (xhr.responseText != '') {
								$G('result_' + temp.prefix + '_' + index).addClass('invalid').innerHTML = xhr.responseText;
								temp.error.push(xhr.responseText);
							} else {
								$G('p_' + temp.prefix + '_' + index).remove();
								total++;
							}
							_upload(files, index + 1);
						});
					} else {
						_upload(files, index + 1);
					}
				} else {
					temp.options.oncomplete.call(temp, temp.error.join('\n'), total);
				}
			}
			if (temp.multiple) {
				forEach(this.files, function() {
					var file = temp._ext(this.name);
					if (temp.options.fileext.indexOf(file.ext) != -1) {
						temp._display(file);
						temp.count++;
						temp.index++;
					}
				});
				temp.uploading = true;
				temp.options.onupload.call(temp);
				_upload(this.files, 0);
			} else {
				var file = temp._ext(this.value);
				if (temp.options.fileext.indexOf(file.ext) != -1) {
					temp._display(file);
					var form = document.createElement('form');
					form.id = 'form_' + temp.prefix + '_' + temp.index;
					form.action = temp.form.action;
					temp.frmContainer.appendChild(form);
					this.removeEvent('change', _doUploadChanged);
					form.appendChild(this);
					for (var name in temp.options.customSettings) {
						$G(form).create('input', {
							'name': name,
							'value': encodeURIComponent(temp.options.customSettings[name]),
							'type': 'hidden'
						});
					}
					temp.count++;
					temp.index++;
					var _input = $G(temp.parent).create('input', {
						'id': temp.prefix + temp.index,
						'name': temp.name,
						'class': temp.className,
						'type': 'file'
					});
					_input.addEvent('change', _doUploadChanged);
				} else {
					alert(INVALID_FILE_TYPE);
				}
			}
		};
		input.addEvent('change', _doUploadChanged);
		this.uploading = false;
		this.error = new Array();
		total = 0;
		var _submit = function(forms, index) {
			var id = forms[index].id;
			var form = $E(id);
			var result = $E(id.replace('form_', 'result_'));
			$G(id.replace('form_', 'close_')).remove();
			result.className = 'icon-loading';
			var frm = new GForm(id);
			frm.result = result;
			frm.submit(function(xhr) {
				var ds = xhr.responseText.toJSON();
				if (ds) {
					if (ds[0].error) {
						val = eval(ds[0].error);
						frm.result.innerHTML = val;
						frm.result.className = 'icon-invalid';
						temp.error.push(val);
					}
				} else if (xhr.responseText != '') {
					frm.result.innerHTML = xhr.responseText;
					frm.result.className = 'icon-invalid';
					temp.error.push(xhr.responseText);
				} else {
					frm.result.className = 'icon-valid';
					total++;
				}
				index++;
				if (index < forms.length && temp.uploading) {
					_submit.call(temp, forms, index);
				} else {
					temp.options.oncomplete.call(temp, temp.error.join('\n'), total);
				}
			});
		};
		this.form.addEvent('submit', function(e) {
			GEvent.stop(e);
			if (!temp.uploading && temp.index > 0) {
				temp.uploading = true;
				$E(temp.prefix + temp.index).disabled = 'disabled';
				temp.options.onupload.call(temp);
				total = 0;
				_submit(temp.frmContainer.getElementsByTagName('form'), 0);
			}
		});
	},
	cancle: function() {
		this.uploading = false;
	},
	_ext: function(name) {
		var obj = new Object;
		var files = name.replace(/\\/g, '/').split('/');
		obj.name = files[files.length - 1];
		var exts = obj.name.split('.');
		obj.ext = exts[exts.length - 1].toLowerCase();
		return obj;
	},
	_display: function(file) {
		var p = document.createElement('p');
		this.result.appendChild(p);
		p.id = 'p_' + this.prefix + '_' + this.index;
		var img = document.createElement('img');
		img.src = this._getIcon(file.ext);
		p.appendChild(img);
		var span = document.createElement('span');
		span.innerHTML = file.name;
		p.appendChild(span);
		var a = document.createElement('a');
		a.className = 'icon-delete';
		a.id = 'close_' + this.prefix + '_' + this.index;
		p.appendChild(a);
		var temp = this;
		callClick(a, function() {
			temp._remove(this.id.replace('close_' + temp.prefix + '_', ''));
		});
		var span = document.createElement('span');
		p.appendChild(span);
		span.id = 'result_' + this.prefix + '_' + this.index;
		if (this.multiple) {
			var bar = document.createElement('span');
			bar.className = 'bar_graphs';
			p.appendChild(bar);
			var span = document.createElement('span');
			span.className = 'value_graphs';
			span.id = 'bar_' + this.prefix + '_' + this.index;
			bar.appendChild(span);
		}
	},
	_getIcon: function(ext) {
		var icons = new Array('file', 'aiff', 'avi', 'bmp', 'c', 'cpp', 'css', 'dll', 'doc', 'docx', 'exe', 'flv', 'gif', 'htm', 'html', 'iso', 'jpeg', 'jpg', 'js', 'midi', 'mov', 'mp3', 'mpg', 'ogg', 'pdf', 'php', 'png', 'ppt', 'pptx', 'psd', 'rar', 'rm', 'rtf', 'sql', 'swf', 'tar', 'tgz', 'tiff', 'txt', 'wav', 'wma', 'wmv', 'xls', 'xml', 'xvid', 'zip');
		var i = icons.indexOf(ext);
		i = i > 0 ? i : 0;
		return this.options.iconpath + icons[i] + '.png';
	},
	_remove: function(index) {
		$G('p_' + this.prefix + '_' + index).remove();
		$G('form_' + this.prefix + '_' + index).remove();
		this.count--;
	}
};

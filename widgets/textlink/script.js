/**
 @name GProductSlide
 @description คลาสสำหรับการแสดงรูปภาพแบบไสลด์โชว์
 @author http://www.goragod.com (goragod wiriya)
 @version 08-10-60

 @param string className คลาสของ GProductSlide ค่าเริ่มต้นคือ gproductslide
 @param string buttonContainerClass ค่าเริ่มต้นคือ button_container_gproductslide
 @param string buttonClass ค่าเริ่มต้นคือ button_gproductslide
 @param ing slideTime เวลาในการเปลี่ยนไสลด์อัตโนมัติ (msec) ค่าเริ่มต้นคือ 10000,
 @param boolean showButton แสดงปุ่มกดเลือกรูปภาพหรือไม่ ค่าเริ่มต้นคือ true,
 @param boolean showNumber แสดงตัวเลขในปุ่มกดเลือกรูปภาพหรือไม่ ค่าเริ่มต้นคือ false,
 @param boolean loop true (ค่าเริ่มต้น) วนแสดงรูปไปเรื่อยๆ
 */
var GProductSlide = GClass.create();
GProductSlide.prototype = {
  initialize: function (div, options) {
    this.options = {
      className: 'gproductslide',
      buttonContainerClass: 'button_container_gproductslide',
      buttonClass: 'button_gproductslide',
      slideTime: 10000,
      showButton: true,
      showNumber: false,
      loop: true
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.container = $G(div);
    this.container.addClass(this.options.className);
    this.container.style.overflow = 'hidden';
    var tmp = this;
    this.next = this.container.create('span');
    this.next.className = 'hidden';
    this.next.title = 'Next';
    callClick(this.next, function () {
      window.clearTimeout(tmp.SlideTime);
      tmp._nextSlide();
    });
    this.prev = this.container.create('span');
    this.prev.className = 'hidden';
    this.prev.title = 'Prev';
    callClick(this.prev, function () {
      window.clearTimeout(tmp.SlideTime);
      tmp._prevSlide();
    });
    this.buttons = this.container.create('p');
    this.buttons.className = 'hidden';
    this.buttons.style.zIndex = 2;
    this.button = $G(this.buttons.create('p'));
    this.button.className = this.options.buttonClass;
    this.datas = new Array();
    forEach(this.container.getElementsByTagName('figure'), function () {
      tmp._initItem(this);
    });
    this.currentId = -1;
  },
  add: function (picture, url) {
    var a, figure = document.createElement('figure');
    this.container.appendChild(figure);
    if (url != '') {
      var a = document.createElement('a');
      a.href = url;
      figure.appendChild(a);
    } else {
      a = figure;
    }
    var img = document.createElement('img');
    img.src = picture;
    img.className = 'nozoom';
    a.appendChild(img);
    this._initItem(figure);
    return this;
  },
  JSONData: function (data) {
    try {
      for (var i = 0; i < data.length; i++) {
        this.add(data[i].picture, data[i].url);
      }
    } catch (e) {
    }
    return this;
  },
  _initItem: function (obj) {
    var i = this.datas.length;
    this.datas.push($G(obj));
    obj.style.display = i == 0 ? 'block' : 'none';
    var a = $G(this.button.create('a'));
    a.rel = i;
    var span = a.create('span');
    span.className = 'preview';
    span.style.backgroundImage = 'url(' + obj.firstChild.src + ')';
    if (this.options.showNumber) {
      a.appendChild(document.createTextNode(i + 1));
    }
    this.buttons.className = (this.options.showButton && i > 0) ? this.options.buttonContainerClass : 'hidden';
    var tmp = this;
    callClick(a, function () {
      window.clearTimeout(tmp.SlideTime);
      tmp._show(this.rel);
    });
  },
  _prevSlide: function () {
    if (this.datas.length > 0) {
      var next = this.currentId - 1;
      if (next < 0 && this.options.loop) {
        next = this.datas.length - 1;
      }
      this._playIng(next);
    }
  },
  _nextSlide: function () {
    if (this.datas.length > 0) {
      var next = this.currentId + 1;
      if (next >= this.datas.length && this.options.loop) {
        next = 0;
      }
      this._playIng(next);
    }
  },
  _playIng: function (id) {
    if ($E(this.container.id)) {
      this._show(id);
      if (this.datas.length > 1) {
        var temp = this;
        this.SlideTime = window.setTimeout(function () {
          temp.playSlideShow.call(temp);
        }, this.options.slideTime);
      }
    }
  },
  playSlideShow: function () {
    this._nextSlide();
    return this;
  },
  _show: function (id) {
    if (this.datas[id]) {
      var temp = this;
      forEach(this.datas, function (item, index) {
        if (id == index) {
          item.style.display = 'block';
          item.style.zIndex = 1;
        } else if (temp.currentId == index) {
          item.style.display = 'list-item';
          item.style.zIndex = 0;
        } else {
          item.style.display = 'none';
          item.style.zIndex = 0;
        }
      });
      this.datas[id].addClass('fadein');
      temp._setButton(id);
      temp.currentId = id;
      window.setTimeout(function () {
        temp.datas[id].removeClass('fadein');
      }, 1000);
    }
  },
  _setButton: function (id) {
    forEach(this.button.getElementsByTagName('a'), function () {
      this.className = this.rel == id ? 'current' : '';
    });
    this.prev.className = id == 0 ? 'hidden' : 'btnnav prev';
    this.next.className = id == this.datas.length - 1 ? 'hidden' : 'btnnav next';
  }
};

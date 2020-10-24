/*
 GAJAX
 Javascript Libraly And Ajax Frame Work
 design by http://www.goragod.com (Goragod Wiriya)
 23-10-57
 */
var domloaded = false;
var emptyFunction = function () {
};
var resultFunction = function () {
  return true;
};
var ajaxAccepts = {
  xml: "application/xml, text/xml",
  html: "text/html",
  text: "text/plain",
  json: "application/json, text/javascript",
  all: "text/html, text/plain, application/xml, text/xml, application/json, text/javascript"
};
var GBrowser = {
  IE: !!(window.attachEvent && !window.opera),
  Opera: !!window.opera,
  WebKit: navigator.userAgent.indexOf('AppleWebKit/') > -1,
  Gecko: navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1,
  MobileSafari: !!navigator.userAgent.match(/Apple.*Mobile.*Safari/)
};
function sleep(ms) {
  var start = new Date().getTime();
  while (new Date().getTime() < start + ms) {
  }
}
function floatval(val) {
  var res = parseFloat(val);
  return isNaN(res) ? 0 : res;
}
function CurrencyFormatted(amount) {
  amount = ((floatval(amount) + 0.005) * 100 / 100);
  var s = new String(amount);
  return s.substr(0, s.indexOf('.') + 3);
}
function strToDate(date) {
  var patt = /(([0-9]{4,4})-([0-9]{1,2})-([0-9]{1,2})|today|tomorrow|yesterday)([\s]{0,}([+-])[\s]{0,}([0-9]+))?/;
  var hs = patt.exec(date);
  if (hs) {
    if (hs[2] == null || hs[2] == '') {
      d = new Date();
    } else {
      d = new Date(parseFloat(hs[2]), parseFloat(hs[3]) - 1, hs[4], 0, 0, 0, 0);
    }
    if (hs[1] == 'yesterday') {
      d.setDate(d.getDate() - 1);
    } else if (hs[1] == 'tomorrow') {
      d.setDate(d.getDate() + 1);
    }
    if (hs[6] == '+' && parseFloat(hs[7]) > 0) {
      d.setDate(d.getDate() + parseFloat(hs[7]));
    } else if (hs[6] == '-' && parseFloat(hs[7]) > 0) {
      d.setDate(d.getDate() - parseFloat(hs[7]));
    }
    return d;
  } else {
    return null;
  }
}
function mktimeToDate(mktime) {
  return new Date(mktime * 1000);
}
Date.prototype.dateFormat = function (format) {
  var result = "";
  for (var i = 0; i < format.length; i++) {
    result += this.dateToString(format.charAt(i));
  }
  return result;
};
Date.prototype.dateToString = function (character) {
  switch (character) {
    case "d":
      return this.getDate();
    case "D":
      return Date.dayNames[this.getDay()];
    case "y":
      return this.getFullYear().toString();
    case "Y":
      return (this.getFullYear() + Date.yearOffset).toString();
    case "m":
      return this.getMonth() + 1;
    case "M":
      return Date.monthNames[this.getMonth()];
    case "H":
      return this.getHours().toString().leftPad(2, '0');
    case "h":
      return this.getHours();
    case "A":
      return this.getHours() < 12 ? 'AM' : 'PM';
    case "a":
      return this.getHours() < 12 ? 'am' : 'pm';
    case "I":
      return this.getMinutes().toString().leftPad(2, '0');
    case "i":
      return this.getMinutes();
    case "S":
      return this.getSeconds().toString().leftPad(2, '0');
    case "s":
      return this.getSeconds();
    default:
      return character;
  }
};
Date.prototype.tomktime = function () {
  return Math.floor(this.getTime() / 1000);
};
Date.prototype.moveDate = function (value) {
  this.setDate(this.getDate() + value);
  return this;
};
Date.prototype.moveMonth = function (value) {
  this.setMonth(this.getMonth() + value);
  return this;
};
Date.prototype.moveYear = function (value) {
  this.setFullYear(this.getFullYear() + value);
  return this;
};
Date.prototype.isLeapYear = function () {
  var year = this.getFullYear();
  return ((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
};
Date.prototype.daysInMonth = function () {
  var arr = Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
  arr[1] = this.isLeapYear() ? 29 : 28;
  return arr[this.getMonth()];
};
Date.prototype.dayOfWeek = function () {
  var a = parseInt((14 - this.getMonth()) / 12);
  var y = this.getFullYear() - a;
  var m = this.getMonth() + 12 * a - 2;
  var d = (this.getDate() + y + parseInt(y / 4) - parseInt(y / 100) + parseInt(y / 400) + parseInt((31 * m) / 12)) % 7;
  return d;
};
Date.prototype.compare = function (d) {
  var date, month, year;
  if (Object.isString(d)) {
    var ds = d.split('-');
    year = ds[0].toInt();
    month = ds[1].toInt() - 1;
    date = ds[2].toInt();
  } else {
    date = d.getDate();
    month = d.getMonth();
    year = d.getFullYear();
  }
  var dateStr = this.getDate();
  var monthStr = this.getMonth();
  var yearStr = this.getFullYear();
  var theYear = yearStr - year;
  var theMonth = monthStr - month;
  var theDate = dateStr - date;
  var days = '';
  if (monthStr == 0 || monthStr == 2 || monthStr == 4 || monthStr == 6 || monthStr == 7 || monthStr == 9 || monthStr == 11) {
    days = 31;
  }
  if (monthStr == 3 || monthStr == 5 || monthStr == 8 || monthStr == 10) {
    days = 30;
  }
  if (monthStr == 1) {
    days = 28;
  }
  var inYears = theYear;
  var inMonths = theMonth;
  if (month < monthStr && date > dateStr) {
    inYears = parseFloat(inYears) + 1;
    inMonths = theMonth - 1;
  }
  if (month < monthStr && date <= dateStr) {
    inMonths = theMonth;
  } else if (month == monthStr && (date < dateStr || date == dateStr)) {
    inMonths = 0;
  } else if (month == monthStr && date > dateStr) {
    inMonths = 11;
  } else if (month > monthStr && date <= dateStr) {
    inYears = inYears - 1;
    inMonths = ((12 - -(theMonth)) + 1);
  } else if (month > monthStr && date > dateStr) {
    inMonths = ((12 - -(theMonth)));
  }
  var inDays = theDate;
  if (date > dateStr) {
    inYears = inYears - 1;
    inDays = days - (-(theDate));
  } else if (date == dateStr) {
    inDays = 0;
  }
  var result = ['day', 'month', 'year'];
  result.day = inDays;
  result.month = inMonths;
  result.year = inYears;
  return result;
};
Date.monthNames = ["Jan.", "Feb.", "Mar.", "Apr.", "May.", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec."];
Date.longMonthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
Date.longDayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
Date.dayNames = ["Su.", "Mo.", "We.", "Tu.", "Th.", "Fr.", "Sa."];
Date.yearOffset = 0;
Object.extend = function (d, s) {
  for (var property in s) {
    d[property] = s[property];
  }
  return d;
};
Object.extend(Object, {
  isObject: function (o) {
    return typeof o == "object";
  },
  isFunction: function (o) {
    return typeof o == "function";
  },
  isString: function (o) {
    return typeof o == "string";
  },
  isNumber: function (o) {
    return typeof o == "number";
  },
  isNull: function (o) {
    return typeof o == "undefined";
  },
  isGElement: function (o) {
    return o != null && typeof o == "object" && 'Ready' in o && 'element' in o;
  }
});
var GClass = {
  create: function () {
    return function () {
      this.initialize.apply(this, arguments);
    };
  }
};
var GNative = GClass.create();
GNative.prototype = {
  initialize: function () {
    this.elem = null;
  },
  Ready: function (f) {
    var s = this;
    var p = function () {
      if (domloaded && s.element()) {
        f.call($G(s.elem));
      } else {
        window.setTimeout(p, 10);
      }
    };
    p();
  },
  after: function (e) {
    var p = this.parentNode;
    if (p.firstChild == this || this.nextSibling == null) {
      p.appendChild(e);
    } else {
      p.insertBefore(e, this.nextSibling);
    }
    return e;
  },
  before: function (e) {
    var p = this.parentNode;
    if (p.firstChild == this) {
      p.appendChild(e);
    } else {
      p.insertBefore(e, this);
    }
    return e;
  },
  insert: function (e) {
    e = $G(e);
    this.appendChild(e);
    return e;
  },
  copy: function (o) {
    return $G(this.cloneNode(o || true));
  },
  replace: function (e) {
    var p = this.parentNode;
    p.insertBefore(e, this.nextSibling);
    p.removeChild(this);
    return $G(e);
  },
  remove: function () {
    if (this.element()) {
      this.parentNode.removeChild(this);
    }
  },
  setHTML: function (o) {
    try {
      this.innerHTML = o;
    } catch (e) {
      o = o.replace(/[\r\n\t]/g, '').replace(/<script[^>]*>.*?<\/script>/ig, '');
      this.appendChild(o.toDOM());
    }
  },
  getTop: function () {
    return this.viewportOffset().top;
  },
  getLeft: function () {
    return this.viewportOffset().left;
  },
  getWidth: function () {
    return this.getDimensions().width;
  },
  getHeight: function () {
    return this.getDimensions().height;
  },
  getClientWidth: function () {
    return this.clientWidth - parseInt(this.getStyle('paddingLeft')) - parseInt(this.getStyle('paddingRight'));
  },
  getClientHeight: function () {
    return this.clientHeight - parseInt(this.getStyle('paddingTop')) - parseInt(this.getStyle('paddingBottom'));
  },
  viewportOffset: function () {
    var t = this.offsetTop;
    var l = this.offsetLeft;
    var p = this.offsetParent;
    while (p !== null) {
      t += p.offsetTop;
      l += p.offsetLeft;
      if (p.offsetParent == document.body && p.style.position == 'absolute') {
        break;
      }
      p = p.offsetParent;
    }
    var result = [l, t];
    result.left = l;
    result.top = t;
    return result;
  },
  getDimensions: function () {
    var ow, oh;
    if (this == document) {
      ow = Math.max(Math.max(document.body.scrollWidth, document.documentElement.scrollWidth), Math.max(document.body.offsetWidth, document.documentElement.offsetWidth), Math.max(document.body.clientWidth, document.documentElement.clientWidth));
      oh = Math.max(Math.max(document.body.scrollHeight, document.documentElement.scrollHeight), Math.max(document.body.offsetHeight, document.documentElement.offsetHeight), Math.max(document.body.clientHeight, document.documentElement.clientHeight));
    } else {
      var d = this.getStyle('display');
      if (d != 'none' && d !== null) {
        ow = this.offsetWidth;
        oh = this.offsetHeight;
      } else {
        var s = this.style;
        var ov = s.visibility;
        var op = s.position;
        var od = s.display;
        s.visibility = 'hidden';
        s.position = 'absolute';
        s.display = 'block';
        ow = this.clientWidth;
        oh = this.clientHeight;
        s.display = od;
        s.position = op;
        s.visibility = ov;
      }
    }
    var result = [ow, oh];
    result.width = ow;
    result.height = oh;
    return result;
  },
  getOffsetParent: function () {
    var e = this.offsetParent;
    if (!e) {
      e = this.parentNode;
      while (e != document.body && e.style.position == 'static') {
        e = e.parentNode;
      }
    }
    return GElement(e);
  },
  getStyle: function (s) {
    s = (s == 'float' && this.currentStyle) ? 'styleFloat' : s;
    s = (s == 'borderColor') ? 'borderBottomColor' : s;
    var v = (this.currentStyle) ? this.currentStyle[s] : null;
    v = (!v && window.getComputedStyle) ? document.defaultView.getComputedStyle(this, null).getPropertyValue(s.replace(/([A-Z])/g, "-$1").toLowerCase()) : v;
    if (s == 'opacity') {
      return Object.isNull(v) ? 100 : (parseFloat(v) * 100);
    } else {
      return v == 'auto' ? null : v;
    }
  },
  setStyle: function (p, v) {
    if (p == 'opacity') {
      if (window.ActiveXObject) {
        this.style.filter = "alpha(opacity=" + (v * 100) + ")";
      }
      this.style.opacity = v;
    } else if (p == 'float' || p == 'styleFloat' || p == 'cssFloat') {
      if (Object.isNull(this.style.styleFloat)) {
        this.style['cssFloat'] = v;
      } else {
        this.style['styleFloat'] = v;
      }
    } else if (p == 'backgroundColor' && this.tagName.toLowerCase() == 'iframe') {
      if (document.all) {
        this.contentWindow.document.bgColor = v;
      } else {
        this.style.backgroundColor = v;
      }
    } else if (p == 'borderColor') {
      this.style.borderLeftColor = v;
      this.style.borderTopColor = v;
      this.style.borderRightColor = v;
      this.style.borderBottomColor = v;
    } else {
      this.style[p] = v;
    }
    return this;
  },
  center: function () {
    var size = this.getDimensions();
    if (this.style.position == 'fixed') {
      this.style.top = ((document.viewport.getHeight() - size.height) / 2) + 'px';
      this.style.left = ((document.viewport.getWidth() - size.width) / 2) + 'px';
    } else {
      this.style.top = (document.viewport.getscrollTop() + ((document.viewport.getHeight() - size.height) / 2)) + 'px';
      this.style.left = (document.viewport.getscrollLeft() + ((document.viewport.getWidth() - size.width) / 2)) + 'px';
    }
    return this;
  },
  get: function (p) {
    try {
      return this.getAttribute(p);
    } catch (e) {
      return null;
    }
  },
  set: function (p, v) {
    try {
      this.setAttribute(p, v);
    } catch (e) {
    }
    return this;
  },
  hasClass: function (v) {
    var vs = v.split(' ');
    var cs = this.className.split(' ');
    for (var c = 0; c < cs.length; c++) {
      for (v = 0; v < vs.length; v++) {
        if (vs[v] != '' && vs[v] == cs[c]) {
          return vs[v];
        }
      }
    }
    return false;
  },
  addClass: function (v) {
    if (!v) {
      this.className = '';
    } else {
      var rm = v.split(' ');
      var cs = new Array();
      forEach(this.className.split(' '), function (c) {
        if (c !== '' && rm.indexOf(c) == -1) {
          cs.push(c);
        }
      });
      cs.push(v);
      this.className = cs.join(' ');
    }
    return this;
  },
  removeClass: function (v) {
    if (!Object.isNull(this.className)) {
      var rm = v.split(' ');
      var cs = new Array();
      forEach(this.className.split(' '), function (c) {
        if (c !== '' && rm.indexOf(c) == -1) {
          cs.push(c);
        }
      });
      this.className = cs.join(' ');
    }
    return this;
  },
  replaceClass: function (source, replace) {
    if (!Object.isNull(this.className)) {
      var rm = (replace + ' ' + source).split(' ');
      var cs = new Array();
      forEach(this.className.split(' '), function (c) {
        if (c !== '' && rm.indexOf(c) == -1) {
          cs.push(c);
        }
      });
      cs.push(replace);
      this.className = cs.join(' ');
    }
    return this;
  },
  hide: function () {
    this.display = this.getStyle('display');
    this.setStyle('display', 'none');
    return this;
  },
  show: function () {
    if (this.getStyle('display') == 'none') {
      this.setStyle('display', 'block');
    }
    return this;
  },
  visible: function () {
    return this.getStyle('display') != 'none';
  },
  toggle: function () {
    if (this.visible()) {
      this.hide();
    } else {
      this.show();
    }
    return this;
  },
  nextNode: function () {
    var n = this;
    do {
      n = n.nextSibling;
    } while (n && n.nodeType != 1);
    return n;
  },
  previousNode: function () {
    var p = this;
    do {
      p = p.previousSibling;
    } while (p && p.nodeType != 1);
    return p;
  },
  firstNode: function () {
    var p = this.firstChild;
    do {
      p = p.nextSibling;
    } while (p && p.nodeType != 1);
    return p;
  },
  callEvent: function (t) {
    var evt;
    if (document.createEvent) {
      evt = document.createEvent('Events');
      evt.initEvent(t, true, true);
      this.dispatchEvent(evt);
    } else if (document.createEventObject) {
      evt = document.createEventObject();
      this.fireEvent('on' + t, evt);
    }
    return this;
  },
  addEvent: function (t, f, c) {
    if (this.addEventListener) {
      c = !c ? false : c;
      this.addEventListener(t, f, c);
    } else if (this.attachEvent) {
      var tmp = this;
      tmp["e" + t + f] = f;
      tmp[t + f] = function () {
        tmp["e" + t + f](window.event);
      };
      tmp.attachEvent("on" + t, tmp[t + f]);
    }
    return this;
  },
  removeEvent: function (t, f) {
    if (this.removeEventListener) {
      this.removeEventListener(((t == 'mousewheel' && window.gecko) ? 'DOMMouseScroll' : t), f, false);
    } else if (this.detachEvent) {
      var tmp = this;
      tmp.detachEvent("on" + t, tmp[t + f]);
      tmp["e" + t + f] = null;
      tmp[t + f] = null;
    }
    return this;
  },
  highlight: function (o) {
    if (!this._highlight) {
      this._highlight = new GHighlight(this);
    }
    this._highlight.play(o);
    return this;
  },
  fadeTo: function (v, o) {
    if (!this._fade) {
      this._fade = new GFade(this);
    }
    this._fade.play({
      'from': this.getStyle('opacity'),
      'to': v,
      'onComplete': o || emptyFunction
    });
    return this;
  },
  fadeIn: function (o) {
    this.fadeTo(100, o);
    return this;
  },
  fadeOut: function (o) {
    this.fadeTo(0, o);
    return this;
  },
  shake: function () {
    var p = [15, 30, 15, 0, -15, -30, -15, 0, 15, 30, 15, 0, -15, -30, -15, 0];
    var o = this.style.position;
    this.style.position = 'relative';
    var m = this;
    function s(a) {
      if (a < p.length) {
        m.style.left = p[a] + 'px';
        a++;
        setTimeout(function () {
          s(a);
        }, 20);
      } else {
        m.style.position = o;
      }
    }
    s(0);
    return this;
  },
  load: function (u, o, c) {
    var s = {
      cache: true
    };
    for (var p in o) {
      s[p] = o[p];
    }
    var req = new GAjax(s);
    var d = u.split('?');
    var s = this;
    req.send(d[0], d[1], function (x) {
      s.setValue(x.responseText);
      if (c) {
        c.call(s, x);
      }
    });
    return this;
  },
  setValue: function (v) {
    function _find(e, a) {
      var s = e.getElementsByTagName('option');
      for (var i = 0; i < s.length; i++) {
        if (s[i].value == a) {
          return i;
        }
      }
      return -1;
    }
    v = decodeURIComponent(v);
    var t = this.tagName.toLowerCase();
    if (t == 'img') {
      this.src = v;
    } else if (t == 'select') {
      this.selectedIndex = _find(this, v);
    } else if (t == 'input') {
      if (this.type == 'checkbox' || this.type == 'radio') {
        this.checked = (parseFloat(v) == 1);
      } else {
        this.value = v.unentityify();
      }
    } else if (t == 'textarea') {
      this.value = v.unentityify();
    } else {
      this.setHTML(v);
    }
    return this;
  },
  element: function () {
    return Object.isString(this.elem) ? document.getElementById(this.elem) : this.elem;
  },
  create: function (em, o) {
    var v;
    if (em == 'iframe' || em == 'input') {
      var n = o.name || o.id || '';
      var i = o.id || o.name || '';
      if (window.ActiveXObject) {
        try {
          if (em == 'iframe') {
            v = document.createElement('<iframe id="' + i + '" name="' + n + '" scrolling="no" />');
          } else {
            v = document.createElement('<input id="' + i + '" name="' + n + '" type="' + o.type + '" />');
          }
        } catch (e) {
          v = document.createElement(em);
          v.name = n;
          v.id = i;
        }
      } else {
        v = document.createElement(em);
        v.name = n;
        v.id = i;
      }
    } else {
      v = document.createElement(em);
    }
    if (this.elem) {
      this.appendChild(v);
    }
    for (var p in o) {
      v[p] = o[p];
    }
    return $G(v);
  },
  hideTooltip: function () {
    if (this.tooltip) {
      this.tooltip.hide();
      this.tooltipShow = false;
    }
    return this;
  },
  showTooltip: function (value) {
    if (!this.tooltip) {
      this.tooltip = new GTooltip({
        id: 'GElelment_Tooltip_' + this.id,
        opacity: 70,
        autohide: false
      });
      var self = this;
      this.addEvent('blur', function () {
        self.tooltip.hide();
        self.tooltipShow = true;
      });
      this.addEvent('focus', function () {
        if (self.tooltipShow) {
          self.tooltip.show(this, self.tooltip.value);
        }
      });
    }
    this.tooltip.show(this, value);
    return this;
  },
  valid: function (className) {
    if (this.ret) {
      if (this.ret.hasClass('validationResult')) {
        this.ret.remove();
        this.ret = false;
      } else {
        this.ret.replaceClass('invalid', 'valid');
        this.ret.innerHTML = this.retDef ? this.retDef : '';
      }
    }
    this.replaceClass('invalid wait', 'valid' + (className ? ' ' + className : ''));
    return this;
  },
  invalid: function (value, className) {
    if (!this.ret) {
      if (typeof this.result === 'string' && this.result !== '' && $E(this.result)) {
        this.ret = $G(this.result);
      } else {
        var id = this.id || this.name;
        if ($E('result_' + id)) {
          this.ret = $G('result_' + id);
        }
      }
      if (this.ret && !this.retDef) {
        this.retDef = this.ret.innerHTML;
      }
    }
    if (this.ret) {
      this.ret.innerHTML = value;
      this.ret.replaceClass('valid', 'invalid' + (className ? ' ' + className : ''));
    }
    this.replaceClass('valid wait', 'invalid');
    return this;
  },
  reset: function () {
    if (this.ret) {
      if (this.ret.hasClass('validationResult')) {
        this.ret.remove();
        this.ret = false;
      } else {
        this.ret.replaceClass('invalid valid', '');
        this.ret.innerHTML = this.retDef ? this.retDef : '';
      }
    }
    this.replaceClass('invalid valid wait required', '');
    return this;
  },
  inint: function (e) {
    this.elem = e;
    var elem = this.element();
    if (!elem) {
      return this;
    } else {
      this.elem = elem;
      Object.extend(elem, this);
      return elem;
    }
  }
};
var GElement = new GNative();
function $G(e) {
  return Object.isGElement(e) ? e : GElement.inint(e);
}
function $E(e) {
  e = Object.isString(e) ? document.getElementById(e) : e;
  return Object.isObject(e) ? e : null;
}
document.viewport = {
  getWidth: function () {
    return document.documentElement.clientWidth || document.body.clientWidth || self.innerWidth;
  },
  getHeight: function () {
    return document.documentElement.clientHeight || document.body.clientHeight || self.innerHeight;
  },
  getscrollTop: function () {
    return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
  },
  getscrollLeft: function () {
    return window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;
  }
};
function forEach(a, f) {
  var i, l = a.length, x = new Array();
  for (i = 0; i < l; i++) {
    x.push(a[i]);
  }
  for (i = 0; i < l; i++) {
    if (f.call(x[i], x[i], i) == true) {
      break;
    }
  }
}
Function.prototype.bind = function (o) {
  var __method = this;
  return function () {
    return __method.apply(o, arguments);
  };
};
function functionReady(f, o) {
  var _p = function () {
    if (domloaded && typeof f != "undefined") {
      o.apply();
    } else {
      window.setTimeout(_p, 10);
    }
  };
  _p();
}
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function (t, i) {
    i || (i = 0);
    var l = this.length;
    if (i < 0) {
      i = l + i;
    }
    for (; i < l; i++) {
      if (this[i] == t) {
        return i;
      }
    }
    return -1;
  };
}
var GAjax = GClass.create();
GAjax.prototype = {
  initialize: function (options) {
    this.options = {
      method: 'post',
      cache: false,
      asynchronous: true,
      contentType: 'application/x-www-form-urlencoded',
      encoding: 'UTF-8',
      Accept: 'all',
      onTimeout: emptyFunction,
      onError: emptyFunction,
      onProgress: emptyFunction,
      timeout: 0,
      loadingClass: 'wait'
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.options.method = this.options.method.toLowerCase();
    this.loader = null;
  },
  xhr: function () {
    var xmlHttp = null;
    try {
      xmlHttp = new XMLHttpRequest();
    } catch (e) {
      try {
        xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
      } catch (e) {
        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
    }
    return xmlHttp;
  },
  send: function (url, parameters, callback) {
    var self = this;
    this._xhr = this.xhr();
    this._abort = false;
    if (!Object.isNull(this._xhr)) {
      var option = this.options;
      if (option.method == 'get') {
        url += '?' + parameters;
        parameters = null;
      } else {
        parameters = parameters == null ? '' : parameters;
      }
      if (option.cache == false) {
        var match = /\?/;
        if (match.test(url)) {
          url = url + '&timestamp=' + new Date().getTime();
        } else {
          url = url + '?timestamp=' + new Date().getTime();
        }
      }
      this._xhr.open(option.method, url, option.asynchronous);
      if (option.method == 'post') {
        this._xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        this._xhr.setRequestHeader('Accept', ajaxAccepts[option.Accept]);
        if (option.contentType && option.encoding) {
          this._xhr.setRequestHeader('Content-Type', option.contentType + '; charset=' + option.encoding);
        }
      }
      if (option.timeout > 0) {
        this.calltimeout = window.setTimeout(_calltimeout, option.timeout);
      }
      this._xhr.onreadystatechange = function () {
        if (self._xhr.readyState == 4) {
          self.hideLoading();
          window.clearTimeout(self.calltimeout);
          if (self._xhr.status == 200 && !self._abort && Object.isFunction(callback)) {
            self.responseText = self._xhr.responseText;
            self.responseXML = self._xhr.responseXML;
            if (callback) {
              callback(self);
            }
          } else {
            option.onError(self);
          }
        }
      };
      if (this._xhr.upload) {
        $G(this._xhr.upload).addEvent('progress', function (e) {
          option.onProgress.call(e, Math.ceil(100 * e.loaded / e.total));
        });
      }
      var _calltimeout = function () {
        window.clearTimeout(self.calltimeout);
        self.hideLoading();
        option.onTimeout.bind(self);
      };
      self.showLoading();
      this._xhr.send(parameters);
      if (!option.asynchronous) {
        window.clearTimeout(this.calltimeout);
        this.responseText = this._xhr.responseText;
        this.responseXML = this._xhr.responseXML;
      }
    }
    return this;
  },
  autoupdate: function (url, interval, getrequest, callback) {
    this._xhr = this.xhr();
    this.interval = interval * 1000;
    if (!Object.isNull(this._xhr)) {
      this.url = url;
      this.getrequest = getrequest;
      this.callback = callback;
      this._abort = false;
      this._getupdate();
    }
    return this;
  },
  _getupdate: function () {
    if (this._abort == false) {
      var parameters = null;
      var url = this.url;
      var option = this.options;
      if (Object.isFunction(this.getrequest)) {
        if (option.method == 'get') {
          url += '?' + this.getrequest();
        } else {
          parameters = this.getrequest();
        }
      }
      parameters = (option.method == 'post' && parameters == null) ? '' : parameters;
      if (option.cache == false) {
        var match = /\?/;
        if (match.test(url)) {
          url = url + '&timestamp=' + new Date().getTime();
        } else {
          url = url + '?timestamp=' + new Date().getTime();
        }
      }
      var xhr = this._xhr;
      var temp = this;
      xhr.open(option.method, url, true);
      if (option.method == 'post') {
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', ajaxAccepts[option.Accept]);
        if (option.contentType && option.encoding) {
          xhr.setRequestHeader('Content-Type', option.contentType + '; charset=' + option.encoding);
        }
      }
      xhr.send(parameters);
      xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
          if (temp.callback) {
            temp.callback(xhr);
          }
          window.clearTimeout(temp.calltimeout);
          temp.timeinterval = window.setTimeout(temp._getupdate.bind(temp), temp.interval);
        }
      };
      var _calltimeout = function () {
        window.clearTimeout(temp.calltimeout);
        temp.timeinterval = window.setTimeout(temp._getupdate.bind(temp), temp.interval);
      };
      if (option.timeout > 0) {
        this.calltimeout = window.setTimeout(_calltimeout, option.timeout);
      }
    }
  },
  getRequestBody: function (pForm) {
    pForm = $E(pForm);
    var nParams = new Array();
    forEach(pForm.getElementsByTagName('*'), function () {
      var t = this.tagName.toLowerCase();
      if (t == 'input') {
        if ((this.checked == true && this.type == "radio") || (this.checked == true && this.type == "checkbox") || (this.type != "radio" && this.type != "checkbox")) {
          nParams.push(this.name + '=' + this.value);
        }
      } else if (t == 'select') {
        nParams.push(this.name + '=' + this.value);
      } else if (t == 'textarea') {
        nParams.push(this.name + '=' + encodeURIComponent(this.innerHTML));
      }
    });
    return nParams.join("&");
  },
  showLoading: function () {
    if (this.loading) {
      if (this.loading == 'wait' && this.center == false) {
        if (this.loader == null) {
          this.loader = new GLoading();
        }
        this.loader.show();
      } else if ($E(this.loading)) {
        this.wait = $G(this.loading);
        if (this.center) {
          this.wait.center();
        }
        this.wait.addClass(this.options.loadingClass);
      }
    }
    return this;
  },
  hideLoading: function () {
    if (this.loading) {
      if (this.loader) {
        this.loader.hide();
      } else if (this.wait) {
        this.wait.removeClass(this.options.loadingClass);
      }
    }
    return this;
  },
  inintLoading: function (loading, center, c) {
    this.loading = loading;
    this.center = center;
    if (c) {
      this.options.loadingClass = c;
    }
    return this;
  },
  abort: function () {
    clearTimeout(this.timeinterval);
    this._abort = true;
    return this;
  }
};
var GLoader = GClass.create();
GLoader.prototype = {
  initialize: function (reader, geturl, callback) {
    this.myhistory = new Array();
    this.geturl = geturl;
    this.callback = callback;
    this.req = new GAjax();
    var my_location = location.toString();
    var a = my_location.indexOf('?');
    var b = my_location.indexOf('#');
    var locs = my_location.split(/[\?\#]/);
    if (a > -1 && b > -1) {
      this.lasturl = a < b ? locs[1] : locs[2];
    } else if (a > -1) {
      this.lasturl = locs[1];
    } else {
      this.lasturl = '';
    }
    var temp = this;
    window.setInterval(function () {
      locs = location.toString().replace('_=_', '').split('#');
      locs = locs[1] && locs[1].indexOf('=') > -1 ? locs[1] : '';
      if (locs !== '' && locs != temp.lasturl) {
        temp.lasturl = locs;
        temp.myhistory.push(locs);
        if (temp.myhistory.length > 2) {
          temp.myhistory.shift();
        }
        temp.req.send(reader, locs, callback);
      }
    }, 100);
  },
  inintLoading: function (loading, center) {
    this.req.inintLoading(loading, center);
    return this;
  },
  inint: function (obj) {
    var temp = this;
    var patt1 = new RegExp('^.*' + location.hostname + '/(.*?)$');
    var patt2 = new RegExp('.*#.*?');
    forEach($E(obj).getElementsByTagName('a'), function (a) {
      if (a.target == '' && a.onclick == null && a.href != '' && patt1.exec(a.href) && !patt2.exec(a.href)) {
        a.onclick = function (e) {
          var evt = e || window.event;
          if (!(evt.shiftKey || evt.ctrlKey || evt.metaKey || evt.altKey)) {
            return temp.location(this.href);
          }
        };
      }
    });
    return this;
  },
  location: function (url) {
    var locs = window.location.toString().split('#');
    var ret = this.geturl.call(this, url);
    if (ret) {
      window.location = locs[0] + decodeURIComponent(ret) + '&' + new Date().getTime();
      return false;
    } else {
      window.location = url;
    }
    return true;
  },
  back: function () {
    if (this.myhistory.length >= 2) {
      window.location = WEB_URL + 'index.php?' + this.myhistory[this.myhistory.length - 2];
    } else {
      window.location = WEB_URL + 'index.php';
    }
  }
};
var GTooltips = new Array();
var GTooltip = GClass.create();
GTooltip.prototype = {
  initialize: function (o) {
    this.options = {
      id: '',
      delayin: 200,
      delayout: 500,
      autohide: true,
      autohidedelay: 5000,
      opacity: 100,
      cache: false
    };
    for (var property in o) {
      this.options[property] = o[property];
    }
    this.iframe = $G(document.body).create('iframe', {
      id: 'iframe_' + this.options.id,
      name: 'iframe_' + this.options.id,
      frameBorder: 0,
      scrollbar: 0
    });
    this.iframe.setStyle('opacity', 0);
    this.iframe.setStyle('position', 'absolute');
    this.iframe.setStyle('display', 'none');
    this.iframe.setStyle('zIndex', 1001);
    if (this.options.id !== '') {
      if ($E(this.options.id)) {
        this.tooltip = $G(this.options.id);
      } else {
        this.tooltip = $G(document.body).create('div', {
          id: 'div_' + this.options.id,
          name: 'div_' + this.options.id
        });
      }
    } else {
      this.tooltip = $G(document.body).create('div');
    }
    this.tooltip.setStyle('opacity', 0);
    this.tooltip.setStyle('position', 'absolute');
    this.tooltip.setStyle('display', 'none');
    this.tooltip.setStyle('zIndex', 1002);
    this.tooltip.onmouseover = this.cancleHideDelay.bind(this);
    this.tooltip.onmouseout = this.delayHide.bind(this);
    this.id = GTooltips.length;
    GTooltips[this.id] = this;
    $G(document.body).addEvent('click', function () {
      for (var i = 0; i < GTooltips.length; i++) {
        if (GTooltips[i].options.autohide) {
          GTooltips[i].hide();
        }
      }
    });
    this.req = new GAjax({
      cache: this.options.cache
    });
  },
  showAjax: function (elem, url, query, onload) {
    if (this.ajax_elem != elem) {
      this.ajax_elem = elem;
      this.req.abort();
      var temp = this;
      this.req.send(url, query, function (xhr) {
        var data = xhr.responseText;
        if (data !== '') {
          if (temp.iframe.style.display != 'none') {
            temp.show(elem, data);
            onload.call(temp, xhr);
          } else {
            this.delayin = window.setTimeout(function () {
              temp.show(elem, data);
              onload.call(temp, xhr);
            }, temp.options.delayin);
          }
        }
      });
      var el = $E(elem);
      var old_onmouseout = el.onmouseout;
      var req = this.req;
      el.onmouseout = function () {
        req.abort();
        window.clearTimeout(temp.delayin);
        window.clearTimeout(temp.delayout);
        window.clearTimeout(temp.timeautohidedelay);
        el.onmouseout = old_onmouseout;
        temp.delayout = window.setTimeout(function () {
          temp.hide.call(temp);
        }, temp.options.delayout);
      };
    }
    return this;
  },
  show: function (s, v) {
    s = $G(s);
    var sPos = s.viewportOffset();
    var sHeight = s.getHeight();
    var sWidth = s.getWidth();
    var cHeight = document.viewport.getHeight();
    var cWidth = document.viewport.getWidth();
    var cTop = document.viewport.getscrollTop();
    var cLeft = document.viewport.getscrollLeft();
    this.node = s;
    this.tooltip.setStyle('display', 'block');
    this.iframe.setStyle('display', 'block');
    this.tooltip.style.width = 'auto';
    this.tooltip.innerHTML = v;
    this.value = v;
    var l, t, w;
    var p = s.hasClass('tooltip-bottom tooltip-top tooltip-left tooltip-right');
    if (p == 'tooltip-bottom') {
      t = sPos.top + sHeight + 6;
      if (t + this.tooltip.getHeight() > cTop + cHeight) {
        t = sPos.top - this.tooltip.getHeight() - 6;
        this.tooltip.className = 'tooltip-bottom';
      } else {
        this.tooltip.className = 'tooltip-top';
      }
      l = sPos.left;
    } else if (p == 'tooltip-top') {
      t = sPos.top - this.tooltip.getHeight() - 6;
      if (t < cTop) {
        t = sPos.top + sHeight + 6;
        this.tooltip.className = 'tooltip-top';
      } else {
        this.tooltip.className = 'tooltip-bottom';
      }
      l = sPos.left;
    } else {
      var rw = cWidth - sPos.left + cLeft - sWidth;
      var lw = sPos.left - cLeft;
      this.tooltip.className = lw < rw ? 'tooltip-left' : 'tooltip-right';
      var tWidth = this.tooltip.getClientWidth();
      var oWidth = this.tooltip.getWidth() - tWidth;
      if (lw < rw) {
        l = sPos.left + sWidth + 6;
        w = Math.min(tWidth, rw - oWidth - 16);
      } else {
        l = sPos.left - tWidth - oWidth - 6;
        if (l < cLeft + 10) {
          l = cLeft + 10;
          w = sPos.left - cLeft - 36;
        } else {
          w = Math.min(tWidth, sPos.left);
          l = sPos.left - w - 26;
        }
      }
      t = (sPos.top + ((sHeight - this.tooltip.getHeight()) / 2));
      if (w != tWidth) {
        this.tooltip.style.width = w + 'px';
      }
    }
    this.tooltip.style.left = l + 'px';
    this.tooltip.style.top = t + 'px';
    this.iframe.style.left = (l - 6) + 'px';
    this.iframe.style.top = (t - 6) + 'px';
    this.iframe.style.width = (12 + this.tooltip.getWidth()) + 'px';
    this.iframe.style.height = (12 + this.tooltip.getHeight()) + 'px';
    this.cancleHideDelay();
    var temp = this;
    for (i = 0; i < GTooltips.length; i++) {
      if (i != this.id && GTooltips[i].options.autohide) {
        GTooltips[i].hide();
      }
    }
    this.tooltip.fadeTo(this.options.opacity, function () {
      temp.timeautohidedelay = window.setTimeout(temp.hide.bind(temp), temp.options.autohidedelay);
    });
  },
  delayHide: function () {
    this.timedelayhide = window.setTimeout(this.hide.bind(this), this.options.autohidedelay);
  },
  cancleHideDelay: function () {
    if (this.req) {
      this.req.abort();
    }
    window.clearTimeout(this.timeautohidedelay);
    window.clearTimeout(this.timedelayhide);
    window.clearTimeout(this.delayout);
  },
  hide: function () {
    var self = this;
    this.tooltip.fadeOut(function () {
      self.tooltip.setStyle('display', 'none');
      self.iframe.setStyle('display', 'none');
    });
  }
};
var gform_id = 0;
var GForm = GClass.create();
GForm.prototype = {
  initialize: function (frm, frmaction, loading, center, onbeforesubmit) {
    if (typeof DO_NOT_EMPTY == 'undefined') {
      window.DO_NOT_EMPTY = 'Please fill';
    }
    if (typeof DATA_NOT_MATCH == 'undefined') {
      window.DATA_NOT_MATCH = 'Data do not match';
    }
    frm = $G(frm);
    if (frmaction) {
      frm.set('action', frmaction);
    }
    this.loader = null;
    this.loading = loading;
    this.center = center;
    this.onbeforesubmit = Object.isFunction(onbeforesubmit) ? onbeforesubmit : function () {
      return true;
    };
    var temp = this;
    var _dokeypress = function (e) {
      var data = this.data;
      var val = this.value;
      var key = GEvent.keyCode(e);
      if (!((key > 36 && key < 41) || key == 8 || key == 9 || key == 13 || GEvent.isCtrlKey(e))) {
        if (data.maxlength !== null && val.length >= data.maxlength) {
          GEvent.stop(e);
          return false;
        } else if (data.type !== 'email' && data.type !== 'url' && data.type !== 'color' && data.type !== 'date' && data.pattern !== null) {
          val = String.fromCharCode(key);
          if (val !== '' && !data.pattern.test(val)) {
            GEvent.stop(e);
            return false;
          }
        }
      }
      return true;
    };
    var _docurrency = function (e) {
      this.value = CurrencyFormatted(this.value);
    };
    var _docheck = function (e) {
      var val = this.value;
      var data = this.data;
      if (e && data.type == 'number' && e.type == 'change') {
        val = val.toInt();
        if (data.min !== null) {
          val = Math.max(data.min, val);
        }
        if (data.max !== null) {
          val = Math.min(data.max, val);
        }
        self.value = val;
      } else if (data.required !== null) {
        if (val == '') {
          this.addClass('required');
          if (e) {
            this.invalid(data.title !== '' ? data.title : DO_NOT_EMPTY);
          }
        } else {
          this.reset();
        }
      } else if (data.pattern !== null && val !== '') {
        if (data.pattern.test(val)) {
          this.reset();
        } else {
          this.invalid(data.title !== '' ? data.title : DATA_NOT_MATCH);
        }
      }
    };
    var _doFileChanged = function () {
      this.text.value = this.value;
      if (this.files) {
        var preview = $E(this.get('data-preview'));
        if (preview) {
          var input = this;
          var max = floatval(this.get('data-max'));
          forEach(this.files, function () {
            if (max > 0 && this.size > max) {
              input.invalid(input.title);
            } else if (window.FileReader) {
              var r = new FileReader();
              r.onload = function (evt) {
                preview.src = evt.target.result;
                input.valid();
              };
              r.readAsDataURL(this);
            }
          });
        }
      }
    };
    var elements = new Array();
    var _oninint = function () {
      var obj = new Object;
      obj.tagName = $G(this).tagName.toLowerCase();
      obj.title = this.title;
      obj.required = this.get('required');
      obj.disabled = this.get('disabled') !== null;
      obj.maxlength = null;
      obj.result = this.get('data-result');
      if (obj.tagName == 'textarea') {
        obj.maxlength = this.get('maxlength');
        if (obj.maxlength !== null) {
          obj.maxlength = obj.maxlength.toInt();
        }
      } else if (obj.tagName == 'input') {
        obj.type = this.get('type').toLowerCase();
      }
      obj.pattern = this.get('pattern');
      if (obj.pattern !== null) {
        this.setAttribute('pattern', '(.*){0,}');
        obj.pattern = new RegExp('^(?:' + obj.pattern + ')$');
      }
      if (this.hasClass('currency')) {
        obj.type = 'currency';
        if (obj.pattern === null) {
          obj.pattern = /^(?:[0-9\.]+)$/;
        }
      }
      if (obj.type == 'number' || obj.type == 'date') {
        obj.min = this.get('min');
        obj.max = this.get('max');
      }
      var autofocus = this.get('autofocus');
      var text = this;
      if (obj.type == 'date') {
        var o = {
          'type': 'hidden',
          'name': this.name,
          'id': this.id
        };
        var txt_date = frm.create('input', o);
        text = $G().create('input', {
          'type': 'text'
        });
        if (obj.title != '') {
          text.title = obj.title;
        }
        if (obj.disabled) {
          text.disabled = true;
          txt_date.disabled = true;
        }
        text.className = this.className;
        var calendar = new GCalendar(text, function () {
          txt_date.value = this.getDateFormat('y-m-d');
          txt_date.callEvent('change');
        });
        txt_date.calendar = calendar;
        if (obj.min) {
          calendar.minDate(obj.min);
        }
        if (obj.max) {
          calendar.maxDate(obj.max);
        }
        txt_date.value = this.value;
        window.setInterval(function () {
          if (txt_date.value != calendar.oldDate) {
            calendar.oldDate = txt_date.value;
            calendar.setDate(txt_date.value);
          }
          if (txt_date.disabled != text.disabled) {
            text.disabled = txt_date.disabled ? true : false;
          }
        }, 500);
        this.replace(text);
      } else if (obj.type == 'number' || obj.type == 'email' || obj.type == 'url' || obj.type == 'color' || obj.type == 'currency') {
        var o = {
          'type': 'text',
          'name': this.name,
          'disabled': this.disabled
        };
        if (this.id != '') {
          o.id = this.id;
        }
        text = $G().create('input', o);
        if (this.value != '') {
          text.value = this.value;
        }
        if (obj.title != '') {
          text.title = obj.title;
        }
        if (obj.maxlength !== null) {
          text.maxlength = obj.maxlength;
        }
        if (this.get('readonly') !== null) {
          text.set('readonly', true);
        }
        if (this.size) {
          text.size = this.size;
        }
        text.className = this.className;
        text.value = this.value;
        this.replace(text);
      } else if (obj.type == 'text' && this.className == 'color') {
        obj.type = 'color';
        text = this;
      } else if (obj.type == 'file') {
        if (this.hasClass('g-file')) {
          var p = this.parentNode;
          this.setStyle('opacity', 0);
          this.style.cursor = 'pointer';
          this.style.position = 'absolute';
          this.style.left = 0;
          this.style.top = 1;
          p.style.position = 'relative';
          this.addEvent('change', _doFileChanged);
          text = $G(p).create('input', {'type': 'text'});
          text.disabled = true;
          text.placeholder = this.placeholder;
          this.text = text;
          this.style.zIndex = text.style.zIndex + 1;
        }
      }
      if (obj.type == 'text' || obj.type == 'number' || obj.type == 'email' || obj.type == 'url' || obj.type == 'color' || obj.type == 'currency') {
        text.addEvent('focus', function () {
          this.select();
        });
      }
      obj.element = text;
      text.data = obj;
      elements.push(obj);
      if (obj.type == 'number') {
        if (obj.min !== null) {
          obj.min = obj.min.toInt();
        }
        if (obj.max !== null) {
          obj.max = obj.max.toInt();
        }
        if (obj.pattern == null) {
          obj.pattern = /^(?:[0-9]+)$/;
        }
      } else if (obj.type == 'email') {
        if (obj.pattern == null) {
          obj.pattern = /^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/;
        }
      } else if (obj.type == 'url') {
        if (obj.pattern == null) {
          obj.pattern = /^((ftp|http(s)?):\/\/)?\w+([\.\-]\w+)*\.\w{2,4}(\:\d+)*([\/\.\-\?\&\%\#\=]\w+)*\/?$/i;
        }
      } else if (obj.type == 'color') {
        var color = new GDDColor(text, function (c) {
          this.input.style.backgroundColor = c;
          this.input.style.color = this.invertColor(c);
          this.input.value = c;
          this.input.valid();
          this.input.callEvent('change');
        });
        text.style.cursor = 'pointer';
        if (obj.pattern == null) {
          obj.pattern = /^((transparent)|(\#[0-9a-fA-F]{6,6}))$/i;
        }
      }
      text.result = obj.result;
      if (obj.pattern !== null || obj.type == 'number') {
        text.addEvent('change', _docheck);
      }
      if (obj.pattern !== null || obj.required !== null) {
        text.addEvent('keyup', _docheck);
      }
      if (obj.pattern !== null || (obj.maxlength !== null && obj.tagName == 'textarea')) {
        text.addEvent('keypress', _dokeypress);
      }
      if (obj.type == 'currency') {
        text.addEvent('blur', _docurrency);
        _docurrency.call(text);
      }
      if (autofocus !== null) {
        text.focus();
        if (obj.type == 'text') {
          text.select();
        }
      }
      if (obj.required !== null) {
        text.required = false;
        text.addEvent('focus', _docheck);
        _docheck.call(text);
      }
    };
    forEach(frm.getElementsByTagName('input'), _oninint);
    forEach(frm.getElementsByTagName('select'), _oninint);
    forEach(frm.getElementsByTagName('textarea'), _oninint);
    frm.onsubmit = function () {
      var loading = true;
      var ret = true;
      if (temp.onbeforesubmit.call(this)) {
        forEach(elements, function () {
          var val = this.element.value;
          if (this.required !== null && val == '') {
            this.element.addClass('required').highlight().focus();
            ret = false;
            return true;
          } else if (this.pattern !== null && val !== '') {
            if (this.pattern.test(val)) {
              this.element.valid();
            } else {
              this.element.invalid(this.title !== '' ? this.title : DATA_NOT_MATCH);
              this.element.highlight().focus();
              this.element.select();
              ret = false;
              return true;
            }
          }
        });
        if (ret && Object.isFunction(temp.callback)) {
          temp.showLoading();
          var uploadCallback = function () {
            if (!loading) {
              try {
                temp.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                temp.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
              } catch (e) {
              }
              temp.hideLoading();
              temp.form.method = old_method;
              temp.form.target = old_target;
              if (temp.form.encoding) {
                temp.form.encoding = old_enctype;
              } else {
                temp.form.enctype = old_enctype;
              }
              window.setTimeout(function () {
                io.removeEvent('load', uploadCallback);
                io.remove();
              }, 1);
              window.setTimeout(function () {
                temp.callback(temp);
              }, 1);
            }
          };
          var io = temp.createIframe();
          io.addEvent('load', uploadCallback);
          var old_target = this.target || '';
          var old_method = this.method || "post";
          var old_enctype = this.encoding ? this.encoding : this.enctype;
          if (this.encoding) {
            this.encoding = 'multipart/form-data';
          } else {
            this.enctype = 'multipart/form-data';
          }
          this.target = io.id;
          this.method = 'post';
          window.setTimeout(function () {
            loading = false;
            frm.submit();
          }, 1);
          ret = false;
        }
      } else {
        ret = false;
      }
      return ret;
    };
    frm.GForm = this;
    this.form = frm;
  },
  onsubmit: function (callback) {
    this.callback = callback;
    return this;
  },
  submit: function (callback) {
    var loading = true;
    var temp = this;
    this.showLoading();
    var uploadCallback = function () {
      if (!loading) {
        temp.hideLoading();
        try {
          temp.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
          temp.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
        } catch (e) {
        }
        temp.form.method = old_method;
        temp.form.target = old_target;
        window.setTimeout(function () {
          io.removeEvent('load', uploadCallback);
          io.remove();
        }, 1);
        window.setTimeout(function () {
          callback(temp);
        }, 1);
      }
    };
    if (this.form.encoding) {
      this.form.encoding = 'multipart/form-data';
    } else {
      this.form.enctype = 'multipart/form-data';
    }
    var io = this.createIframe();
    io.addEvent('load', uploadCallback);
    var old_target = this.form.target || '';
    var old_method = this.form.method || "post";
    this.form.target = io.id;
    this.form.method = "post";
    window.setTimeout(function () {
      loading = false;
      temp.form.submit();
    }, 1);
    return this;
  },
  createIframe: function () {
    var frameId = 'GForm_Submit_' + gform_id + '_' + (this.form.id || this.form.name);
    gform_id++;
    var io = $G(document.body).create('iframe', {
      id: frameId,
      name: frameId
    });
    io.setStyle('position', 'absolute');
    io.setStyle('top', '-1000px');
    io.setStyle('left', '-1000px');
    return io;
  },
  showLoading: function () {
    if (this.loading && $E(this.loading)) {
      this.loading = $G(this.loading);
      if (this.center) {
        this.loading.center();
      }
      this.loading.addClass('show');
    } else {
      var self = this;
      forEach(this.form.getElementsByTagName('input'), function () {
        if (this.getAttribute('type').toLowerCase() == 'submit') {
          self.loader = $G(this);
        }
      });
      if (this.loader) {
        this.loader.addClass('wait');
      }
    }
    return this;
  },
  hideLoading: function () {
    if (this.loading && $E(this.loading)) {
      this.loading.removeClass('show');
    } else if (this.loader) {
      this.loader.removeClass('wait');
    }
    return this;
  },
  inintLoading: function (loading, center) {
    this.loading = loading;
    this.center = center;
    return this;
  }
};
var GModal = GClass.create();
GModal.prototype = {
  initialize: function (options) {
    this.id = 'modaldiv';
    this.btnclose = 'btnclose';
    this.backgroundClass = 'modalbg';
    this.opacity = 0.8;
    this.onhide = emptyFunction;
    this.onclose = emptyFunction;
    for (var property in options) {
      this[property] = options[property];
    }
    var self = this;
    var checkESCkey = function (e) {
      if (GEvent.keyCode(e) == 27) {
        self.hide();
        GEvent.stop(e);
      }
    };
    var container_div = 'GModal_' + this.id;
    var doc = $G(document);
    doc.addEvent('keypress', checkESCkey);
    doc.addEvent('keydown', checkESCkey);
    if (!$E(container_div)) {
      var div = doc.createElement('div');
      doc.body.appendChild(div);
      div.id = container_div;
      div.style.left = '-1000px';
      div.style.position = 'absolute';
      var c = doc.createElement('div');
      div.appendChild(c);
      c.className = this.id;
      var s = doc.createElement('span');
      div.appendChild(s);
      s.className = this.btnclose;
      s.style.position = 'absolute';
      s.style.top = '0px';
      s.style.right = '0px';
      s.style.cursor = 'pointer';
      s.onclick = function () {
        self.hide();
      };
    }
    this.div = $G(container_div);
    this.body = $G(this.div.firstChild);
    this.body.style.overflow = 'auto';
    this.div.setStyle('opacity', 0);
  },
  show: function (value) {
    this.body.style.height = 'auto';
    this.body.setHTML(value);
    var imgs = this.body.getElementsByTagName('img');
    var self = this;
    var viewport_width = document.viewport.getWidth();
    var viewport_height = document.viewport.getHeight();
    forEach(imgs, function () {
      new preload(this, function () {
        dm = self.body.getDimensions();
        hOffset = dm.height - self.body.getClientHeight() + parseInt(self.body.getStyle('marginTop')) + parseInt(self.body.getStyle('marginBottom')) + 20;
        wOffset = dm.width - self.body.getClientWidth() + parseInt(self.body.getStyle('marginLeft')) + parseInt(self.body.getStyle('marginRight')) + 20;
        h = viewport_height - hOffset;
        if (dm.height > h) {
          self.body.style.height = h + 'px';
        }
        w = viewport_width - wOffset;
        if (dm.width > w) {
          self.body.style.width = w + 'px';
        }
        self.div.center();
      });
    });
    this.div.style.display = 'block';
    var dm = this.body.getDimensions();
    var hOffset = dm.height - this.body.getClientHeight() + parseInt(this.body.getStyle('marginTop')) + parseInt(this.body.getStyle('marginBottom')) + 20;
    var wOffset = dm.width - this.body.getClientWidth() + parseInt(this.body.getStyle('marginLeft')) + parseInt(this.body.getStyle('marginRight')) + 20;
    var h = document.viewport.getHeight() - hOffset;
    if (dm.height > h) {
      this.body.style.height = h + 'px';
    }
    var w = document.viewport.getWidth() - wOffset;
    if (dm.width > w) {
      this.body.style.width = w + 'px';
    }
    this.div.center();
    this.overlay();
    var _modalComplete = function () {
      if (window.ActiveXObject) {
        this.style.filter = "none";
      }
    };
    new GFade(this.div).play({
      'from': 0,
      'to': 100,
      'speed': 1,
      'duration': 20,
      'onComplete': _modalComplete
    });
    this.div.style.zIndex = 1000;
    return this;
  },
  hide: function () {
    if (Object.isFunction(this.onhide)) {
      this.onhide.call(this);
    }
    new GFade(this.div).play({
      'from': 100,
      'to': 0,
      'speed': 1,
      'duration': 20,
      'onComplete': this._hide.bind(this)
    });
    return this;
  },
  overlay: function () {
    var frameId = 'iframe_' + this.div.id;
    if (!$E(frameId)) {
      var io = $G(document.body).create('div', {
        id: frameId,
        height: '100%'
      });
      io.setStyle('position', 'fixed');
      io.setStyle('zIndex', 999);
      io.className = this.backgroundClass;
    }
    this.iframe = $G(frameId);
    this.iframe.style.left = '0px';
    this.iframe.style.top = '0px';
    this.iframe.setStyle('opacity', this.opacity);
    this.iframe.style.display = 'block';
    var d = $G(document).getDimensions();
    this.iframe.style.height = d.height + 'px';
    this.iframe.style.width = d.width + 'px';
    var self = this;
    this.iframe.addEvent('click', function () {
      self.hide();
    });
    return this;
  },
  _hide: function () {
    this.iframe.style.display = 'none';
    this.div.style.display = 'none';
    this.body.innerHTML = '';
    if (Object.isFunction(this.onclose)) {
      this.onclose.call(this);
    }
  }
};
var GFx = emptyFunction;
GFx.prototype = {
  _run: function () {
    this.playing = true;
    this.step();
  },
  stop: function () {
    this.playing = false;
    this.options.onComplete.call(this.Element);
  }
};
var GFade = GClass.create();
GFade.prototype = Object.extend(new GFx(), {
  initialize: function (el) {
    this.options = {
      from: 0,
      to: 100,
      speed: 50,
      duration: 5,
      unit: '',
      onComplete: emptyFunction
    };
    this.Element = $G(el);
    this.playing = false;
    this.timer = 0;
  },
  play: function (options) {
    for (var property in options) {
      this.options[property] = options[property];
    }
    if (this.options.to > this.options.from) {
      this.name = 'fadeIn';
      this.to = (this.options.to > 100) ? 100 : this.options.to;
      this.from = (this.options.from < 0) ? 0 : this.options.from;
    } else {
      this.name = 'fadeOut';
      this.to = (this.options.to < 0) ? 0 : this.options.to;
      this.from = (this.options.from > 100) ? 100 : this.options.from;
    }
    if (!this.playing) {
      this.now = this.from;
      this._run();
    }
    return this;
  },
  step: function () {
    try {
      if (this.playing) {
        this.Element.setStyle('opacity', this.now / 100);
      }
      var now = (this.name == 'fadeIn') ? this.now + this.options.duration : this.now - this.options.duration;
      if (this.playing && ((this.name == 'fadeOut' && now >= this.to) || (this.name == 'fadeIn' && now <= this.to))) {
        this.now = now;
        var temp = this;
        this.timer = window.setTimeout(temp.step.bind(temp), temp.options.speed);
      } else {
        this.stop();
      }
    } catch (e) {
    }
  }
});
var GHighlight = GClass.create();
GHighlight.prototype = Object.extend(new GFx(), {
  initialize: function (el) {
    this.options = {
      from: 'red',
      to: 'auto',
      speed: 10,
      time: 20,
      unit: '',
      onComplete: emptyFunction
    };
    this.Element = $G(el);
    this.playing = false;
    this.timer = 0;
    this.destination = {};
    this.srcStyle = {};
  },
  play: function (options) {
    for (var property in options) {
      this.options[property] = options[property];
    }
    if (this.options.from == 'red') {
      this.options.from = {
        'backgroundColor': '#FFBFBF'
      };
    } else if (this.options.from == 'green') {
      this.options.from = {
        'backgroundColor': '#E3F4E3'
      };
    }
    var source = this.options.from;
    if (Object.isObject(source)) {
      var destination = {};
      for (property in source) {
        destination[property] = source[property].ToRgb();
      }
      this.from = destination;
    }
    source = this.options.to;
    var destination = {};
    var c;
    if (Object.isObject(source)) {
      for (property in source) {
        destination[property] = source[property].ToRgb();
      }
      this.to = destination;
    } else if (source == 'auto') {
      source = this.options.from;
      for (property in source) {
        this.srcStyle[property] = this.Element.style[property];
        c = this.Element.getStyle(property).ToRgb();
        destination[property] = c == 'transparent' ? [255, 255, 255] : c;
        this.destination[property] = c;
      }
      this.to = destination;
    }
    source = this.options.from;
    if (source == 'auto') {
      source = this.options.to;
      for (property in source) {
        destination[property] = this.Element.getStyle(property).ToRgb();
      }
      this.from = destination;
    }
    this.delta = [];
    var to, from;
    for (property in this.from) {
      to = this.to[property];
      from = this.from[property];
      this.delta[property] = [(to[0] - from[0]) / this.options.time, (to[1] - from[1]) / this.options.time, (to[2] - from[2]) / this.options.time];
    }
    if (!this.playing) {
      this.now = 0;
      this._run();
    }
    return this;
  },
  step: function () {
    if (this.playing) {
      var colors;
      for (var property in this.from) {
        colors = this.from[property];
        this.Element.setStyle(property, 'rgb(' + parseInt(colors[0] + (this.delta[property][0] * this.now)) + ',' + parseInt(colors[1] + (this.delta[property][1] * this.now)) + ',' + parseInt(colors[2] + (this.delta[property][2] * this.now)) + ')');
      }
    }
    this.now++;
    if (this.now > this.options.time) {
      for (property in this.destination) {
        if (this.options.to == 'auto') {
          this.Element.style[property] = this.srcStyle[property];
        } else {
          this.Element.setStyle(property, this.srcStyle[property]);
        }
      }
      this.stop();
    } else {
      this.timer = window.setTimeout(this.step.bind(this), this.options.speed);
    }
  }
});
var GScroll = GClass.create();
GScroll.prototype = Object.extend(new GFx(), {
  initialize: function (container, scroller) {
    this.options = {
      speed: 30,
      duration: 1,
      pauseit: 1,
      scrollto: 'top'
    };
    this.container = $G(container);
    this.scroller = $G(scroller);
    this.container.addEvent('mouseover', function () {
      this.rel = 'pause';
    });
    this.container.addEvent('mouseout', function () {
      this.rel = 'play';
    });
    this.container.rel = 'play';
    this.playing = false;
    var size = this.container.getDimensions();
    this.containerHeight = size.height;
    this.containerWidth = size.width;
  },
  play: function (options) {
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.scrollerTop = 0;
    this.scrollerLeft = 0;
    this._run();
    return this;
  },
  step: function () {
    if (this.container.rel == 'play' || this.options.pauseit != 1) {
      if (this.options.scrollto == 'bottom') {
        this.scrollerTop = this.scrollerTop > this.containerHeight ? 0 - this.scroller.getHeight() : this.scrollerTop + this.options.duration;
        this.scroller.style.top = this.scrollerTop + 'px';
      } else if (this.options.scrollto == 'left') {
        this.scrollerLeft = this.scrollerLeft + this.scroller.getWidth() < 0 ? this.containerWidth : this.scrollerLeft - this.options.duration;
        this.scroller.style.left = this.scrollerLeft + 'px';
      } else if (this.options.scrollto == 'right') {
        this.scrollerLeft = this.scrollerLeft > this.containerWidth ? 0 - this.scrollerWidth : this.scrollerLeft + this.options.duration;
        this.scroller.style.left = this.scrollerLeft + 'px';
      } else {
        this.scrollerTop = this.scrollerTop + this.scroller.getHeight() < 0 ? this.containerHeight : this.scrollerTop - this.options.duration;
        this.scroller.style.top = this.scrollerTop + 'px';
      }
    }
    this.timer = window.setTimeout(this.step.bind(this), this.options.speed);
  }
});
var HScroll = GClass.create();
HScroll.prototype = Object.extend(new GFx(), {
  initialize: function (container, scroller) {
    this.options = {
      speed: 30,
      duration: 5,
      arrowTop: 'arrowTop',
      arrowBottom: 'arrowBottom'
    };
    var temp = this;
    this.scroller = $G(scroller);
    this.containerHeight = $G(container).getDimensions().height;
  },
  play: function (options) {
    for (var property in options) {
      this.options[property] = options[property];
    }
    var temp = this;
    var arrowTop = $G(this.options.arrowTop);
    arrowTop.addEvent('mouseover', function () {
      temp.rel = 'play';
      temp.pos = 'down';
    });
    arrowTop.addEvent('mouseout', function () {
      temp.rel = 'pause';
    });
    var arrowBottom = $G(this.options.arrowBottom);
    arrowBottom.addEvent('mouseover', function () {
      temp.rel = 'play';
      temp.pos = 'up';
    });
    arrowBottom.addEvent('mouseout', function () {
      temp.rel = 'pause';
    });
    this.scrollerTop = 0;
    this._run();
    return this;
  },
  step: function () {
    if (this.rel == 'play') {
      if (this.pos == 'up' && this.scrollerTop < 0) {
        this.scrollerTop = this.scrollerTop + this.options.duration;
        this.scroller.style.top = this.scrollerTop + 'px';
      } else if (this.pos == 'down' && this.scroller.getHeight() + this.scrollerTop > this.containerHeight) {
        this.scrollerTop = this.scrollerTop - this.options.duration;
        this.scroller.style.top = this.scrollerTop + 'px';
      }
    }
    this.timer = window.setTimeout(this.step.bind(this), this.options.speed);
  }
});
var VScroll = GClass.create();
VScroll.prototype = Object.extend(new GFx(), {
  initialize: function (container, scroller, options) {
    this.options = {
      speed: 30,
      duration: 5,
      className: 'item',
      arrowLeft: 'arrowLeft',
      arrowRight: 'arrowRight'
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    var temp = this;
    var arrowLeft = $G(this.options.arrowLeft);
    arrowLeft.addEvent('mouseover', function () {
      temp.rel = 'play';
      temp.pos = 'left';
    });
    arrowLeft.addEvent('mouseout', function () {
      temp.rel = 'pause';
    });
    var arrowRight = $G(this.options.arrowRight);
    arrowRight.addEvent('mouseover', function () {
      temp.rel = 'play';
      temp.pos = 'right';
    });
    arrowRight.addEvent('mouseout', function () {
      temp.rel = 'pause';
    });
    this.scroller = $G(scroller);
    var a, w = 0;
    forEach(this.scroller.childNodes, function () {
      if (this.nodeType == 1) {
        a = $G(this);
        a.className = temp.options.className;
        w += a.getWidth() + parseInt(a.getStyle('marginLeft')) + parseInt(a.getStyle('marginRight'));
      }
    });
    this.scroller.style.width = w + 'px';
    this.scroller.style.position = 'absolute';
    this.container = $G(container);
    this.containerWidth = this.container.getWidth();
    this.rel = 'pause';
  },
  play: function () {
    this.scrollerWidth = this.scroller.getWidth();
    this.scrollerLeft = 0;
    this._run();
    return this;
  },
  step: function () {
    if (this.rel == 'play') {
      if (this.pos == 'left') {
        this.scrollerLeft = Math.min(0, this.scrollerLeft + this.options.duration);
        this.scroller.style.left = this.scrollerLeft + 'px';
      } else if (this.pos == 'right') {
        this.scrollerLeft = Math.max(this.containerWidth - this.scrollerWidth, this.scrollerLeft - this.options.duration);
        this.scroller.style.left = this.scrollerLeft + 'px';
      }
    } else if (this.rel == 'move') {
      if (this.scrollTo < this.scrollerLeft && this.scrollerWidth + this.scrollerLeft > this.containerWidth) {
        this.scrollerLeft = this.scrollerLeft - this.options.duration;
        this.scrollerLeft = this.scrollerLeft < this.scrollTo ? this.scrollTo : this.scrollerLeft;
        this.scroller.style.left = this.scrollerLeft + 'px';
      } else if (this.scrollTo > this.scrollerLeft && this.scrollerLeft < 0) {
        this.scrollerLeft = this.scrollerLeft + this.options.duration;
        this.scrollerLeft = this.scrollerLeft > this.scrollTo ? this.scrollTo : this.scrollerLeft;
        this.scroller.style.left = this.scrollerLeft + 'px';
      } else {
        this.rel = 'pause';
      }
    }
    this.timer = window.setTimeout(this.step.bind(this), this.options.speed);
  },
  MoveTo: function (e) {
    if ($E(e)) {
      e = $G(e);
      this.scrollTo = this.scroller.getLeft() - ((e.getLeft() + e.getWidth()) - this.containerWidth + 5);
    } else {
      this.scrollTo = 0;
    }
    this.rel = 'move';
    return this;
  }
});
var GSlide = GClass.create();
GSlide.prototype = Object.extend(new GFx(), {
  initialize: function () {
    this.options = {
      speed: 30,
      duration: 1,
      from: 0,
      to: 0,
      onSlide: emptyFunction
    };
  },
  play: function (options) {
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.Pos = this.options.from;
    this._run();
    return this;
  },
  step: function () {
    var option = this.options;
    if (option.to > option.from && this.Pos < option.to) {
      this.Pos = this.Pos + option.duration;
      this.Pos = this.Pos > option.to ? option.to : this.Pos;
      option.onSlide.call(this);
      this.timer = window.setTimeout(this.step.bind(this), option.speed);
    } else if (option.to < option.from && this.Pos > option.to) {
      this.Pos = this.Pos - option.duration;
      this.Pos = this.Pos < option.to ? option.to : this.Pos;
      option.onSlide.call(this);
      this.timer = window.setTimeout(this.step.bind(this), option.speed);
    }
  }
});
var GCrossFade = GClass.create();
GCrossFade.prototype = Object.extend(new GFx(), {
  initialize: function (elem, options) {
    this.options = {
      speed: 10,
      loop: true,
      auto: true,
      onChanged: emptyFunction
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.Slide = $G(elem);
    var size = this.Slide.getDimensions();
    this.width = size.width;
    this.height = size.height;
    var img = document.createElement('img');
    img.style.position = 'absolute';
    img.style.left = '-10000px';
    this.Slide.appendChild(img);
    this.img1 = $G(img);
    img = document.createElement('img');
    img.style.position = 'absolute';
    img.style.left = '-10000px';
    this.Slide.appendChild(img);
    this.img2 = $G(img);
    this.currImg = this.img2;
    this.fader = 0;
    this.action = 'stop';
  },
  next: function (val) {
    window.clearTimeout(this.fader);
    var pos = this.Pos + val;
    pos = pos >= this.pictures.length ? this.pictures.length - 1 : pos;
    pos = pos < 0 ? 0 : pos;
    if (pos != this.Pos) {
      this.show(pos);
    }
    return this;
  },
  play: function () {
    this.nextPos = this.Pos + 1;
    this._run();
    return this;
  },
  step: function () {
    if (this.options.loop) {
      this.nextPos = this.nextPos >= this.pictures.length ? 0 : this.nextPos;
    } else if (this.nextPos >= this.pictures.length) {
      return;
    }
    var temp = this;
    new preload(this.pictures[this.nextPos], function () {
      temp.currImg = temp.currImg == temp.img2 ? temp.img1 : temp.img2;
      var old = temp.currImg == temp.img2 ? temp.img1 : temp.img2;
      temp._resizeImage(temp.currImg, this);
      temp.currImg.style.zIndex = 1;
      old.style.zIndex = 0;
      new GFade(temp.currImg).play({
        'onComplete': function () {
          old.setStyle('opacity', 0);
          temp.fader = window.setTimeout(temp.step.bind(temp), temp.options.speed * 1000);
          temp.Pos = temp.nextPos;
          temp.options.onChanged.call(temp);
          temp.nextPos++;
        }
      });
    });
  },
  show: function (id) {
    var temp = this;
    new preload(this.pictures[id], function () {
      temp.currImg = temp.currImg == temp.img2 ? temp.img1 : temp.img2;
      var old = temp.currImg == temp.img2 ? temp.img1 : temp.img2;
      temp._resizeImage(temp.currImg, this);
      temp.currImg.style.zIndex = 1;
      old.style.zIndex = 0;
      new GFade(temp.currImg).play({
        'onComplete': function () {
          old.setStyle('opacity', 0);
          temp.Pos = id;
          temp.options.onChanged.call(temp);
        }
      });
    });
    return this;
  },
  pictures: function (files) {
    this.pictures = files.split(',');
    this.picturesWidth = new Array();
    this.picturesHeight = new Array();
    if (this.options.auto) {
      this.Pos = 0;
      this.play();
    } else {
      this.show(0);
    }
    return this;
  },
  _resizeImage: function (img, obj) {
    img.src = obj.src;
    var w = obj.width;
    var h = obj.height;
    var nw, nh;
    if (w >= h) {
      if (w > this.width) {
        nw = this.width;
        nh = (this.width * h) / w;
      } else if (h > this.height) {
        nh = this.height;
        nw = (this.height * w) / h;
      } else {
        nh = h;
        nw = w;
      }
    } else {
      if (h > this.height) {
        nh = this.height;
        nw = (this.height * w) / h;
      } else if (w > this.width) {
        nw = this.width;
        nh = (this.width * h) / w;
      } else {
        nh = h;
        nw = w;
      }
    }
    img.style.width = nw + 'px';
    img.style.height = nh + 'px';
    img.style.top = ((this.height - nh) / 2) + 'px';
    img.style.left = ((this.width - nw) / 2) + 'px';
  }
});
var preload = GClass.create();
preload.prototype = {
  initialize: function (img, onComplete) {
    var temp = new Image();
    if (img.src) {
      temp.src = img.src;
      temp.original = img;
    } else {
      temp.src = img;
    }
    var _preload = function () {
      if (temp.complete) {
        onComplete.call(temp);
      } else {
        window.setTimeout(_preload, 30);
      }
    };
    window.setTimeout(_preload, 30);
  }
};
var GEvent = {
  isButton: function (e, code) {
    e = !e ? window.event : e;
    if (e.which == null) {
      button = (e.button < 2) ? 0 : ((e.button == 4) ? 1 : 2);
    } else {
      button = (e.which < 2) ? 0 : ((e.which == 2) ? 1 : 2);
    }
    return button == code;
  },
  isLeftClick: function (e) {
    return GEvent.isButton(e, 0);
  },
  isMiddleClick: function (e) {
    return GEvent.isButton(e, 1);
  },
  isRightClick: function (e) {
    return GEvent.isButton(e, 2);
  },
  isCtrlKey: function (e) {
    return !e ? window.event.ctrlKey : e.ctrlKey;
  },
  isShiftKey: function (e) {
    return !e ? window.event.shiftKey : e.shiftKey;
  },
  isAltKey: function (e) {
    return !e ? window.event.altKey : e.altKey;
  },
  element: function (e) {
    e = !e ? window.event : e;
    var node = e.target ? e.target : e.srcElement;
    return e.nodeType == 3 ? node.parentNode : node;
  },
  keyCode: function (e) {
    e = !e ? window.event : e;
    return e.which || e.keyCode;
  },
  stop: function (e) {
    e = !e ? window.event : e;
    if (e.stopPropagation) {
      e.stopPropagation();
    }
    e.cancelBubble = true;
    if (e.preventDefault) {
      e.preventDefault();
    }
    e.returnValue = false;
  },
  pointer: function (e) {
    e = !e ? window.event : e;
    return {
      x: e.pageX || (e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)),
      y: e.pageY || (e.clientY + (document.documentElement.scrollTop || document.body.scrollTop))
    };
  },
  pointerX: function (e) {
    return GEvent.pointer(e).x;
  },
  pointerY: function (e) {
    return GEvent.pointer(e).y;
  }
};
var Cookie = {
  get: function (k) {
    var v = document.cookie.match('(?:^|;)\\s*' + k.escapeRegExp() + '=([^;]*)');
    return (v) ? decodeURIComponent(v[1]) : null;
  },
  set: function (k, v, options) {
    var _options = {
      path: false,
      domain: false,
      duration: false,
      secure: false
    };
    for (var property in options) {
      _options[property] = options[property];
    }
    v = encodeURIComponent(v);
    if (_options.domain) {
      v += '; domain=' + _options.domain;
    }
    if (_options.path) {
      v += '; path=' + _options.path;
    }
    if (_options.duration) {
      var date = new Date();
      date.setTime(date.getTime() + _options.duration * 24 * 60 * 60 * 1000);
      v += '; expires=' + date.toGMTString();
    }
    if (_options.secure) {
      v += '; secure';
    }
    document.cookie = k + '=' + v;
    return this;
  },
  remove: function (k) {
    Cookie.set(k, '', {
      duration: -1
    });
    return this;
  }
};
var GLoading = GClass.create();
GLoading.prototype = {
  initialize: function () {
    this.waittime = 0;
    this.loading = null;
  },
  show: function () {
    window.clearTimeout(this.waittime);
    if (this.loading == null && !$E('wait')) {
      var div = document.createElement('div');
      div.id = 'wait';
      div.innerHTML = '<dd><dt></dt></dd>';
      document.body.appendChild(div);
    }
    this.loading = $G('wait');
    this.loading.addClass('show');
    return this;
  },
  hide: function () {
    if (this.loading) {
      this.loading.replaceClass('show', 'complete');
      var self = this;
      this.waittime = window.setTimeout(function () {
        self.loading.removeClass('wait show complete');
      }, 500);
    }
    return this;
  }
};
var GValidator = GClass.create();
GValidator.prototype = {
  initialize: function (input, events, validtor, action, callback, form) {
    this.timer = 0;
    this.req = new GAjax();
    this.interval = 1000;
    this.input = $G(input);
    this.input.Validator = this;
    this.title = this.input.get('title');
    this.validtor = validtor;
    this.action = action;
    this.callback = callback;
    this.form = form;
    var temp = this;
    if (form && form !== '') {
      form = $G(form);
      form.addEvent('submit', function () {
        temp.abort();
      });
    }
    forEach(events.split(','), function () {
      temp.input.addEvent(this, temp.validate.bind(temp));
    });
  },
  validate: function () {
    this.abort();
    var ret = Object.isFunction(this.validtor) ? this.validtor.call(this) : true;
    if (this.form && ret && this.action && ret !== '' && this.action !== '') {
      this.input.addClass('wait');
      var temp = this;
      this.timer = window.setTimeout(function () {
        temp.req.send(temp.action, ret, function (xhr) {
          temp.input.removeClass('wait');
          if (temp.callback) {
            ret = temp.callback.call(temp, xhr);
          } else {
            ret = xhr.responseText;
          }
          if (!ret || ret == '') {
            temp.valid();
          } else {
            try {
              ret = eval(ret);
            } catch (e) {
            }
            temp.invalid(ret);
          }
        });
      }, this.interval);
    }
  },
  abort: function () {
    window.clearTimeout(this.timer);
    this.req.abort();
    this.input.reset();
    return this;
  },
  interval: function (value) {
    this.interval = value;
    return this;
  },
  valid: function (className) {
    this.input.valid(className);
  },
  invalid: function (value, className) {
    this.input.invalid(value, className);
  },
  reset: function () {
    this.input.set('title', this.title);
    this.input.reset();
  }
};
Object.extend(String.prototype, {
  hexToRgb: function (a) {
    var h = this.match(new RegExp('^[#]{0,1}([\\w]{1,2})([\\w]{1,2})([\\w]{1,2})$'));
    var rgb = [];
    if (!h) {
      rgb = [255, 255, 255];
    } else {
      for (var i = 1; i < h.length; i++) {
        if (h[i].length == 1) {
          h[i] += h[i];
        }
        rgb.push(parseInt(h[i], 16));
      }
    }
    if (a) {
      return [parseFloat(rgb[0]), parseFloat(rgb[1]), parseFloat(rgb[2])];
    } else {
      return 'rgb(' + rgb.join(',') + ')';
    }
  },
  ToRgb: function () {
    if (this.match(/^#[0-9a-f]{3,6}$/i)) {
      return this.hexToRgb(true);
    } else if (value = this.match(/(\d+),\s*(\d+),\s*(\d+),\s*(\d+)/)) {
      return (parseFloat(value[4]) < 1) ? 'transparent' : [parseFloat(value[1]), parseFloat(value[2]), parseFloat(value[3])];
    } else {
      return ((value = this.match(/(\d+),\s*(\d+),\s*(\d+)/))) ? [parseFloat(value[1]), parseFloat(value[2]), parseFloat(value[3])] : this.toLowerCase();
    }
  },
  entityify: function () {
    return this.replace(/</g, '&lt;').
      replace(/>/g, '&gt;').
      replace(/"/g, '&quot;').
      replace(/'/g, '&#39;').
      replace(/\\/g, '&#92;').
      replace(/&/g, '&amp;');
  },
  unentityify: function () {
    return this.replace(/&lt;/g, '<').
      replace(/&gt;/g, '>').
      replace(/&quot;/g, '"').
      replace(/&#[0]?39;/g, "'").
      replace(/&#92;/g, '\\').
      replace(/&amp;/g, '&');
  },
  toJSON: function () {
    try {
      return eval('(' + this + ')');
    } catch (e) {
      return false;
    }
  },
  toInt: function () {
    return floatval(this);
  },
  currFormat: function () {
    return this.toInt().toFixed(2);
  },
  escapeRegExp: function () {
    return this.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1');
  },
  capitalize: function () {
    return this.replace(/\b[a-z]/g, function (m) {
      return m.toUpperCase();
    });
  },
  evalScript: function () {
    var regex = /<script.*?>(.*?)<\/script>/g;
    var t = this.replace(/[\r\n]/g, '').replace(/\/\/<\!\[CDATA\[/g, '').replace(/\/\/\]\]>/g, '');
    m = regex.exec(t);
    while (m) {
      try {
        eval(m[1]);
      } catch (e) {
      }
      m = regex.exec(t);
    }
    return this;
  },
  leftPad: function (c, f) {
    var r = '';
    for (var i = 0; i < (c - this.length); i++) {
      r = r + f;
    }
    return r + this;
  },
  trim: function () {
    return this.replace(/^(\s|&nbsp;)+|(\s|&nbsp;)+$/g, "");
  },
  ltrim: function () {
    return this.replace(/^(\s|&nbsp;)+/, "");
  },
  rtrim: function () {
    return this.replace(/(\s|&nbsp;)+$/, "");
  },
  strip_tags: function (allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
    var php = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return this.replace(php, '').replace(tags, function ($0, $1) {
      return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
  },
  toDOM: function () {
    var s = function (a) {
      return a.replace(/&gt;/g, ">").
        replace(/&lt;/g, "<").
        replace(/&nbsp;/g, " ").
        replace(/&quot;/g, '"').
        replace(/&#[0]?39;/g, "'").
        replace(/&#92;/g, '\\').
        replace(/&amp;/g, "&");
    };
    var t = function (a) {
      return a.replace(/ /g, "");
    };
    var u = function (a) {
      var b = document.createDocumentFragment();
      var c = a.indexOf(' ');
      if (c == -1) {
        var d = a.toLowerCase();
        b.appendChild(document.createElement(d));
      } else {
        d = t(a.substring(0, c)).toLowerCase();
        if (document.all && (d == 'input' || d == 'iframe')) {
          try {
            b.appendChild(document.createElement('<' + a + '/>'));
            return b;
          } catch (e) {
          }
        }
        a = a.substring(c + 1);
        b.appendChild(document.createElement(d));
        while (a.length > 0) {
          var e = a.indexOf('=');
          if (e >= 0) {
            var f = t(a.substring(0, e)).toLowerCase();
            var g = a.indexOf('"');
            a = a.substring(g + 1);
            g = a.indexOf('"');
            var h = s(a.substring(0, g));
            a = a.substring(g + 2);
            if (document.all && f == 'style') {
              b.lastChild.style.cssText = h;
            } else if (f == 'class') {
              b.lastChild.className = h;
            } else {
              b.lastChild.setAttribute(f, h);
            }
          } else {
            break;
          }
        }
      }
      return b;
    };
    var v = function (a, b, c) {
      var d = a;
      var e = b;
      c = c.toLowerCase();
      var f = e.indexOf('</' + c + '>');
      d = d.concat(e.substring(0, f));
      e = e.substring(f);
      while (d.indexOf('<' + c) != -1) {
        d = d.substring(d.indexOf('<' + c));
        d = d.substring(d.indexOf('>') + 1);
        e = e.substring(e.indexOf('>') + 1);
        f = e.indexOf('</' + c + '>');
        d = d.concat(e.substring(0, f));
        e = e.substring(f);
      }
      return b.length - e.length;
    };
    var w = function (a) {
      var b = document.createDocumentFragment();
      while (a && a.length > 0) {
        var c = a.indexOf("<");
        if (c == -1) {
          a = s(a);
          b.appendChild(document.createTextNode(a));
          a = null;
        }
        if (c > 0) {
          var d = s(a.substring(0, c));
          b.appendChild(document.createTextNode(d));
          a = a.substring(c);
        }
        if (c == 0) {
          var e = a.indexOf('<!--');
          if (e == 0) {
            var f = a.indexOf('-->');
            var g = a.substring(4, f);
            g = s(g);
            b.appendChild(document.createComment(g));
            a = a.substring(f + 3);
          } else {
            var h = a.indexOf('>');
            if (a.substring(h - 1, h) == '/') {
              var i = a.indexOf('/>');
              var j = a.substring(1, i);
              b.appendChild(u(j));
              a = a.substring(i + 2);
            } else {
              var k = a.indexOf('>');
              var l = a.substring(1, k);
              var m = document.createDocumentFragment();
              m.appendChild(u(l));
              a = a.substring(k + 1);
              var n = a.substring(0, a.indexOf('</'));
              a = a.substring(a.indexOf('</'));
              if (n.indexOf('<') != -1) {
                var o = m.lastChild.nodeName;
                var p = v(n, a, o);
                n = n.concat(a.substring(0, p));
                a = a.substring(p);
              }
              a = a.substring(a.indexOf('>') + 1);
              m.lastChild.appendChild(w(n));
              b.appendChild(m);
            }
          }
        }
      }
      return b;
    };
    return w(this);
  }
});
var GDrag = GClass.create();
GDrag.prototype = {
  initialize: function (src, options) {
    this.options = {
      beginDrag: emptyFunction,
      moveDrag: emptyFunction,
      endDrag: emptyFunction
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.src = $G(src);
    var self = this;
    function _mousemove(e) {
      self.mousePos = GEvent.pointer(e);
      self.options.moveDrag.call(self);
    }
    function _selectstart(e) {
      GEvent.stop(e);
    }
    function _dragstart(e) {
      GEvent.stop(e);
    }
    function _mouseup(e) {
      document.removeEvent('mouseup', _mouseup);
      document.removeEvent('mousemove', _mousemove);
      document.removeEvent('selectstart', _selectstart);
      document.removeEvent('dragstart', _dragstart);
      if (self.src.releaseCapture) {
        self.src.releaseCapture();
      }
      self.mousePos = GEvent.pointer(e);
      GEvent.stop(e);
      self.options.endDrag.call(self.src);
    }
    function _mousedown(e) {
      var delay;
      var temp = this;
      function _cancleClick(e) {
        window.clearTimeout(delay);
        this.removeEvent('mouseup', _cancleClick);
      }
      if (GEvent.isLeftClick(e)) {
        GEvent.stop(e);
        self.mousePos = GEvent.pointer(e);
        if (this.setCapture) {
          this.setCapture();
        }
        delay = window.setTimeout(function () {
          document.addEvent('mouseup', _mouseup);
          document.addEvent('mousemove', _mousemove);
          document.addEvent('selectstart', _selectstart);
          document.addEvent('dragstart', _dragstart);
          self.options.beginDrag.call(self);
        }, 100);
        temp.addEvent('mouseup', _cancleClick);
      }
    }
    this.src.addEvent('mousedown', _mousedown);
    function touchHandler(event) {
      var touches = event.changedTouches,
        first = touches[0],
        type = "";
      switch (event.type) {
        case "touchstart":
          type = "mousedown";
          break;
        case "touchmove":
          type = "mousemove";
          break;
        case "touchend":
          type = "mouseup";
          break;
        default:
          return;
      }
      var simulatedEvent = document.createEvent("MouseEvent");
      simulatedEvent.initMouseEvent(type, true, false, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0, null);
      first.target.dispatchEvent(simulatedEvent);
      event.preventDefault();
    }
    this.src.addEvent("touchstart", touchHandler, true);
    this.src.addEvent("touchmove", touchHandler, true);
    this.src.addEvent("touchend", touchHandler, true);
  }
};
var GDragMove = GClass.create();
GDragMove.prototype = {
  initialize: function (move_id, drag_id, options) {
    this.options = {
      beginDrag: resultFunction,
      moveDrag: resultFunction,
      endDrag: emptyFunction
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.dragObj = $G(drag_id);
    this.dragObj.style.cursor = 'move';
    this.moveObj = $G(move_id);
    var Hinstance = this;
    function _beginDrag() {
      if (Hinstance.options.beginDrag.call(Hinstance.moveObj)) {
        var elemPos = Hinstance.moveObj.viewportOffset();
        Hinstance.mouseOffset = {
          x: this.mousePos.x - elemPos.left,
          y: this.mousePos.y - elemPos.top
        };
      }
    }
    function _moveDrag() {
      if (Hinstance.options.moveDrag.call(Hinstance.moveObj)) {
        Hinstance.moveObj.style.top = (this.mousePos.y - Hinstance.mouseOffset.y) + 'px';
        Hinstance.moveObj.style.left = (this.mousePos.x - Hinstance.mouseOffset.x) + 'px';
      }
    }
    function _endDrag() {
      Hinstance.options.endDrag.call(Hinstance.moveObj);
    }
    var o = {
      beginDrag: _beginDrag,
      moveDrag: _moveDrag,
      endDrag: _endDrag
    };
    new GDrag(this.dragObj, o);
  }
};
var GSortTable = GClass.create();
GSortTable.prototype = {
  initialize: function (id, options) {
    this.options = {
      sortClass: 'sort',
      tag: 'tr',
      endDrag: function () {
      }
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.changed = false;
    var self = this,
      dropitems = new Array(),
      hoverItem = null,
      position = 0;

    function checkMouseOver(item, mousePos) {
      var elemPos = item.viewportOffset();
      var elemSize = item.getDimensions();
      var mouseover = mousePos.x > elemPos.left && mousePos.y > elemPos.top;
      return mouseover && mousePos.x < elemPos.left + elemSize.width && mousePos.y < elemPos.top + elemSize.height;
    }
    function doBeginDrag() {
      self.changed = false;
      self.dragItem = this;
      hoverItem = this;
      position = this.mousePos.y;
    }
    function doMoveDrag() {
      var temp = this;
      forEach(dropitems, function () {
        if (checkMouseOver(this, temp.mousePos)) {
          if (this != hoverItem) {
            self.changed = true;
            if (temp.mousePos.y > position) {
              temp.src.parentNode.insertBefore(temp.src, this.nextSibling);
            } else {
              temp.src.parentNode.insertBefore(temp.src, this);
            }
            hoverItem = this;
            return true;
          }
        }
      });
      position = this.mousePos.y;
    }
    function doEndDrag() {
      if (self.changed) {
        self.options.endDrag.call(this);
      }
    }
    var o = {
      beginDrag: doBeginDrag,
      moveDrag: doMoveDrag,
      endDrag: doEndDrag
    };
    forEach($E(id).getElementsByTagName(self.options.tag), function () {
      if ($G(this).hasClass(self.options.sortClass)) {
        new GDrag(this, o);
        dropitems.push(this);
      }
    });
  }
};
var GAutoSave = GClass.create();
GAutoSave.prototype = {
  initialize: function (url, onchanged, callback, wait) {
    this.timer = 0;
    this.req = new GAjax();
    this.url = url;
    this.onchanged = onchanged;
    this.callback = callback;
    this.wait = wait || 1000;
  },
  inintLoading: function (loading, center) {
    this.req.inintLoading(loading, center);
    return this;
  },
  add: function (id, evt, callback) {
    var self = this;
    var _event = function () {
      window.clearTimeout(self.timer);
      if (Object.isFunction(callback)) {
        callback.call(this);
      }
      var temp = this;
      self.timer = window.setTimeout(function () {
        var q = self.onchanged.call(temp);
        if (q != '') {
          self.req.send(self.url, q, self.callback);
        }
      }, self.wait);
    };
    $G(id).addEvent(evt, _event);
    return this;
  }
};
var GCalendar = GClass.create();
GCalendar.prototype = {
  initialize: function (id, onchanged) {
    this.input = $G(id);
    this.input.addClass('gcalendar');
    this.input.set('readonly', true);
    this.onchanged = onchanged || emptyFunction;
    this.mdate = null;
    this.xdate = null;
    this.mode = 0;
    this.format = 'd M Y';
    this.date = new Date();
    this.cdate = new Date();
    if (!$E('gcalendar_div')) {
      var div = document.createElement('div');
      document.body.appendChild(div);
      div.id = 'gcalendar_div';
    }
    this.calendar = $G('gcalendar_div');
    this.calendar.style.position = 'absolute';
    this.calendar.style.display = 'none';
    this.calendar.style.zIndex = 99;
    this._datechanged();
    var self = this;
    this.input.addEvent('click', function (e) {
      self.mode = 0;
      self.cdate.setTime(self.date.valueOf());
      self._draw();
      GEvent.stop(e);
    });
    this.input.addEvent('keydown', function (e) {
      var key = GEvent.keyCode(e);
      if (key == 9) {
        self.calendar.style.display = 'none';
      } else if (key == 32) {
        self._toogle(e);
      } else if (key == 37 || key == 39) {
        self.moveDate(key == 39 ? 1 : -1);
        if (self.calendar.style.display != 'none') {
          self._draw();
        }
        GEvent.stop(e);
      } else if (key == 38 || key == 40) {
        if (GEvent.isShiftKey(e)) {
          self.moveYear(key == 40 ? 1 : -1);
        } else if (GEvent.isCtrlKey(e)) {
          self.moveMonth(key == 40 ? 1 : -1);
        } else {
          self.moveDate(key == 40 ? 7 : -7);
        }
        if (self.calendar.style.display != 'none') {
          self._draw();
        }
        GEvent.stop(e);
      }
    });
    $G(document.body).addEvent('click', function () {
      self.calendar.style.display = 'none';
    });
  },
  _datechanged: function () {
    if (this.xdate && this.date > this.xdate) {
      this.date.setTime(this.xdate.valueOf());
    } else if (this.mdate && this.date < this.mdate) {
      this.date.setTime(this.mdate.valueOf());
    }
    this.cdate.setTime(this.date.valueOf());
    this.input.value = this.date.dateFormat(this.format);
    this.onchanged.call(this);
  },
  _toogle: function (e) {
    if (this.calendar.style.display == 'block') {
      this.calendar.style.display = 'none';
    } else {
      this.mode = 0;
      this.cdate.setTime(this.date.valueOf());
      this._draw();
    }
    GEvent.stop(e);
  },
  _draw: function () {
    var self = this;
    this.calendar.innerHTML = '';
    var div = document.createElement('div');
    this.calendar.appendChild(div);
    div.className = 'gcalendar';
    var p = document.createElement('p');
    div.appendChild(p);
    var a = document.createElement('a');
    p.appendChild(a);
    a.innerHTML = '&larr;';
    $G(a).addEvent('click', function (e) {
      self._move(e, -1);
    });
    if (this.mode < 2) {
      a = document.createElement('a');
      p.appendChild(a);
      a.innerHTML = this.cdate.dateFormat(this.mode == 1 ? 'Y' : 'M Y');
      $G(a).addEvent('click', function (e) {
        self.mode++;
        self._draw();
        GEvent.stop(e);
      });
    } else {
      var start_year = this.cdate.getFullYear() - 6;
      a = document.createElement('span');
      p.appendChild(a);
      a.appendChild(document.createTextNode((start_year + Date.yearOffset) + '-' + (start_year + 11 + Date.yearOffset)));
    }
    a = document.createElement('a');
    p.appendChild(a);
    a.innerHTML = '&rarr;';
    $G(a).addEvent('click', function (e) {
      self._move(e, 1);
    });
    table = document.createElement('table');
    div.appendChild(table);
    var thead = document.createElement('thead');
    table.appendChild(thead);
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);
    var intmonth = this.cdate.getMonth() + 1;
    var intyear = this.cdate.getFullYear();
    var intdate = this.cdate.getDate();
    var cls = '';
    var today = new Date();
    var today_month = today.getMonth() + 1;
    var today_year = today.getFullYear();
    var today_date = today.getDate();
    var sel_month = this.date.getMonth() + 1;
    var sel_year = this.date.getFullYear();
    var sel_date = this.date.getDate();
    var r = 0;
    var c = 0;
    var bg = '';
    if (this.mode == 2) {
      for (var i = start_year; i < start_year + 12; i++) {
        c = (i - start_year) % 4;
        if (c == 0) {
          row = tbody.insertRow(r);
          bg = (bg == 'bg1') ? 'bg2' : 'bg1';
          row.className = 'gcalendar_' + bg;
          r++;
        }
        cell = row.insertCell(c);
        cls = 'month';
        if (i == sel_year) {
          cls = cls + ' select';
        }
        if (i == today_year) {
          cls = cls + ' today';
        }
        cell.className = cls;
        cell.appendChild(document.createTextNode(i + Date.yearOffset));
        cell.oDate = new Date(i, 1, 1, 12, 0, 0, 0);
        $G(cell).addEvent('click', function (e) {
          self.cdate.setTime(this.oDate.valueOf());
          self.mode--;
          self._draw();
          GEvent.stop(e);
        });
      }
    } else if (this.mode == 1) {
      forEach(Date.monthNames, function (month, i) {
        c = i % 4;
        if (c == 0) {
          row = tbody.insertRow(r);
          bg = (bg == 'bg1') ? 'bg2' : 'bg1';
          row.className = 'gcalendar_' + bg;
          r++;
        }
        cell = row.insertCell(c);
        cls = 'month';
        if (intyear == sel_year && i + 1 == sel_month) {
          cls = cls + ' select';
        }
        if (intyear == today_year && i + 1 == today_month) {
          cls = cls + ' today';
        }
        cell.className = cls;
        cell.appendChild(document.createTextNode(month));
        cell.oDate = new Date(intyear, i, 1, 0, 0, 0, 0);
        $G(cell).addEvent('click', function (e) {
          self.cdate.setTime(this.oDate.valueOf());
          self.mode--;
          self._draw();
          GEvent.stop(e);
        });
      });
    } else {
      row = thead.insertRow(0);
      forEach(Date.dayNames, function (item, i) {
        cell = document.createElement('th');
        row.appendChild(cell);
        cell.appendChild(document.createTextNode(item));
      });
      var tmp_prev_month = intmonth - 1;
      var tmp_next_month = intmonth + 1;
      var tmp_next_year = intyear;
      var tmp_prev_year = intyear;
      if (tmp_prev_month == 0) {
        tmp_prev_month = 12;
        tmp_prev_year--;
      }
      if (tmp_next_month == 13) {
        tmp_next_month = 1;
        tmp_next_year++;
      }
      var initial_day = 1;
      var tmp_init = new Date(intyear, intmonth, 1, 0, 0, 0, 0).dayOfWeek();
      var max_prev = new Date(tmp_prev_year, tmp_prev_month, 0, 0, 0, 0, 0).daysInMonth();
      var max_this = new Date(intyear, intmonth, 0, 0, 0, 0, 0).daysInMonth();
      if (tmp_init !== 0) {
        initial_day = max_prev - (tmp_init - 1);
      }
      tmp_next_year = tmp_next_year.toString();
      tmp_prev_year = tmp_prev_year.toString();
      tmp_next_month = tmp_next_month.toString();
      tmp_prev_month = tmp_prev_month.toString();
      var pointer = initial_day;
      var flag_init = initial_day == 1 ? 1 : 0;
      var tmp_month = initial_day == 1 ? intmonth : parseInt(tmp_prev_month);
      var tmp_year = initial_day == 1 ? intyear : parseInt(tmp_prev_year);
      if (this.mdate !== null) {
        var min_month = this.mdate.getMonth() + 1;
        var min_year = this.mdate.getFullYear();
        var min_date = this.mdate.getDate();
      }
      if (this.xdate !== null) {
        var max_month = this.xdate.getMonth() + 1;
        var max_year = this.xdate.getFullYear();
        var max_date = this.xdate.getDate();
      }
      var flag_end = 0;
      r = 0;
      for (var x = 0; x < 42; x++) {
        if (tmp_init !== 0 && pointer > max_prev && flag_init == 0) {
          flag_init = 1;
          pointer = 1;
          tmp_month = intmonth;
          tmp_year = intyear;
        }
        if (flag_init == 1 && flag_end == 0 && pointer > max_this) {
          flag_end = 1;
          pointer = 1;
          tmp_month = parseInt(tmp_next_month);
          tmp_year = parseInt(tmp_next_year);
        }
        c = x % 7;
        if (c == 0) {
          row = tbody.insertRow(r);
          r++;
        }
        cell = row.insertCell(c);
        cell.oDate = new Date(tmp_year, tmp_month - 1, pointer, 0, 0, 0, 0);
        cell.title = cell.oDate.dateFormat(self.format);
        cell.appendChild(document.createTextNode(pointer));
        var canclick = true;
        if (this.mdate !== null && this.xdate !== null) {
          canclick = tmp_year == min_year && tmp_month == min_month && pointer >= min_date;
          canclick = canclick || (tmp_year == max_year && tmp_month == max_month && pointer <= max_date);
        } else if (this.mdate !== null) {
          canclick = tmp_year > min_year || (tmp_year == min_year && tmp_month > min_month);
          canclick = canclick || (tmp_year == min_year && tmp_month == min_month && pointer >= min_date);
        } else if (this.xdate !== null) {
          canclick = tmp_year < max_year || (tmp_year == max_year && tmp_month < max_month);
          canclick = canclick || (tmp_year == max_year && tmp_month == max_month && pointer <= max_date);
        }
        if (canclick) {
          $G(cell).addEvent('click', function (e) {
            self.date.setTime(this.oDate.valueOf());
            self._datechanged();
            var input = $E(self.input);
            input.focus();
            input.select();
          });
          cls = tmp_month == intmonth ? 'curr' : 'ex';
        } else {
          cls = 'ex';
        }
        if (tmp_year == sel_year && tmp_month == sel_month && pointer == sel_date) {
          cls = cls + ' select';
        }
        if (tmp_year == today_year && tmp_month == today_month && pointer == today_date) {
          cls = cls + ' today';
        }
        cell.className = cls;
        pointer++;
      }
    }
    var vpo = this.input.viewportOffset();
    var t = vpo.top + this.input.getHeight() + 5;
    var dm = this.calendar.getDimensions();
    var ch = document.viewport.getHeight();
    var ct = document.viewport.getscrollTop();
    if ((t + dm.height + 5) >= (ch + ct)) {
      this.calendar.style.top = (vpo.top - dm.height - 5) + 'px';
    } else {
      this.calendar.style.top = t + 'px';
    }
    var cw = document.viewport.getWidth();
    var l = vpo.left + dm.width > cw ? cw - dm.width : vpo.left;
    this.calendar.style.left = l + 'px';
    this.calendar.style.display = 'block';
  },
  _move: function (e, value) {
    if (this.mode == 2) {
      this.cdate.setFullYear(this.cdate.getFullYear() + (value * 12));
    } else if (this.mode == 1) {
      this.cdate.setFullYear(this.cdate.getFullYear() + value);
    } else {
      this.cdate.setMonth(this.cdate.getMonth() + value);
    }
    this._draw();
    GEvent.stop(e);
  },
  moveDate: function (day) {
    this.date.setDate(this.date.getDate() + day);
    this._datechanged();
    return this;
  },
  moveMonth: function (month) {
    this.date.setMonth(this.date.getMonth() + month);
    this._datechanged();
    return this;
  },
  moveYear: function (year) {
    this.date.setFullYear(this.date.getFullYear() + year);
    this._datechanged();
    return this;
  },
  setFormat: function (value) {
    this.format = value;
    this._datechanged();
    return this;
  },
  setDate: function (date) {
    this.date = this._toDate(date);
    this._datechanged();
    return this;
  },
  getDate: function () {
    var d = new Date();
    d.setTime(this.date.valueOf());
    return d;
  },
  getDateFormat: function (format) {
    format = format || this.format;
    return this.getDate().dateFormat(format);
  },
  minDate: function (date) {
    if (Object.isNull(date)) {
      if (this.mdate == null) {
        this.mdate = new Date();
      }
      this.mdate.setTime(this.date.valueOf());
    } else {
      this.mdate = this._toDate(date);
    }
    return this;
  },
  maxDate: function (date) {
    if (Object.isNull(date)) {
      if (this.xdate == null) {
        this.xdate = new Date();
      }
      this.xdate.setTime(this.date.valueOf());
    } else {
      this.xdate = this._toDate(date);
    }
    return this;
  },
  setText: function (value) {
    this.input.value = value;
  },
  _toDate: function (date) {
    var d = null;
    if (Object.isString(date)) {
      d = strToDate(date);
      d = d == null ? new Date() : d;
    } else {
      d = new Date();
      if (!Object.isNull(date)) {
        d.setTime(date.valueOf());
      }
    }
    return d;
  }
};
var Clock = GClass.create();
Clock.prototype = {
  initialize: function (id, options) {
    this.options = {
      reverse: false,
      onTimer: null
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.hour_offset = 0;
    this.display = $E(id);
    if (this.display.innerHTML == '') {
      this.display.innerHTML = new Date().dateFormat('H:I:S');
    }
    var temp = this;
    this.clock = window.setInterval(function () {
      temp._updateTime.call(temp);
    }, 1000);
  },
  hourOffset: function (val) {
    var d = new Date();
    var Second = d.getSeconds();
    var Minute = d.getMinutes();
    var Hour = d.getHours();
    Hour += parseFloat(val);
    if (Hour >= 24) {
      Hour = 0;
    }
    this.display.innerHTML = Hour.toString().leftPad(2, '0') + ':' + Minute.toString().leftPad(2, '0') + ':' + Second.toString().leftPad(2, '0');
    return this;
  },
  stop: function () {
    window.clearInterval(this.clock);
  },
  _updateTime: function () {
    var ds = this.display.innerHTML.split(':');
    var Hour = parseFloat(ds[0]);
    var Minute = parseFloat(ds[1]);
    var Second = parseFloat(ds[2]);
    if (this.options.reverse) {
      Second--;
      if (Hour == 0 && Minute == 0 && Second == 0) {
        this.stop();
      } else {
        if (Second < 0) {
          Second = 59;
          Minute--;
        }
        if (Minute < 0) {
          Minute = 59;
          Hour--;
        }
      }
    } else {
      Second++;
      if (Second >= 60) {
        Second = 0;
        Minute++;
      }
      if (Minute >= 60) {
        Minute = 0;
        Hour++;
      }
      if (Hour >= 24) {
        Hour = 0;
      }
    }
    this.display.innerHTML = Hour.toString().leftPad(2, '0') + ':' + Minute.toString().leftPad(2, '0') + ':' + Second.toString().leftPad(2, '0');
    if (Object.isFunction(this.options.onTimer)) {
      this.options.onTimer.call(this, Hour, Minute, Second);
    }
  }
};
var G_FxZooms = new Array();
GFxZoom = GClass.create();
GFxZoom.prototype = Object.extend(new GFx(), {
  initialize: function (elem, options) {
    this.options = {
      duration: 2,
      speed: 1,
      offset: 0,
      fitdoc: true,
      onComplete: emptyFunction,
      onResize: emptyFunction
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.options.duration = this.options.duration > 8 ? 8 : this.options.duration;
    this.options.duration -= (this.options.duration % 2 == 0 ? 0 : 1);
    this.Player = $G(elem);
    this.Player.style.zIndex = 9999999;
    var tmp = this.Player.viewportOffset();
    this.t = tmp.top;
    this.l = tmp.left;
    tmp = this.Player.getDimensions();
    this.w = tmp.width;
    this.h = tmp.height;
  },
  play: function (dw, dh, dl, dt) {
    var cw = document.viewport.getWidth();
    var ch = document.viewport.getHeight();
    if (this.options.fitdoc) {
      if (dw > cw) {
        dh = Math.round(cw * dh / dw);
        dw = cw;
      }
      if (dh > ch) {
        dw = Math.round(ch * dw / dh);
        dh = ch;
      }
      dw = dw - this.options.offset;
      dh = dh - this.options.offset;
    }
    this.dw = dw;
    this.dh = dh;
    if (dl == null) {
      dl = document.viewport.getscrollLeft() + ((cw - dw) / 2);
    }
    if (dt == null) {
      dt = document.viewport.getscrollTop() + ((ch - dh) / 2);
    }
    this.lStep = ((dl - this.l) / 2) / this.options.duration;
    this.tStep = ((dt - this.t) / 2) / this.options.duration;
    this.wStep = ((dw - this.w) / 2) / this.options.duration;
    this.hStep = ((dh - this.h) / 2) / this.options.duration;
    this.timer = window.setInterval(this.step.bind(this), this.options.speed);
    this.options.onResize.call(this);
  },
  step: function () {
    if (this.w != this.dw || this.h != this.dh) {
      this.l += this.lStep;
      this.t += this.tStep;
      this.w += this.wStep;
      this.h += this.hStep;
      this.Player.style.left = this.l + 'px';
      this.Player.style.top = this.t + 'px';
      this.Player.style.width = this.w + 'px';
      this.Player.style.height = this.h + 'px';
      this.options.onResize.call(this);
    } else {
      this.stop();
    }
  },
  stop: function () {
    window.clearInterval(this.timer);
    this.options.onComplete.call(this);
  }
});
var G_ddcolors = new Array();
var GDDColor = GClass.create();
GDDColor.prototype = {
  initialize: function (id, onchanged) {
    this.onchanged = onchanged || function () {
    };
    this.color = '';
    var self = this;
    self.input = $G(id);
    self.input.addClass('gddcolor');
    self.input.style.cursor = 'pointer';
    self.input.tabIndex = 0;
    this.id = G_ddcolors.length;
    G_ddcolors[this.id] = this;
    var div = document.createElement('div');
    document.body.appendChild(div);
    div.className = 'gddcolor_div';
    div.style.display = 'none';
    div.style.position = 'absolute';
    self.ddcolor = $G(div);
    self.createColors();
    var _doPreview = function (e) {
      for (var i = 0; i < G_ddcolors.length; i++) {
        G_ddcolors[i].ddcolor.style.display = 'none';
      }
      var vpo = this.viewportOffset();
      var t = vpo.top + this.getHeight() + 5;
      var h = self.ddcolor.getHeight();
      var ch = document.viewport.getHeight();
      var ct = document.viewport.getscrollTop();
      if ((t + h + 5) >= (ch + ct)) {
        self.ddcolor.style.top = (vpo.top - h - 5) + 'px';
      } else {
        self.ddcolor.style.top = t + 'px';
      }
      self.ddcolor.style.left = vpo.left + 'px';
      self.ddcolor.style.display = 'block';
      GEvent.stop(e);
    };
    var _validateColor = function (e) {
      var key = GEvent.keyCode(e);
      var ctrl = GEvent.isCtrlKey(e);
      if (!((key > 36 && key < 41) || key == 8 || key == 9 || ctrl)) {
        var c = new String.fromCharCode(key);
        var numcheck = /[\#0-9a-fA-F]/;
        if (!numcheck.test(c)) {
          GEvent.stop(e);
        }
      }
      return true;
    };
    var _dochanged = function (e) {
      var c = this.value;
      self.pickColor(c);
      self.showDemo(c);
      self.input.innerHTML = c;
    };
    var _dokeydown = function (e) {
      var key = GEvent.keyCode(e);
      if (key == 13 || key == 32) {
        return _doPreview.call(this, e);
      } else if (key == 9) {
        for (var i = 0; i < G_ddcolors.length; i++) {
          G_ddcolors[i].ddcolor.style.display = 'none';
        }
      } else if (key == 27) {
        self.ddcolor.style.display = 'none';
        self.input.focus();
      } else if (key > 36 && key < 41) {
        _doPreview.call(this, e);
        $E('color_' + self.id + '_0_0').focus();
      }
      return true;
    };
    self.input.addEvent('click', _doPreview);
    self.input.addEvent('keypress', _validateColor);
    self.input.addEvent('keydown', _dokeydown);
    if (self.input.type == 'text') {
      self.input.addEvent('keyup', _dochanged);
      self.input.addEvent('change', _dochanged);
    }
    $G(document.body).addEvent('click', function () {
      for (var i = 0; i < G_ddcolors.length; i++) {
        G_ddcolors[i].ddcolor.style.display = 'none';
      }
    });
    if (self.input.value) {
      var color = '';
      window.setInterval(function () {
        if (self.input.value !== color) {
          color = self.input.value;
          self.input.style.backgroundColor = color;
          self.input.style.color = self.invertColor(color);
          self.input.callEvent('change');
        }
      }, 50);
    }
  },
  createColors: function () {
    var id = this.id;
    var R = new Array('3', '6', 'F', '3', '6', 'F', '3', '6', 'F', '3', '6', 'F', '3', '6', 'F', '3', '6', 'F', '0', '9', 'C', '0', '9', 'C', '0', '9', 'C', '0', '9', 'C', '0', '9', 'C', '0', '9', 'C');
    var G = new Array('0', '3', '6', '9', 'C', 'F', 'F', 'C', '9', '6', '3', '0', '0', '3', '6', '9', 'C', 'F');
    var B = new Array('0', '3', '6', '9', 'C', 'F', 'F', 'C', '9', '6', '3', '0');
    var r, c, z, x, a, p, s, t, self = this;
    var t = self.input.tabIndex + 1;
    var m = 0,
      n = 0,
      l = B.length,
      q = G.length;
    var el, hs, patt = /((color_[0-9]+_)([0-9]+)_)([0-9]+)/;
    var _dokeydown = function (e) {
      var key = GEvent.keyCode(e);
      hs = patt.exec(this.id);
      z = parseFloat(hs[3]);
      x = parseFloat(hs[4]);
      if (key > 36 && key < 41) {
        if (key == 37) {
          x = x - 1;
        } else if (key == 38) {
          if (z == l + 1) {
            x = x < 5 ? 0 : 1;
          }
          z = z - 1;
        } else if (key == 39) {
          x = x + 1;
        } else if (key == 40) {
          if (z == l - 1) {
            x = x < 5 ? 0 : 1;
          }
          z = z + 1;
        }
        el = $E(hs[2] + z + '_' + x);
        if (el) {
          el.focus();
          self.showDemo(el.title);
        }
      } else if (key == 13 || key == 32) {
        if (z == l) {
          if (x == 0) {
            self.doClick('Transparent');
          } else {
            self.doClick('');
          }
        } else if (key == 13 || z > l) {
          self.doClick(this.title);
        } else {
          self.pickColor(this.title);
        }
        GEvent.stop(e);
      } else if (key == 27) {
        self.ddcolor.style.display = 'none';
        self.input.focus();
      }
    };
    for (c = 0; c < l; c++) {
      x = document.createElement('p');
      this.ddcolor.appendChild(x);
      m = 0;
      for (r = 0; r < q; r++) {
        z = '#' + R[n] + R[n] + G[r] + G[r] + B[c] + B[c];
        a = $G(document.createElement('a'));
        x.appendChild(a);
        a.id = 'color_' + id + '_' + c + '_' + r;
        a.tabIndex = t;
        s = document.createElement('span');
        a.appendChild(s);
        s.style.backgroundColor = z;
        a.title = z;
        if (m < 5) {
          m++;
        } else {
          m = 0;
          n++;
        }
        a.addEvent('click', function (e) {
          self.pickColor(this.title);
          GEvent.stop(e);
        });
        a.addEvent('mouseover', function (e) {
          self.showDemo(this.title);
        });
        a.addEvent('keydown', _dokeydown);
      }
    }
    p = document.createElement('p');
    this.ddcolor.appendChild(p);
    p.className = 'gddcolor_p';
    a = $G(document.createElement('a'));
    p.appendChild(a);
    a.id = 'color_' + id + '_' + c + '_0';
    a.tabIndex = t;
    a.appendChild(document.createTextNode('Transparent'));
    a.addEvent('click', function (evt) {
      self.doClick('Transparent');
    });
    a.addEvent('mouseover', function (evt) {
      self.showDemo('Transparent');
    });
    a.addEvent('keydown', _dokeydown);
    a = $G(document.createElement('a'));
    p.appendChild(a);
    a.id = 'color_' + id + '_' + c + '_1';
    c++;
    a.tabIndex = t;
    a.appendChild(document.createTextNode('Clear'));
    a.addEvent('click', function (evt) {
      self.doClick('');
    });
    a.addEvent('mouseover', function (evt) {
      self.showDemo('Clear');
    });
    a.addEvent('keydown', _dokeydown);
    this.demoColor = document.createElement('span');
    p.appendChild(this.demoColor);
    this.customColor = document.createElement('p');
    this.ddcolor.appendChild(this.customColor);
    t++;
    for (r = 0; r < G.length; r++) {
      a = $G(document.createElement('a'));
      this.customColor.appendChild(a);
      a.id = 'color_' + id + '_' + c + '_' + r;
      a.tabIndex = t;
      a.appendChild(document.createElement('span'));
      a.addEvent('click', function (evt) {
        self.doClick(this.title);
      });
      a.addEvent('mouseover', function (evt) {
        self.showDemo(this.title);
      });
      a.addEvent('keydown', _dokeydown);
    }
  },
  doClick: function (c) {
    this.ddcolor.style.display = 'none';
    this.onchanged.call(this, c);
    this.input.focus();
  },
  pickColor: function (c) {
    var n, self = this;
    var rgb = c.hexToRgb(true);
    var m = Math.min(rgb[0], rgb[1], rgb[2]);
    var as = this.customColor.getElementsByTagName('a');
    m = Math.floor((255 - m) / as.length);
    forEach(as, function () {
      n = self.rgbToHex(rgb);
      this.title = n;
      this.firstChild.style.backgroundColor = n;
      rgb[0] = Math.min(255, rgb[0] + m);
      rgb[1] = Math.min(255, rgb[1] + m);
      rgb[2] = Math.min(255, rgb[2] + m);
    });
  },
  showDemo: function (c) {
    var a;
    if (c == 'Transparent') {
      c = 'transparent';
      a = 'Transparent';
    } else if (c == 'Clear') {
      c = 'transparent';
      a = '';
    } else {
      a = c;
    }
    this.demoColor.style.backgroundColor = c;
    this.demoColor.innerHTML = a;
    this.demoColor.style.color = this.invertColor(c);
  },
  setColor: function (c) {
    if (c != '' && c != this.color) {
      this.color = c.toUpperCase();
      this.pickColor(this.color);
      this.showDemo(this.color);
      this.doClick(this.color);
    }
  },
  getColor: function () {
    return this.color;
  },
  invertColor: function (c) {
    if (c.toLowerCase() == 'transparent') {
      return this.ddcolor.style.color;
    } else {
      var rgb = c.hexToRgb(true);
      rgb[0] = 255 - rgb[0];
      rgb[1] = 255 - rgb[1];
      rgb[2] = 255 - rgb[2];
      return this.rgbToHex(rgb);
    }
  },
  rgbToHex: function (rgb) {
    function toHex(c) {
      var c = c.toString(16).toUpperCase();
      return c.leftPad(2, '0');
    }
    return '#' + toHex(rgb[0]) + toHex(rgb[1]) + toHex(rgb[2]);
  }
};
var GDPanels = [];
var gdpanels_len = 0;
var GDPanel = GClass.create();
GDPanel.prototype = {
  initialize: function (a, div, prefix) {
    this.prefix = prefix || 'gdpanel';
    var self = this;
    $E(div).className = this.prefix + ' ' + this.prefix + gdpanels_len;
    $E(a).className = this.prefix + '-arrow ' + this.prefix + gdpanels_len;
    gdpanels_len++;
    GDPanels[a] = div;
    callClick(a, function () {
      self.show(this);
      return false;
    });
    function _isPanel(src) {
      var c, tag = src.tagName.toLowerCase();
      var test = self.prefix + ' ' + self.prefix + '-arrow';
      while (src && src != document.body) {
        c = $G(src).hasClass(test);
        if (c) {
          return c == self.prefix + '-arrow' || tag == 'input' || tag == 'select' || tag == 'textarea' || tag == 'label' || tag == 'button' ? src : null;
        } else {
          src = src.parentNode;
        }
      }
      return null;
    }
    $G(document.body).addEvent('click', function (e) {
      if (_isPanel(GEvent.element(e)) === null) {
        self.show(null);
      }
    });
  },
  show: function (src) {
    var c = '', a, div;
    if (src) {
      c = src.className.replace(this.prefix + '-arrow ', this.prefix + ' ');
    }
    for (a in GDPanels) {
      div = $E(GDPanels[a]);
      if (div) {
        if (div.className == c) {
          $G(a).addClass('hover');
          $G(div).addClass('show');
        } else {
          $G(a).removeClass('hover');
          $G(div).removeClass('show');
        }
      }
    }
  },
  hide: function () {
    this.show(null);
  }
};
var G_Lightbox = null;
var GLightbox = GClass.create();
GLightbox.prototype = {
  initialize: function (options) {
    this.id = 'gslide_div';
    this.btnclose = 'btnclose';
    this.backgroundClass = 'modalbg';
    this.previewClass = 'gallery_preview';
    this.loadingClass = 'spinner';
    this.btnnav = 'btnnav';
    this.opacity = 0.9;
    this.onshow = null;
    this.onhide = null;
    this.onclose = null;
    for (var property in options) {
      this[property] = options[property];
    }
    var self = this;
    var checkESCkey = function (e) {
      var k = GEvent.keyCode(e);
      if (k == 27) {
        self.hide(e);
      } else if (k == 37) {
        self.showPrev(e);
      } else if (k == 39) {
        self.showNext(e);
      }
    };
    var container_div = 'GLightbox_' + this.id;
    var doc = $G(document);
    doc.addEvent('keydown', checkESCkey);
    if (!$E(container_div)) {
      var div = doc.createElement('div');
      doc.body.appendChild(div);
      div.id = container_div;
      div.style.left = '-1000px';
      div.style.position = 'fixed';
      var c = doc.createElement('div');
      div.appendChild(c);
      c.className = this.id;
      var c2 = doc.createElement('figure');
      c.appendChild(c2);
      c2.className = this.previewClass;
      this.img = doc.createElement('img');
      c2.appendChild(this.img);
      c = doc.createElement('figcaption');
      c2.appendChild(c);
      var s = doc.createElement('span');
      c2.appendChild(s);
      s.className = this.loadingClass;
      c2 = doc.createElement('p');
      c.appendChild(c2);
      s = doc.createElement('span');
      div.appendChild(s);
      s.className = this.btnclose;
      s.style.position = 'absolute';
      s.style.top = '0px';
      s.style.right = '0px';
      s.style.cursor = 'pointer';
      var a = doc.createElement('a');
      div.appendChild(a);
      a.className = this.btnnav + ' zoomin';
      a.id = 'GLightbox_zoom';
      callClick(a, function (e) {
        self._fullScreen(e);
      });
      this.zoom = a;
      a = doc.createElement('a');
      div.appendChild(a);
      a.className = this.btnnav + ' prev';
      callClick(a, function (e) {
        self.showPrev(e);
      });
      a = doc.createElement('a');
      div.appendChild(a);
      a.className = this.btnnav + ' next';
      callClick(a, function (e) {
        self.showNext(e);
      });
    }
    this.zoom = $E('GLightbox_zoom');
    this.div = $G(container_div);
    this.body = $G(this.div.firstChild);
    this.preview = $G(this.body.firstChild);
    this.body.nextSibling.onclick = function () {
      self.hide();
    };
    this.img = this.preview.firstChild;
    this.caption = this.img.nextSibling.firstChild;
    this.loading = this.img.nextSibling.nextSibling;
    this.body.style.overflow = 'hidden';
    this.div.setStyle('opacity', 0);
    this.currentId = 0;
    this.imgs = new Array();
  },
  clear: function () {
    this.currentId = 0;
    this.imgs.length = 0;
  },
  add: function (a) {
    var img = $E(a);
    img.id = this.imgs.length;
    this.imgs.push(img);
    var self = this;
    callClick(img, function () {
      self.currentId = this.id.toInt();
      self.show(this, false);
      return false;
    });
  },
  showNext: function (e) {
    if (this.div.style.display == 'block' && this.imgs.length > 0) {
      this.currentId++;
      if (this.currentId >= this.imgs.length) {
        this.currentId = 0;
      }
      var img = this.imgs[this.currentId];
      this.show(img, false);
    }
  },
  showPrev: function (e) {
    if (this.div.style.display == 'block' && this.imgs.length > 0) {
      this.currentId--;
      if (this.currentId < 0) {
        this.currentId = this.imgs.length - 1;
      }
      var img = this.imgs[this.currentId];
      this.show(img, false);
    }
  },
  _fullScreen: function (e) {
    if (this.div.style.display == 'block' && this.imgs.length > 0) {
      var img = this.imgs[this.currentId];
      this.show(img, this.zoom.className == this.btnnav + ' zoomout');
    }
  },
  show: function (obj, fullscreen) {
    this.zoom.className = this.btnnav + (fullscreen ? ' zoomin' : ' zoomout');
    var self = this;
    this.loading.className = this.loadingClass + ' show';
    if (obj.href) {
      img = obj.href;
      caption = obj.title;
    } else {
      img = obj.src;
      caption = obj.alt;
    }
    new preload(img, function () {
      self.loading.className = self.loadingClass;
      self.img.src = this.src;
      if (!fullscreen) {
        var w = this.width;
        var h = this.height;
        var vp = self.body.viewportOffset();
        var dm = self.body.getDimensions();
        var hOffset = dm.height - self.body.getClientHeight() + parseInt(self.body.getStyle('marginTop')) + parseInt(self.body.getStyle('marginBottom'));
        var wOffset = dm.width - self.body.getClientWidth() + parseInt(self.body.getStyle('marginLeft')) + parseInt(self.body.getStyle('marginRight'));
        var src_h = document.viewport.getHeight() - hOffset - 20;
        var src_w = document.viewport.getWidth() - wOffset - 20;
        var nw, nh;
        if (w >= h) {
          if (w > src_w) {
            nw = src_w;
            nh = (src_w * h) / w;
          } else if (h > src_h) {
            nh = src_h;
            nw = (src_h * w) / h;
          } else {
            nh = h;
            nw = w;
          }
        } else {
          if (h > src_h) {
            nh = src_h;
            nw = (src_h * w) / h;
          } else if (w > src_w) {
            nw = src_w;
            nh = (src_w * h) / w;
          } else {
            nh = h;
            nw = w;
          }
        }
        self.img.style.width = nw + 'px';
        self.img.style.height = nh + 'px';
      } else {
        self.img.style.width = 'auto';
        self.img.style.height = 'auto';
      }
      new GDragMove('GLightbox_' + self.id, self.img);
      if (caption && caption != '') {
        self.caption.innerHTML = caption.replace(/[\n]/g, '<br>');
        self.caption.parentNode.className = 'show';
      } else {
        self.caption.parentNode.className = '';
      }
      self.div.style.display = 'block';
      self.div.center();
      self.overlay();
      new GFade(self.div).play({
        'from': 0,
        'to': 100,
        'speed': 1,
        'duration': 20,
        'onComplete': self._show.bind(self)
      });
      self.div.style.zIndex = 1000;
    });
    return this;
  },
  hide: function (e) {
    if (Object.isFunction(this.onhide)) {
      this.onhide.call(this);
    }
    new GFade(this.div).play({
      'from': 100,
      'to': 0,
      'speed': 1,
      'duration': 20,
      'onComplete': this._hide.bind(this)
    });
    return this;
  },
  overlay: function () {
    var frameId = 'iframe_' + this.div.id;
    if (!$E(frameId)) {
      var io = $G(document.body).create('iframe', {
        id: frameId, height: '100%', frameBorder: 0
      });
      io.setStyle('position', 'absolute');
      io.setStyle('zIndex', 999);
      io.className = this.backgroundClass;
    }
    this.iframe = $G(frameId);
    this.iframe.style.left = '0px';
    this.iframe.style.top = '0px';
    this.iframe.setStyle('opacity', this.opacity);
    this.iframe.style.display = 'block';
    var iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
    var self = this;
    $G(self.iframe.contentWindow.document).addEvent('click', function (e) {
      self.hide();
    });
    var d = $G(document).getDimensions();
    this.iframe.style.height = d.height + 'px';
    this.iframe.style.width = '100%';
    return this;
  },
  _show: function () {
    if (Object.isFunction(this.onshow)) {
      this.onshow.call(this);
    }
  },
  _hide: function () {
    this.iframe.style.display = 'none';
    this.div.style.display = 'none';
    if (Object.isFunction(this.onclose)) {
      this.onclose.call(this);
    }
  }
};
function callClick(input, func) {
  var _doKeyPress = function (e) {
    var key = GEvent.keyCode(e);
    if (key == 13) {
      var tmp = e;
      if (func.call(this) !== true) {
        GEvent.stop(tmp);
        return false;
      }
    }
  };
  input = $E(input);
  if (input && input.onclick == null) {
    input.style.cursor = 'pointer';
    input.tabIndex = 0;
    input.onclick = func;
    $G(input).addEvent('keypress', _doKeyPress);
  }
}
var _loadCompleted = function () {
  domloaded = true;
  if (document.addEventListener) {
    document.removeEventListener("DOMContentLoaded", _loadCompleted, false);
    window.removeEventListener("load", _loadCompleted, false);
  } else {
    document.detachEvent("onreadystatechange", _loadCompleted);
    window.detachEvent("onload", _loadCompleted);
  }
  $G(document);
  $G(document.body);
};
if (document.addEventListener) {
  document.addEventListener("DOMContentLoaded", _loadCompleted, false);
  window.addEventListener("load", _loadCompleted, false);
} else {
  document.attachEvent("onreadystatechange", _loadCompleted);
  window.attachEvent("onload", _loadCompleted);
}
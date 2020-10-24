// js/gcms.js
var doLoginSubmit = function (xhr) {
  var ds = xhr.responseText.split('|');
  if (ds.length == 3) {
    if (ds[0] !== '') {
      alert(ds[0]);
    }
    if (ds[2] == 'home') {
      window.location = WEB_URL + 'index.php';
    } else if (ds[2] == 'editprofile') {
      window.location = WEB_URL + 'index.php?module=editprofile';
    } else if (ds[1] == 3) {
      window.location = decodeURIComponent(ds[2]);
    } else if (ds[2] == 'back' || ds[1] == 2) {
      if (loader) {
        loader.back();
      } else {
        window.history.go(-1);
      }
    } else if (ds[1] != 1 && ds[2] !== '' && ds[2] != 'reload' && $E('login-box')) {
      hideModal();
      var content = decodeURIComponent(ds[2]);
      var login = $G('login-box');
      login.setHTML(content);
      content.evalScript();
      if (loader) {
        loader.inint(login);
      }
    } else {
      window.location = replaceURL('action', 'login');
    }
  } else {
    if (ds[0] !== '') {
      alert(ds[0]);
      if (ds[1] && $E(ds[1])) {
        var el = $G(ds[1]);
        el.highlight().focus();
        el.select();
      }
    } else {
      alert(xhr.responseText);
      window.location = replaceURL('action', 'login');
    }
  }
};
var doLogout = function (e) {
  setQueryURL('action', 'logout');
};
var doMember = function (e) {
  GEvent.stop(e);
  var action = $G(this).id;
  if (this.hasClass('register')) {
    action = 'register';
  } else if (this.hasClass('forgot')) {
    action = 'forgot';
  }
  showModal(WEB_URL + 'xhr.php', 'action=modal&module=' + action);
  return false;
};
var onMemberSubmit = function (e) {
  if (!$E('register_accept').checked) {
    alert(REGISTER_NOT_ACCEPT);
    $G('register_accept').highlight().focus();
    return false;
  } else {
    return true;
  }
};
function popupwindow(a, b, w, h) {
  var c = (screen.width - w) / 2;
  var d = (screen.height - h) / 2;
  c = (c < 0) ? 0 : c;
  d = (d < 0) ? 0 : d;
  var e = 'height=' + h + ',';
  e += 'width=' + w + ',';
  e += 'top=' + d + ',';
  e += 'left=' + c + ',';
  e += 'resizable=0, scrollbars=0, status=0,toolbar=0, menubars=0, location=0';
  var f = window.open(a, b, e);
  f.window.focus();
}
function getCurrentURL() {
  var patt = /^(.*)=(.*)$/;
  var urls = new Object();
  var u = window.location.href;
  var us2 = u.split('#');
  u = us2.length == 2 ? us2[0] : u;
  var us1 = u.split('?');
  u = us1.length == 2 ? us1[0] : u;
  if (us1.length == 2) {
    forEach(us1[1].split('&'), function () {
      hs = patt.exec(this);
      if (hs) {
        urls[hs[1].toLowerCase()] = this;
      } else {
        urls[this] = this;
      }
    });
  }
  if (us2.length == 2) {
    forEach(us2[1].split('&'), function () {
      hs = patt.exec(this);
      if (hs) {
        if (MODULE_URL == '1' && hs[1] == 'module') {
          if (hs[2] == FIRST_MODULE) {
            u = WEB_URL + 'index.php';
          } else {
            u = WEB_URL + hs[2].replace('-', '/') + '.html';
          }
        } else {
          urls[hs[1].toLowerCase()] = this;
        }
      } else {
        urls[this] = this;
      }
    });
  }
  var us = new Array();
  for (var p in urls) {
    us.push(urls[p]);
  }
  if (us.length > 0) {
    u += '?' + us.join('&');
  }
  return u;
}
var createLikeButton = emptyFunction;
var counter_time = 0;
$G(window).Ready(function () {
  if (navigator.userAgent.indexOf("MSIE") > -1) {
    document.body.addClass("ie");
  }
  if (typeof use_ajax != 'undefined' && use_ajax == 1) {
    loader = new GLoader(WEB_URL + 'xhr.php', getURL, function (xhr) {
      var content = $G('content');
      var datas = xhr.responseText.toJSON();
      if (datas) {
        editor = null;
        document.title = decodeURIComponent(datas[0].title).unentityify();
        selectMenu(decodeURIComponent(datas[0].menu));
        var data = decodeURIComponent(datas[0].content);
        content.setHTML(data);
        loader.inint(content);
        data.evalScript();
        if (datas[0].to && $E(datas[0].to)) {
          window.scrollTo(0, $G(datas[0].to).getTop() - 10);
        } else if ($E('scroll-to')) {
          window.scrollTo(0, $G('scroll-to').getTop());
        }
        if ($E('db_elapsed')) {
          $E('db_elapsed').innerHTML = datas[0].db_elapsed;
        }
        if ($E('db_quries')) {
          $E('db_quries').innerHTML = datas[0].db_quries;
        }
        if (Object.isFunction(createLikeButton)) {
          createLikeButton();
        }
      } else {
        content.setHTML(xhr.responseText);
      }
    });
    loader.inintLoading('wait', false);
    loader.inint(document);
  }
  var hs, q2, patt = /^lang_([a-z]{2,2})$/;
  forEach(document.body.getElementsByTagName("a"), function (item) {
    hs = patt.exec(item.id);
    if (hs) {
      item.onclick = function () {
        var lang = this.id.replace('lang_', '');
        var urls = document.location.toString().replace('#', '&').split('?');
        if (urls[1]) {
          var new_url = new Object();
          forEach(urls[1].split('&'), function (q) {
            q2 = q.split('=');
            if (q2.length == 2) {
              new_url[q2[0]] = q2[1];
            }
          });
          new_url['lang'] = lang;
          var qs = Array();
          for (var property in new_url) {
            qs.push(property + '=' + new_url[property]);
          }
          document.location = urls[0] + '?' + qs.join('&');
          return false;
        } else {
          return true;
        }
      };
    }
  });
  var _getCounter = function () {
    return 'counter=' + counter_time;
  };
  new GAjax().autoupdate(WEB_URL + 'useronline.php', counter_refresh_time, _getCounter, function (xhr) {
    var datas = xhr.responseText.toJSON();
    if (datas) {
      var d, v;
      for (d in datas) {
        v = datas[d];
        if (d == 'all' && $E('counter')) {
          $E('counter').innerHTML = v.toString().leftPad(counter_digit, '0');
        } else if (d == 'today' && $E('counter-today')) {
          $E('counter-today').innerHTML = v.toString().leftPad(counter_digit, '0');
        } else if (d == 'online' && $E('useronline')) {
          $E('useronline').innerHTML = v.toString().leftPad(counter_digit, '0');
        } else if (d == 'pagesview' && $E('pages-view')) {
          $E('pages-view').innerHTML = v.toString().leftPad(counter_digit, '0');
        } else if (d == 'count') {
          counter_time = v;
        } else if (d == 'useronline') {
          var online = $E('counter-online');
          if (online) {
            forEach(online.getElementsByTagName('img'), function () {
              this.onmouseover = null;
            });
            var lis = online.getElementsByTagName('li');
            for (var i = lis.length - 1; i >= 0; i--) {
              online.removeChild(lis[i]);
            }
            var li, img, span;
            forEach(v, function () {
              li = document.createElement('li');
              online.appendChild(li);
              img = document.createElement('img');
              img.id = this.id;
              img.src = this.icon;
              img.style.cursor = 'pointer';
              img.onclick = function () {
                loaddoc(WEB_URL + 'index.php?module=member&id=' + this.id);
              };
              li.appendChild(img);
              span = document.createElement('span');
              li.appendChild(span);
              span.innerHTML = this.displayname;
            });
          }
        } else if (window[d] && typeof window[d] == 'function') {
          window[d](v);
        }
      }
    }
  });
});
var getURL = function (url) {
  var loader_patt0 = /.*?module=.*?/;
  var loader_patt1 = new RegExp('^' + WEB_URL + '(.*)/([0-9]+)/([0-9]+)/(.*).html$');
  var loader_patt2 = new RegExp('^' + WEB_URL + '(.*)/([0-9]+)/(.*).html$');
  var loader_patt3 = new RegExp('^' + WEB_URL + '(.*)/(.*).html$');
  var loader_patt4 = new RegExp('^' + WEB_URL + '(.*).html$');
  var p1 = /module=(.*)?/;
  var urls = url.split('?');
  var new_q = new Array();
  if (urls[1] && loader_patt0.exec(urls[1])) {
    return '#' + urls[1];
  } else if (hs = loader_patt1.exec(urls[0])) {
    new_q.push('#module=' + hs[1] + '-' + hs[4] + '&cat=' + hs[2] + '&id=' + hs[3]);
  } else if (hs = loader_patt2.exec(urls[0])) {
    new_q.push('#module=' + hs[1] + '-' + hs[3] + '&cat=' + hs[2]);
  } else if (hs = loader_patt3.exec(urls[0])) {
    new_q.push('#module=' + hs[1] + '-' + hs[2]);
  } else if (hs = loader_patt4.exec(urls[0])) {
    new_q.push('#module=' + hs[1]);
  } else {
    return null;
  }
  if (urls[1]) {
    forEach(urls[1].split('&'), function (q) {
      if (q != 'action=logout' && q != 'action=login' && !p1.test(q)) {
        new_q.push(q);
      }
    });
  }
  return new_q.join('&');
};
function selectMenu(module) {
  if ($E('topmenu')) {
    var tmp = false;
    forEach($E('topmenu').getElementsByTagName('li'), function (item, index) {
      var cs = new Array();
      if (index == 0) {
        tmp = item;
      }
      forEach(this.className.split(' '), function (c) {
        if (c == module) {
          tmp = false;
          cs.push(c + ' select');
        } else if (c !== '' && c != 'select' && c != 'default') {
          cs.push(c);
        }
      });
      this.className = cs.join(' ');
    });
    if (tmp) {
      $G(tmp).addClass('default');
    }
  }
}
function inintIndex() {
  $G(window).Ready(function () {
    if (G_Lightbox === null) {
      G_Lightbox = new GLightbox();
    } else {
      G_Lightbox.clear();
    }
    forEach($E('content').getElementsByTagName('img'), function (item, index) {
      if (!$G(item).hasClass('nozoom')) {
        new preload(item, function () {
          if (floatval(this.width) > floatval(item.width)) {
            G_Lightbox.add(item);
          }
        });
      }
    });
  });
}
function changeLanguage(lang) {
  $G(window).Ready(function () {
    forEach(lang.split(','), function () {
      $G('lang_' + this).addEvent('click', function (e) {
        GEvent.stop(e);
        window.location = replaceURL('lang', this.title);
      });
    });
  });
}
function replaceURL(keys, values, url) {
  var patt = /^(.*)=(.*)$/;
  var ks = keys.toLowerCase().split(',');
  var vs = values.split(',');
  var urls = new Object();
  var u = url || window.location.href;
  var us2 = u.split('#');
  u = us2.length == 2 ? us2[0] : u;
  var us1 = u.split('?');
  u = us1.length == 2 ? us1[0] : u;
  if (us1.length == 2) {
    forEach(us1[1].split('&'), function () {
      hs = patt.exec(this);
      if (!hs || ks.indexOf(hs[1].toLowerCase()) == -1) {
        urls[this] = this;
      }
    });
  }
  if (us2.length == 2) {
    forEach(us2[1].split('&'), function () {
      hs = patt.exec(this);
      if (!hs || ks.indexOf(hs[1].toLowerCase()) == -1) {
        urls[this] = this;
      }
    });
  }
  var us = new Array();
  for (var p in urls) {
    us.push(urls[p]);
  }
  forEach(ks, function (item, index) {
    if (vs[index] && vs[index] != '') {
      us.push(item + '=' + vs[index]);
    }
  });
  u += '?' + us.join('&');
  return u;
}
function getWidgetNews(id, module, interval, callback) {
  var req = new GAjax();
  var _callback = function (xhr) {
    if (xhr.responseText !== '') {
      if ($E(id)) {
        var div = $G(id);
        div.setHTML(xhr.responseText);
        if (Object.isFunction(callback)) {
          callback.call(div);
        }
        if (loader) {
          loader.inint(div);
        }
      } else {
        req.abort();
      }
    }
  };
  var _getRequest = function () {
    return 'id=' + id;
  };
  if (interval == 0) {
    req.send(WEB_URL + 'widgets/' + module + '/getnews.php', 'id=' + id, _callback);
  } else {
    req.autoupdate(WEB_URL + 'widgets/' + module + '/getnews.php', floatval(interval), _getRequest, _callback);
  }
}
var fbLogin = function () {
  FB.login(function (response) {
    FB.api('/me', function (response) {
      if (!response.error) {
        if (!response.email || response.email == '') {
          alert(EMAIL_EMPTY);
        } else {
          var q = new Array();
          for (var prop in response) {
            q.push(prop + '=' + response[prop]);
          }
          send(WEB_URL + 'modules/member/fb_login.php', 'u=' + encodeURIComponent(getCurrentURL()) + '&data=' + encodeURIComponent(q.join('&')), function (xhr) {
            var ds = xhr.responseText.toJSON();
            if (ds) {
              if (ds[0].error) {
                alert(eval(ds[0].error));
              } else if (ds[0].isMember == 1) {
                if (ds[0].message) {
                  var data = {};
                  data['message'] = decodeURIComponent(ds[0].message);
                  if (ds[0].picture) {
                    data['picture'] = ds[0].picture;
                  }
                  data['link'] = WEB_URL + 'index.php';
                  FB.api('/me/feed', 'post', data);
                }
                if ($E('login_next')) {
                  ds[0].location = $E('login_next').value;
                }
                if (ds[0].location) {
                  if (ds[0].location == 'back') {
                    if (loader) {
                      loader.back();
                    } else {
                      window.history.go(-1);
                    }
                  } else {
                    window.location = ds[0].location;
                  }
                } else {
                  window.location = replaceURL('action', 'login');
                }
              }
            } else if (xhr.responseText != '') {
              alert(xhr.responseText);
            }
          });
        }
      }
    });
  }, {scope: 'email,user_birthday,publish_stream'});
};
function inintFacebook(appId, lng) {
  window.fbAsyncInit = function () {
    FB.init({
      appId: appId,
      cookie: false,
      xfbml: true,
      version: 'v2.1'
    });
  };
  (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id))
      return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/" + (lng == 'th' ? 'th_TH' : 'en_US') + "/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
}
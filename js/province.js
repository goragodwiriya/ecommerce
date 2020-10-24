/*
 gProvince
 ajax multi select
 design by http://www.goragod.com (goragod wiriya)
 17-11-54
 */
var gProvince = GClass.create();
gProvince.prototype = {
  initialize: function (options) {
    this.onchanged = options.onchanged || emptyFunction;
    this.onCountryChanged = options.countryChanged || emptyFunction;
    this.province = $G(options.province);
    this.district = $G(options.district);
    this.tambon = $G(options.tambon);
    if (options.zipcode) {
      this.zipcode = $G(options.zipcode);
    }
    if (options.country) {
      this.country = $G(options.country);
    }
    this.action = options.action || '';
    this.req = new GAjax();
    var self = this;
    var _doprovince = function () {
      var query = '';
      if (!Object.isNull(this) && this == self.province) {
        query = 'province=' + self.province.value;
      } else if (!Object.isNull(this) && this == self.district) {
        query = 'province=' + self.province.value + '&district=' + self.district.value;
      } else {
        query = 'province=' + self.province.value + '&district=' + self.district.value + '&tambon=' + self.tambon.value;
      }
      ;
      self.req.send(self.action, query, function (xhr) {
        var itemsNode = xhr.responseXML.getElementsByTagName('items')[0];
        var provinceNode = itemsNode.getElementsByTagName('province')[0].getElementsByTagName('*');
        self.populate(self.province, provinceNode, self.province.value);
        var districtNode = itemsNode.getElementsByTagName('district')[0].getElementsByTagName('*');
        self.populate(self.district, districtNode, self.district.value);
        var tambonNode = itemsNode.getElementsByTagName('tambon')[0].getElementsByTagName('*');
        self.populate(self.tambon, tambonNode, self.tambon.value);
        if (options.zipcode) {
          var zipcodenNode = itemsNode.getElementsByTagName('zipcode')[0].getElementsByTagName('*');
          self.populate(self.zipcode, zipcodenNode, self.zipcode.value);
        }
      });
    };
    var _docountry = function () {
      self.onCountryChanged.call(this);
    };
    var _dozipcode = function () {
      var id = self.zipcode.id.replace('ID', '');
      if ($E(id)) {
        $E(id).value = this.value;
      }
    };
    this.province.addEvent('change', _doprovince);
    this.district.addEvent('change', _doprovince);
    if (options.zipcode) {
      this.zipcode.addEvent('change', _dozipcode);
    }
    _doprovince(this.province);
    if (options.country) {
      this.country.addEvent('change', _docountry);
      _docountry.call(this.country);
    }
  },
  inintLoading: function (loading, center) {
    this.req.inintLoading(loading, center);
    return this;
  },
  populate: function (obj, items, select) {
    for (var i = obj.options.length - 1; i > 0; i--) {
      obj.removeChild(obj.options[i]);
    }
    ;
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
      ;
    }
    obj.selectedIndex = selectedIndex;
    obj.options[0].value = '';
  }
};
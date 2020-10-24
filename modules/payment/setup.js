// modules/payment/setup.js
function inintPaymentConfig(t1, t2, t3) {
  var _doAdd = function () {
    var patt = /pm_([0-9]+)/;
    var id = 0;
    forEach($E('payment_method').getElementsByTagName('div'), function () {
      if (hs = patt.exec(this.id)) {
        id = hs[1];
      }
    });
    var a = parseFloat(id) + 1;
    var row = '<div class="input-groups-table" id="pm_' + a + '">';
    row += '<label class="width" for="method_' + a + '">' + t1 + ' ' + (a + 1) + '</label>';
    row += '<span class="width"><img src="' + WEB_URL + 'modules/payment/img/bank.png" id="method_img_' + a + '" alt="bank" /></span>';
    row += '<span class="width g-input icon-edit"><input type="text" id="method_' + a + '" name="method[]" title="' + t2 + '" size="60" /></span>';
    row += '<label class="width"><input type="file" id="method_file_' + a + '" name="method_file[]" title="' + t3 + '" /></label>';
    row += '</div>';
    $G('pm_' + id).after(row.toDOM());
  };
  callClick('pm_add', _doAdd);
}
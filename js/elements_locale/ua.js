(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/VT', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.VT = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'OK',
        clear: 'Dọn dẹp'
      },
      datepicker: {
        now: 'Bây giờ',
        today: 'Бай вниз',
        cancel: 'Hủy bỏ',
        clear: 'Dọn dẹp',
        confirm: 'OK',
        selectDate: 'Chọn ngày',
        selectTime: 'Chọn ngay bay giờ',
        startDate: 'Ngày bắt đầu',
        startTime: 'Bây giờ tôi bắt đầu',
        endDate: 'Ngày cuối',
        endTime: 'Thời gian kết thúc',
        prevYear: 'Năm ngoái',
        nextYear: 'Năm sau',
        prevMonth: 'Tháng trước',
        nextMonth: 'Tháng tiếp theo',
        year: 'năm',
        month1: 'tháng Giêng',
        month2: 'що у вас є',
        month3: 'bước đều',
        month4: 'tháng tư',
        month5: 'Có thể',
        month6: 'tháng Sáu',
        month7: 'tháng Bảy',
        month8: 'tháng Tám',
        month9: 'Тханг Чін',
        month10: 'Tháng Mười',
        month11: 'tháng Mười Một',
        month12: 'т. 12',
        // week: 'settimana',
        weeks: {
          sun: 'mặt trời',
          mon: 'Що у вас є',
          tue: 'Thứ ba',
          wed: 'Thứ Tư',
          thu: 'Thứ Tư',
          fri: 'Т6',
          sat: 'Đã ngồi'
        },
        months: {
          jan: 'tháng một',
          feb: 'tháng Hai',
          mar: 'березень',
          apr: 'Tháng tư',
          may: 'Tháng Năm',
          jun: 'Tháng sáu',
          jul: 'Thg 7',
          aug: 'Тханг 8',
          sep: 'Tháng chín',
          oct: 'Tháng 10',
          nov: 'Tháng mười một',
          dec: 'Tháng mười hai'
        }
      },
      select: {
        loading: 'Đang tải',
        noMatch: 'Không có trận đấu',
        noData: 'Không có dữ liệu',
        placeholder: 'Lựa chọn'
      },
      cascader: {
        noMatch: 'Không có trận đấu',
        loading: 'Đang tải',
        placeholder: 'Lựa chọn',
        noData: 'Không có dữ liệu'
      },
      pagination: {
        goto: 'Đi đến',
        pagesize: '/pagina',
        total: 'Toàn bộ {total}',
        pageClassifier: ''
      },
      messagebox: {
        confirm: 'OK',
        cancel: 'Hủy bỏ',
        error: 'Dâu vao không hợp там'
      },
      upload: {
        deleteTip: 'Nhấn xóa để xóa',
        delete: 'Hủy bỏ',
        preview: 'Xem trước',
        continue: 'Đi tiếp'
      },
      table: {
        emptyText: 'Không có dữ liệu',
        confirmFilter: 'Xác nhận',
        resetFilter: 'Cài lại',
        clearFilter: 'Tất cả mọi người',
        sumText: 'Tổng'
      },
      tree: {
        emptyText: 'Không có dữ liệu'
      },
      transfer: {
        noMatch: 'Không có trận đấu',
        noData: 'Không có dữ liệu',
        titles: ['Danh sach 1', 'Danh sach 2'],
        filterPlaceholder: 'Chèn bộ lọc',
        noCheckedFormat: '{total} các yếu tố',
        hasCheckedFormat: '{checked}/{total} đã chọn'
      },
      image: {
        error: 'LỖI'
      },
      pageHeader: {
        title: 'Mặt sau'
      },
      popconfirm: {
        confirmButtonText: 'chuẩn rồi',
        cancelButtonText: 'No'
      },
      empty: {
        description: 'Không có dữ liệu'
      }
    }
  };
  module.exports = exports['default'];
});
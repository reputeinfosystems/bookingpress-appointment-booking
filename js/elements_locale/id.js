(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/id', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.id = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'Oke',
        clear: 'Jernih'
      },
      datepicker: {
        now: 'Sekarang',
        today: 'Hari ini',
        cancel: 'Membatalkan',
        clear: 'Bersih',
        confirm: 'Oke',
        selectDate: 'pilih tanggal',
        selectTime: 'Pilih waktu',
        startDate: 'Tanggal Mulai',
        startTime: 'Waktu Mulai',
        endDate: 'Tanggal Berakhir',
        endTime: 'Waktu Akhir',
        prevYear: 'Tahun sebelumnya',
        nextYear: 'Tahun depan',
        prevMonth: 'Bulan sebelumnya',
        nextMonth: 'Bulan depan',
        year: 'Tahun',
        month1: 'Januari',
        month2: 'Februari',
        month3: 'Maret',
        month4: 'April',
        month5: 'Mei',
        month6: 'Juni',
        month7: 'Juli',
        month8: 'Agustus',
        month9: 'September',
        month10: 'Oktober',
        month11: 'November',
        month12: 'Desember',
        // week: 'minggu',
        weeks: {
          sun: 'Min',
          mon: 'Sen',
          tue: 'Sel',
          wed: 'Rab',
          thu: 'Kam',
          fri: 'Jum',
          sat: 'Sab'
        },
        months: {
          jan: 'Jan',
          feb: 'Feb',
          mar: 'Mar',
          apr: 'Apr',
          may: 'Mei',
          jun: 'Jun',
          jul: 'Jul',
          aug: 'Agu',
          sep: 'Sep',
          oct: 'Okt',
          nov: 'Nov',
          dec: 'Des'
        }
      },
      select: {
        loading: 'Pemuatan',
        noMatch: 'Tidak ada data yang cocok',
        noData: 'Tidak ada data',
        placeholder: 'Pilih'
      },
      cascader: {
        noMatch: 'Tidak ada data yang cocok',
        loading: 'Memuat',
        placeholder: 'Pilih',
        noData: 'Tidak ada data'
      },
      pagination: {
        goto: 'Pergi ke',
        pagesize: '/halaman',
        total: 'Total {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Pesan',
        confirm: 'Oke',
        cancel: 'Membatalkan',
        error: 'Masukan ilegal'
      },
      upload: {
        deleteTip: 'tekan hapus untuk menghapus',
        delete: 'Menghapus',
        preview: 'Pratinjau',
        continue: 'Melanjutkan'
      },
      table: {
        emptyText: 'Tidak ada data',
        confirmFilter: 'Mengonfirmasi',
        resetFilter: 'Mengatur ulang',
        clearFilter: 'Semua',
        sumText: 'Jml'
      },
      tree: {
        emptyText: 'Tidak ada data'
      },
      transfer: {
        noMatch: 'Tidak ada data yang cocok',
        noData: 'Tidak ada data',
        titles: ['Daftar 1', 'Daftar 2'],
        filterPlaceholder: 'Masukkan kata kunci',
        noCheckedFormat: '{total} item',
        hasCheckedFormat: '{checked}/{total} diperiksa'
      },
      image: {
        error: 'GAGAL'
      },
      pageHeader: {
        title: 'Punggung'
      },
      popconfirm: {
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
      },
      empty: {
        description: 'Tidak ada data'
      }
    }
  };
  module.exports = exports['default'];
});
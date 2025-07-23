(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/hr', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.hr = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'OK',
        clear: 'Čisto'
      },
      datepicker: {
        now: 'Sada',
        today: 'Danas',
        cancel: 'Otkaži',
        clear: 'Čisto',
        confirm: 'OK',
        selectDate: 'Odaberi datum',
        selectTime: 'Odaberite Vrijeme',
        startDate: 'Početni datum',
        startTime: 'Vrijeme početka',
        endDate: 'Datum završetka',
        endTime: 'Vrijeme završetka',
        prevYear: 'Prošla godina',
        nextYear: 'Sljedeća godina',
        prevMonth: 'Prethodni mjesec',
        nextMonth: 'Sljedeći mjesec',
        year: '',
        month1: 'Siječanj',
        month2: 'Veljača',
        month3: 'Ožujak',
        month4: 'Travanj',
        month5: 'Svibanj',
        month6: 'Lipanj',
        month7: 'Srpanj',
        month8: 'Kolovoz',
        month9: 'Rujan',
        month10: 'Listopad',
        month11: 'Studeni',
        month12: 'Prosinac',
        week: 'tjedan',
        weeks: {
          sun: 'Ned',
          mon: 'Pon',
          tue: 'Uto',
          wed: 'Sri',
          thu: 'Čet',
          fri: 'Pet',
          sat: 'Sub'
        },
        months: {
          jan: 'Jan',
          feb: 'Feb',
          mar: 'Mar',
          apr: 'Apr',
          may: 'May',
          jun: 'Jun',
          jul: 'Jul',
          aug: 'Aug',
          sep: 'Sep',
          oct: 'Oct',
          nov: 'Nov',
          dec: 'Dec'
        }
      },
      select: {
        loading: 'Učitavanje',
        noMatch: 'Nema pronađenih podataka',
        noData: 'Nema podataka',
        placeholder: 'Izaberi'
      },
      cascader: {
        noMatch: 'Nema pronađenih podataka',
        loading: 'Učitavanje',
        placeholder: 'Izaberi',
        noData: 'Nema podataka'
      },
      pagination: {
        goto: 'Idi na',
        pagesize: '/stranica',
        total: 'Ukupno {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Poruka',
        confirm: 'OK',
        cancel: 'Otkaži',
        error: 'Pogrešan unos'
      },
      upload: {
        deleteTip: 'pritisnite izbriši za brisanje',
        delete: 'Izbriši',
        preview: 'Pregled',
        continue: 'Nastaviti'
      },
      table: {
        emptyText: 'Nema podataka',
        confirmFilter: 'Potvrdi',
        resetFilter: 'Resetiraj',
        clearFilter: 'Sve',
        sumText: 'Suma'
      },
      tree: {
        emptyText: 'Nema podataka'
      },
      transfer: {
        noMatch: 'Nema podudarnih podataka',
        noData: 'Nema podataka',
        titles: ['Lista 1', 'Lista 2'], // to be translated
        filterPlaceholder: 'Unesite ključnu riječ', // to be translated
        noCheckedFormat: '{total} stavke', // to be translated
        hasCheckedFormat: '{checked}/{total} provjeren' // to be translated
      },
      image: {
        error: 'NEUSPJEH' // to be translated
      },
      pageHeader: {
        title: 'leđa' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'Da', // to be translated
        cancelButtonText: 'Ne' // to be translated
      },
      empty: {
        description: 'Nema podataka'
      }
    }
  };
  module.exports = exports['default'];
});
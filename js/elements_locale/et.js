(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/et', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.et = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';
  
  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'OK',
        clear: 'Selge'
      },
      datepicker: {
        now: 'Nüüd',
        today: 'Täna',
        cancel: 'Tühista',
        clear: 'Selge',
        confirm: 'OK',
        selectDate: 'Valige kuupäev',
        selectTime: 'Valige aeg',
        startDate: 'Algus kuupäev',
        startTime: 'Algusaeg',
        endDate: 'Lõppkuupäev',
        endTime: 'Lõpuaeg',
        prevYear: 'Eelmine aasta',
        nextYear: 'Järgmine aasta',
        prevMonth: 'Eelmine kuu',
        nextMonth: 'Järgmine kuu',
        day: 'päev',
        week: 'Nädal',
        month: 'Kuu',
        year: '',
        month1: 'jaanuaril',
        month2: 'veebruar',
        month3: 'märtsil',
        month4: 'aprill',
        month5: 'mai',
        month6: 'juunini',
        month7: 'juulil',
        month8: 'august',
        month9: 'septembril',
        month10: 'oktoober',
        month11: 'novembril',
        month12: 'detsembril',
        weeks: {
          sun: 'pü',
          mon: 'es',
          tue: 'te',
          wed: 'ko',
          thu: 'ne',
          fri: 're',
          sat: 'la'
        },
        months: {
          jan: 'jaa',
          feb: 'vee',
          mar: 'mär',
          apr: 'apr',
          may: 'mai',
          jun: 'Juun',
          jul: 'Juul',
          aug: 'aug',
          sep: 'sep',
          oct: 'okt',
          nov: 'nov',
          dec: 'det'
        }
      },
      select: {
        loading: 'Laadimine',
        noMatch: 'Vastavaid andmeid pole',
        noData: 'Andmed puuduvad',
        placeholder: 'Valige'
      },
      cascader: {
        noMatch: 'Vastavaid andmeid pole',
        loading: 'Laadimine',
        placeholder: 'Valige',
        noData: 'Andmed puuduvad'
      },
      pagination: {
        goto: 'Minema',
        pagesize: '/leht',
        total: 'Kokku {total}',
        pageClassifier: ''
      },
      messagebox: {
        confirm: 'Okei',
        cancel: 'Tühista',
        error: 'Ebaseaduslik sisestus'
      },
      upload: {
        deleteTip: 'eemaldamiseks vajutage Kustuta',
        delete: 'Kustuta',
        preview: 'Eelvaade',
        continue: 'Jätka'
      },
      table: {
        emptyText: 'Andmed puuduvad',
        confirmFilter: 'Kinnita',
        resetFilter: 'Lähtesta',
        clearFilter: 'Kõik',
        sumText: 'Summa'
      },
      tree: {
        emptyText: 'Andmeid pole'
      },
      transfer: {
        noMatch: 'Vastavaid andmeid pole',
        noData: 'Andmed puuduvad',
        titles: ['Nimekiri 1', 'Nimekiri 2'],
        filterPlaceholder: 'Sisestage märksõna',
        noCheckedFormat: '{total} üksust',
        hasCheckedFormat: '{checked}/{total} kontrollitud'
      },
      image: {
        error: 'EBAÕNNESTUS' // to be translated
      },
      pageHeader: {
        title: 'tagasi' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'Jah', // to be translated
        cancelButtonText: 'Ei' // to be translated
      },
      empty: {
        description: 'Andmeid pole'
      }
    }
  };
  module.exports = exports['default'];
});
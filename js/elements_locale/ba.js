(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/ba', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.ba = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';
  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'uredu',
        clear: 'Jasno'
      },
      datepicker: {
        now: 'Ora',
        today: 'Danas',
        cancel: 'Otkaži',
        clear: 'Počisti',
        confirm: 'OK',
        selectDate: 'Odaberite datum',
        selectTime: 'Odaberite sada',
        startDate: 'Datum početka',
        startTime: 'Sada počinjem',
        endDate: 'Datum završetka',
        endTime: 'Vrijeme završetka',
        prevYear: 'Prošle godine',
        nextYear: 'Sljedeće godine',
        prevMonth: 'Prethodni mjesec',
        nextMonth: 'Sljedeći mjesec',
        year: 'godine',
        month1: 'Januar',
        month2: 'februar',
        month3: 'mart',
        month4: 'april',
        month5: 'maja',
        month6: 'juna',
        month7: 'Luglio',
        month8: 'jula',
        month9: 'septembra',
        month10: 'oktobar',
        month11: 'novembar',
        month12: 'decembar',
        // week: 'settimana',
        weeks: {
          sun: 'Ned',
          mon: 'pon',
          tue: 'uto',
          wed: 'sri',
          thu: 'čet',
          fri: 'pet',
          sat: 'Sat'
        },
        months: {
          jan: 'Jan',
          feb: 'feb',
          mar: 'mar',
          apr: 'apr',
          may: 'Maj',
          jun: 'Ispod',
          jul: 'jul',
          aug: 'Prije',
          sep: 'Set',
          oct: 'okt',
          nov: 'nov.',
          dec: 'dec'
        }
      },
      select: {
        loading: 'Učitavanje',
        noMatch: 'Nema poklapanja',
        noData: 'Nema podataka',
        placeholder: 'Odaberite'
      },
      cascader: {
        noMatch: 'Nema poklapanja',
        loading: 'Učitavanje',
        placeholder: 'Odaberite',
        noData: 'Nema podataka'
      },
      pagination: {
        goto: 'Idi',
        pagesize: '/pagina',
        total: 'Totale {total}',
        pageClassifier: ''
      },
      messagebox: {
        confirm: 'OK',
        cancel: 'Otkaži',
        error: 'Pogrešan unos'
      },
      upload: {
        deleteTip: 'Pritisnite delete da uklonite',
        delete: 'Otkaži',
        preview: 'Pregled',
        continue: 'Nastavi'
      },
      table: {
        emptyText: 'Nema podataka',
        confirmFilter: 'Potvrda',
        resetFilter: 'Resetovati',
        clearFilter: 'Svi',
        sumText: 'Suma'
      },
      tree: {
        emptyText: 'Nema podataka'
      },
      transfer: {
        noMatch: 'Nema poklapanja',
        noData: 'Nema podataka',
        titles: ['Lista 1', 'Lista 2'],
        filterPlaceholder: 'Umetnite filter',
        noCheckedFormat: '{total} elementi',
        hasCheckedFormat: '{checked}/{total} odabrano'
      },
      image: {
        error: 'GREŠKA'
      },
      pageHeader: {
        title: 'Backards'
      },
      popconfirm: {
        confirmButtonText: 'da',
        cancelButtonText: 'No'
      },
      empty: {
        description: 'Nema podataka'
      }
    }
  };
  module.exports = exports['default'];
});

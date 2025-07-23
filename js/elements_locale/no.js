(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/no', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.no = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'ok',
        clear: 'klar'
      },
      datepicker: {
        now: 'nå',
        today: 'i dag',
        cancel: 'Avbryt',
        clear: 'Klar',
        confirm: 'Ok',
        selectDate: 'Velg en dato',
        selectTime: 'Velg et tidspunkt',
        startDate: 'Startdato',
        startTime: 'Starttid',
        endDate: 'Sluttdato',
        endTime: 'Sluttid',
        prevYear: 'I fjor',
        nextYear: 'Neste år',
        prevMonth: 'Forrige måned',
        nextMonth: 'Neste måned',
        year: '',
        month1: 'januar',
        month2: 'februar',
        month3: 'mars',
        month4: 'april',
        month5: 'mai',
        month6: 'juni',
        month7: 'juli',
        month8: 'august',
        month9: 'september',
        month10: 'oktober',
        month11: 'november',
        month12: 'desember',
        // week: 'week',
        weeks: {
          sun: 'Søn',
          mon: 'Man',
          tue: 'Tir',
          wed: 'Ons',
          thu: 'Tor',
          fri: 'Fre',
          sat: 'Lør'
        },
        months: {
          jan: 'jan',
          feb: 'feb',
          mar: 'mar',
          apr: 'apr',
          may: 'mai',
          jun: 'jun',
          jul: 'jul',
          aug: 'aug',
          sep: 'sep',
          oct: 'okt',
          nov: 'nov',
          dec: 'dec'
        }
      },
      select: {
        loading: 'Laster',
        noMatch: 'Ingen resultater',
        noData: 'Ingen data',
        placeholder: 'plukke ut'
      },
      cascader: {
        noMatch: 'Ingen resultater',
        loading: 'Laster',
        placeholder: 'plukke ut',
        noData: 'Ingen data'
      },
      pagination: {
        goto: 'Gå til',
        pagesize: '/side',
        total: 'Totalt {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Beskjed',
        confirm: 'Bekrefte',
        cancel: 'Avbryt',
        error: 'Feil!'
      },
      upload: {
        deleteTip: 'trykk på Slett for å slette',
        delete: 'Å slå av',
        preview: 'forhåndsvisning',
        continue: 'Fortsette'
      },
      table: {
        emptyText: 'Ingen data',
        confirmFilter: 'Bekrefte',
        resetFilter: 'Å vaske',
        clearFilter: 'Alle',
        sumText: 'Total'
      },
      tree: {
        emptyText: 'Ingen data'
      },
      transfer: {
        noMatch: 'Ingen resultater',
        noData: 'Ingen data',
        titles: ['liste 1', 'liste 2'],
        filterPlaceholder: 'Skriv inn et nøkkelord',
        noCheckedFormat: '{total} varer',
        hasCheckedFormat: '{checked}/{total} valgt'
      },
      image: {
        error: 'Feil ved innlasting av bildet' // to be translated
      },
      pageHeader: {
        title: 'Å gå tilbake' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'Ja', // to be translated
        cancelButtonText: 'Nei' // to be translated
      },
      empty: {
        description: 'Ingen data'
      }
    }
  };
  module.exports = exports['default'];
});
(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/se', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.se = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';
  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'OK',
        clear: 'Klar'
      },
      datepicker: {
        now: 'Nu',
        today: 'I dag',
        cancel: 'Annullera',
        clear: 'Städa',
        confirm: 'OK',
        selectDate: 'Välj datum',
        selectTime: 'Välj nu',
        startDate: 'Start datum',
        startTime: 'Nu börjar jag',
        endDate: 'Slutdatum',
        endTime: 'Sluttid',
        prevYear: 'Förra året',
        nextYear: 'Nästa år',
        prevMonth: 'Förra månaden',
        nextMonth: 'Nästa månad',
        year: 'år',
        month1: 'januari',
        month2: 'februari',
        month3: 'Mars',
        month4: 'april',
        month5: 'Maj',
        month6: 'juni',
        month7: 'juli',
        month8: 'augusti',
        month9: 'september',
        month10: 'oktober',
        month11: 'november',
        month12: 'december',
        // week: 'settimana',
        weeks: {
          sun: 'Sol',
          mon: 'mån',
          tue: 'Mar',
          wed: 'ons',
          thu: 'Tors',
          fri: 'fre',
          sat: 'lö'
        },
        months: {
          jan: 'Jan',
          feb: 'feb',
          mar: 'Mar',
          apr: 'apr',
          may: 'Mag',
          jun: 'Juni',
          jul: 'jul',
          aug: 'Aug',
          sep: 'Sept.',
          oct: 'okt',
          nov: 'nov',
          dec: 'dec'
        }
      },
      select: {
        loading: 'Läser in',
        noMatch: 'Inga träffar',
        noData: 'Inga data',
        placeholder: 'Välj'
      },
      cascader: {
        noMatch: 'Inga träffar',
        loading: 'Läser in',
        placeholder: 'Välj',
        noData: 'Inga data'
      },
      pagination: {
        goto: 'Gå till',
        pagesize: '/pagina',
        total: 'Total {total}',
        pageClassifier: ''
      },
      messagebox: {
        confirm: 'OK',
        cancel: 'Annullera',
        error: 'Felaktig input'
      },
      upload: {
        deleteTip: 'Tryck på delete för att ta bort',
        delete: 'Annullera',
        preview: 'Förhandsvisning',
        continue: 'Fortsätt'
      },
      table: {
        emptyText: 'Inga data',
        confirmFilter: 'Bekräftelse',
        resetFilter: 'Återställa',
        clearFilter: 'Alla',
        sumText: 'Belopp'
      },
      tree: {
        emptyText: 'Inga data'
      },
      transfer: {
        noMatch: 'Inga träffar',
        noData: 'Inga data',
        titles: ['Lista 1', 'Lista 2'],
        filterPlaceholder: 'Sätt i filter',
        noCheckedFormat: '{total} element',
        hasCheckedFormat: '{checked}/{total} vald'
      },
      image: {
        error: 'MISSLYCKADES'
      },
      pageHeader: {
        title: 'tillbaka'
      },
      popconfirm: {
        confirmButtonText: 'Ja',
        cancelButtonText: 'Nej',
      },
      empty: {
        description: 'Inga data'
      }
    }
  };
  module.exports = exports['default'];
});
(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/sq', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {};
    global.ELEMENT.lang.sq = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'Ne rregull',
        clear: 'Qartë'
      },
      datepicker: {
        now: 'Tani',
        today: 'Sot',
        cancel: 'Anulo',
        clear: 'Qartë',
        confirm: 'Ne rregull',
        selectDate: 'Zgjidhni datën',
        selectTime: 'Zgjidhni kohën',
        startDate: 'Data e fillimit',
        startTime: 'Koha e nisjes',
        endDate: 'Data e përfundimit',
        endTime: 'Koha e Fundit',
        prevYear: 'Vitin e kaluar',
        nextYear: 'Vitin tjeter',
        prevMonth: 'Muajin e kaluar',
        nextMonth: 'Muajin tjeter',
        year: '',
        month1: 'janar',
        month2: 'shkurt',
        month3: 'marsh',
        month4: 'prill',
        month5: 'Maj',
        month6: 'qershor',
        month7: 'korrik',
        month8: 'gusht',
        month9: 'shtator',
        month10: 'tetor',
        month11: 'Nëntor',
        month12: 'dhjetor',
        week: 'javë',
        weeks: {
          sun: 'Diell',
          mon: 'Hënë',
          tue: 'marte',
          wed: 'e mërkurë',
          thu: 'e enjte',
          fri: 'e premte',
          sat: 'Shtu'
        },
        months: {
          jan: 'janar',
          feb: 'shkurt',
          mar: 'mars',
          apr: 'Prill',
          may: 'maj',
          jun: 'qershor',
          jul: 'korrik',
          aug: 'gusht',
          sep: 'shtator',
          oct: 'tetor',
          nov: 'nëntor',
          dec: 'dhjetor'
        }
      },
      select: {
        loading: 'Po ngarkohet',
        noMatch: 'Nuk ka të dhëna që përputhen',
        noData: 'Nuk ka të dhëna',
        placeholder: 'Zgjidhni'
      },
      cascader: {
        noMatch: 'Nuk ka të dhëna që përputhen',
        loading: 'Po ngarkohet',
        placeholder: 'Zgjidhni',
        noData: 'Nuk ka të dhëna'
      },
      pagination: {
        goto: 'Shkoni në',
        pagesize: '/faqe',
        total: 'Gjithsej {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Mesazh',
        confirm: 'Ne rregull',
        cancel: 'Anulo',
        error: 'Të dhëna të paligjshme'
      },
      upload: {
        deleteTip: 'Shtypni delete për ta hequr',
        delete: 'Fshi',
        preview: 'Parapamje',
        continue: 'Vazhdoni'
      },
      table: {
        emptyText: 'Nuk ka të dhëna',
        confirmFilter: 'ConfirmKonfirmo',
        resetFilter: 'Rivendos',
        clearFilter: 'Të gjitha',
        sumText: 'Shuma'
      },
      tree: {
        emptyText: 'Nuk ka të dhëna'
      },
      transfer: {
        noMatch: 'Nuk ka të dhëna që përputhen',
        noData: 'Nuk ka të dhëna',
        titles: ['Lista 1', 'Lista 2'], // to be translated
        filterPlaceholder: 'Fut fjalën kyçe', // to be translated
        noCheckedFormat: '{total} artikuj', // to be translated
        hasCheckedFormat: '{checked}/{total} të kontrolluara' // to be translated
      },
      image: {
        error: 'Dështoi'
      },
      pageHeader: {
        title: 'Mbrapa' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'po',
        cancelButtonText: 'Nr'
      },
      empty: {
        description: 'Nuk ka të dhëna'
      }
    }
  };
  module.exports = exports['default'];
});

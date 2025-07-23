(function (global, factory) {
    if (typeof define === "function" && define.amd) {
      define('element/locale/lv', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.lv = mod.exports;
    }
  })(this, function (module, exports) {
    'use strict';
  
    exports.__esModule = true;
    exports.default = {
      el: {
        colorpicker: {
          confirm: `Labi`,
          clear: 'Skaidrs'
        },
        datepicker: {
          now: 'Tagad',
          today: 'Šodien',
          cancel: 'Atcelt',
          clear: 'Skaidrs',
          confirm: `Labi`,
          selectDate: 'Seleccionar data',
          selectTime: 'Izvēlieties laiku',
          startDate: 'Sākuma datums',
          startTime: 'Sākuma laiks',
          endDate: 'Beigu datums',
          endTime: 'Beigu laiks',
          prevYear: 'Iepriekšējais gads',
          nextYear: 'Nākamgad',
          prevMonth: 'Iepriekšējais mēnesis',
          nextMonth: 'Nākammēnes',
          year: '',
          month1: 'Janvāris',
          month2: 'Februāris',
          month3: 'Marts',
          month4: 'Aprīlis',
          month5: 'Maijs',
          month6: 'Jūnijs',
          month7: 'Jūlijs',
          month8: 'Augusts',
          month9: 'Septembris',
          month10: 'Oktobris',
          month11: 'Novembris',
          month12: 'Decembris',
          // week: 'nedēļa',
          weeks: {
            sun: 'Svē', 
            mon: 'Pir',
            tue: 'Otr',              
            wed: 'Tre',
            thu: 'Cet',
            fri: 'Pie',
            sat: 'Ses'
          },
          months: {
            jan: 'Jan',
            feb: 'Feb',
            mar: 'Mart',
            apr: 'Apr',
            may: 'Mai',
            jun: 'Jūn',
            jul: 'Jūl',
            aug: 'Aug',
            sep: 'Sep',
            oct: 'Okt',
            nov: 'Nov',
            dec: 'Dec'
          }
        },
        select: {
          loading: 'iekraušana',
          noMatch: 'Nav datu, kas atbilstu',
          noData: 'Nav datu',
          placeholder: 'Izvēlieties'
        },
        cascader: {
          noMatch: 'Nav datu, kas atbilstu',
          loading: 'iekraušana',
          placeholder: 'Izvēlieties',
          noData: 'Nav datu'
        },
        pagination: {
          goto: 'Iet uz',
          pagesize: '/lappuse',
          total: 'Kopā: {total}',
          pageClassifier: ''
        },
        messagebox: {
          confirm: `Labi'`,
          cancel: 'Atcelt',
          error: 'Kļūda'
        },
        upload: {
          deleteTip: 'Noklikšķiniet uz dzēst, lai noņemtu',
          delete: 'Dzēst',
          preview: 'Priekšskatījums',
          continue: 'Turpināt'
        },
        table: {
          emptyText: 'Nav datu',
          confirmFilter: 'Apstiprināt',
          resetFilter: 'Atiestatīt',
          clearFilter: 'Viss',
          sumText: 'kopā'
        },
        tree: {
          emptyText: 'Nav datu'
        },
        transfer: {
          noMatch: 'Nav datu, kas atbilstu',
          noData: 'Nav datu',
          titles: ['1. saraksts', '2. saraksts'],
          filterPlaceholder: 'Ievadiet atslēgvārdu',
          noCheckedFormat: '{total} vienumi',
          hasCheckedFormat: 'Atlasīts {checked}/{total}'
        },
        image: {
          error: 'NEizdevās'
        },
        pageHeader: {
          title: 'Atpakaļ'
        },
        popconfirm: {
          confirmButtonText: 'Jā',
          cancelButtonText: 'Nē'
        },
        empty: {
          description: 'Nav datu'
        }
      }
    };
    module.exports = exports['default'];
  });
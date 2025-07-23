(function (global, factory) {
    if (typeof define === "function" && define.amd) {
      define('element/locale/he', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.he = mod.exports;
    }
  })(this, function (module, exports) {
    'use strict';
  
    exports.__esModule = true;
    exports.default = {
      el: {
        colorpicker: {
          confirm: 'OK',
          clear: 'chiaro'
        },
        datepicker: {
          now: 'עַכשָׁיו',
          today: 'היום',
          cancel: 'לְבַטֵל',
          clear: 'ברור',
          confirm: 'לְאַשֵׁר',
          selectDate: 'בחר תאריך',
          selectTime: 'בחר זמן',
          startDate: 'תאריך התחלה',
          startTime: 'שעת התחלה',
          endDate: 'תאריך סיום',
          endTime: 'זמן סיום',
          prevYear: 'שנה קודמת',
          nextYear: 'שנה הבאה',
          prevMonth: 'חודש הקודם',
          nextMonth: 'חודש הבא',
          year: 'שָׁנָה',
          month1: 'ינואר',
          month2: 'פברואר',
          month3: 'מרץ',
          month4: 'אפריל',
          month5: 'מאי',
          month6: 'יוני',
          month7: 'יולי',
          month8: 'אוגוסט',
          month9: 'ספטמבר',
          month10: 'אוקטובר',
          month11: 'נובמבר',
          month12: 'דצמבר',
          // week: 'settimana',
          weeks: {
            sun: 'ראשון',
            mon: 'שני',
            tue: 'שלישי',
            wed: 'רביעי',
            thu: 'חמישי',
            fri: 'שישי',
            sat: 'יום שבת'
          },
          months: {
            jan: 'ינ.',
            feb: 'פבר.',
            mar: 'מרץ',
            apr: 'אפר.',
            may: 'מאי',
            jun: 'יוני',
            jul: 'יולי',
            aug: 'אוג.',
            sep: 'ספט.',
            oct: 'אוק.',
            nov: 'נוב.',
            dec: 'דצ.'
          }
        },
        select: {
          loading: 'Caricamento',
          noMatch: 'Nessuna corrispondenza',
          noData: 'Nessun dato',
          placeholder: 'Seleziona'
        },
        cascader: {
          noMatch: 'Nessuna corrispondenza',
          loading: 'Caricamento',
          placeholder: 'Seleziona',
          noData: 'Nessun dato'
        },
        pagination: {
          goto: 'Vai a',
          pagesize: '/pagina',
          total: 'Totale {total}',
          pageClassifier: ''
        },
        messagebox: {
          confirm: 'OK',
          cancel: 'Annulla',
          error: 'Input non valido'
        },
        upload: {
          deleteTip: 'Premi cancella per rimuovere',
          delete: 'Cancella',
          preview: 'Anteprima',
          continue: 'Continua'
        },
        table: {
          emptyText: 'Nessun dato',
          confirmFilter: 'Conferma',
          resetFilter: 'Reset',
          clearFilter: 'Tutti',
          sumText: 'Somma'
        },
        tree: {
          emptyText: 'Nessun dato'
        },
        transfer: {
          noMatch: 'Nessuna corrispondenza',
          noData: 'Nessun dato',
          titles: ['Lista 1', 'Lista 2'],
          filterPlaceholder: 'Inserisci filtro',
          noCheckedFormat: '{total} elementi',
          hasCheckedFormat: '{checked}/{total} selezionati'
        },
        image: {
          error: 'ERRORE'
        },
        pageHeader: {
          title: 'Indietro'
        },
        popconfirm: {
          confirmButtonText: 'Sì',
          cancelButtonText: 'No'
        },
        empty: {
          description: 'Nessun dato'
        }
      }
    };
    module.exports = exports['default'];
  });
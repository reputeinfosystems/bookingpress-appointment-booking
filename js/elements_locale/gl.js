(function (global, factory) {
    if (typeof define === "function" && define.amd) {
      define('element/locale/gl', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.gl = mod.exports;
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
          now: 'agora',
          today: 'hoxe',
          cancel: 'cancelar',
          clear: 'claro',
          confirm: 'OK',
          selectDate: 'seleccionar data',
          selectTime: 'seleccionarHora',
          startDate: 'data de inicio',
          startTime: 'hora de inicio',
          endDate: 'endDate',
          endTime: 'tempo final',
          prevYear: 'ano anterior',
          nextYear: 'o próximo ano',
          prevMonth: 'mes anterior',
          nextMonth: 'o vindeiro més',
          year: 'Ano',
          month1: 'Xaneiro',
          month2: 'Febreiro',
          month3: 'Marzo',
          month4: 'Abril',
          month5: 'Maio',
          month6: 'Xuño',
          month7: 'Xullo',
          month8: 'Agosto',
          month9: 'Setembro',
          month10: 'Outubro',
          month11: 'Novembro',
          month12: 'Decembro',
          // week: 'settimana',
          weeks: {
            sun: 'Dom',
            mon: 'Lun',
            tue: 'Mar',
            wed: 'Mer',
            thu: 'Xov',
            fri: 'Ven',
            sat: 'Sab'
          },
          months: {
            jan: 'Xan',
            feb: 'Feb',
            mar: 'Mar',
            apr: 'Abr',
            may: 'Mai',
            jun: 'Xuñ',
            jul: 'Xul',
            aug: 'Ago',
            sep: 'Set',
            oct: 'Out',
            nov: 'Nov',
            dec: 'Dec'
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
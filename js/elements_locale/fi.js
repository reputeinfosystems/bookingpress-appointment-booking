(function (global, factory) {
    if (typeof define === "function" && define.amd) {
      define('element/locale/fi', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.fi = mod.exports;
    }
  })(this, function (module, exports) {
    'use strict';
  
    exports.__esModule = true;
    exports.default = {
      el: {
        colorpicker: {
          confirm: `Ok`,
          clear: 'Asia selvä'
        },
        datepicker: {
          now: 'Nyt',
          today: 'Tänään',
          cancel: 'Peruuttaa',
          clear: 'Asia selvä',
          confirm: `Ok`,
          selectDate: 'Valitse päivämäärä',
          selectTime: 'Valitse aika',
          startDate: 'Aloituspäivämäärä',
          startTime: 'Aloitusaika',
          endDate: 'Päättymispäivä',
          endTime: 'Loppu aika',
          prevYear: 'Edellinen vuosi',
          nextYear: 'Ensi vuonna',
          prevMonth: 'Edellinen kuukausi',
          nextMonth: 'Ensikuussa',
          year: '',
          month1: 'tammikuu',
          month2: 'helmikuu',
          month3: 'maaliskuu',
          month4: 'huhtikuu',
          month5: 'toukokuu',
          month6: 'kesäkuu',
          month7: 'heinäkuu',
          month8: 'elokuu',
          month9: 'syyskuu',
          month10: 'lokakuu',
          month11: 'marraskuu',
          month12: 'joulukuu',
          week: 'Viikko',
          weeks: {
            sun: 'sunn',
            mon: 'maan',
            tue: 'tiis',
            wed: 'kesk',
            thu: 'tors',
            fri: 'perj',
            sat: 'laua'
          },
          months: {
            jan: 'tam',
            feb: 'hel',
            mar: 'maa',
            apr: 'huh',
            may: 'tou',
            jun: 'kes',
            jul: 'hei',
            aug: 'elo',
            sep: 'syy',
            oct: 'lok',
            nov: 'mar',
            dec: 'jou'
          }
        },
        select: {
          loading: 'Ladataan',
          noMatch: 'Vastaavaa tietoa ei ole',
          noData: 'Ei ole tietoa',
          placeholder: 'Valita'
        },
        cascader: {
          noMatch: 'Vastaavaa tietoa ei ole',
          loading: 'Ladataan',
          placeholder: 'Valita',
          noData: 'Ei ole tietoa'
        },
        pagination: {
          goto: 'Mene',
          pagesize: '/sivu',
          total: 'yhteensä {total}',
          pageClassifier: ''
        },
        messagebox: {
          confirm: `okei`,
          cancel: 'peruuttaa',
          error: 'väärä sisääntulo'
        },
        upload: {
          deleteTip: 'Napsauta poista poistaaksesi',
          delete: 'poistamalla',
          preview: 'Esikatselu',
          continue: 'Jatkaa'
        },
        table: {
          emptyText: 'Ei ole tietoa',
          confirmFilter: 'Vahvistaa',
          resetFilter: 'Puhdas',
          clearFilter: 'aikeissa',
          sumText: 'aikeissa'
        },
        tree: {
          emptyText: 'Ei ole tietoa'
        },
        transfer: {
          noMatch: 'Vastaavaa tietoa ei ole',
          noData: 'Ei ole tietoa',
          titles: ['Lista 1', 'Lista 2'],
          filterPlaceholder: 'Kirjoita avainsana',
          noCheckedFormat: '{total} elementti',
          hasCheckedFormat: '{checked}/{total} valittu'
        },
        image: {
          error: 'SE ON Epäonnistunut'
        },
        pageHeader: {
          title: 'Takaisin'
        },
        popconfirm: {
          confirmButtonText: 'Joo',
          cancelButtonText: 'Ei'
        },
        empty: {
          description: 'Ei dataa'
        }
      }
    };
    module.exports = exports['default'];
  });
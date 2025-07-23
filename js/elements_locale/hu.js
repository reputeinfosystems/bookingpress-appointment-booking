(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/hu', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.hu = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'OK',
        clear: 'Egyértelmű'
      },
      datepicker: {
        now: 'Most',
        today: 'Ma',
        cancel: 'Megszünteti',
        clear: 'Egyértelmű',
        confirm: 'OK',
        selectDate: 'Válassza ki a dátumot',
        selectTime: 'Válassza ki az időt',
        startDate: 'Kezdő dátum',
        startTime: 'Kezdési idő',
        endDate: 'Befejezés dátuma',
        endTime: 'Idő vége',
        prevYear: 'Előző év',
        nextYear: 'Következő év',
        prevMonth: 'Előző hónap',
        nextMonth: 'Következő hónap',
        year: '',
        month1: 'Január',
        month2: 'Február',
        month3: 'Március',
        month4: 'Április',
        month5: 'Május',
        month6: 'Június',
        month7: 'Július',
        month8: 'Augusztus',
        month9: 'Szeptember',
        month10: 'Október',
        month11: 'November',
        month12: 'December',
        weeks: {
          sun: 'Vas',
          mon: 'Hét',
          tue: 'Ked',
          wed: 'Sze',
          thu: 'Csü',
          fri: 'Pén',
          sat: 'Szo'
        },
        months: {
          jan: 'Jan',
          feb: 'Feb',
          mar: 'Már',
          apr: 'Ápr',
          may: 'Máj',
          jun: 'Jún',
          jul: 'Júl',
          aug: 'Aug',
          sep: 'Szep',
          oct: 'Okt',
          nov: 'Nov',
          dec: 'Dec'
        }
      },
      select: {
        loading: 'Betöltés',
        noMatch: 'Nincs egyező adat',
        noData: 'Nincs adat',
        placeholder: 'Válassza ki'
      },
      cascader: {
        noMatch: 'Nincs egyező adat',
        loading: 'Betöltés',
        placeholder: 'Válassza ki',
        noData: 'Nincsenek adatok'
      },
      pagination: {
        goto: 'Ugrás',
        pagesize: '/oldal',
        total: 'Össz {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Üzenet',
        confirm: 'OK',
        cancel: 'Megszünteti',
        error: 'Illegális bevitel'
      },
      upload: {
        deleteTip: 'az eltávolításhoz nyomja meg a törlést',
        delete: 'Töröl',
        preview: 'Előnézet',
        continue: 'Folytatni'
      },
      table: {
        emptyText: 'Nincsenek adatok',
        confirmFilter: 'megerősít',
        resetFilter: 'Alaphelyzetbe állítás',
        clearFilter: 'Minden',
        sumText: 'Összeg'
      },
      tree: {
        emptyText: 'Nincsenek adatok'
      },
      transfer: {
        noMatch: 'Nincs egyező adat',
        noData: 'Nincsenek adatok',
        titles: ['Lista 1', 'Lista 2'],
        filterPlaceholder: 'Írja be a kulcsszót',
        noCheckedFormat: '{total} tételeket',
        hasCheckedFormat: '{checked}/{total} ellenőrizve'
      },
      image: {
        error: 'NEM SIKERÜLT' // to be translated
      },
      pageHeader: {
        title: 'Vissza' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'Igen', // to be translated
        cancelButtonText: 'Nem' // to be translated
      },
      empty: {
        description: 'Nincsenek adatok'
      }
    }
  };
  module.exports = exports['default'];
});
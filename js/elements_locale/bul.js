(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/bul', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.bul = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'Добре',
        clear: 'Ясно'
      },
      datepicker: {
        now: 'Сега',
        today: 'днес',
        cancel: 'Отмяна',
        clear: 'Ясно',
        confirm: 'Добре',
        selectDate: 'Изберете дата',
        selectTime: 'Изберете час',
        startDate: 'Начална дата',
        startTime: 'Начален час',
        endDate: 'Крайна дата',
        endTime: 'Крайно време',
        prevYear: 'Предходната година', // to be translated
        nextYear: 'Следващата година', // to be translated
        prevMonth: 'Предишен месец', // to be translated
        nextMonth: 'Следващият месец', // to be translated
        year: 'година',
        month1: 'януари',
        month2: 'февруари',
        month3: 'Март',
        month4: 'април',
        month5: 'Може',
        month6: 'юни',
        month7: 'Юли',
        month8: 'Август',
        month9: 'Септември',
        month10: 'октомври',
        month11: 'ноември',
        month12: 'декември',
        // week: 'setmana',
        weeks: {
          sun: 'слънце',
          mon: 'пн',
          tue: 'вт',
          wed: 'ср',
          thu: 'чт',
          fri: 'пт',
          sat: 'сб',
        },
        months: {
          jan: 'ян',
          feb: 'фев',
          mar: 'март',
          apr: 'апр',
          may: 'май',
          jun: 'юни',
          jul: 'юли',
          aug: 'авг',
          sep: 'септ',
          oct: 'окт',
          nov: 'ноемв',
          dec: 'дек'
        }
      },
      select: {
        loading: 'Зареждане',
        noMatch: 'Няма съвпадащи данни',
        noData: 'Няма данни',
        placeholder: 'Изберете'
      },
      cascader: {
        noMatch: 'Няма съвпадащи данни',
        loading: 'Зареждане',
        placeholder: 'Изберете',
        noData: 'Няма данни'
      },
      pagination: {
        goto: 'Отидете на',
        pagesize: '/страница',
        total: 'Обща сума {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Съобщение',
        confirm: 'Добре',
        cancel: 'Отмяна',
        error: 'Незаконно въвеждане'
      },
      upload: {
        deleteTip: 'Натиснете изтриване, за да премахнете',
        delete: 'Изтрий',
        preview: 'Визуализация',
        continue: 'Продължи'
      },
      table: {
        emptyText: 'Няма данни',
        confirmFilter: 'Потвърждение',
        resetFilter: 'Нулиране',
        clearFilter: 'всичко',
        sumText: 'Сума'
      },
      tree: {
        emptyText: 'Няма данни'
      },
      transfer: {
        noMatch: 'Няма съвпадащи данни',
        noData: 'Няма данни',
        titles: ['Списък 1', 'Списък 2'], // to be translated
        filterPlaceholder: 'Въведете ключова дума', // to be translated
        noCheckedFormat: '{обща сума} предмети', // to be translated
        hasCheckedFormat: '{проверено}/{обща сума} проверено' // to be translated
      },
      image: {
        error: 'СЕ ПРОВАЛИ' // to be translated
      },
      pageHeader: {
        title: 'обратно' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'да', // to be translated
        cancelButtonText: 'Не' // to be translated
      },
      empty: {
        description: 'Няма данни'
      }
    }
  };
  module.exports = exports['default'];
});
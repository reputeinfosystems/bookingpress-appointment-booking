(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/bg', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.bg = mod.exports;
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
        today: 'Днес',
        cancel: 'Отмяна',
        clear: 'Ясно',
        confirm: 'Добре',
        selectDate: 'Изберете дата',
        selectTime: 'Изберете времето',
        startDate: 'Начална дата',
        startTime: 'Начален час',
        endDate: 'Крайна дата',
        endTime: 'Краен час',
        prevYear: 'Предходната година', // to be translated
        nextYear: 'Следващата година', // to be translated
        prevMonth: 'Предишен месец', // to be translated
        nextMonth: 'Следващият месец', // to be translated
        year: 'година',
        month1: 'Януари',
        month2: 'Февруари',
        month3: 'Март',
        month4: 'Април',
        month5: 'Май',
        month6: 'Юни',
        month7: 'Юли',
        month8: 'Август',
        month9: 'Септември',
        month10: 'Октомври',
        month11: 'Ноември',
        month12: 'Декември',
        // week: 'Седмица',
        weeks: {
          sun: 'Нед',
          mon: 'Пон',
          tue: 'Вто',
          wed: 'Сря',
          thu: 'Чет',
          fri: 'Пет',
          sat: 'Съб'
        },
        months: {
          jan: 'Яну',
          feb: 'Фев',
          mar: 'Мар',
          apr: 'Апр',
          may: 'Май',
          jun: 'Юни',
          jul: 'Юли',
          aug: 'Авг',
          sep: 'Сеп',
          oct: 'Окт',
          nov: 'Ное',
          dec: 'Дек'
        }
      },
      select: {
        loading: 'Зареждане',
        noMatch: 'Няма съвпадащи данни',
        noData: 'Няма данни',
        placeholder: 'Изберете'
      },
      cascader: {
        noMatch: 'Няма намерени',
        loading: 'Зареждане',
        placeholder: 'Избери',
        noData: 'Няма данни'
      },
      pagination: {
        goto: 'Иди на',
        pagesize: '/страница',
        total: 'Общо {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Съобщение',
        confirm: 'Добре',
        cancel: 'Откажи',
        error: 'Невалидни данни'
      },
      upload: {
        deleteTip: 'press delete to remove', // to be translated
        deleteTip: 'натиснете изтриване, за да премахнете', // to be translated
        delete: 'Изтрий',
        preview: 'Предварителен преглед',
        continue: 'Продължи'
      },
      table: {
        emptyText: 'Няма данни',
        confirmFilter: 'Потвърди',
        resetFilter: 'Нулиране',
        clearFilter: 'Всички',
        sumText: 'Сума' // to be translated
      },
      tree: {
        emptyText: 'Няма данни'
      },
      transfer: {
        noMatch: 'Няма съвпадащи данни',
        noData: 'Няма данни',
        titles: ['Списък 1', 'Списък 2'], // to be translated
        filterPlaceholder: 'Въведете ключова дума', // to be translated
        noCheckedFormat: '{total} предмети', // to be translated
        hasCheckedFormat: '{checked}/{total} проверено' // to be translated
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
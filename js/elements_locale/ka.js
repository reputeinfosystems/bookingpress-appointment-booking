(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/ka', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.ka = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'კარგი',
        clear: 'ნათელი'
      },
      datepicker: {
        now: 'ახლა',
        today: 'დღეს',
        cancel: 'გაუქმება',
        clear: 'ნათელი',
        confirm: 'კარგი',
        selectDate: 'აირჩიეთ თარიღი',
        selectTime: 'აირჩიეთ დრო',
        startDate: 'Დაწყების თარიღი',
        startTime: 'დაწყების დრო',
        endDate: 'ვადის გასვლის თარიღი',
        endTime: 'Ჟამის აღსასრული',
        prevYear: 'Გასულ წელს',
        nextYear: 'Მომავალ წელს',
        prevMonth: 'Გასული თვე',
        nextMonth: 'Შემდეგი თვე',
        year: 'წელიწადი',
        month1: 'იანვარი',
        month2: 'თებერვალი',
        month3: 'მარტი',
        month4: 'აპრილი',
        month5: 'მაისი',
        month6: 'ივნისი',
        month7: 'ივლისი',
        month8: 'აგვისტო',
        month9: 'სექტემბერი',
        month10: 'ოქტომბერი',
        month11: 'ნოემბერი',
        month12: 'დეკემბერი',
        // week: 'εβδομάδα',
        weeks: {
          sun: 'კვი',
          mon: 'ორშ',
          tue: 'სამ',
          wed: 'ოთხ',
          thu: 'ხუთ',
          fri: 'პარ',
          sat: 'შაბ'
        },
        months: {
          jan: 'იან',
          feb: 'თებ',
          mar: 'მარ',
          apr: 'აპრ',
          may: 'მაისი',
          jun: 'ივნ',
          jul: 'ივლისი',
          aug: 'აგვ',
          sep: 'სექ',
          oct: 'ოქტ',
          nov: 'ნოემ',
          dec: 'დეკ'
        }
      },
      select: {
        loading: 'Ჩატვირთვა',
        noMatch: 'შესატყვისი მონაცემები არ არის',
        noData: 'Მონაცემები არ არის',
        placeholder: 'შერჩევა'
      },
      cascader: {
        noMatch: 'შედეგი არ იყო ნაპოვნი',
        loading: 'Ჩატვირთვა',
        placeholder: 'შერჩევა',
        noData: 'Მონაცემები არ არის'
      },
      pagination: {
        goto: 'გადართვა',
        pagesize: '/გვერდი',
        total: 'სულ {სულ}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'შეტყობინება',
        confirm: 'კარგი',
        cancel: 'ბათილად ცნობა',
        error: 'არასწორი შეყვანა'
      },
      upload: {
        deleteTip: 'წასაშლელად დააჭირეთ წაშლას',
        delete: 'წაშლა',
        preview: 'გადახედვა',
        continue: 'გააგრძელე'
      },
      table: {
        emptyText: 'Მონაცემები არ არის',
        confirmFilter: 'Დადასტურება',
        resetFilter: 'გადატვირთვა',
        clearFilter: 'ყველა',
        sumText: 'სულ'
      },
      tree: {
        emptyText: 'Მონაცემები არ არის'
      },
      transfer: {
        noMatch: 'შედეგი არ იყო ნაპოვნი',
        noData: 'Მონაცემები არ არის',
        titles: ['სია 1', 'სია 2'],
        filterPlaceholder: 'შეიყვანეთ საკვანძო სიტყვა',
        noCheckedFormat: '{სულ} ელემენტი',
        hasCheckedFormat: 'არჩეულია {შემოწმებული}/{სულ}'
      },
      image: {
        error: 'ვერ მოხერხდა' // to be translated
      },
      pageHeader: {
        title: 'უკან' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'დიახ', // to be translated
        cancelButtonText: 'არა' // to be translated
      },
      empty: {
        description: 'Მონაცემები არ არის'
      }
    }
  };
  module.exports = exports['default'];
});
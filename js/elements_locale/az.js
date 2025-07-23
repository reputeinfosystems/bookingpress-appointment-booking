(function (global, factory) {
    if (typeof define === "function" && define.amd) {
      define('element/locale/az', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.az = mod.exports;
    }
  })(this, function (module, exports) {
    'use strict';
  
    exports.__esModule = true;
    exports.default = {
      el: {
        colorpicker: {
          confirm: `tamam`,
          clear: 'Təmiz'
        },
        datepicker: {
          now: 'İndi',
          today: 'Bu gün',
          cancel: 'Ləğv et',
          clear: 'Təmiz',
          confirm: `tamam`,
          selectDate: 'Tarix seçin',
          selectTime: 'Vaxt seçin',
          startDate: 'Başlama tarixi',
          startTime: 'Başlama vaxtı',
          endDate: 'Bitmə vaxtı',
          endTime: 'Bitmə vaxtı',
          prevYear: 'Əvvəlki il',
          nextYear: 'Növbəti il',
          prevMonth: 'əvvəlki ay',
          nextMonth: 'Gələn ay',
          year: '',
          month1: 'Yanvar',
          month2: 'Fevral',
          month3: 'Mart',
          month4: 'Aprel',
          month5: 'May',
          month6: 'İyun',
          month7: 'İyul',
          month8: 'Avqust',
          month9: 'Sentyabr',
          month10: 'Oktyabr',
          month11: 'Noyabr',
          month12: 'Dekabr',
          // week: 'setmana',
          weeks: {
            sun: 'B.',
            mon: 'B.e',
            tue: 'C.A.',
            wed: 'Ç',
            thu: 'Cü.A',
            fri: 'Cü',
            sat: 'Şə'
          },
          months: {
            jan: 'Yan',
            feb: 'Fev',
            mar: 'Mart',
            apr: 'Apr',
            may: 'May',
            jun: 'İyun',
            jul: 'İyul',
            aug: 'Avq',
            sep: 'Sen',
            oct: 'Okt',
            nov: 'Noy',
            dec: 'Dek'
          }
        },
        select: {
          loading: 'yüklənir',
          noMatch: 'Uyğunlaşacaq məlumat yoxdur',
          noData: 'Məlumat yoxdur',
          placeholder: 'seçin'
        },
        cascader: {
          noMatch: 'Uyğunlaşacaq məlumat yoxdur',
          loading: 'yüklənir',
          placeholder: 'seçin',
          noData: 'Məlumat yoxdur'
        },
        pagination: {
          goto: 'Getmək',
          pagesize: '/səhifə',
          total: 'Cəmi {total}',
          pageClassifier: ''
        },
        messagebox: {
          confirm: `Tamam`,
          cancel: 'Ləğv et',
          error: 'Yanlış giriş'
        },
        upload: {
          deleteTip: 'silmək üçün sil düyməsini basın',
          delete: 'aradan qaldırmaq',
          preview: 'Önizləmə',
          continue: 'Davam et'
        },
        table: {
          emptyText: 'Məlumat yoxdur',
          confirmFilter: 'Təsdiq edin',
          resetFilter: 'Təmiz',
          clearFilter: 'qədər',
          sumText: 'qədər'
        },
        tree: {
          emptyText: 'Məlumat yoxdur'
        },
        transfer: {
          noMatch: 'Uyğunlaşacaq məlumat yoxdur',
          noData: 'Məlumat yoxdur',
          titles: ['Siyahı 1', 'Siyahı 2'],
          filterPlaceholder: 'Açar sözü daxil edin',
          noCheckedFormat: '{cəmi} element',
          hasCheckedFormat: '{checked}/{total} seçildi'
        },
        image: {
          error: 'HA FALLAT'
        },
        pageHeader: {
          title: 'Geri'
        },
        popconfirm: {
          confirmButtonText: 'Bəli',
          cancelButtonText: 'Yox'
        },
        empty: {
          description: 'Sense Dades'
        }
      }
    };
    module.exports = exports['default'];
  });
(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('element/locale/el', ['module', 'exports'], factory);
  } else if (typeof exports !== "undefined") {
    factory(module, exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, mod.exports);
    global.ELEMENT.lang = global.ELEMENT.lang || {}; 
    global.ELEMENT.lang.el = mod.exports;
  }
})(this, function (module, exports) {
  'use strict';

  exports.__esModule = true;
  exports.default = {
    el: {
      colorpicker: {
        confirm: 'Εντάξει',
        clear: 'σαφής'
      },
      datepicker: {
        now: 'Τώρα',
        today: 'Σήμερα',
        cancel: 'Ματαίωση',
        clear: 'σαφής',
        confirm: 'Εντάξει',
        selectDate: 'Επιλέξτε ημερομηνία',
        selectTime: 'Επιλέξτε ώρα',
        startDate: 'Ημερομηνία έναρξης',
        startTime: 'Ωρα έναρξης',
        endDate: 'Ημερομηνία Λήξης',
        endTime: 'Τέλος χρόνου',
        prevYear: 'Προηγούμενο Έτος',
        nextYear: 'Επόμενο Έτος',
        prevMonth: 'Προηγούμενος Μήνας',
        nextMonth: 'Επόμενος Μήνας',
        year: 'Έτος',
        month1: 'Ιανουάριος',
        month2: 'Φεβρουάριος',
        month3: 'Μάρτιος',
        month4: 'Απρίλιος',
        month5: 'Μάιος',
        month6: 'Ιούνιος',
        month7: 'Ιούλιος',
        month8: 'Αύγουστος',
        month9: 'Σεπτέμβριος',
        month10: 'Οκτώβριος',
        month11: 'Νοέμβριος',
        month12: 'Δεκέμβριος',
        // week: 'εβδομάδα',
        weeks: {
          sun: 'Κυρ',
          mon: 'Δευ',
          tue: 'Τρι',
          wed: 'Τετ',
          thu: 'Πεμ',
          fri: 'Παρ',
          sat: 'Σαβ'
        },
        months: {
          jan: 'Ιαν',
          feb: 'Φεβ',
          mar: 'Μαρ',
          apr: 'Απρ',
          may: 'Μαϊ',
          jun: 'Ιουν',
          jul: 'Ιουλ',
          aug: 'Αυγ',
          sep: 'Σεπ',
          oct: 'Οκτ',
          nov: 'Νοε',
          dec: 'Δεκ'
        }
      },
      select: {
        loading: 'Φόρτωση',
        noMatch: 'Δεν υπάρχουν δεδομένα που να ταιριάζουν',
        noData: 'Χωρίς δεδομένα',
        placeholder: 'Επιλογή'
      },
      cascader: {
        noMatch: 'Δεν βρέθηκαν αποτελέσματα',
        loading: 'Φόρτωση',
        placeholder: 'Επιλογή',
        noData: 'Χωρίς δεδομένα'
      },
      pagination: {
        goto: 'Μετάβαση σε',
        pagesize: '/σελίδα',
        total: 'Σύνολο {total}',
        pageClassifier: ''
      },
      messagebox: {
        title: 'Μήνυμα',
        confirm: 'Εντάξει',
        cancel: 'Ακύρωση',
        error: 'Άκυρη εισαγωγή'
      },
      upload: {
        deleteTip: 'πατήστε διαγραφή για κατάργηση',
        delete: 'Διαγραφή',
        preview: 'Προεπισκόπηση',
        continue: 'συνεχίσει'
      },
      table: {
        emptyText: 'Χωρίς Δεδομένα',
        confirmFilter: 'Επιβεβαίωση',
        resetFilter: 'Επαναφορά',
        clearFilter: 'Όλα',
        sumText: 'Σύνολο'
      },
      tree: {
        emptyText: 'Χωρίς Δεδομένα'
      },
      transfer: {
        noMatch: 'Δεν βρέθηκαν αποτελέσματα',
        noData: 'Χωρίς δεδομένα',
        titles: ['Λίστα 1', 'Λίστα 2'],
        filterPlaceholder: 'Εισάγετε λέξη-κλειδί',
        noCheckedFormat: '{total} είδη',
        hasCheckedFormat: '{checked}/{total} επιλεγμένα'
      },
      image: {
        error: 'ΑΠΕΤΥΧΕ' // to be translated
      },
      pageHeader: {
        title: 'Πίσω' // to be translated
      },
      popconfirm: {
        confirmButtonText: 'Ναί', // to be translated
        cancelButtonText: 'Οχι' // to be translated
      },
      empty: {
        description: 'Χωρίς Δεδομένα'
      }
    }
  };
  module.exports = exports['default'];
});
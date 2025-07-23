(function (global, factory) 
{
    if (typeof define === "function" && define.amd) {
      define('element/locale/is', ['module', 'exports'], factory);
    } else if (typeof exports !== "undefined") {
      factory(module, exports);
    } else {
      var mod = 
      {
        exports: {}
      };
      factory(mod, mod.exports);
      global.ELEMENT.lang = global.ELEMENT.lang || {}; 
      global.ELEMENT.lang.is = mod.exports;
    }
  })(this, function (module, exports) 
  {
    'use strict';
    exports.__esModule = true;
    exports.default = {
      el: 
      {
        colorpicker: 
        {
          confirm: 'Allt í lagi',
          clear: 'Hreinsa'
        },
        datepicker: 
        {
          now: 'Nú',
          today: 'Í dag',
          cancel: 'Hætta við',
          clear: 'skýr',
          confirm: 'Allt í lagi',
          selectDate: 'Veldu dagsetningu',
          selectTime: 'Veldu tíma',
          startDate: 'Upphafsdagur',
          startTime: 'Byrjunartími',
          endDate: 'Loka dagsetning',
          endTime: 'Lokatími',
          prevYear: 'Fyrra ár',
          nextYear: 'Á næsta ári',
          prevMonth: 'Fyrri mánuður',
          nextMonth: 'Næsta mánuði',
          year: 'ári',
          month1: 'janúar',
          month2: 'febrúar',
          month3: 'mars',
          month4: 'apríl',
          month5: 'maí',
          month6: 'júní',
          month7: 'júlí',
          month8: 'ágúst',
          month9: 'september',
          month10: 'október',
          month11: 'nóvember',
          month12: 'desember',
          week: 'vika',
          weeks: 
          {
              sun: 'Sun',
              mon: 'mán',
              tue: 'þri',
              wed: 'mið',
              thu: 'fim',
              fri: 'fös',
              sat: 'lau',
          },
          months: 
          {
              jan: 'Jan',
              feb: 'feb',
              mar: 'mar',
              apr: 'apr',
              may: 'maí',
              jun: 'júní',
              jul: 'júlí',
              aug: 'ágúst',
              sep: 'sept',
              oct: 'okt',
              nov: 'nóv',
              dec: 'des'
          }
        },
        select: {
          loading: 'Hleðsla',
          noMatch: 'Engin samsvarandi gögn',
          noData: 'Engin gögn',
          placeholder: 'Veldu'
        },
        cascader: {
          noMatch: 'Engin samsvarandi gögn',
          loading: 'Hleðsla',
          placeholder: 'Veldu',
          noData: 'Engin gögn'
        },
        pagination: 
				{
					goto: 'Fara til',
					pagesize: '/page',
					total: 'Samtals {alls}',
					pageClassifier: ''
				},
				messagebox: 
        {
					title: 'Skilaboð',
					confirm: 'Allt í lagi',
					cancel: 'Hætta við',
					error: 'Ólöglegt inntak'
				},
				upload: 
        {
					deleteTip: 'ýttu á delete til að fjarlægja',
					delete: 'Eyða',
					preview: 'Forskoðun',
					continue: 'Halda áfram'
				},
				table: 
        {
					emptyText: 'Engin gögn',
					confirmFilter: 'Staðfesta',
					resetFilter: 'Endurstilla',
					clearFilter: 'Allt',
					sumText: 'Summa'
				},
				tree: 
        {
					emptyText: 'Engin gögn'
				},
				transfer: 
        {
					noMatch: 'Engin samsvarandi gögn',
					noData: 'Engin gögn',
					titles: ['Listi 1', 'Listi 2'], // to be translated
					filterPlaceholder: 'Sláðu inn leitarorð', // to be translated
					noCheckedFormat: '{alls} hlutir', // to be translated
					hasCheckedFormat: '{athugað}/{alls} athugað' // to be translated
				},
				image: {
					error: 'MIKIÐ'
				},
				pageHeader: {
					title: 'Til baka' // to be translated
				},
				popconfirm: {
					confirmButtonText: 'Já',
					cancelButtonText: 'Nei'
				},
				empty: {
					description: 'Engin gögn'
				}
			}
		};
		module.exports     = exports['default'];
	}
);
  
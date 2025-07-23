(function (global, factory) {
	if (typeof define === "function" && define.amd) {
		define( 'element/locale/cs', ['module', 'exports'], factory );
	} else if (typeof exports !== "undefined") {
		factory( module, exports );
	} else {
		var mod = {
			exports: {}
		};
		factory( mod, mod.exports );
		global.ELEMENT.lang    = global.ELEMENT.lang || {};
		global.ELEMENT.lang.cs = mod.exports;
	}
})(
	this,
	function (module, exports) {
		'use strict';

		exports.__esModule = true;
		exports.default    = {
			el: {
				colorpicker: {
					confirm: 'OK',
					clear: 'Průhledná'
				},
				datepicker: {
					now: 'Nyní',
					today: 'Dnes',
					cancel: 'zrušení',
					clear: 'Průhledná',
					confirm: 'OK',
					selectDate: 'Vyberte datum',
					selectTime: 'Select time',
					startDate: 'Vyberte čas',
					startTime: 'Doba spuštění',
					endDate: 'Datum ukončení',
					endTime: 'Čas ukončení',
					prevYear: 'Minulý rok',
					nextYear: 'Příští rok',
					prevMonth: 'Předchozí měsíc',
					nextMonth: 'Příští měsíc',
					year: '',
					month1: 'leden',
					month2: 'Únor',
					month3: 'březen',
					month4: 'duben',
					month5: 'Smět',
					month6: 'červen',
					month7: 'červenec',
					month8: 'srpen',
					month9: 'září',
					month10: 'říjen',
					month11: 'listopad',
					month12: 'prosinec',
					week: 'týden',
					weeks: {
						sun: 'ne',
						mon: 'po',
						tue: 'út',
						wed: 'st',
						thu: 'čt',
						fri: 'pá',
						sat: 'so'
					},
					months: {
						jan: 'Jan',
						feb: 'února',
						mar: 'Mar',
						apr: 'dubna',
						may: 'Smět',
						jun: 'června',
						jul: 'července',
						aug: 'Aug',
						sep: 'září',
						oct: 'Oct',
						nov: 'listopad',
						dec: 'prosinec'
					}
				},
				select: {
					loading: 'načítání',
					noMatch: 'Žádné odpovídající údaje',
					noData: 'Žádná data',
					placeholder: 'Vybrat'
				},
				cascader: {
					noMatch: 'Žádné odpovídající údaje',
					loading: 'načítání',
					placeholder: 'Vybrat',
					noData: 'Žádná data'
				},
				pagination: {
					goto: 'Jít do',
					pagesize: '/strana',
					total: 'Celkem {total}',
					pageClassifier: ''
				},
				messagebox: {
					title: 'Zpráva',
					confirm: 'OK',
					cancel: 'zrušení',
					error: 'Nelegální vstup'
				},
				upload: {
					deleteTip: 'pro odstranění stiskněte Delete',
					delete: 'Vymazat',
					preview: 'Náhled',
					  continue: 'Pokračovat'
				},
				table: {
					emptyText: 'Žádná data',
					confirmFilter: 'Potvrdit',
					resetFilter: 'Resetovat',
					clearFilter: 'Všechno',
					sumText: 'Součet'
				},
				tree: {
					emptyText: 'Žádná data'
				},
				transfer: {
					noMatch: 'Žádné odpovídající údaje',
					noData: 'Žádná data',
					titles: ['Seznam 1', 'Seznam 2'], // to be translated
					filterPlaceholder: 'Zadejte klíčové slovo', // to be translated
					noCheckedFormat: '{total} položek', // to be translated
					hasCheckedFormat: '{checked}/{total} zkontrolováno' // to be translated
				},
				image: {
					error: 'SELHLA'
				},
				pageHeader: {
					title: 'Zadní' // to be translated
				},
				popconfirm: {
					confirmButtonText: 'Ano',
					cancelButtonText: 'Ne'
				},
				empty: {
					description: 'Žádná data'
				}
			}
		};
		module.exports     = exports['default'];
	}
);

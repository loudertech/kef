
/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Tag
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: base-source.js,v b2033fcf1822 2011/10/26 22:49:23 eduar $
 */

var Base = {

	PROTOTYPE: 1,
	JQUERY: 2,
	EXT: 3,
	MOOTOOLS: 4,

	activeFramework: 0,

	_onReadyCallbacks: [],

	bind: function(){
        var _func = arguments[0] || null;
        var _obj = arguments[1] || this;
        var i = 0;
        var _args = [];
        for(var i=0;i<arguments.length;i++){
        	if(i>1){
        		_args[_args.length] = arguments[i];
        	};
        	i++;
        };
        return function(){
			return _func.apply(_obj, _args);
        };
	},

	_checkFramework: function(){
		if(typeof Prototype != "undefined"){
			Base.activeFramework = Base.PROTOTYPE;
			return;
		};
		if(typeof jQuery != "undefined") {
			Base.activeFramework = Base.JQUERY;
			return;
		}
		if(typeof Ext != "undefined"){
			Base.activeFramework = Base.EXT;
			return;
		};
		if(typeof MooTools != "undefined"){
			Base.activeFramework = Base.MOOTOOLS;
			return;
		};
		return 0;
	},

	$: function(element){
		return document.getElementById(element);
	},

	show: function(element){
		document.getElementById(element).style.display = "";
	},

	hide: function(element){
		document.getElementById(element).style.display = "none";
	},

	setValue: function(element, value){
		document.getElementById(element).value = value;
	},

	getValue: function(element){
		element = document.getElementById(element);
		if(element.tagName=='SELECT'){
			return element.options[element.selectedIndex].value;
		} else {
			return element.value;
		}
	},

	up: function(element, levels){
		var l = 0;
		var finalElement = element;
		while(finalElement){
			finalElement = finalElement.parentNode;
			if(l>=levels){
				return finalElement;
			}
			l++;
		};
		return finalElement;
	},

	onReady: function(callback){
		Base._onReadyCallbacks[Base._onReadyCallbacks.length] = callback;
	},

	init: function(){
		Base._checkFramework();
		for(var i=0;i<Base._onReadyCallbacks.length;i++){
			Base._onReadyCallbacks[i]();
		}
	}

};

var NumericField = {

	maskNum: function(evt){
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		var kc = evt.keyCode;
		var ev = (evt.altKey==false)&&(evt.shiftKey==false)&&((kc>=48&&kc<=57)||(kc>=96&&kc<=105)||(kc==8)||(kc==9)||(kc==13)||(kc==17)||(kc==36)||(kc==35)||(kc==37)||(kc==46)||(kc==39)||(kc==190)||(kc==110));
		if(!ev){
			ev = (evt.ctrlKey==true&&(kc==67||kc==86||kc==88));
			if(!ev){
				ev = (evt.shiftKey==true&&(kc==9||(kc>=35&&kc<=39)));
				if(!ev){
					ev = (evt.altKey==true&&(kc==84||kc==82));
				}
			}
		};
		if(!ev){
			evt.preventDefault();
    		evt.stopPropagation();
    		evt.stopped = true;
		}
	},

	format: function(element){
		if(element.value!==''){
			var integerPart = '';
			var decimalPart = '';
			var decimalPosition = element.value.indexOf('.');
			if(decimalPosition!=-1){
				decimalPart = element.value.substr(decimalPosition);
				integerPart = element.value.substr(0, decimalPosition);
			} else {
				integerPart = element.value;
			};
			document.title = integerPart+' '+decimalPart;
		};
	}

};

var DateCalendar = {

	build: function(element, name, value){
		var year = parseInt(value.substr(0, 4), 10);
		var month = parseInt(value.substr(5, 2), 10);
		var day = parseInt(value.substr(8, 2), 10);
		DateCalendar._buildMonth(element, year, month, value);
	},

	_buildMonth: function(element, year, month, activeDate){
		var numberDays = DateField.getNumberDays(year, month);
		var firstDate = new Date(year, month-1, 1);
		var lastDate = new Date(year, month-1, numberDays);
		var html = '<table class="calendarTable" cellspacing="0">';
		html+='<tr><td class="arrowPrev"><img src="'+$Kumbia.path+'img/prevw.gif"/></td>';
		html+='<td colspan="5" class="monthName">'+DateCalendar.getMonthName(month)+'</td>';
		html+='<td class="arrowNext"><img src="'+$Kumbia.path+'img/nextw.gif"/></td></tr>';
		html+='<tr><th>Dom</th><th>Lun</th><th>Mar</th><th>Mie</th><th>Jue</th><th>Vie</th><th>Sáb</th></tr>';
		html+='<tr>';
		if(month==1){
			var numberDaysPast = DateField.getNumberDays(year-1, 12);
		} else {
			var numberDaysPast = DateField.getNumberDays(year-1, month-1);
		};
		var dayOfWeek = firstDate.getDay();
		for(var i=(numberDaysPast-dayOfWeek+1);i<numberDaysPast;i++){
			html+='<td class="outMonthDay">'+(i+1)+'</td>';
		};
		var numberDay = 1;
		var date;
		while(numberDay<=numberDays){
			if(month<10){
				date = year+'-0'+month+'-'+numberDay;
			} else {
				date = year+'-'+month+'-'+numberDay;
			}
			if(activeDate==date){
				html+='<td class="selectedDay" title="'+date+'">'+numberDay+'</td>';
			} else {
				html+='<td title="'+date+'">'+numberDay+'</td>';
			};
			if(dayOfWeek==6){
				html+='</tr><tr>';
				dayOfWeek = 0;
			} else {
				dayOfWeek++;
			};
			numberDay++;
		};
		numberDay = 1;
		if(dayOfWeek<7){
			for(var i=dayOfWeek;i<7;i++){
				html+='<td class="outMonthDay">'+numberDay+'</td>';
				numberDay++;
			};
		};
		html+='</tr></table>';

		var position = element.up(1).cumulativeOffset();
		var calendarDiv = document.getElementById('calendarDiv');
		if(calendarDiv){
			calendarDiv.parentNode.removeChild(calendarDiv);
		};
		calendarDiv = document.createElement('DIV');
		calendarDiv.id = 'calendarDiv';
		calendarDiv.addClassName('calendar');
		calendarDiv.update(html);
		calendarDiv.style.top = (position[1]+22)+'px';
		calendarDiv.style.left = (position[0])+'px';
		document.body.appendChild(calendarDiv);
		window.setTimeout(function(){
			new Event.observe(window, 'click', DateCalendar.removeCalendar);
		}, 150);
	},

	removeCalendar: function(event){
		if(event.target.tagName!='INPUT'&&event.target.tagName!='SELECT'){
			var calendarDiv = document.getElementById('calendarDiv');
			if(calendarDiv){
				calendarDiv.parentNode.removeChild(calendarDiv);
			};
			new Event.stopObserving(window, 'click', DateCalendar.removeCalendar);
		}
	},

	getMonthName: function(month){
		switch(month){
			case 1:
				return 'Enero';
			case 2:
				return 'Febrero';
			case 3:
				return 'Marzo';
			case 4:
				return 'Abril';
			case 5:
				return 'Mayo';
			case 6:
				return 'Junio';
			case 7:
				return 'Julio';
			case 8:
				return 'Agosto';
			case 9:
				return 'Septiembre';
		}
	}

};

var DateField = {

	_monthTable: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],

	_listeners: {},

	observe: function(elementName, eventName, procedure){
		if(typeof DateField._listeners[eventName] == "undefined"){
			DateField._listeners[eventName] = {};
		};
		DateField._listeners[eventName][elementName] = procedure;
	},

	fire: function(eventName, elementValue){
		if(typeof DateField._listeners[eventName] != "undefined"){
			for(var elementName in DateField._listeners[eventName]){
				DateField._listeners[eventName][elementName](elementValue);
			}
		}
	},

	getNumberDays: function(year, month){
		var numberDays = DateField._monthTable[month-1];
		if(month==2){
			if(parseInt(year, 10)%4==0){
				numberDays = 29;
			}
		};
		return numberDays;
	},

	getElement: function(name, context){
		if(typeof context == "undefined"){
			return Base.$(name);
		} else {
			return Base.up(context, 4).querySelector('#'+name);
		}
	},

	getValue: function(name, context){
		var element = DateField.getElement(name, context);
		if(element.tagName=='SELECT'){
			return element.options[element.selectedIndex].value;
		} else {
			return element.value;
		}
	},

	refresh: function(name, context){

		var html = '', n, numberDays;
		var year = DateField.getValue(name+'Year', context);
		var month = DateField.getValue(name+'Month', context);
		var day = DateField.getValue(name+'Day', context);
		var daySelect = DateField.getElement(name+'Day', context);

		var value = year+'-'+month+'-'+day;
		DateField.getElement(name, context).value = value;

		while(daySelect.lastChild){
			daySelect.removeChild(daySelect.lastChild);
		};
		if(month.substr(0, 1)=='0'){
			month = month.substr(1, 1);
		};
		var numberDays = DateField.getNumberDays(year, month);
		for(var i=1;i<=numberDays;++i){
			n = (i < 10) ? '0'+i : i;
			if(n==day){
				html+='<option value="'+n+'" selected="selected">'+n+'</option>';
			} else {
				html+='<option value="'+n+'">'+n+'</option>';
			}
		};
		daySelect.innerHTML = html;
		DateField.fire('change', value);
	},

	showCalendar: function(element, name){
		DateCalendar.build(element, name, Base.getValue(name));
	}

};

var Utils = {

	getKumbiaURL: function(url){
		if(typeof url == "undefined"){
			url = "";
		};
		if($Kumbia.app!=""){
			return $Kumbia.path+$Kumbia.app+"/"+url;
		} else {
			return $Kumbia.path+url;
		}
	},

	getAppURL: function(url){
		if(typeof url == "undefined"){
			url = "";
		};
		if($Kumbia.app!=""){
			return $Kumbia.path+$Kumbia.app+"/"+url;
		} else {
			return $Kumbia.path+url;
		}
	},

	getURL: function(url){
		if(typeof url == "undefined"){
			return $Kumbia.path;
		} else {
			return $Kumbia.path+url;
		}
	},

	redirectParentToAction: function(url){
		new Utils.redirectToAction(url, window.parent);
	},

	redirectOpenerToAction: function(url){
		new Utils.redirectToAction(url, window.opener);
	},

	redirectToAction: function(url, win){
		win = win ? win : window;
		win.location = Utils.getKumbiaURL() + url;
	},

	upperCaseFirst: function(str){
		var first = str.substring(0, 1).toUpperCase();
		return first+str.substr(1, str.length-1)
	},

	round: function(number, decimals){
		var decimalPlace = Math.pow(100, decimals);
		return Math.round(number * decimalPlace) / decimalPlace;
	}

};

function ajaxRemoteForm(form, up, callback){
	if(callback==undefined){
		callback = {};
	};
	new Ajax.Updater(up, form.action, {
		 method: "post",
		 asynchronous: true,
         evalScripts: true,
         onSuccess: function(transport){
			$(up).update(transport.responseText)
		},
		onLoaded: callback.before!=undefined ? callback.before: function(){},
		onComplete: callback.success!=undefined ? callback.success: function(){},
  		parameters: Form.serialize(form)
    });
  	return false;
};

var AJAX = {

	doRequest: function(url, options){
		var framework = Base.activeFramework;
		if(typeof options == 'undefined'){
			options = {};
		};
		switch(framework){
			case Base.PROTOTYPE:
				var callbackMap = {
					'before': 'onLoading',
					'success': 'onSuccess',
					'complete': 'onComplete',
					'error': 'onFailure'
				};
				$H(callbackMap).each(function(callback){
					if(typeof options[callback[0]] != 'undefined'){
						options[callback[1]] = function(procedure, transport){
							procedure.bind(this, transport.responseText)();
						}.bind(this, options[callback[0]]);
					}
				});
				return new Ajax.Request(url, options);
			case Base.JQUERY:
				var paramMap = {
					'method': 'type',
					'parameters': 'data',
					'asynchronous': 'async'
				};
				$.each(paramMap, function(index, value){
					if(typeof options[index] != 'undefined'){
						options[value] = options[index];
					}
				});
				options.url = url;
				return $.ajax(options);
			case Base.EXT:
				var paramMap = {
					'before': 'beforerequest',
					'error': 'failure',
					'parameters': 'params'
				};
				var index;
				for(index in paramMap){
					if(typeof options[index] != 'undefined'){
						options[paramMap[index]] = options[index];
					}
				};
				options.url = url;
				return Ext.Ajax.request(options);
			case Base.MOOTOOLS:
				var paramMap = {
					'parameters': 'data',
					'asynchronous': 'async',
					'before': 'onRequest',
					'success': 'onSuccess',
					'error': 'onFailure',
					'complete': 'onComplete'
				};
				var index;
				for(index in paramMap){
					if(typeof options[index] != 'undefined'){
						options[paramMap[index]] = options[index];
					}
				};
				options.url = url;
				var request = new Request(options);
				request.send();
				return request;
			break;
		};
	},

	update: function(url, element, options){
		if(typeof options == 'undefined'){
			options = {};
		};
		options.success = function(responseText){
			Base.$(element).innerHTML = responseText;
		};
		Base.bind(options.success, element, element);
		return AJAX.doRequest(url, options);
	}

};

AJAX.xmlRequest = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	return AJAX.doRequest(options.url, options)
};

AJAX.viewRequest = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	container = params.container;
	options.evalScripts = true;
	if(!document.getElementById(container)){
		throw "CoreError: DOM Container '"+container+"' no encontrado";
	};
	return AJAX.update(container, options.url, options);
};

AJAX.execute = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	return AJAX.doRequest(options.url, options)
}

AJAX.query = function(queryAction){
	var me;
	new Ajax.Request(Utils.getKumbiaURL(queryAction), {
		method: 'GET',
		asynchronous: false,
		onSuccess: function(transport){
			var xml = transport.responseXML;
			var data = xml.getElementsByTagName("data");
			if(Prototype.Browser.IE){
				xmlValue = data[0].text;
			} else {
				xmlValue = data[0].textContent;
			};
			me = xmlValue;
		}
	});
	return me;
};

var BaseUI = {

	autocomplete: function(inputId, url, change){
		if(Base.activeFramework==Base.JQUERY){
			$('#'+inputId).autocomplete({
				source: function(request, response){
					$.post(url, {data:request.term}, function(data){
						response($.map(data, function(item){
							return {
								value: item.key,
								label: item.value
							}
						}));
					}, "json");
				},
				minLength: 2,
				dataType: "json",
				cache: false,
				focus: function(event, ui){
					return false;
				},
				select: function(event, ui){
					var idField = event.target.attributes.id.value;
					var pkField = $('#'+idField.replace('_det',''));
					if(pkField){
						pkField.val(ui.item.value);
					}
					this.value = ui.item.label;
					return false;
				}
			})
		}
	}

};

if(document.addEventListener){
	document.addEventListener('DOMContentLoaded', Base.init, false);
} else {
	document.attachEvent('readystatechange', Base.init);
};


/** Kumbia - PHP Rapid Development Framework ***************************
*
* Copyright (C) 2005 Andrs Felipe Gutirrez (andresfelipe at vagoogle.net)
* NumberFormat: ProWebMasters.net based script
*
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*****************************************************************************/

var ComboTag = {

	allData: {},
	field: {},
	options: {},
	acField: {},
	
	autoCompleter: function(){
		var value = ComboTag.field.value;
		/*if(allData == {}){
			new Ajax.request();
		}*/
		ComboTag.acField.select('li').each(function(li) {
			if(!li.getAttribute('index').startsWith(value)||!li.getAttribute('detail').include(value)){
				li.hide();
			}else{
				li.show();
			}
		});
	},

	initialize: function(options, data) {
		ComboTag.field = $(options.fieldDetail);
		ComboTag.acField = $(options.acField);
		ComboTag.options = options;
		//ComboTag.allData = data;
		ComboTag.field.observe('keyup', ComboTag.autoCompleter);
		/*data.each(function (element){
			var li = document.createElement('LI');
			var detalle = element.detail.join(' ');
			li.update(element.index + ' - ' + detalle);
			li.setAttribute('index', element.index);
			li.setAttribute('detail', detalle);
			ComboTag.acField.appendChild(li);
		});*/
		ComboTag.acField.show();
	}

}

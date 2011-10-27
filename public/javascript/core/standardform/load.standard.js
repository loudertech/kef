
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
 * @category	Kumbia
 * @package		Core
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (C) 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright   Copyright (C) 2007-2007 Julian Cortes (andresfelipe at gmail.com)
 */

function save_master_data(action){

	//reportType
	var obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "reportType"
	if(document.fl.reportType) {
		obj.value = $("reportType").value;
	}
	$('saveDataForm').appendChild(obj)

	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "reportTypeField";
	if(document.fl.reportTypeField) {
		obj.value = $("reportTypeField").value;
	};
	$('saveDataForm').appendChild(obj)

	for(var i=0;i<Fields.length;i++){
		if($C(Fields[i])){
			obj = document.createElement("INPUT");
			obj.type = "hidden"
		};
		if($C(Fields[i]+"_up")){
			if($C(Fields[i]+"_up").value){
				obj = $C(Fields[i]+"_up");
				obj.style.display = "none";
			}
		};
		obj.name = "fl_"+Fields[i];
		if($C(Fields[i]).type=='checkbox'){
			obj.value = $C(Fields[i]).checked;
		} else {
			if($C(Fields[i]+"_up")){
				if(!$V(Fields[i]+"_up")){
					obj.value = $V(Fields[i]);
				}
			} else {
				obj.value = $C(Fields[i]).value;
			}
		};
		$('saveDataForm').appendChild(obj);
	}

	//Controller-Action
	if($Kumbia.app==""){
		$('saveDataForm').action = $Kumbia.path + document.fl.aaction.value + "/" + action;
	} else {
		$('saveDataForm').action = $Kumbia.path + $Kumbia.app + "/" + document.fl.aaction.value + "/" + action;
	};
	$('saveDataForm').submit();

}

function enable_form(){

	for(var i=0;i<Fields.length;i++){
		if($C(Fields[i])){
			$C(Fields[i]).enable();
		} else {
			alert('KumbiaError: El campo "'+Fields[i]+'" no existe');
			return;
		}
	};

	for(var i=0;i<DateFields.length;i++){
		$("xfl_"+DateFields[i]+'_Month_ID').enable();
		$("xfl_"+DateFields[i]+'_Day_ID').enable();
		$("xfl_"+DateFields[i]+'_Year_ID').enable();
	};

	for(var i=0;i<timeFields.length;i++){
		$("time"+timeFields[i]+"_hour").enable();
		$("time"+timeFields[i]+"_minutes").enable();
	};

	for(var i=0;i<imageFields.length;i++){
		$C(imageFields[i]+"_up").enable();
	};

	for(var i=0;i<emailFields.length;i++){
		$(emailFields[i]+'_email1').enable();
		$(emailFields[i]+'_email2').enable();
	};

	if($('aceptar')){
		$('aceptar').enable();
	};
	if($('cancelar')){
		$('cancelar').enable();
	}
}

function disable_form(){

	for(var i=0;i<Fields.length;i++){
		$C(Fields[i]).disable();
		if($("actAction").lang!='Modificar'){
			if($C(Fields[i]).tagName=="SELECT"){
				$C(Fields[i]).selectedIndex = 0;
				if($C(Fields[i]+"_helper")){
					cancel_helper(Fields[i]);
				}
			}
		}
	};

	for(var i=0;i<timeFields.length;i++){
		$("time"+timeFields[i]+"_hour").disable();
		$("time"+timeFields[i]+"_minutes").disable();
	};

	for(var i=0;i<imageFields.length;i++){
		$C(imageFields[i]+"_up").disable();
	};

	for(var i=0;i<DateFields.length;i++){
		$("xfl_"+DateFields[i]+'_Month_ID').disable();
		$("xfl_"+DateFields[i]+'_Day_ID').disable();
		$("xfl_"+DateFields[i]+'_Year_ID').disable();
	};

	for(var i=0;i<emailFields.length;i++){
		$(emailFields[i]+'_email1').disable();
		$(emailFields[i]+'_email2').disable();
	};

	if($("aceptar")) $("aceptar").disable();
	if($("cancelar")) $("cancelar").disable();
	if($("adiciona")) $("adiciona").enable();
	if($("consulta")) $("consulta").enable();
	if($("modifica")) $("modifica").disable();
	if($("visualiza")) $("visualiza").enable();
	if($("borra")) $("borra").disable();
	if($("reporte")) $("reporte").enable();
	if($("anterior")) $("anterior").enable();
	if($("primero")) $("primero").enable();
	if($("siguiente")) $("siguiente").enable();
	if($("ultimo")) $("ultimo").enable();
	if($("actAction").lang=='Modificar'||$("actAction").lang=='Borrar') {
		if($("modifica"))$("modifica").enable();
		if($("borra")) $("borra").enable();
	}
}

function enable_insert(obj, cleanForm){

	if(window.before_enable_insert){
		if(before_enable_insert()==false){
			return false
		}
	};

	enable_form();

	if(obj.value){
		$("actAction").lang = obj.lang
	} else {
		$("actAction").lang = obj
	};

	if(cleanForm!=1){
		for(var i=0;i<AddFields.length;i++){
			if($C(AddFields[i]).tagName=="SELECT"){
				$C(AddFields[i]).selectedIndex = 0;
			};
			if($C(AddFields[i]).tagName=="TEXTAREA"){
				$C(AddFields[i]).innerText = "";
			};
			if($C(AddFields[i]).tagName=="INPUT"){
				if($C(AddFields[i]).type!="hidden"){
					if($C(AddFields[i]).type=="checkbox"){
						$C(AddFields[i]).checked = false;
					} else {
						$C(AddFields[i]).value = "";
					}
				}
			}
		}
	};

	for(var i=0;i<AutoValuesFields.length;i++){
		$C(AutoValuesFields[i]).value = AutoValuesFFields[i];
	};

	for(var i=0;i<AutoFields.length;i++){
		$C(AutoFields[i]).readOnly = true;
	};

	for(var i=0;i<queryOnlyFields.length;i++){
		if($C(queryOnlyFields[i]).tagName!="SELECT"){
			$C(queryOnlyFields[i]).readOnly = true;
		} else{
			$C(queryOnlyFields[i]).disable();
		}
	};

	for(var i=0;i<queryOnlyDateFields.length;i++){
		$("xfl_"+queryOnlyDateFields[i]+'_Month_ID').disable();
		$("xfl_"+queryOnlyDateFields[i]+'_Day_ID').disable();
		$("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = true;
	}

	for(var i=0;i<AddFields.length;i++){
		if($C(AddFields[i]).disabled==false&&$C(AddFields[i]).readOnly==false){
			$C(AddFields[i]).activate();
			break;
		}
	}

	if($("adiciona")){
		$("adiciona").disable();
	};
	if($("consulta")){
		$("consulta").disable();
	};
	if($("reporte")){
		$("reporte").disable()
	}
	if($("visualiza")){
		$("visualiza").disable()
	}

	if(window.after_enable_insert){
		if(after_enable_insert()==false){
			return false
		}
	}
}


function enable_update(obj, cleanForm){

	if(window.before_enable_update){
		if(before_enable_update()==false){
			return false
		}
	}

	if($("anterior")) {
		$("anterior").disable();
	}
	if($("primero")) {
		$("primero").disable();
	}
	if($("siguiente")) {
		$("siguiente").disable();
	}
	if($("ultimo")) {
		$("ultimo").disable();
	}

	$("actAction").lang = obj.lang

	for(var i=0;i<=UFields.length-1;i++){
		$C(UFields[i]).enable()
	}

	for(var i=0;i<=queryOnlyFields.length-1;i++){
		if($C(queryOnlyFields[i]).tagName!="SELECT"){
			$C(queryOnlyFields[i]).readOnly = true
		} else {
			$C(queryOnlyFields[i]).disable()
		}
	}

	for(var i=0;i<=DateFields.length-1;i++){
		$("xfl_"+DateFields[i]+'_Month_ID').enable()
		$("xfl_"+DateFields[i]+'_Day_ID').enable()
		$("xfl_"+DateFields[i]+'_Year_ID').enable()
	}

	for(var i=0;i<=queryOnlyDateFields.length-1;i++){
		$("xfl_"+queryOnlyDateFields[i]+'_Month_ID').enable()
		$("xfl_"+queryOnlyDateFields[i]+'_Day_ID').enable()
		$("xfl_"+queryOnlyDateFields[i]+'_Year_ID').enable()
	}

	for(i=0;i<=imageFields.length-1;i++){
		$C(imageFields[i]+"_up").disable()
	}

	for(var i=0;i<=emailFields.length-1;i++){
		$(emailFields[i]+'_email1').enable()
		$(emailFields[i]+'_email2').enable()
	}

	for(var i=0;i<=UFields.length-1;i++){
		if($C(UFields[i]).disabled==false&&$C(UFields[i]).readOnly==false){
			$C(UFields[i]).activate()
			break
		}
	}

	if($("modifica")){
		$("modifica").disable()
	}
	if($("borra")){
		$("borra").disable()
	}

	$("aceptar").enable()
	$("cancelar").enable()

	if(typeof window.after_enable_update != "undefined"){
		if(after_enable_update()==false){
			return false;
		}
	}

}

function enable_query(obj){

	if(window.before_enable_query){
		if(before_enable_query()==false){
			return false;
		}
	}

	enable_form();
	for(var i=0;i<=AutoFields.length-1;i++){
		$C(AutoFields[i]).readOnly = false
	}

	for(var i=0;i<=queryOnlyFields.length-1;i++){
		$C(queryOnlyFields[i]).readOnly = false
	}

	for(var i=0;i<=queryOnlyDateFields.length-1;i++){
		$("xfl_"+queryOnlyDateFields[i]+'_Month_ID').enable()
		$("xfl_"+queryOnlyDateFields[i]+'_Day_ID').enable()
		$("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = false
	}

	for(var i=0;i<=emailFields.length-1;i++){
		$(emailFields[i]+'_email1').value = ""
		$(emailFields[i]+'_email2').value = ""
		$(emailFields[i]+'_email1').enable()
		$(emailFields[i]+'_email2').enable()
	}

	$("actAction").lang = obj.lang
	for(var i=0;i<=Fields.length-1;i++){
		if($C(Fields[i]).tagName=="SELECT"){
			$C(Fields[i]).selectedIndex = 0
		}
		if($C(Fields[i]).tagName=="TEXTAREA"){
			$C(Fields[i]).innerText = ""
		}
		if($C(Fields[i]).tagName=="INPUT"){
			if($C(Fields[i]).type!="hidden"){
				if($C(Fields[i]).type=="checkbox"){
					$C(Fields[i]).checked = false
				} else {
					$C(Fields[i]).value = ""
				}
			}
		}
	}
	if($("adiciona")) {
		$("adiciona").disable()
	}
	if($("consulta")){
		$("consulta").disable()
	}
	if($("reporte")) {
		$("reporte").disable()
	}
	if($("visualiza")) {
		$("visualiza").disable()
	}

	if(typeof window.after_enable_query == "function"){
		if(after_enable_query()==false){
			return false;
		}
	}

}

function form_validation(){
	var not_null = []
	try {
		not_null.clear()
		for(i=0;i<=NotNullFields.length-1;i++){
			switch($C(NotNullFields[i]).tagName){
				case 'INPUT':
				case 'TEXTAREA':
				if($C(NotNullFields[i]).type!='hidden'){
					if(!$V(NotNullFields[i])){
						not_null.push(NotNullFields[i]);
					}
				}
				break;
				case 'SELECT':
				if($C(NotNullFields[i]+"_up")){
					if($C(NotNullFields[i]+"_up").visible()){
						if(!$V(NotNullFields[i])){
							not_null.push(NotNullFields[i]);
						}
					} else {
						if($V(NotNullFields[i])=='@'){
							not_null.push(NotNullFields[i]);
						}
					}
				} else {
					if($V(NotNullFields[i])=='@'){
						not_null.push(NotNullFields[i]);
					}
				}
				break;
			}
		}
		duration = 0.7;
		if(not_null.length>0){
			new Effect.ScrollTo($C(not_null.first()), {
				duration: 0.5,
				afterFinish: function(){
					not_null.each(function(item){
						if(item==not_null.first()){
							alert('El campo "'+eval("Labels."+item)+'" es Obligatorio');
							$C(item).select();
							$C(item).focus();
						}
						new Effect.Highlight($C(item), {
							duration: duration > 0.1 ? duration-=0.05 : 0.1,
							startcolor: "#FF0000"
						});
					});
				}
			});
			return false;
		}
	}
	catch(e){
		alert(e.message);
	}
}

function form_accept(){
	if($("actAction").lang=='Adicionar'){
		if(window.before_validation){
			if(before_validation()==false){
				return false;
			}
		};
		if(form_validation()==false){
			return false;
		};
		if(typeof window.after_validation == "function"){
			if(after_validation()==false){
				return false;
			}
		};
		if(typeof window.before_insert == "function"){
			if(before_insert()==false){
				return false;
			}
		};
		save_master_data('insert');
	};

	if($("actAction").lang=='Modificar'){
		if(window.before_validation){
			if(before_validation()==false){
				return false;
			}
		};
		if(form_validation()==false){
			return false;
		};
		if(window.after_validation){
			if(after_validation()==false){
				return false;
			}
		};
		if(window.before_update){
			if(before_update()==false){
				return false;
			}
		};
		save_master_data('update');
	};

	if($("actAction").lang=='Consultar'){
		if(typeof window.before_query == "function"){
			if(before_query()==false){
				return false;
			}
		};
		save_master_data('query');
	};

	if($("actAction").lang=='Reporte'){
		if(typeof window.before_report == "function"){
			if(before_report()==false){
				return false;
			}
		};
		save_master_data('report');
	};

	disable_form();
}

function cancel_form(){

	if(window.before_cancel_input){
		if(before_cancel_input($("actAction").lang)==false){
			return false;
		}
	}

	if($('actAction').value!='Modificar'&&$('actAction').value!='Borrar'){
		for(var i=0;i<Fields.length;i++){
			if($C(Fields[i]).tagName=="SELECT"){
				$C(Fields[i]).selectedIndex = 0
			};
			if($C(Fields[i]).tagName=="INPUT"){
				if($C(Fields[i]).type!="hidden"){
					$C(Fields[i]).value = $C(Fields[i]).defaultValue;
				}
			};
			$C(Fields[i]).className = "iNormal";
		}
	}
	for(var i=0;i<emailFields.length;i++){
		$(emailFields[i]+'_email1').value = $(emailFields[i]+'_email1').defaultValue;
		$(emailFields[i]+'_email2').value = $(emailFields[i]+'_email2').defaultValue;
	}

	disable_form();

	var reportOptions = $("reportOptions");
	reportOptions.parentNode.hide();
	reportOptions.hide();

	if(window.after_cancel_input){
		if(after_cancel_input($("actAction").lang)==false){
			return false;
		}
	}

}

function enable_delete(){
	if(window.before_delete){
		if(before_delete()==false){
			return false;
		}
	}
	if(confirm("¿Está seguro que desea borrar el registro?")){
		save_master_data('delete');
	}
}

function enable_report(obj){

	if(typeof window.before_enable_report != "undefined"){
		if(before_enable_report()==false){
			return false;
		}
	};

	enable_form();

	var reportOptions = $("reportOptions");
	reportOptions.parentNode.show();
	reportOptions.show();

	for(var i=0;i<AutoFields.length;i++){
		$C(AutoFields[i]).readOnly = false;
	};

	for(var i=0;i<queryOnlyFields.length;i++){
		$C(queryOnlyFields[i]).readOnly = false;
	};

	for(var i=0;i<queryOnlyDateFields.length;i++){
		$("xfl_"+queryOnlyDateFields[i]+'_Month_ID').enable();
		$("xfl_"+queryOnlyDateFields[i]+'_Day_ID').enable();
		$("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = false;
	};

	$("actAction").lang = obj.lang;
	for(var i=0;i<Fields.length;i++){
		var fieldId = "flid_"+Fields[i];
		var inputField = document.getElementById(fieldId);
		if(inputField.tagName=="SELECT"){
			inputField.selectedIndex = 0;
		} else {
			if(inputField.tagName=="INPUT"){
				if(inputField.type!="hidden"&&inputField.type=="checkbox"){
					inputField.checked = false;
				} else {
					inputField.value = "";
				}
			} else {
				inputField.value = "";
			}
		}
	};
	if($("adiciona")){
		$("adiciona").disable();
	};
	if($("consulta")){
		$("consulta").disable();
	};
	if($("reporte")){
		$("reporte").disable();
	};
	if($("visualiza")){
		$("visualiza").disable();
	};
	if(typeof window.after_enable_report != undefined){
		if(after_enable_report()==false){
			return false;
		}
	}

}

function show_upload_image(component){
	if($('actAction').lang=='Adicionar'||$('actAction').lang=='Modificar'){
		if($('a_'+component).innerHTML=='Subir Imagen'){
			$C(component).hide();
			$C(component+'_up').show();
			$C(component+'_up').enable();
			$('a_'+component).innerHTML = 'Cancelar';
		} else {
			$C(component).show();
			$C(component+'_up').disable();
			$C(component+'_up').hide();
			$('a_'+component).innerHTML = 'Subir Imagen';
		}
	}
}

function show_helper(helper){
	if(!$C(helper).disabled){
		$(helper+"_helper").value = ""
		$(helper+"_helper").show()
		$("helper_new_"+helper).hide()
		$("helper_save_"+helper).show()
		$("helper_cancel_"+helper).show()
		$C(helper).hide()
		$C(helper).selectedIndex = 0
		$(helper+"_helper").focus()
	}
}

function cancel_helper(helper){
	$(helper+"_helper").value = ""
	$(helper+"_helper").hide()
	$("helper_new_"+helper).show()
	$("helper_save_"+helper).hide()
	$("helper_cancel_"+helper).hide()
	$C(helper).show()
}

function save_helper(helper){
	if(!$F(helper+"_helper")){
		alert("El valor no puede ser nulo")
		$(helper+"_helper").activate()
		return
	}
	var url = $Kumbia.path+document.fl.aaction.value+"/_save_helper?name="+helper+"&valor="+$(helper+"_helper").value
	new Ajax.Request(url, {
		onLoaded: function() {
			$(helper+"_spinner").show()
		},
		onComplete: function() {
			$(helper+"_spinner").hide()
			new Ajax.Request($Kumbia.path+document.fl.aaction.value+"/_get_detail/?name="+helper+"&valor="+$(helper+"_helper").value, {
				asynchronous: false,
				onSuccess: function(resp){
					xml = resp.responseXML;
					items = xml.getElementsByTagName("row");
					while($C(helper).lastChild){
						$C(helper).removeChild($C(helper).lastChild);
					}
					option = document.createElement("OPTION");
					option.value = '@';
					if(document.all){
						option.innerText = 'Seleccione...';
					} else {
						option.text = 'Seleccione...';
					}
					$C(helper).appendChild(option)
					for(var i=0;i<=items.length-1;i++){
						option = document.createElement("OPTION");
						option.value = items[i].getAttribute('value');
						if(document.all){
							option.innerText = items[i].getAttribute('text');
						} else {
							option.text = items[i].getAttribute('text');
						}
						$C(helper).appendChild(option)
						if(items[i].getAttribute('selected')=="1"){
							$C(helper).selectedIndex = i+1;
						}
					}
				}
			});
			cancel_helper(helper);
		}
	})
}

function keep_action(action){
	switch(action){
		case 'insert':
		enable_insert($("adiciona"), 1);
		break;
		case 'update':
		enable_update($("modifica"), 1);
		break;
	}
}

new Event.observe(document, 'dom:loaded', function(){
	if(typeof window.after_form_load != "undefined"){
		after_form_load();
	};
});
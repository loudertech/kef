var mensajesCreados = new Array();
var totfilas=1;

var Validator = Class.create({

	_types: ['text','number','decimal','date','select','email','format_number','format_decimal','format_money','format_percent'],

	//Arreglo de errores: -1 -> error personal, 0 -> campo nulo, 1 -> fallo por minimo, 2 -> fallo por maximo, 3 -> no es un numero, 4 -> el formato de email es incorrecto
	_errors: ['El campo est&aacute; vac&iacute;o.','El valor del campo es inferior al m&iacute;nimo aceptado.','El valor del campo es superior al m&aacute;ximo aceptado.',
		'El valor no es num&eacute;rico.','El campo debe contener un email v&aacute;lido.'],

	_validate: {
		isNull: false, //Permite que el campo este vacio o no
		letZero: true, //Permite que el campo este en cero o no
		doDeFormat: true, //Realiza un deformateo de los campos tipo format automaticamente
		doTrim: true, //Realiza una funcion que quita espacios al principio y al final de la cadena de texto
		doToUpperCase: true, //Convierte a mayusculas el valor del campo
		doToLowerCase: false, //Convierte a mayusculas el valor del campo
		removeTags: true, //Remueve los tags de un campo tipo texto
		removeScripts: true, //Remueve los scripts de un campo tipo texto
		blankStrings: ['@'], //Matriz con todos los strings que serÃ¡n considerados como nulos
		valideRange: false, //Realiza validacion sobre rangos
		minimumNumber: Number.MIN_VALUE, //Minimo valor numerico a validar en el rango
		maximumNumber: Number.MAX_VALUE, //Maximo valor numerico a validar en el rango
		minimumDate: '0000/00/00', //Minimo valor de fecha a validar en el rango
		maximumDate: '9999/12/31', //Maximo valor de fecha a validar en el rango
		format: {}, //Objeto format de la clase Format para realizar deformateos automaticos
		value: null, //Valor que debe ser específico para un campo
		onErrorBlank: false //Si encuentra un error limpie el campo.
	},

	_fields: [],

	_names: [],

	_preDefinedOptions: {},

	error_messages: [],

	initialize: function(){
		options = !Object.isUndefined(arguments[0]) ? arguments[0] : { };
		Object.extend(this._preDefinedOptions,options);
		this._fields = new Array();
	},

	//Agrega campos para ser validados, Recibe un nombre, un tipo que debe estar entre los tipos definidos en el arreglo de tipos, como opcionales estan un selector para hallar el campo
	//de forma unica y si este no se especifica por defecto es igual a #{name} y por ultimo otro argumento opcional son las configuraciones personalizadas para la validacion de ese campo,
	//si no se especifica por defecto es el que se haya definido en el constructor para el tipo de campo que sea.
	addField: function(name,type){
		if(name.blank()) throw "Validator.addField\n * El nombre del campo no puede ser vacio: " + name + ".";
		if(this._names.indexOf(name) != -1) throw "Validator.addField\n * Ya existe un campo con ese nombre: " + name + ".";
		if(this._types.indexOf(type) == -1) throw "Validator.addField\n * No existe ese tipo: " + type + ".";
		selector = !Object.isUndefined(arguments[2]) && arguments[2] != null ? arguments[2] : "#" + name;
		if($$(selector).length == 0) throw "Validator.addField\n * El selector " + selector + " debe apuntar a un campo existente.";
		if($$(selector).length > 1) throw "Validator.addField\n * El selector " + selector + " debe apuntar a un campo unico.";
		var validate = {};
		Object.extend(validate,this._validate);
        if(type == 'email'){
            validate.doToUpperCase = false;
            validate.doToLowerCase = true;
        }
		if(!Object.isUndefined(this._preDefinedOptions[type]))
			Object.extend(validate,this._preDefinedOptions[type]);
		if(!Object.isUndefined(arguments[3]) && !Object.isUndefined(this._preDefinedOptions[arguments[3].group]))
			Object.extend(validate,this._preDefinedOptions[arguments[3].group]);
		if(!Object.isUndefined(arguments[3]))
			Object.extend(validate,arguments[3]);
		field = {'name': name, 'type': type,'options': validate, 'selector': selector};
		this._fields.push(field);
		//Object.extend(this._fields,field);
		this._names.push(name);
	},

	removeField: function(name){//Remueve un campo especifico de la lista de campos a validar.
		for(var i=0;i<this._fields.length;i++){
			if(this._fields[i].name == name) {
				this._fields = this._fields.splice(i,1);
				return;
			}
		}
		throw "Validator.removeField\n * No existe un campo con ese nombre: " + name + ".";
	},

	fieldExists: function(name){
		for(var i=0;i<this._fields.length;i++){
			if(this._fields[i].name == name) {
				return true;
			}
		}
		return false;
	},

    getField: function(name){
		for(var i=0;i<this._fields.length;i++){
			if(this._fields[i].name == name) {
				return this._fields[i];
			}
		}
		return false;
    },
	
	//Modifica las opciones por defecto para un tipo de campo o grupo de campos. Ademas tambien modifica las opciones para un campo especifico.
	//Returna true si la modificacion se pudo efectuar, false en caso contrario.
	modifyOptions: function(options,name){
		if(this._names.indexOf(name) != -1){
			for(var i=0;i<this._fields.length;i++){
				if(this._fields[i].name == name){
					Object.extend(this._fields[i].options,options);
					break;
				}
			}
		}else{
			if(Object.isUndefined(this._preDefinedOptions[name])) return false;
			Object.extend(this._preDefinedOptions[name],options);
			for(var i=0;i<this._fields.length;i++){
				if(this._fields[i].type == name || this._fields[i].options.group == name){
					Object.extend(this._fields[i].options,options);
				}
			}
		}
		return true;
	},

	valide: function(){//Realiza las validaciones.
		//Limpia los mensajes de error anteriores.
		var format = '';
		this.error_messages.clear();
		for(var i=0;i<this._fields.length;i++){
			var num_errors = this.error_messages.length;
			var campo = $$(this._fields[i].selector)[0];
			//Ejecuta las funciones definidas antes de validar y pre-validaciones.
			if(this._fields[i].options['beforeValidate'])
				this._fields[i].options['beforeValidate'](campo,this._fields[i].name);
			if(this._fields[i].options['myPreValidate'] && !this._fields[i].options['myPreValidate'](campo.value,this._fields[i].name))
				this._createError(this._fields[i],-1,this._fields[i].selector,this._fields[i].options['msg_error']);
			if(this._fields[i].type != "select" && this._fields[i].type != "number" && this._fields[i].type != "decimal"){
				if(this._fields[i].options.doTrim) campo.value = campo.value.strip();
				if(this._fields[i].options.doToLowerCase) campo.value = campo.value.toLowerCase();
				if(this._fields[i].options.doToUpperCase) campo.value = campo.value.toUpperCase();
				if(this._fields[i].options.removeTags) campo.value = campo.value.stripTags();
				if(this._fields[i].options.removeScripts) campo.value = campo.value.stripScripts();
			}
			var valor = 0;
			switch(this._fields[i].type){
				case "number": valor = !campo.value.blank() && !isNaN(campo.value) ? parseInt(campo.value) : 0; break;
				case "decimal": valor = !campo.value.blank() && !isNaN(campo.value) ? parseFloat(campo.value) : 0; break;
				case "format_number": format = 'numeric'; break;
				case "format_decimal": format = 'numeric'; break;
				case "format_money": format = 'money'; break;
				case "format_percent": format = 'percent'; break;
			}
			//Valida si un valor numerico lo es o no.
			if(this._fields[i].type.match(/^number|decimal*$/) && isNaN(campo.value))
				this._createError(this._fields[i],3,this._fields[i].selector);
			//Valida los rangos de un valor numerico.
			else if(this._fields[i].type.match(/^number|decimal$/) && this._fields[i].options.valideRange && !campo.value.empty()) {
				if(valor < this._fields[i].options.minimumNumber) 
					this._createError(this._fields[i],1,this._fields[i].selector);
				if(valor > this._fields[i].options.maximumNumber) 
					this._createError(this._fields[i],2,this._fields[i].selector);
			}else if(this._fields[i].type.match(/^format_(number|decimal|money)$/) && this._fields[i].options.valideRange && !campo.value.empty()) {
				valor = this._fields[i].options.format.execute('deFormat',campo.value);
				if(valor < this._fields[i].options.minimumNumber) 
					this._createError(this._fields[i],1,this._fields[i].selector);
				if(valor > this._fields[i].options.maximumNumber) 
					this._createError(this._fields[i],2,this._fields[i].selector);
			}
			//Valida si es un email correcto.
			if(this._fields[i].type.match(/^email$/) && !campo.value.match(/(^[a-z]([\w_\.]*)@([a-z_\.]*)([.][a-z]{3})([.][a-z]{2})?$)/i) && !campo.value.blank())
				this._createError(this._fields[i],4,this._fields[i].selector);
			//Realiza los deFormateos automaticos
			if(this._fields[i].type.match(/^format_[a-z]*$/) && Object.isFunction(this._fields[i].options.format.deFormat) && this._fields[i].options.doDeFormat) 
				campo.value = this._fields[i].options.format.deFormat(campo.value,format);
			//Verifica si el valor es nulo o no.
			if(!this._fields[i].options.isNull && campo.value.empty()) 
				this._createError(this._fields[i],0,this._fields[i].selector);
			//Verifica si es nulo teniendo en cuenta los caracteres definidos como nulos.
			if(!this._fields[i].options.isNull && this._fields[i].options.blankStrings.indexOf(campo.value) != -1)
				this._createError(this._fields[i],0,this._fields[i].selector);
			//Realiza validaciones estandar con la opcion include, que puede tener mas de un valor. include: Positive,Negative
			if(!Object.isUndefined(this._fields[i].options['include'])){
				var functions = this._fields[i].options['include'].split(",");
				for(var j=0;j<functions.length;j++){
					/*var funct = eval("this._"+functions[j]);
					f(!Object.isFunction(funct)) throw "Validator.Valide\n * No existe una validacion estandar con ese nombre: " + functions[j] + ".";
					var ret = funct(campo.value,this._fields[i].name,this._fields[i].type,this._fields[i].options['value']);*/
                    var ret = this.execute(campo.value,this._fields[i].name,this._fields[i].type,this._fields[i].options['value']);
					if(ret !== true){
						if(ret !== false){
							this._createError(this._fields[i],-1,this._fields[i].selector,ret);
						}
					}
				}
			}
			//Ejecuta las funciones definidas despues de validar y post-validaciones.
			if(this._fields[i].options['myPostValidate'] && !this._fields[i].options['myPostValidate'](campo.value,this._fields[i].name))
				this._createError(this._fields[i],-1,this._fields[i].selector,this._fields[i].options['msg_error']);
			if(this._fields[i].options['afterValidate'])
				this._fields[i].options['afterValidate'](campo,this._fields[i].name);
			//En caso de existir errores en algun campo reformatea los campos para q qden bn
			if(this.error_messages.length > num_errors){
				if(this._fields[i].type.match(/^format_[a-z]*$/) && Object.isFunction(this._fields[i].options.format.deFormat) && this._fields[i].options.doDeFormat) 
					campo.value = this._fields[i].options.format.execute(format,campo.value);
				continue;
			}
		}
		if(this.error_messages.length > 0)
			return false;
		else
			return true;
	},

	getErrorMessages: function(){//Retorna los mensajes de error.
		return this.error_messages;
	},

	_createError: function(field,errno,selector){
		var msg_error = '';
		if(errno == -1)
			msg_error = !Object.isUndefined(arguments[3]) ? arguments[3] : 'Debe cumplir un requisito espec&iacute;fico.';
		else
			msg_error = this._errors[errno];
		alias = name;
		if(!Object.isUndefined(field.options['alias']))
			alias = field.options['alias'];
		error = {'name': field.name, 'msg': "Error en el campo: '" + alias + "'. " + msg_error,'errno': errno, 'selector': selector};
		this.error_messages.push(error);
		if(field.options['onErrorBlank']) $$(selector)[0].value = ($$(selector)[0].type != 'select-one' ? '' : '@');
	},
	
	//Validaciones estandar, hacen mas sencillo realizar validaciones especiales y muy comunes.
	//Valida que un numero sea positivo.
	_Positive: function(value,name,type){
		if(['number','decimal'].indexOf(type) == -1) throw "Validator.Positive\n * El campo: " + name + " debe ser tipo numerico para poder validar si es positivo o no.";
		if(isNaN(value)) return false;
		if(value < 0) 
			return "El valor del campo debe ser positivo.";
		return true;
	},
	
	//Valida que un numero sea negativo.
	_Negative: function(value,name,type){
		if(['number','decimal'].indexOf(type) == -1) throw "Validator.Negative\n * El campo: " + name + " debe ser tipo numerico para poder validar si es negativo o no.";
		if(isNaN(value)) return false;
		if(value > 0) 
			return "El valor del campo debe ser negativo.";
		return true;
	},
	
	//Valida que un dato sea un valor especifico.
	_Equals: function(value,name,type,constant){
		if(value != constant) 
			return "El valor del campo debe igual a " + constant + ".";
		return true;
	},

	//Valida que un dato esta en pasado.
    _InPast: function(value,name,type){
        var d = new Date();
        alert(this);
        var field = this.getField(name);
        if(field == false)
            return "El campo " + name + " no existe.";
		if(!Object.isUndefined(field.options['dateFormat'])){
            var str=value; 
            var format=field.options['dateFormat'];
            var fecha = new Object();
            //Años
            var patt1=/[Y]/gi;
            var res = format.match(patt1);
            var patt2=new RegExp("[Y]{"+res.length + "}","gi");
            var tmp = format.replace(/[^Y]/gi,".");
            tmp = tmp.replace(patt2,"(\\d{" + res.length + "})");
            fecha['ano'] = str.match(new RegExp(tmp))[1];
            //Meses
            var patt1=/[M]/gi;
            var res = format.match(patt1);
            var patt2=new RegExp("[M]{"+res.length + "}","gi");
            var tmp = format.replace(/[^M]/gi,".");
            tmp = tmp.replace(patt2,"(\\d{" + res.length + "})");
            fecha['mes'] = str.match(new RegExp(tmp))[1];
            //Dias
            var patt1=/[D]/gi;
            var res = format.match(patt1);
            var patt2=new RegExp("[D]{"+res.length + "}","gi");
            var tmp = format.replace(/[^D]/gi,".");
            tmp = tmp.replace(patt2,"(\\d{" + res.length + "})");
            fecha['dia'] = str.match(new RegExp(tmp))[1];
            //Calculo
            var fec1 = new Date();
            var fec2 = new Date();
            fec2.setDate(fecha['dia']);
            fec2.setMonth(fecha['mes'] - 1);
            fec2.setYear(fecha['ano']);
            if(fec1.getTime() <= fec2.getTime()){
			    return "El valor del campo esta en Presente o Futuro.";
            }
            return true;
        }
        return "No hay como comparar el campo.";
    },

	execute: function(function_name){
		var result = '';
        switch(function_name){
            case "InPast": result = this._InPast(arguments[1],arguments[2],arguments[3]);break;
            case "Equals": result = this._Equals(arguments[1],arguments[2],arguments[3],arguments[4]);break;
            case "Negative": result = this._Negative(arguments[1],arguments[2],arguments[3]);break;
            case "Positive": result = this._Positive(arguments[1],arguments[2],arguments[3]);break;
            default: throw "Validator.Execute\n * No existe una validacion estandar con ese nombre: " + function_name + ".";break;
        }
		return result;
    }

});

/** Forma de uso
//Valida rangos con valideRange, tanto de fecha como numericos, realiza operaciones beforeValidate y afterValidate, myPreValidate y myPostValidate que ejecutan funciones de validacion y generan
//errores personalizados con la propiedad msg_error, deformatea automaticamente los campos que sean ingresados como format_*
var format = new Format({type: 'numeric', properties: {'decimals': 2} });
var val = new Validator({'format_number': {'format': format} });
Event.observe(window,"load",function(){
	val.addField("detalle","format_number",null,{'valideRange': true, 'minimumNumber': 3, 'maximumNumber': 6, 'doDeFormat': true, 'beforeValidate': function(elemento,nombre){elemento.value = format.numeric(elemento.value) } });
	val.addField("tercero","select",null,{'myPostValidate': function(valor,nombre){
		if($F('banco')=='S'){
			if(valor != 'N') return false;
		}
		return true;
		}
		,'msg_error': "El campo debe ser 'N'."});
	val.addField("centros","select",null,{'afterValidate': function(nombre,elemento){elemento.value = 'S'} });
	val.addField("bodegas","select");
	val.addField("activos","select");
	val.addField("detalla","select");
	alert(val.modifyOptions({"isNull": true},'select')); //Modificando las opciones de un tipo de dato
	alert(val.modifyOptions({"isNull": false},'detalla'));//Modificando las opciones de un campo en particular
});

function validar(){
	var msg = '';
	if(!val.valide()){
		errores = val.getErrorMessages();
		var nombre = '';
		for(var i=0;i<errores.length;i++){
			if(nombre != errores[i].name){
				nombre = errores[i].name;
				new Effect.Highlight($$(errores[i].selector)[0], { startcolor: '#FF4444',endcolor: '#FFFFFF',duration: 3 });
			}
			msg += errores[i].msg + "<br />";
		}
		showMiniMsg("validate_errors_msg",msg,"error","position: top","width: 700","height: 70","hide: 5000","style: overflow:auto;");
		$$(errores[0].selector)[0].activate();
	}
}
**/

 
function comprobar(campo){
	var field = arguments[1]!=null?arguments[1]:$(campo);
	if($(campo+"msg")){
 		var mensaje = $(campo+"msg");
 		if(field.value!=""&&field.value!="@")
 			new Effect.Fade(mensaje,{duration: 1.5});
 	}
}
 
/* 
 * Funcion que busca coincidencias de una cadena de caracteres dentro de un arreglo de objetos de los cuales se extrae su id
 * Parametros: 
 * array = arreglo de objetos, String o convinados en el cual se va a realizar la busqueda
 * pattern = valor del id que se desea buscar
 * Retorna: el valor del id completo encontrado, en caso de no encontrarlo retorna null
 * 
 */
 function buscarId(array,pattern){
	var cos,find=null;
	for(var i = 0;i < array.length;i++){
		if(array[i] instanceof Object){
			if(array[i].getAttribute("id") == null){
				continue;
			}
			if(new String(array[i].getAttribute("id")).match(pattern)){
				find=array[i].getAttribute("id");
				break;
			}
		}else{
			if(array[i]!=null && array[i].match(pattern)){
				find=array[i];
				break;
			}
		}
	}
	return find;
}

/* 
 * Funcion que Retorna parametros en forma de un arreglo asociativo con el primer elemento como nombre del parametro y segundo elemento
 * un arreglo de todos los valores de que tiene ese parametro.
 * Parametros: 
 * parametros = arreglo de String que contiene todos los parametros que se desean evaluar
 * Opcionalmente se pueden incluir un parametro mï¿½s, que se refiere al inicio desde el cual se va a empezar a evaluar los parametros, por 
 * defecto este valor estï¿½ en 1
 * 
 */
function getParametros(parametros){
	var init = arguments[1]?arguments[1]:1;
	var arreglo = new Array();
	for(i=init;i<parametros.length;i++) {
		var args = parametros[i].split(/:\ |\|/);
		arreglo[args[0]]=args.slice(1);
	}
	return arreglo;
}

/* 
 * Funcion que despliega un mensaje en algun lugar de la vista
 * Parametros: 
 * id = identificaciï¿½n que va a ser usada en el nombre del div que contiene el mensaje
 * msg = mensaje que se va a desplegar
 * tipo = clase de mensaje que se va a mostrar (error, warning)
 * Opcionalmente se pueden incluir varios parametros mï¿½s, uno de ellos tiene que ver con la ubicacion del mensaje
 * por obligaciï¿½n se necesita una posiciï¿½n en pantalla que de un top o un left, un elemento que nos de la 
 * ubicacion del para que el mensaje se posicione de manera relativa o una combinaciï¿½n de los dos.
 * 
 */
function showMiniMsg(id,msg,tipo){
	createNewMsg(id,msg,tipo);
	mensajesCreados.push("#"+id);
	var param = getParametros(arguments,3);
	var izq=0,tope=0;
	if(param["style"])
		$(id).setAttribute('style',$(id).getAttribute('style') + ";" + param["style"][0]);
	if(param["width"])
		$(id).style.width = param["width"][0] + "px";
	if(param["height"])
		$(id).style.height = param["height"][0] + "px";
	if(param["element"]){
		if(param["parent"]){
			izq = $$("#"+param["parent"][0]+" #"+param["element"][0])[0].cumulativeOffset().left + 
					$$("#"+param["parent"][0]+" #"+param["element"][0])[0].getWidth();
			tope = $$("#"+param["parent"][0]+" #"+param["element"][0])[0].cumulativeOffset().top;
		}else{
			izq = $(param["element"][0]).cumulativeOffset().left + $(param["element"][0]).getWidth();
			tope = $(param["element"][0]).cumulativeOffset().top;
		}
	}
	if(param["position"]){
		switch(param["position"][0]){
			case 'top': izq = (document.viewport.getWidth() / 2) - ($(id).getWidth() / 2);tope = 0;break;
			case 'center': izq = (document.viewport.getWidth() / 2) - ($(id).getWidth() / 2);
							tope = (document.viewport.getHeight() / 2) - ($(id).getHeight() / 2);break;
			case 'bottom': izq = (document.viewport.getWidth() / 2) - ($(id).getWidth() / 2);
							tope = document.viewport.getHeight() - $(id).getHeight();break;
			case 'left':izq = 0;
							tope = (document.viewport.getHeight() / 2) - ($(id).getHeight() / 2);break;
			case 'middle': izq = (document.viewport.getWidth() / 2) - ($(id).getWidth() / 2);
							tope = (document.viewport.getHeight() / 2) - ($(id).getHeight() / 2);break;
			case 'rigth': izq = document.viewport.getWidth() - $(id).getWidth();
							tope = (document.viewport.getHeight() / 2) - ($(id).getHeight() / 2);break;
		}
	}
	if(param["top"])
		tope = parseInt(param["top"][0]);
	if(param["left"])
		izq = parseInt(param["left"][0]);
	if(param["paddingTop"])
		tope += parseInt(param["paddingTop"][0]);
	if(param["paddingLeft"])
		izq += parseInt(param["paddingLeft"][0]);
	$(id).style.left = izq + "px";
	$(id).style.top = tope + "px";
	new Effect.Appear($(id),{duration: 0.8});
	if(param["hide"])
		setTimeout("new Effect.Fade($('" + id + "'),{duration: 0.8});",param["hide"][0]);
}

/* 
 * Funcion que crea un div con todos los atributos para ser desplegado como un mensaje emergente en pantalla
 * Parametros: 
 * id = identificador del div que se va a crear nuevo
 * texto = mensaje que se va a desplegar
 * tipo = clase de mensaje que se va a mostrar (error, warning)
 * 
 */
function createNewMsg(id,texto,tipo){
	if(Object.isElement($(id)))
		d = $(id);
	else
 		var d = document.createElement("DIV");
 	switch (tipo.toLowerCase()){
 		default: 
        case "success": d.addClassName("successMessage");break;
 		case "error": d.addClassName("error_message");break;
 		case "advertencia":
 		case "warning": d.addClassName("warning_message");break;
 		case "notice": d.addClassName("notice_message");break;
 	}
 	d.addClassName("error_message");
	d.writeAttribute("id",id);
	d.setStyle({
		position: 'absolute',
 		display: 'none',
		overflow: 'visible',
		fontWeight: 'bold',
		margin: 'auto',
		marginLeft: '5px',
		marginRigth: '1px',
		paddingTop: '3px',
		paddingLeft: '25px',
		paddingButton: '3px',
		paddingRight: '2px',
		backgroundRepeat: 'no-repeat',
		backgroundPosition: 'center',
		backgroundPosition: '5px',
		textAlign: 'left',
		zIndex: "50"
	});
	document.body.appendChild(d);
	d.update(texto);
	return d.identify();
}

/* 
 * Funcion que chequea los mensajes para verificar si se deben desaparecer o no.
 * Parametros: 
 * field = campo que se desea validar, puede ser ingresado como campo o su id
 * idMsg = identificador del div del mensaje
 * 
 */
function checkMsg(field,idMsg){
	field = field instanceof Object ? field : $(field);
	idMsg = buscarId(mensajesCreados,idMsg);
	if(idMsg==null)
		return;
	var msg = $$(idMsg)[0];
	if(msg){
 		if(field.value!=""&&field.value!="@")
 			new Effect.Fade(msg,{duration: 0.8});
 	}
}

/* 
 * Funcion que adiciona una fila completa a un detalle.
 * Parametros: 
 * element = elemento que se tiene que comprobar para asegurar el ingreso de una nueva fila
 * Requisitos: - el elemento que va a contener todas las filas debe tener id contenedor
 * - cada fila debe tener un englobe que tenga id contenidoX donde X es la ubicacion de esa fila
 * - existen 2 clases campo_blanco y campo_revisar, la primera es para los campos q aun no tienen datos dentro
 *   la segunda es para los campos que ya se aseguro que tienen datos dentro.
 * Se asume que el primer elemento de los contenidos es el numero de la fila y que el ultimo elemento
 * es el boton para adicionar o borrar    
 * 
 */
function agregarFila(element){
	//aun en revision !!!!
	var campos = $$(".campo_blanco");var index=0;
	$$(".campo_blanco").each(function(element){
		if($(buscarId(element.ancestors(),"tab")) && !$(buscarId(element.ancestors(),"tab")).visible()){
			campos.splice(index--,1);
		}
		index++;
	});
	if(element!=null){
		if($F(element)=='' || $F(element)=='@')
			return;
	}else{
		var campos2 = $$('.not_null');index=0;
		$$(".not_null").each(function(element){
			if($(buscarId(element.ancestors(),"tab")) && !$(buscarId(element.ancestors(),"tab")).visible()){
				campos2.splice(index--,1);
			}
			index++;
		});
		for(var i=0;i<campos2.length;i++){
			campos2[i].value = campos2[i].value.replace(/^\s*|\s*$/g,"");
			if(campos2[i].value=='' || campos2[i].value=='@'){
				showMiniMsg("not_null_msg","Por favor complete los campos en blanco antes de adicionar una fila","advertencia","element: "+buscarId(campos2[i].ancestors(),"contenido"));
				setTimeout("new Effect.Fade($('not_null_msg'),{duration: 0.8});",4000);
				campos2[i].activate();
				return;
			}
		}
	}
	var idContenedor = buscarId(campos[0].ancestors(),"contenedor");
	index = (totfilas instanceof Array)?totfilas[parseInt(idContenedor.substr(idContenedor.length-1,1))-1]:totfilas;
	var r = $$("#"+idContenedor+" #contenido"+index)[0].cloneNode(true);
	campos.each(function(element){
			element.removeClassName('campo_blanco');
			element.addClassName('campo_revisar');
	});
	$(idContenedor).appendChild(r);
	$$(".campo_blanco").each(function(element){
		if(!$(buscarId(element.ancestors(),"tab")) || $(buscarId(element.ancestors(),"tab")).visible()){
			switch (element.type){
				case "select-one": element.value='@';break;
				case "text": element.value='';break;
				default: element.value='';break; 
			}
		}
	});
	$$("#"+idContenedor+" #contenido"+index+" .etiquetaAccion")[0].ancestors()[0].innerHTML = 
		$$("#"+idContenedor+" #contenido"+index+" .etiquetaAccion")[0].ancestors()[0].innerHTML.replace(/agregarFila\([a-zA-Z]*\);/,"borrarFila("+index+",this);");
	$$("#"+idContenedor+" #contenido"+index+" .etiquetaAccion")[0].update("<img src='"+$Kumbia.path+"img/del.png' alt='Del' title='Borrar Fila' />");
	r.setAttribute("id","contenido"+(index+1));
	$$("#"+idContenedor+" #"+r.id+" .etiquetaAccion")[0].update("<img src='"+$Kumbia.path+"img/add.png' alt='Add' title='Agregar Fila' />");
	/*r.childElements()[r.childElements().length-1].update("<div class='etiquetaAccion' onclick='agregarFila(null);'><img "+
						"src='"+$Kumbia.path+"img/add.png' title='Agregar Fila' alt='Add' /></div>");*/
	r.firstDescendant().update(index+1);
	if(totfilas instanceof Array){
		totfilas[parseInt(idContenedor.substr(idContenedor.length-1,1))-1]++;
	}else{
		totfilas++;
	}
	//r.setAttribute("id","contenido"+(index+1));
	r.childElements()[1].firstDescendant().activate();
}

/* 
 * Funcion que borra una fila completa a un detalle.
 * Parametros: 
 * row = numero de la fila que se desea borrar, este contador debe comenzar en uno
 * Requisitos: - el elemento que va a contener todas las filas debe tener id contenedor
 * - cada fila debe tener un englobe que tenga id contenidoX donde X es la ubicacion de esa fila
 * - existen 2 clases campo_blanco y campo_revisar, la primera es para los campos q aun no tienen datos dentro
 *   la segunda es para los campos que ya se aseguro que tienen datos dentro.
 * Se asume que el primer elemento de los contenidos es el numero de la fila y que el ultimo elemento
 * es el boton para adicionar o borrar    
 * 
 */
function borrarFila(row){
	if(row==0)
		return;
	var idContenedor = arguments[1]?buscarId(arguments[1].ancestors(),"contenedor"):"contenedor";
	var tmp = $(idContenedor).childElements()[row-1];
	if(!tmp)
		return;
	$(idContenedor).removeChild(tmp);
	var index = 0;
	if(totfilas instanceof Array){
		index = --totfilas[parseInt(idContenedor.substr(idContenedor.length-1,1))-1]; 
	}else{
		index = --totfilas;
	}
	if($(tmp.getAttribute("id")+"msg")){
		new Effect.Fade($(tmp.getAttribute("id")+"msg"),{duration: 0.5});
	}
	var hijos = $(idContenedor).childElements();
	for(var i=0; i<index; i++){
		hijos[i].firstDescendant().update(i+1);
		hijos[i].setAttribute("id","contenido"+(i+1));
		if(i==index-1){
			if(!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant() || 
					!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant() ||
					hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant().getAttribute("alt") == "Add"){
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML = 
					$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML.replace(/borrarFila\(\d*,[a-zA-Z]*\);/,"agregarFila(null);");
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].update("<img src='"+$Kumbia.path+"img/add.png' alt='Add' title='Agregar Fila' />");
				/*hijos[i].childElements()[hijos[i].childElements().length-1].update("<div class='etiquetaAccion' "+
					"onclick='agregarFila(null);'><img src='"+$Kumbia.path+"img/add.png' title='Agregar Fila' alt='Add' /></div>");*/
			}
		}else{
			if(!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant() || 
					!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant() ||
					hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant().getAttribute("alt") == "Add"){
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML = 
					$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML.replace(/agregarFila\([a-zA-Z]*\);/,"borrarFila("+(i+1)+",this);");
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].update("<img src='"+$Kumbia.path+"img/del.png' title='Borrar Fila' alt='Del' />");
			}else if(!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant() || 
					!hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant() ||
					hijos[i].childElements()[hijos[i].childElements().length-1].firstDescendant().firstDescendant().getAttribute("alt") == "Del"){
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML = 
					$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].ancestors()[0].innerHTML.replace(/borrarFila\(\d*,[a-zA-Z]*\);/,"borrarFila("+(i+1)+",this);");
				$$("#"+idContenedor+" #contenido"+(i+1)+" .etiquetaAccion")[0].update("<img src='"+$Kumbia.path+"img/del.png' title='Borrar Fila' alt='Del' />");
			}
		}
	}
	hijos[index-1].childElements()[1].firstDescendant().activate();
}

/*
 * Funciones para las pestanhas
 *
 */
var Tabs = {

    setActiveTab: function(element, number){
        if(element.hasClassName("active_tab")){
            return;
        } else {
            element.removeClassName("inactive_tab");
        }
        $$(".active_tab").each(function(tab_element){
            tab_element.removeClassName("active_tab");
        });
        $$(".tab_basic").each(function(tab_element){
            if(tab_element==element){
                tab_element.addClassName("active_tab");
            } else {
                tab_element.addClassName("inactive_tab");
            }
        });
        $$(".tab_content").each(function(tab_content){
            if(tab_content.id!="tab"+number){
                tab_content.hide();
            }
        });
        $("tab"+number).show();
        var campos = $$(".campo_formulario"+number);
		campos.each(function(element){
			element.enable();
		});
		//campos[0].activate();
		//campos = $$(".error_message");
		//campos.each(function(element){
			//element.hide();
		//});
	}
};

function grid(element){
	var i = 0;
	$(element).childElements().each(
		function(td){
			$('titulos').childElements()[i++].width = td.getWidth();
		}
	)
}

function $$$(element){
	return Selector.findChildElements(element,$A(arguments).without(element));
}

var Format = Class.create({

	data: 0,
	
	_numeric: {
		decimals: 0,
		puntoDec: ',',
		sepMiles: '.',
		letNegative: true,//Permite establecer si se aceptan o no numeros negativos
		blankToZero: true,//Permite establecer si cuando se recibe una cadena de caracteres vacia se trate como un cero o no se le de formato.
		leftZeros: 0,//Permite agregarle un determindado numero de ceros a la izquierda del numero.
		complete: 0,//Permite agregarle un caracter a la izquierda del numero hasta que se complete el numero especificado.
		completeCaracter: '0',//Caracter para completar la cadena.
		onCompleteTruncate: true//Si el numero ingresado es mayor que el valor puesto en la propiedad complete, este especifica si se trunca o no.
	},
	
	_percent: {
		decimals: 0,
		puntoDec: ',',
		sepMiles: '.',
		simbPer: '%',
		letNegative: true,
		blankToZero: true,
		leftZeros: 0,
		complete: 0,
		completeCaracter: '0',
		onCompleteTruncate: true
	},
	
	_money: {
		decimals: 0,
		puntoDec: ',',
		sepMiles: '.',
		simbMon: '$',
		letNegative: true,
		blankToZero: true,
		leftZeros: 0,
		complete: 0,
		completeCaracter: '0',
		onCompleteTruncate: true
	},

	initialize: function(){//format = new Format({type: '_numeric', properties: {} },{type: '_money', properties: {} }); Los argumentos son opcionales.
		var obj = {};
		for(var i = 0;i < arguments.length;i++){
			if(arguments[i].properties.decimals > 8) arguments[i].properties.decimals = 8;
			switch(arguments[i].type){
				case 'numeric': Object.extend(this._numeric,arguments[i].properties);break;
				case 'percent': Object.extend(this._percent,arguments[i].properties);break;
				case 'money': Object.extend(this._money,arguments[i].properties);break;
				default: break;
			}
		}
	},
	
	changeProperties: function(){//format.changeProperties({type: '_numeric', properties: {} }); Los argumentos son opcionales.
		for(var i = 0;i < arguments.length;i++){
			if(arguments[i].properties.decimals > 8) arguments[i].properties.decimals = 8;
			switch(arguments[i].type){
				case 'numeric': Object.extend(this._numeric,arguments[i].properties);break;
				case 'percent': Object.extend(this._percent,arguments[i].properties);break;
				case 'money': Object.extend(this._money,arguments[i].properties);break;
				default: break;
			}
		}
	},
	
	numeric: function(number){//format.numeric(numero); Retorna una cadena de caracteres formateada con las propiedades que han sido definidas para los numeros.
		if(number === '') {
			if(this._numeric.blankToZero) number = 0;
			else return '';
		}
		if(!Object.isNumber(number)) number = parseFloat(number);
		this.data = number;
		return this.__transform(this._numeric);
	},
	
	money: function(number){//format.money(numero); Retorna una cadena de caracteres formateada con las propiedades que han sido definidas para las monedas.
		if(number === '') {
			if(this._money.blankToZero) number = 0;
			else return '';
		}
		if(!Object.isNumber(number)) number = parseFloat(number);
		this.data = number;
		return this._money.simbMon + " " + this.__transform(this._money);
	},
	
	percent: function(number){//format.percent(numero); Retorna una cadena de caracteres formateada con las propiedades que han sido definidas para los porcentajes.
		if(number === '') {
			if(this._percent.blankToZero) number = 0;
			else return '';
		}
		if(!Object.isNumber(number)) number = parseFloat(number);
		this.data = number;
		return this.__transform(this._percent) +  this._percent.simbPer;
	},
	
	__transform: function(properties){
		var parteEntera = parseInt(this.data);
		var isNegative = false;
		if(parteEntera < 0) isNegative = properties.letNegative ? true : false; 
		parteEntera = Math.abs(parteEntera);
		var parteDecimal = (Math.abs(this.data) + '').replace(parteEntera,'').replace('.','');
		str = parteEntera + '';
		str = str.toArray().reverse().join('').replace(/(\d{3})/g,"$1"+properties.sepMiles).toArray().reverse().join('').replace(/^[properties.sepMiles]/,'');
		str = isNegative ? '-' + str : str;
		if(properties.leftZeros > 0 && !str.empty()){
			str = '0'.times(properties.leftZeros) + str;
		}
		if(properties.complete > 0 && !str.empty()){
			str = properties.completeCaracter.times(properties.complete - str.length) + str;
			str = properties.onCompleteTruncate ? str.truncate(properties.complete) : str;
		}
		if(properties.decimals > 0){
			strTmp = parteDecimal + '';
			strTmp = parteDecimal + '0'.times(properties.decimals - strTmp.length);
			str += properties.puntoDec + strTmp.substr(0,properties.decimals);
		}
		return str;
	},
	
	deFormat: function(str,type){//format.deFormat(string,tipo); Retorna un numero sin formato.
		var result = '0';
		if(str.blank()){
			switch(type){
				case 'numeric': if(this._numeric.blankToZero) return 0; else return "";
				case 'percent': if(this._percent.blankToZero) return 0; else return "";
				case 'money': if(this._money.blankToZero) return 0; else return "";
			}
		}
		if(!isNaN(str)) return parseFloat(str);
		switch(type){
			case 'numeric': result = str.replace(/[this._numeric.sepMiles]/g,'').replace(this._numeric.puntoDec,'.');break;
			case 'percent': result = str.replace(/[this._percent.sepMiles]|[this._percent.simbPer]$/g,'').replace(this._percent.puntoDec,'.');break;
			case 'money': result = str.replace(this._money.simbMon+' ','').replace(/[this._money.sepMiles]/g,'').replace(this._money.puntoDec,'.');break;
			default: break;
		}
		return isNaN(parseFloat(result)) ? '' : parseFloat(result);
	},
	
	execute: function(function_name){
		var result = '';
		switch(function_name){
			case 'numeric': result = this.numeric(arguments[1]); break;
			case 'money': result =  this.money(arguments[1]); break;
			case 'percent': result =  this.percent(arguments[1]); break;
			case 'deFormat': result =  this.deFormat(arguments[1],arguments[2]); break;
		}
		return result;
	}
	
});
/* Ejemplos de Uso: 
format = new Format({ type: 'numeric', properties: { decimals: 2, puntoDec: ',', sepMiles: '.', letNegative: true, blankToZero: true } },
	{type: 'percent', properties: { decimals: 7, puntoDec: ',', sepMiles: '.', simbPer: ' %', letNegative: false, blankToZero: false } },
	{type: 'money', properties: { decimals: 2, puntoDec: ',', sepMiles: '.', simbMon: 'US$', letNegative: false, blankToZero: false } });
<div><input id="temporal" type="text"></div>
<div onclick="$('temporal').value = format.percent($F('temporal'));">Mostrar</div>
<div onclick="alert(format.deFormat($F('temporal'),'percent'));">Mostrar2</div>
*/

var Messages = {
	ver: function(val){
		msg = "";
		errores = val.getErrorMessages();
		var nombre = '';
		for(var i=0;i<errores.length;i++){
			msg += errores[i].msg + "<br />";
		}
		new Effect.Highlight($$(errores[0].selector)[0], { startcolor: '#FF9999',endcolor: '#FFFFFF',queue: 'end', scope: 'detalle'});
		showMiniMsg("validate_errors_msg",msg,"error","position: top","width: 500","height: 70","hide: 5000","style: overflow:auto;");
		$$(errores[0].selector)[0].activate();
	}

}

<html>
	<head>
		<title>KumbiaError</title>
		<style type="text/css">
			body {
				margin: 0px;
				font-family: "Lucida Grande", "Tahoma","sans-serif";
			}
			div, li, ul {
				font-family: "Lucida Grande", "Tahoma","sans-serif";
				font-size: 12px;
			}
			h2 {
				font-family: "Trebuchet MS","sans-serif";
				font-size: 18px;
			}
		</style>
	</head>
	<body>

<?php

if(stripos($_SERVER['SERVER_SOFTWARE'], "Apache")!==false){
	if(!in_array('mod_rewrite', apache_get_modules())){
		echo "
		<div style='background:#ccdeff'>
		<h2>Kumbia Enterprise Framework: No tiene Mod-ReWrite de Apache instalado</h2>
		Debe habilitar/instalar mod_rewrite en su servidor Apache.
		</div>
		<div style='padding:10px'>
		Consulte para m&aacute;s informaci&oacute;n:
		<ul>
			<li><a href='http://httpd.apache.org/docs/2.0/misc/rewriteguide.html'>http://httpd.apache.org/docs/2.0/misc/rewriteguide.html</a>
		</ul>
		</div>";
	} else {
		echo "
		<div style='background:pink;padding:1px'>
		<h2>Kumbia Enterprise Framework: No se pudo utilizar reescritura de URLs </h2>
		</div>

		<div style='padding:10px'>
		Verifique lo siguiente:
		<ul>
			<li>El archivo '".$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/.htaccess' est&aacute; presente
			<li>".apache_get_version()." soporta archivos de sobreescritura de configuraci&oacute;n '.htaccess'
			<li>La opci&oacute;n de configuraci&oacute;n de Apache 'AllowOverride All' no est&aacute; presente
			en el DocumentRoot '".$_SERVER['DOCUMENT_ROOT']."'
		</ul>
		Consulte para m&aacute;s informaci&oacute;n:
		<ul>
			<li><a href='http://httpd.apache.org/docs/2.0/misc/rewriteguide.html'>http://httpd.apache.org/docs/2.0/misc/rewriteguide.html</a>
			<li><a href='http://httpd.apache.org/manual/howto/htaccess.html'>http://httpd.apache.org/manual/howto/htaccess.html</a>
			<li><a href='http://httpd.apache.org/manual/configuring.html'>http://httpd.apache.org/manual/configuring.html</a>
		</ul>
		</div>";
	}
} else {
	echo "
	<div style='background:#ccdeff'>
	<h2>Kumbia Enterprise Framework: Su Web Server no tiene configuradas reglas de reescritura</h2>
	</div>
	Consulte la documentaci&oacute;n de su servidor web para informarse como hacer esto.";
}

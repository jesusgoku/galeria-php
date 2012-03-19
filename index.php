<?php
require('config/config.php');
require('config/config-galeria.php');
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo TXT_TITULO_OWNER . ' - '. TXT_TITULO_SITIO; ?></title>
<link type="text/css" href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link type="text/css" href="libs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<script type="text/javascript" src="libs/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="libs/mustache/mustache.js"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<!--<script type="text/javascript" src="libs/plupload/plupload.full.js"></script>-->
<script type="text/javascript" src="libs/plupload/plupload.js"></script>
<script type="text/javascript" src="libs/plupload/plupload.html5.js"></script>
<script type="text/javascript" src="libs/plupload/plupload.flash.js"></script>
<script type="text/javascript" src="libs/plupload/plupload.silverlight.js"></script>
<script type="text/javascript" src="libs/plupload/plupload.html4.js"></script>
<script type="text/javascript">
<!--
$(function(){
	
	// Prevenir Cambio
	$('a[data-toggle="tab"]').on('shown', function (e) {
		if( $(e.target).attr('href') == '#imagenes' ) {
			e.preventDefault();
			//$(e.relatedTarget).tab('show');
		}
	});
	
	// Plupload
	var uploader = new plupload.Uploader({
		runtimes : 'html5,flash,silverlight,html4',
		browse_button : 'pickfiles',
		container : 'container',
		drop_element: 'filelist',
		max_file_size : '10mb',
		url : 'upload.php',
		flash_swf_url : 'libs/plupload/plupload.flash.swf',
		silverlight_xap_url : 'libs/plupload/plupload.silverlight.xap',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip"}
		],
		//resize : {width : 320, height : 240, quality : 90},
		multipart: true,
		multipart_params: { accion: 1 },
		chunk_size: '1024kb',
		urlstream_upload: true
	});

	uploader.bind('Init', function(up, params) {
		var tmplRuntime = $('#tmplRuntime').html();
		var tmplOutput = Mustache.render(tmplRuntime,params);
		$('#filelist').append(tmplOutput);
	});

	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});
	
	$('#clearlist').click(function(e){
		e.preventDefault();
		var id_archivo = '';
		var archivo;
		$('#filelist li:has(a)').each(function(){
			id_archivo = $(this).attr('id');
			var archivo = uploader.getFile(id_archivo);
			uploader.removeFile(archivo);
			$('#' + id_archivo).fadeOut(function(){ $(this).remove(); });
		});
		uploader.refresh();
	});
	
	$(document).on('click','#filelist .label-important',function(e){
		var $this = $(this);
		var id_archivo = $this.closest('li').attr('id');
		var archivo = uploader.getFile(id_archivo);
		uploader.removeFile(archivo);
		$('#' + id_archivo).fadeOut(function(){ $(this).remove(); });
		uploader.refresh();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		var dataTmpl = { listaArchivos: new Array() };
		$.each(files, function(i, file) { if(file.status == 1) dataTmpl.listaArchivos.push({ id_archivo: file.id, nombre: file.name }); });
		var tmplFilesAdd = $('#tmplFilesAdd').html();
		var tmplOutput = Mustache.render(tmplFilesAdd, dataTmpl);
		$('#filelist').append(tmplOutput);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
		$('#' + file.id + ' .progress').show();
		$('#' + file.id + ' .bar').css({ width: file.percent + '%' });
	});

	uploader.bind('Error', function(up, err) {
		var dataTmpl = { listaArchivos: new Array() };
		var tmplError = $('#tmplError').html();
		var tmplOutput = Mustache.render(tmplError, err);
		$('#filelist').append(tmplOutput);
		up.refresh(); // Reposition Flash/Silverlight
		// Para que se eliminen
		$('#' + err.file.id).delay(3000).fadeOut(function(){ $(this).remove(); });
	});

	uploader.bind('FileUploaded', function(up, file, info) {
		data = $.parseJSON(info.response);
		$('#' + file.id + " b").text('Completo');
		$('#' + file.id).delay(3000).fadeOut(function(){ $(this).remove(); });
	});
	
});
//-->
</script>
<link type="text/css" href="css/estilos.css" rel="stylesheet" />
</head>

<body>
<div class="tabbable">
	<ul id="tab-buttons" class="nav nav-tabs">
	  <li class="active"><a href="#imagenes" data-toggle="tab">Imagenes</a></li>
	  <li><a href="#subir" data-toggle="tab">Subir</a></li>
	</ul>
	 
	<div id="tabs-content"class="tab-content">
	  <div class="tab-pane active in" id="imagenes">
		<div class="hero-unit">
			<h1>Galeria de Imagenes</h1>
			<p>Echa una mirada a las imagenes disponibles, selecciona la que mas te gusta y usala en tus proyectos.</p>
			<p>Y si no encuentras la que buscas subela...</p>
			<p><a href="#subir" data-toggle="tab" class="btn btn-primary btn-large">Subir</a></p>
		</div><!-- /.hero-unit -->
	  </div><!-- /#imagenes -->
	  <div class="tab-pane" id="subir">
		<!--<div class="hero-unit">
			<h1>Sube tus imagenes</h1>
			<p>Selecciona tus imagenes y espera a que suban.</p>
		</div>--><!-- /.hero-unit -->
		<div id="container">
			<ul id="filelist" class="nav nav-list"></ul>
			<br />
			<a id="pickfiles" href="#" class="btn btn-primary">Seleccionar archivos</a>
			<a id="uploadfiles" href="#" class="btn btn-success">Subir Archivos</a>
			<a id="clearlist" href="#" class="btn btn-success">Borrar Lista</a>
		</div><!-- /#container -->

	  </div><!-- /#subir -->
	</div><!-- /#tab-content -->
</div><!-- /.tabbable -->

	<div id="tmplFilesAdd" class="templates">
		{{#listaArchivos}}
		<li id="{{id_archivo}}">
			<a href="javascript:;">{{nombre}} <b class="label label-info">Esperando</b> <span class="label label-important">eliminar</span></a>
			<div class="progress progress-success progress-striped active" style="display:none;">
				<div class="bar" style="width:0%;"></div>
			</div>
		</li>
		{{/listaArchivos}}
	</div>
	<div id="tmplError" class="templates">
		{{#file}}
		<li id="{{file.id}}">
		{{/file}}
		{{^file}}
		<li>
		{{/file}}
			<a href="javascript:;">Error: <span class="label label-warning">{{code}}</span> - <span class="label label-warning">{{message}}</span> {{#file}} Archivo: <span class="label label-warning">{{file.name}}</span>{{/file}}</a>
		</li>
	</div>
	<div id="tmplRuntime" class="templates">
		<li class="nav-header">Cargador: <span class="label label-success">{{runtime}}</a>
	</div>
</body>
</html>
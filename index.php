<?php
require('config/config.php');
require('config/config-galeria.php');
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo TXT_TITULO_OWNER . ' - '. TXT_TITULO_SITIO; ?></title>
<!--[if lt IE 9]>
<script src="libs/html5.js"></script>
<![endif]-->
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
		max_file_size : '100mb',
		url : 'upload.php',
		flash_swf_url : 'libs/plupload/plupload.flash.swf',
		silverlight_xap_url : 'libs/plupload/plupload.silverlight.xap',
		filters : [
			{title : 'Archivos Comprimidos', extensions : 'zip,rar,tar.gz,tar.bz2'},
			{ title : 'Archivos de Imagen', extensions : 'jpg,gif,png,jpeg' }
		],
		//resize : { width : 320, height: 500, quality : 90},
		multipart: true,
		multipart_params: { accion: 1 },
		chunk_size: '4096kb',
		urlstream_upload: true,
		// Eventos
		preinit: {
			Init: function(up, info){
				var tmplRuntime = $('#tmplRuntime').html();
				var tmplOutput = Mustache.render(tmplRuntime,up);
				$('#filelist').append(tmplOutput);
			},
			UploadFile: function(up, file){
				$('#' + file.id + ' .label-important').remove();
			}
		},
		init: {
			Refresh: function(up) { /*console.log('Refresh');*/ },
			StateChanged: function(up) { /*console.log('StateChanged');*/ },
			QueueChanged: function(up) { /*console.log('Queue Changed');*/ },
			UploadProgress: function(up, file) {
				$('#' + file.id + " b").html(file.percent + "%");
				$('#' + file.id + ' .progress').show();
				$('#' + file.id + ' .bar').css({ width: file.percent + '%' });
			},
			FilesAdded: function(up, files) {
				var dataTmpl = { listaArchivos: new Array() };
				$.each(files, function(i, file){
					if(file.status == 1)
						dataTmpl.listaArchivos.push({
							id: file.id,
							name: file.name,
							size: function(){
								if(typeof file.size == 'undefined') return file.size;
								else return (file.size > 1048576) ? Math.round((file.size / 1048576) * 10) / 10 + ' MB' : Math.round((file.size / 1024) * 10) / 10 + ' KB';
							}
						});
				});
				var tmplFilesAdd = $('#tmplFilesAdd').html();
				var tmplOutput = Mustache.render(tmplFilesAdd, dataTmpl);
				$('#filelist').append(tmplOutput);
				up.refresh(); // Reposition Flash/Silverlight
			},
			FilesRemoved: function(up, files) { /*console.log('Files Removed');*/ },
			FileUploaded: function(up, file, info) {
				data = $.parseJSON(info.response);
				$('#' + file.id + " b").html('<i class="icon icon-ok icon-white"></i> Completo').removeClass('label-info').addClass('label-success');
				$('#' + file.id).delay(3000).fadeOut(function(){ $(this).remove(); up.refresh(); });
			},
			ChunkUploaded: function(up, file, info) { /*console.log('Chunk Uploaded');*/ },
			Error: function(up, args) {
				var dataTmpl = { listaArchivos: new Array() };
				var tmplError = $('#tmplError').html();
				var tmplOutput = Mustache.render(tmplError, args);
				$('#filelist').append(tmplOutput);
				up.refresh();
				// Para que se eliminen
				$('#' + args.file.id).delay(3000).fadeOut(function(){ $(this).remove(); up.refresh(); });
			}
		}
	});

	uploader.init();

	// Comenzar la carga de archivos
	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});
	
	// Limpiar la lista de archivos
	$('#clearlist').click(function(e){
		e.preventDefault();
		var archivos = new Array();
		jQuery.each(uploader.files,function(clave,valor){ archivos.push(valor); });
		jQuery.each(archivos,function(clave, valor){
			if(valor.status == 1){//
				uploader.removeFile(valor);
				$('#' + valor.id).fadeOut(function(){ $(this).remove(); });
			}
		});
		uploader.refresh();
	});
	
	// Eliminar un elemento de la lista
	$('#filelist').on('click','.label-important',function(e){
		var $this = $(this);
		var id_archivo = $this.closest('li').attr('id');
		var archivo = uploader.getFile(id_archivo);
		if(archivo.status == 1){
			uploader.removeFile(archivo);
			$('#' + id_archivo).fadeOut(function(){ $(this).remove(); });
		}
		uploader.refresh();
	});
	
	// Galeria
	$('#galeria-li').on('click','a',function(e){
		e.preventDefault();
		var $this = $(this);
		var nombre = $this.data('nombre');
		var image = $this.data('image');
		$('#myModal h3').text(nombre);
		$('#modal-image').attr({ src: image });
		$('#modal-input').val(image);
		$('#myModal').modal('show');
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
			<!--<p><a href="#subir" data-toggle="tab" class="btn btn-primary btn-large">Subir</a></p>-->
		</div><!-- /.hero-unit -->
		<ul id="galeria-li" class="unstyled">
			<?php
			/*
			Abro el directorio con las imagenes y recojo las extensiones validas (jpg,jpeg,png,gif)
			Luego las ingreso en una array en donde cada imagen tiene como indice la fecha de modificacion
			Luego aplico un reverse sort por clave sobre ese array.
			*/
			$archivosArray = array();
			$folderUpload = 'uploads/';
			$dp = opendir($folderUpload);
			while($file = readdir($dp)):
			if(preg_match('/\.(jpg|jpeg|png|gif)$/',$file)):
				$archivosArray[filectime($folderUpload . $file)] = $file;
			endif; endwhile;
			krsort($archivosArray);
			/*
			Ahora muestro las fotos con el array ordenado
			*/
			foreach($archivosArray as $file):
			$imgInfo = getimagesize($folderUpload . $file);
			$id = sha1($file . time());
			?>
			<li id="<?php echo $id; ?>">
				<ul class="unstyled thumbnails pull-left">
					<li>
						<a class="thumbnail" href="javascript:;" data-image="<?php echo $folderUpload . $file; ?>" data-nombre="<?php echo $file; ?>" style="width:240px;">
							<img src="<?php echo $folderUpload . $file; ?>" width="240" />
						</a>
					</li>
				</ul>
				<div class="pull-left" style="margin-left:10px;">
					<input type="text" class="input-xxlarge" value="<?php echo $folderUpload . $file; ?>" placeholder="nada" />
					<br />
					<span class="label"><?php echo $imgInfo[0]; ?> x <?php echo $imgInfo[1]; ?> px</span>
					<div class="btn-toolbar">
						<div class="btn-group">
							<a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
								Cortar
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
								<li><a href="#">800x600</a></li>
								<li><a href="#">640x480</a></li>
								<li><a href="#">480x320</a></li>
								<li><a href="#">320x240</a></li>
							</ul>
						</div><!-- /.btn-group -->
						<div class="btn-group">
							<a href="#" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
								Copiar
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
								<li><a href="#">800x600</a></li>
								<li><a href="#">640x480</a></li>
								<li><a href="#">480x320</a></li>
								<li><a href="#">320x240</a></li>
							</ul>
						</div><!-- /.btn-group -->
						<div class="btn-group">
							<button class="btn btn-danger">Eliminar</button>
						</div><!-- /.btn-group -->
					</div><!-- /.btn-toolbar -->
				</div><!-- /.pull-left -->
				<!--<div class="clearfix"></div>-->
			</li>
			<?php endforeach; ?>
			<?php
			closedir($dp);
			?>
		</ul>
	  </div><!-- /#imagenes -->
	  <div class="tab-pane" id="subir">
		<!--<div class="hero-unit">
			<h1>Sube tus imagenes</h1>
			<p>Selecciona tus imagenes y espera a que suban.</p>
		</div>--><!-- /.hero-unit -->
		<div id="container">
			<ul id="filelist" class="nav nav-list"></ul>
			<div class="form-actions">
				<a id="pickfiles" href="#" class="btn btn-primary">Seleccionar archivos</a>
				<a id="uploadfiles" href="#" class="btn btn-success">Subir Archivos</a>
				<a id="clearlist" href="#" class="btn btn-warning">Borrar Lista</a>
			</div><!-- /.form-actions -->
		</div><!-- /#container -->

	  </div><!-- /#subir -->
	</div><!-- /#tab-content -->
</div><!-- /.tabbable -->

<div id="tmplFilesAdd" class="templates">
	{{#listaArchivos}}
	<li id="{{id}}" data-id="{{id}}" data-size="{{size}}" data-name="{{name}}" data-role="archivo">
		<a href="javascript:;">{{name}} {{#size}}<span class="label label-info">{{size}}</span>{{/size}} <b class="label label-info">Esperando</b> <span class="label label-important">eliminar</span></a>
		<div class="progress progress-success progress-striped active" style="display:none;">
			<div class="bar" style="width:0%;"></div>
		</div>
	</li>
	{{/listaArchivos}}
</div><!-- /#tmplFilesAdd -->

<div id="tmplError" class="templates">
	{{#file}}
	<li id="{{file.id}}" data-role="error">
	{{/file}}
	{{^file}}
	<li data-role="error">
	{{/file}}
		<a href="javascript:;">Error: <span class="label label-warning">{{code}}</span> - <span class="label label-warning">{{message}}</span> {{#file}} Archivo: <span class="label label-warning">{{file.name}}</span>{{/file}}</a>
	</li>
</div><!-- /#tmplError -->

<div id="tmplRuntime" class="templates">
	<li class="nav-header">Cargador: <span class="label label-success">{{runtime}}</span></li>
	{{#features.dragdrop}}
	<li class="active"><a href="javascript:;">Arrastre Aqui Para agregar archivos</a></li>
	{{/features.dragdrop}}
</div><!-- /#tmplRuntime -->

<div id="tmplGaleriaLi" class="templates">
	{{#listaArchivos}}
	<li id="{{id}}">
		<ul class="unstyled thumbnails pull-left">
			<li>
				<a class="thumbnail" href="javascript:;" data-image="{{ruta}}" data-nombre="{{nombre}}" style="width:240px;">
					<img src="{{ruta}}" width="240" />
				</a>
			</li>
		</ul>
		<div class="pull-left" style="margin-left:10px;">
			<input type="text" class="input-xxlarge" value="{{ruta}}" placeholder="nada" />
			<br />
			<span class="label">{{ancho}} x {{alto}} px</span>
			<div class="btn-toolbar">
				<div class="btn-group">
					<a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
						Cortar
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#">800x600</a></li>
						<li><a href="#">640x480</a></li>
						<li><a href="#">480x320</a></li>
						<li><a href="#">320x240</a></li>
					</ul>
				</div><!-- /.btn-group -->
				<div class="btn-group">
					<a href="#" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
						Copiar
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#">800x600</a></li>
						<li><a href="#">640x480</a></li>
						<li><a href="#">480x320</a></li>
						<li><a href="#">320x240</a></li>
					</ul>
				</div><!-- /.btn-group -->
				<div class="btn-group">
					<button class="btn btn-danger">Eliminar</button>
				</div><!-- /.btn-group -->
			</div><!-- /.btn-toolbar -->
		</div><!-- /.pull-left -->
		<!--<div class="clearfix"></div>-->
	</li>
	{{/listaArchivos}}
</div><!-- /#tmplGaleriaLi -->

</body>
</html>
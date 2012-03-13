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
<script type="text/javascript">
<!--
$(function(){
	
});
//-->
</script>
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
		<div class="hero-unit">
			<h1>Sube tus imagenes</h1>
			<p>Selecciona tus imagenes y espera a que suban.</p>
		</div><!-- /.hero-unit -->
	  </div><!-- /#subir -->
	</div><!-- /#tab-content -->
</div><!-- /.tabbable -->

</body>
</html>
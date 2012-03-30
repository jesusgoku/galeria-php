<?php
require('config/config.php');
require('config/config-galeria.php');

$dataResponse = array(
	'jsonrpc' => '2.0',
	'result' => true,
	'id' => '',
	'error' => array(),
	'msj' => '',
	'tipoMsj' => MSJ_ERROR,
	'dataProce' => array()
);

$error = array();
$tipoMsj = MSJ_ERROR;
$msj = '';

if(isset($_REQUEST['accion'])){
	$accion = $_REQUEST['accion'];
	
	switch($accion){
		case 1:
			// Guardar FotoGrafia
			
			/**
			 * upload.php
			 *
			 * Copyright 2009, Moxiecode Systems AB
			 * Released under GPL License.
			 *
			 * License: http://www.plupload.com/license
			 * Contributing: http://www.plupload.com/contributing
			 */
			
			// HTTP headers for no cache etc
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			
			// Settings
			//$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
			$targetDir = 'uploads/';
			
			//$cleanupTargetDir = false; // Remove old files
			//$maxFileAge = 60 * 60; // Temp file age in seconds
			
			// 5 minutes execution time
			@set_time_limit(5 * 60);
			
			// Uncomment this one to fake upload time
			// usleep(5000);
			
			// Get parameters
			$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
			$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
			$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
			
			// Clean the fileName for security reasons
			$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
			
			// Make sure the fileName is unique but only if chunking is disabled
			if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
				$ext = strrpos($fileName, '.');
				$fileName_a = substr($fileName, 0, $ext);
				$fileName_b = substr($fileName, $ext);
			
				$count = 1;
				while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
					$count++;
			
				$fileName = $fileName_a . '_' . $count . $fileName_b;
			}
			
			// Create target dir
			if (!file_exists($targetDir))
				@mkdir($targetDir);
			
			// Remove old temp files
			/* this doesn't really work by now
				
			if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
				while (($file = readdir($dir)) !== false) {
					$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;
			
					// Remove temp files if they are older than the max age
					if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
						@unlink($filePath);
				}
			
				closedir($dir);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			*/
			
			// Look for the content type header
			if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
				$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
			
			if (isset($_SERVER["CONTENT_TYPE"]))
				$contentType = $_SERVER["CONTENT_TYPE"];
			
			// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
			if (strpos($contentType, "multipart") !== false) {
				if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
					// Open temp file
					$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
					if ($out) {
						// Read binary input stream and append it to temp file
						$in = fopen($_FILES['file']['tmp_name'], "rb");
			
						if ($in) {
							while ($buff = fread($in, 4096))
								fwrite($out, $buff);
						} else $error = array('code' => 101, 'message' => 'Failed to open input stream.');
						fclose($in);
						fclose($out);
						@unlink($_FILES['file']['tmp_name']);
						
					} else $error = array('code' => 102, 'message' => 'Failed to open output stream.');
					
				} else $error = array('code' => 103, 'message' => 'Failed to move uploaded file.');
			} else {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen("php://input", "rb");
			
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else $error = array('code' => 101, 'message' => 'Failed to open input stream.');
			
					fclose($in);
					fclose($out);
				} else $error = array('code' => 102, 'message' => 'Failed to open output stream.');
			}
			
			// Return JSON-RPC response
			$result = empty($error) ? true : false;
			if($result){
				// La Fotografia se guardo correctamente
				$tipoMsj = MSJ_OK;
				if(( $chunks == 0 && $chunk == 0 ) || $chunk == ($chunks - 1)){
					
					require('libs/asido/class.asido.php');
					asido::driver('gd');
					
					$img_rsz = asido::image($targetDir . $fileName, $targetDir . 'thumbs/' . $fileName);
					asido::width($img_rsz,240);
					$img_rsz->save(ASIDO_OVERWRITE_ENABLED);
					
					$imgInfo = getimagesize($targetDir . $fileName);
					$imgId = sha1(time());
					$dataProce['listaArchivos'] = array(
						'id' => $imgId,
						'name' => $fileName,
						'width' => $imgInfo[0],
						'height' => $imgInfo[1],
						'ruta' => $targetDir . $fileName,
						'ruta_thumb' => $targetDir . 'thumbs/' . $fileName
					);
					
				}
			}else{
				// No se guardo la fotografia
			}
			
			break;
		case 3:
			// Eliminar Foto
			
			$msj = 'eliminado';
			$tipoMsj = MSJ_OK;
			break;
		case 5:
			// Lista de Ficheros
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
			$archivosProce = array();
			foreach($archivosArray as $archivo):
				$fileInfo = getimagesize($folderUpload . $archivo);
				$fileId = sha1(time() . $archivo);
				$archivosProce[] = array(
						'id' => $fileId,
						'name' => $archivo,
						'width' => $fileInfo[0],
						'height' => $fileInfo[1],
						'ruta' => $folderUpload . $archivo,
						'ruta_thumb' => $folderUpload . 'thumbs/' . $archivo
				);
			endforeach;
			$dataProce['listaArchivos'] = $archivosProce;
			break;
		default: $msj = 'opcion no valida';
	}
}else $msj = 'origen incorrecto';

$dataResponse['error'] = isset($error) ? $error : array();
$dataResponse['dataProce'] = isset($dataProce) ? $dataProce : array();
$dataResponse['tipoMsj'] = isset($tipoMsj) ? $tipoMsj : MSJ_ERROR;
$dataResponse['result'] = isset($result) ? $result : false;

echo json_encode($dataResponse);

?>
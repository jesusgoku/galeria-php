<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Misc
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Common file for all "ImLib2" based solutions which stores all the 
* supported file formats
*
* @package Asido
* @subpackage Asido.Misc
*/
Class asido_imlib2 {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Maps to supported mime types for saving files
	* @var array
	*/
	Public Static $__mime = array(
	
		// support reading
		//
		'read' => array(
			
			// BMP
			//
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/wbmp',
			'image/bmp',
			
			// GIF
			//
			'application/x-gif',
			'application/gif',
			'image/gif',
			'image/x-gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/jpg',
			'image/jpeg',

			// PBM
			//
			'application/x-portable-bitmap',
			'image/x-portable-bitmap',
			'image/x-portable-anymap',
			'image/x-portable/anymap',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',
			
			// TGA
			//
			'application/tga',
			'application/x-tga',
			'application/x-targa',
			'image/tga',
			'image/x-tga',
			'image/targa',
			'image/x-targa',

			// TIFF
			//
			'image/x-tif',
			'image/x-tiff',
			'application/tif',
			'application/x-tif',
			'application/tiff',
			'application/x-tiff',
			'image/tif',
			'image/tiff',
			
			// XPM
			//
			'image/x-xpixmap',
			'image/x-xpm',
			),

		// support writing
		//
		'write' => array(
		
			// BMP
			//
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/wbmp',
			'image/bmp',
			
			// GIF
			//
			'application/x-gif',
			'application/gif',
			'image/gif',
			'image/x-gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/jpg',
			'image/jpeg',

			// PBM
			//
			'application/x-portable-bitmap',
			'image/x-portable-bitmap',
			'image/x-portable-anymap',
			'image/x-portable/anymap',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',
			
			// TGA
			//
			'application/tga',
			'application/x-tga',
			'application/x-targa',
			'image/tga',
			'image/x-tga',
			'image/targa',
			'image/x-targa',

			// TIFF
			//
			'image/x-tif',
			'image/x-tiff',
			'application/tif',
			'application/x-tif',
			'application/tiff',
			'application/x-tiff',
			'image/tif',
			'image/tiff',
			
			// XPM
			//
			'image/x-xpixmap',
			'image/x-xpm',

			)
		);

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* MIME-type to image format map
	*
	* This is used for conversion and saving ONLY, so  
	* read-only file formats should not appear here
	*
	* @var array
	*/
	Public Static $__mime_map = array(
	
		// BMP
		//
		'image/x-bmp' => 'BMP',
		'image/x-bitmap' => 'BMP',
		'image/x-xbitmap' => 'BMP',
		'image/x-win-bitmap' => 'BMP',
		'image/x-windows-bmp' => 'BMP',
		'image/ms-bmp' => 'BMP',
		'image/x-ms-bmp' => 'BMP',
		'application/bmp' => 'BMP',
		'application/x-bmp' => 'BMP',
		'application/x-win-bitmap' => 'BMP',
		'image/wbmp' => 'BMP',
		'image/bmp' => 'BMP',
		
		// GIF
		//
		'application/x-gif' => 'GIF',
		'application/gif' => 'GIF',
		'image/gif' => 'GIF',
		'image/x-gif' => 'GIF',
		
		// JPEG
		//
		'application/jpg' => 'GIF',
		'application/x-jpg' => 'GIF',
		'image/pjpeg' => 'GIF',
		'image/pipeg' => 'GIF',
		'image/jpg' => 'GIF',
		'image/jpeg' => 'GIF',

		// PBM
		//
		'application/x-portable-bitmap' => 'PNM',
		'image/x-portable-bitmap' => 'PNM',
		'image/x-portable-anymap' => 'PNM',
		'image/x-portable/anymap' => 'PNM',
		
		// PNG
		//
		'application/png' => 'PNG',
		'application/x-png' => 'PNG',
		'image/x-png' => 'PNG',
		'image/png' => 'PNG',
		
		// TGA
		//
		'application/tga' => 'TGA',
		'application/x-tga' => 'TGA',
		'application/x-targa' => 'TGA',
		'image/tga' => 'TGA',
		'image/x-tga' => 'TGA',
		'image/targa' => 'TGA',
		'image/x-targa' => 'TGA',

		// TIFF
		//
		'image/x-tif' => 'TIFF',
		'image/x-tiff' => 'TIFF',
		'application/tif' => 'TIFF',
		'application/x-tif' => 'TIFF',
		'application/tiff' => 'TIFF',
		'application/x-tiff' => 'TIFF',
		'image/tif' => 'TIFF',
		'image/tiff' => 'TIFF',
		
		// XPM
		//
		'image/x-xpixmap' => 'XPM',
		'image/x-xpm' => 'XPM',
	
		);
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_IMagick
*/
require_once ASIDO_DIR . "/class.imagick.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Imagick" driver (as extension)
*
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
*/
Class asido_driver_Imagick_Ext Extends asido_driver {

	/**
	* Maps to supported mime types for saving files
	* @var array
	* @access protected
	*/
	Protected $__mime = array(

		// support reading
		//
		'read' => array(

			),

		// support writing
		//
		'write' => array(

			)
		);

	/**
	* MIME-type to image format map
	*
	* This is used for conversion and saving ONLY, so  
	* read-only file formats should not appear here
	*
	* @var array
	* @access protected
	*/
	Protected $__mime_map = array();

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Constructor
	*/
	Public Function __construct() {
		$this->__mime = asido_imagick::$__mime;
		$this->__mime_map = asido_imagick::$__mime_map;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	Public Function is_compatible() {

		if (!extension_loaded('imagick')) {
			trigger_error(
				'The asido_driver_Imagick_Ext driver is '
					. ' unnable to be initialized, '
					. ' because the IMagick (php_imagick) '
					. ' module is not installed',
				E_USER_ERROR
				);
			return false;
			}
		
		// give access to all the memory
		//
		@ini_set("memory_limit", -1);
		
		// no time limit
		//
		@set_time_limit(-1);
		
		return true;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Do the actual resize of an image
	*
	* @param asido_tmp $tmp
	* @param integer $width
	* @param integer $height
	* @return boolean
	* @access protected
	*/
	Protected Function __resize(asido_tmp $tmp, $width, $height) {
		return imagick_resize($tmp->target,
			$width, $height, IMAGICK_FILTER_UNKNOWN, 0);
		}

	/**
	* Copy one image to another
	*
	* @param asido_tmp $tmp_target
	* @param asido_tmp $tmp_source
	* @param integer $destination_x
	* @param integer $destination_y
	* @return boolean
	* @access protected
	*/
	Protected Function __copy(asido_tmp $tmp_target, asido_tmp $tmp_source, $destination_x, $destination_y) {
		return imagick_composite(
			$tmp_target->target, IMAGICK_COMPOSITE_OP_OVER,
			$tmp_source->source,
			$destination_x, $destination_y);
		}

	/**
	* Make the image greyscale: not supported
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __grayscale(asido_tmp $tmp) {
		return false;
		}

	/**
	* Rotate the image clockwise: only rectangular rotates are supported (90,180,270)
	*
	* @param asido_tmp $tmp
	* @param float $angle
	* @param asido_color $color
	* @return boolean
	* @access protected
	*/
	Protected Function __rotate(asido_tmp $tmp, $angle, asido_color $color) {

		// skip full loops
		//
		if (($angle % 360) == 0) {
			return true;
			}

		// rectangular rotates are OK
		//
		if (($angle % 90) == 0) {
			if (imagick_rotate($tmp->target, $angle)) {
				$tmp->image_width = imagick_getWidth($tmp->target);
				$tmp->image_height = imagick_getHeight($tmp->target);
				return true;
				}
			}
		
		return false;
		}

	/**
	* Crop the image 
	*
	* @param asido_tmp $tmp
	* @param integer $x
	* @param integer $y
	* @param integer $width
	* @param integer $height
	* @return boolean
	* @access protected
	*/
	Protected Function __crop(asido_tmp $tmp, $x, $y, $width, $height) {
		if (!imagick_crop($tmp->target, $x, $y, $width, $height)) {
			return false;
			}
		return true;
		}

	/**
	* Vertically mirror (flip) the image
	* 
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __flip(asido_tmp $tmp) {
		return imagick_flip($tmp->target);
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param asido_image &$image
	* @return boolean
	* @access protected
	*/
	Protected Function __flop(asido_tmp $tmp) {
		return imagick_flop($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Get canvas
	*
	* @param integer $width
	* @param integer $height
	* @param asido_color $color
	* @return asido_tmp
	* @access protected
	*/
	Protected Function __canvas($width, $height, asido_color $color) {
		
		list($r, $g, $b) = $color->get();
		
		$t = new asido_tmp;
		$t->target = imagick_getCanvas(
			"rgb($r, $g, $b)",
			$width, $height
			);
		
		$t->image_width = $width;
		$t->image_height = $height;

		return $t;
		}

	/**
	* Generate a temporary object for the provided argument
	*
	* @param mixed &$handler
	* @param string $filename the filename will be automatically generated 
	*	on the fly, but if you want you can use the filename provided by 
	*	this argument
	* @return asido_tmp
	* @access protected
	*/
	Protected Function __tmpimage($handler, $filename=null) {

		if (!isset($filename)) {
			$filename = $this->__tmpfile();
			}

		imagick_convert($handler, "PNG");
		imagick_writeImage($handler, $filename);
			// ^
			// PNG: no pixel losts

		return $this->prepare(
			new asido_image($filename)
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Open the source and target image for processing it
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __open(asido_tmp $tmp) {

		$error_open = !($tmp->source = imagick_readImage(realpath($tmp->source_filename)));
		$error_open &= !($tmp->target = imagick_cloneHandle($tmp->source));
			
		// get width & height of the image
		//
		if (!$error_open) {
			$tmp->image_width = imagick_getWidth($tmp->source);
			$tmp->image_height = imagick_getHeight($tmp->source);
			}

		return !$error_open;
		}

	/**
	* Write the image after being processed
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __write(asido_tmp $tmp) {
		
		$ret = false;

		if ($tmp->save) {

			// convert, then save
			//
			imagick_convert(
				$tmp->target, $this->__mime_map[$tmp->save]
				);

			$t = $this->__tmpfile();
			if (!imagick_writeImage($tmp->target, $t)) {
				return false;
				}
			
			$ret = @copy($t, $tmp->target_filename);
			@unlink($t);

			} else {

			// weird ... only works with absolute names
			//
			fclose(fopen($tmp->target_filename, 'w'));

			// no convert, just save
			//
			$ret = imagick_writeImage(
				$tmp->target, realpath($tmp->target_filename)
				);
			}
		
		// dispose
		//
		@$this->__destroy_source($tmp);
		@$this->__destroy_target($tmp);

		return $ret;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Destroy the source for the provided temporary object
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/	
	Protected Function __destroy_source(asido_tmp $tmp) {
		return imagick_free($tmp->source);
		}

	/**
	* Destroy the target for the provided temporary object
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/	
	Protected Function __destroy_target(asido_tmp $tmp) {
		return imagick_free($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
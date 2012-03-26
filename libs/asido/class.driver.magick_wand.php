<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Magick_Wand
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_IMagick
*/
require_once ASIDO_DIR . '/class.imagick.php';

/////////////////////////////////////////////////////////////////////////////

/**
* Filter used when resizing images
* @see asido_driver_Magick_Wand::Resize()
*/
if (!defined('ASIDO_MW_RESIZE_FILTER') && defined('MW_GaussianFilter')) {
	define('ASIDO_MW_RESIZE_FILTER', MW_GaussianFilter);
	}

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Magick Wand" driver
*
* @package Asido
* @subpackage Asido.Driver.Magick_Wand
*
* @see http://www.magickwand.org/
*/
Class asido_driver_magick_wand Extends asido_driver {

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
	* @access private
	*/
	Private $__mime_map = array();

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Constructor
	*/
	Public Function __construct() {
		$imagick = new Asido_IMagick;
		$this->__mime = $imagick->__mime;
		$this->__mime_map = $imagick->__mime_map;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	Public Function is_compatible() {

		if (!extension_loaded('magickwand')) {
			trigger_error(
				'The asido_driver_Magick_Wand driver is '
					. ' unnable to be initialized, '
					. ' because the MagickWand (php_magickwand) '
					. ' module is not installed',
				E_USER_ERROR
				);
			return false;
			}
		
		// give access to all the memory
		//
		@ini_set("memory_limit", -1);
		
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
		return MagickResizeImage(
			$tmp->target, $width, $height, ASIDO_MW_RESIZE_FILTER, 0
			);
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

		return MagickCompositeImage(
			$tmp_target->target, $tmp_source->source,
			MW_OverCompositeOp,
			$destination_x, $destination_y);
		}

	/**
	* Make the image greyscale
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __grayscale(asido_tmp $tmp) {
		return MagickSetImageType($tmp->target, MW_GrayscaleType);
		}

	/**
	* Rotate the image clockwise
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
		
		list($r, $g, $b) = $color->get();
		$ret = MagickRotateImage(
			$tmp->target,
			NewPixelWand("rgb($r,$g,$b)"),
			$angle
			);
		
		$tmp->image_width = MagickGetImageWidth($tmp->target);
		$tmp->image_height = MagickGetImageHeight($tmp->target);
		
		return $ret;
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
		if (!MagickCropImage($tmp->target, $width, $height, $x, $y)) {
			return false;
			}

		$t = NewMagickWand();
		MagickNewImage($t, $width, $height);
		if (!MagickCompositeImage($t, $tmp->target, MW_OverCompositeOp, 0, 0)) {
			return false;
			}

		$this->__destroy_target($tmp);
		$tmp->target = $t;
		$tmp->image_width = $width;
		$tmp->image_height = $height;

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
		return MagickFlipImage($tmp->target);
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __flop(asido_tmp $tmp) {
		return MagickFlopImage($tmp->target);
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
		
		$t = new asido_tmp;
		$t->target = NewMagickWand();
		
		list($r, $g, $b) = $color->get();
		MagickNewImage(
			$t->target,
			$width, $height,
			sprintf("#%02x%02x%02x", $r, $g, $b)
			);
		
		$t->image_width = $width;
		$t->image_height = $height;

		return $t;
		}

	/**
	* Generate a temporary object for the provided argument
	*
	* @param mixed $handler
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

		MagickSetImageFormat($handler, "PNG");
		MagickWriteImage($handler, $filename);
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

		$tmp->source = NewMagickWand();
		$error_open = !MagickReadImage(
			$tmp->source, $tmp->source_filename);
		$error_open &= !($tmp->target = CloneMagickWand(
			$tmp->source));
			
		// get width & height of the image
		//
		if (!$error_open) {
			$tmp->image_width = MagickGetImageWidth($tmp->source);
			$tmp->image_height = MagickGetImageHeight($tmp->source);
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
			MagickSetImageFormat(
				$tmp->target, $this->__mime_map[$tmp->save]
				);

			$t = $this->__tmpfile();
			if (!MagickWriteImage($tmp->target, $t)) {
				return false;
				}
			
			$ret = @copy($t, $tmp->target_filename);
			@unlink($t);
			} else {

			// no convert, just save
			//
			$ret = MagickWriteImage(
				$tmp->target, $tmp->target_filename
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
		return DestroyMagickWand($tmp->source);
		}

	/**
	* Destroy the target for the provided temporary object
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/	
	Protected Function __destroy_target(asido_tmp $tmp) {
		return DestroyMagickWand($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
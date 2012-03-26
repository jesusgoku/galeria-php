<?php
/**
* @package Asido
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @subpackage Asido.Driver.ImLib2_Extension
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_ImLib2
*/
require_once ASIDO_DIR . "/class.imlib2.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido ImLib2 driver (as extension)
*
* <b>This driver is EXPERIMENTAL</b>
*
* @package Asido
* @subpackage Asido.Driver.ImLib2_Extension
*
* @see http://pp.siedziba.pl/
* @see http://mmcc.cx/php_imlib/index.php
*/
Class asido_driver_imlib2_ext Extends asido_driver {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Maps to supported mime types for saving files
	* @var array
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
	Protected $__mime_map = array();

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Constructor
	*/
	Public Function __constructor() {
		
		/// supported files
		//
		$this->__mime = asido_imlib2::$__mime;
		$this->__mime_map = asido_imlib2::$__mime_map;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	Public Function is_compatible() {
		
		if (!extension_loaded('imlib')) {
			trigger_error(
				'The asido_driver_ImLib driver is unnable to be '
					. ' initialized, because the ImLib2 (php_imlib) '
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

		// create new target
		//
		if (!$_ = imlib_create_scaled_image($tmp->target, $width, $height)) {
			return false;
			}

		// set new target
		//
		$this->__destroy_target($tmp);
		$tmp->target = $_;

		return $r;
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

		return imlib_blend_image_onto_image(
			$tmp_target->target,
			$tmp_source->source,
			1, // malpha ? merge_alpha ?
			0, 0,
			$tmp_source->image_width, $tmp_source->image_height,
			$destination_x, $destination_y,
			$tmp_source->image_width, $tmp_source->image_height,
			'0', '1', '0' // ???
			);
		
		return $r;
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
			if ($_ = imlib_create_rotated_image($tmp->target, $angle)) {

				$this->__destroy_target($tmp);
				$tmp->target = $_;

				$tmp->image_width = imlib_image_get_width($tmp->target);
				$tmp->image_height = imlib_image_get_height($tmp->target);
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

        	if (!$_ = imlib_create_cropped_image($tmp->target, $x, $y, $width, $height)) {
        		return false;
        		}
		$this->__destroy_target($tmp);
		$tmp->target = $_;

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
		return imlib_image_flip_vertical($tmp->target);
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __flop(asido_tmp $tmp) {
		return imlib_image_flip_horizontal($tmp->target);
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
		$t->target = imlib_create_image($width, $height);
		
		list($r, $g, $b) = $color->get();
		imlib_image_fill_rectange(
			$t->target,
			1, 1, 
			$width, $height,
			$r, $g, $b, 255
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

		imlib_image_set_format($handler, "PNG");
		imlib_save_image($handler, $filename);
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

		$error_open = !($tmp->source = imlib_load_image(realpath($tmp->source_filename)));
		$error_open &= !($tmp->target = imlib_clone_image($tmp->source));
			
		// get width & height of the image
		//
		if (!$error_open) {
			$tmp->image_width = imlib_image_get_width($tmp->source);
			$tmp->image_height = imlib_image_get_height($tmp->source);
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
			imlib_image_set_format(
				$tmp->target, $this->__mime_map[$tmp->save]
				);

			$t = $this->__tmpfile();
			if (!imlib_save_image($tmp->target, $t)) {
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
			$ret = imlib_save_image(
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
	* @abstract
	*/	
	Protected Function __destroy_source(asido_tmp $tmp) {
		return imlib_free_image($tmp->source);
		}

	/**
	* Destroy the target for the provided temporary object
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	* @abstract
	*/	
	Protected Function __destroy_target(asido_tmp $tmp) {
		return imlib_free_image($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
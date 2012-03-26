<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.GD
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see asido_driver_GD
*/
require_once ASIDO_DIR . '/class.driver.gd.php';

/////////////////////////////////////////////////////////////////////////////

/**
* Asido GD(GD2) driver with some of the unsupported methods hacked via some work-arounds.
*
* @package Asido
* @subpackage Asido.Driver.GD
*/
Class asido_driver_gd_hack Extends asido_driver_gd {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Make the image greyscale
	*
	* @param asido_tmp $tmp
	* @return boolean
	* @access protected
	*/
	Protected Function __grayscale(asido_tmp $tmp) {

		// the longer path: do it pixel by pixel
		// 
		if (parent::__grayscale($tmp)) {
			return true;
			}

		// create 256 color palette
		//
		$palette = array();
		for ($c=0; $c<256; $c++) {
			$palette[$c] = imageColorAllocate($tmp->target, $c, $c, $c);
			}

		// read origonal colors pixel by pixel
		//
		for ($y=0; $y<$tmp->image_height; $y++) {
			for ($x=0; $x<$tmp->image_width; $x++) {

				$rgb = imageColorAt($tmp->target, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$gs = (($r*0.299)+($g*0.587)+($b*0.114));
				imageSetPixel($tmp->target, $x, $y, $palette[$gs]);
				}
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
		
		$t = imageCreateTrueColor($tmp->image_width, $tmp->image_height);
		imageAlphaBlending($t, true);

		for ($y = 0; $y < $tmp->image_height; ++$y) {
			imageCopy(
				$t, $tmp->target,
				0, $y,
				0, $tmp->image_height - $y - 1,
				$tmp->image_width, 1
				);
			}
		imageAlphaBlending($t, false);

		$this->__destroy_target($tmp);
		$tmp->target = $t;

		return true;
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param asido_image &$image
	* @return boolean
	* @access protected
	*/
	Protected Function __flop(asido_tmp $tmp) {

		$t = imageCreateTrueColor($tmp->image_width, $tmp->image_height);
		imageAlphaBlending($t, true);

		for ($x = 0; $x < $tmp->image_width; ++$x) {
			imageCopy(
				$t,
				$tmp->target,
				$x, 0,
                		$tmp->image_width - $x - 1, 0,
                		1, $tmp->image_height
                		);
			}
		imageAlphaBlending($t, false);

		$this->__destroy_target($tmp);
		$tmp->target = $t;

		return true;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
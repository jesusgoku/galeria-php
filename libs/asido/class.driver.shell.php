<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver
* @version $Id$
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Set the ASIDO_NICE_LEVEL constant up w/ the nice level (for *nix systems) for 
* the priority of shell commands. The allowed values are 1 to 19, where 19 is the 
* lowest priority. Providing 0 as nice level will disable calling nice at all 
* and the shell command will be called without using it.
*/
if (!defined('ASIDO_NICE_LEVEL')) {
	define('ASIDO_NICE_LEVEL', '0');
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Set the ASIDO_START_PRIORITY constant up w/ the process priority level (for 
* windows-based systems). The allowed values are 'LOW', 'NORMAL', 'HIGH', 
* 'REALTIME', 'ABOVENORMAL' and 'BELOWNORMAL'. Any other value will disable this 
* feature and the shell command will be called without using this feature.
*/
if (!defined('ASIDO_START_PRIORITY')) {
	define('ASIDO_START_PRIORITY', 'NORMAL');
	}

/////////////////////////////////////////////////////////////////////////////

/**
* Common file for all "shell" based solutions 
*
* @package Asido
* @subpackage Asido.Driver
*
* @abstract
*/
Abstract Class asido_driver_shell Extends asido_driver {
	
	/**
	* Path to the executables
	* @var string
	* @access private
	*/
	Private $__exec = '';

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Try to locate the program
	* @param string $program
	* @return string
	*
	* @access protected
	*/
	Protected Function __exec($program) {

    		// safe mode ?
    		//
		if (!ini_get('safe_mode') || !$path = ini_get('safe_mode_exec_dir')) {
        		($path = getenv('PATH')) || ($path = getenv('Path'));
    			}

		$executable = false;
		$p = explode(PATH_SEPARATOR, $path);
		$p[] = getcwd();

		$ext = array();		
		if (OS_WINDOWS) {
			$ext = getenv('PATHEXT')
					? explode(PATH_SEPARATOR, getenv('PATHEXT'))
					: array('.exe','.bat','.cmd','.com');
		
			// extension ?
			//
			array_unshift($ext, '');
			}

		// walk the variants
		//
		foreach ($ext as $e) {
			foreach ($p as $dir) {
				$exe = $dir . DIRECTORY_SEPARATOR . $program . $e;

				// *nix only implementation
				//
				if (OS_WINDOWS ? is_file($exe) : is_executable($exe)) {
					$executable = $exe;
					break;
					}
				}
			}

		return $executable;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Run a command
	* @param string $program
	* @param string $args
	* @return string
	* @access protected
	*/
	Protected Function __command($program, $args = '') {

		$priority_prefix = '';

		if (!OS_WINDOWS) {
			
			// Windows systems
			//
			$allowed_priorities = array(
				'LOW', 'NORMAL', 'HIGH', 'REALTIME', 'ABOVENORMAL', 'BELOWNORMAL'
				);
			$start_priority = strToUpper(ASIDO_START_PRIORITY);
			if (in_array($start_priority, $allowed_priorities)) {
				$priority_prefix = "start /B /{$start_priority} ";
				}
			
			} else {

			// *Nix system
			//
			$nice_level = intval(ASIDO_NICE_LEVEL);
			if($nice_level <= 19 && $nice_level > 0) {
				$priority_prefix = "nice -$nice_level ";
				}
			}

		return $priority_prefix . $this->__exec . $program . ' ' . $args;
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
		return unlink($tmp->source);
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
		return unlink($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>
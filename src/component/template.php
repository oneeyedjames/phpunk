<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

class template {
	/**
	 * @ignore internal variable
	 */
    private $_dirs = [];

	/**
	 * @param string $base_dir Directory path containing template files
	 */
    public function __construct($base_dir) {
        $this->_dirs = [$base_dir];
    }

	/**
	 * Adds directories containing template files.
	 * @param string $dir Directory path
	 */
    public function add_folder($dir) {
        $this->_dirs[] = $dir;
    }

	/**
	 * Searches directories for matching template file.
	 * @param string $view Name of the view
	 * @param string $resource Name of the resource
	 * @return string Path to template file
	 */
    public function locate($view, $resource = false) {
		$files = $resource === false ? [$view, 'index'] :
			["$resource/$view", $view, "$resource/index", 'index'];

        foreach ($files as $filename) {
            foreach ($this->_dirs as $dirname) {
				if (is_file("$dirname/$filename.php"))
    				return "$dirname/$filename.php";
    		}
        }

        $filename = $resource ? "$resource/$view.php" : "$view.php";

        trigger_error("Missing template file $filename", E_USER_WARNING);

    	return false;
    }

	/**
	 * Renders matching template with provided parameters.
	 * @param string $view Name of the view
	 * @param string $resource Name of the resource
	 * @param array $vars OPTIONAL Named parameters passed into template
	 */
    public function load($view, $resource = false, $vars = []) {
        if ($file = $this->locate($view, $resource)) {
            extract($vars, EXTR_SKIP);
            include $file;
        }
    }
}

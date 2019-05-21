<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

class template {
    private $_dirs = array();

    public function __construct($base_dir) {
        $this->_dirs = array($base_dir);
    }

    public function add_folder($dir) {
        $this->_dirs[] = $dir;
    }

    public function locate($view, $resource = false) {
		$files = $resource === false ? array($view, 'index') :
			array("$resource/$view", $view, "$resource/index", 'index');

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

    public function load($view, $resource = false, $vars = array()) {
        if ($file = $this->locate($view, $resource)) {
            extract($vars, EXTR_SKIP);
            include $file;
        }
    }
}

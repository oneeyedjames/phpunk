<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

class user {
	protected $_public_name;
	protected $_private_key;

	public function __construct($public_name, $private_key) {
		$this->_public_name = $public_name;
		$this->_private_key = $private_key;
	}

	public function __get($key) {
		if (isset($this->{"_$key"}))
			return $this->{"_$key"};
	}

	public function create_token($algo = 'md5') {
		return create_token($this->public_name, $this->private_key, $algo);
	}

	public function verify_token($token, $algo = 'md5') {
		return verify_token($token, $this->public_name, $this->private_key, $algo);
	}

	public function create_action_token($action, $resource = false, $algo = 'md5') {
		if ($resource) $action = "$resource:$action";
		return create_token($action, $this->private_key, $algo);
	}

	public function verify_action_token($token, $action, $resource = false, $algo = 'md5') {
		if ($resource) $action = "$resource:$action";
		return verify_token($token, $action, $this->private_key, $algo);
	}
}

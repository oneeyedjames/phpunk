<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use function PHPunk\create_token;
use function PHPunk\verify_token;

/**
 * @property string $public_name Public identifier for user, typically a username
 * @property string $private_key Private credential for user, typically a password
 */
class user {
	protected $_public_name;
	protected $_private_key;

	/**
	 * @param string $public_name Public identifier for user
	 * @param string $private_key Private credential for user
	 */
	public function __construct($public_name, $private_key) {
		$this->_public_name = $public_name;
		$this->_private_key = $private_key;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		if (isset($this->{"_$key"}))
			return $this->{"_$key"};
	}

	/**
	 * Generates a random cryptographically-secure token for user.
	 * @param string $algo OPTIONAL Hashing algorithm, defaults to MD5
	 * @return string Cryptographically-secure token
	 */
	public function create_token($algo = 'md5') {
		return create_token($this->public_name, $this->private_key, $algo);
	}

	/**
	 * Validates a cryptographically-secure token for user.
	 * @param string $token Cryptographically-secure token
	 * @param string $algo OPTIONAL Hashing algorithm, defaults to MD5
	 * @return boolean TRUE if token is valid, FALSE otherwise
	 */
	public function verify_token($token, $algo = 'md5') {
		return verify_token($token, $this->public_name, $this->private_key, $algo);
	}

	/**
	 * Generates a random cryptographically-secure token for user and action.
	 * @param string $action Name of API action
	 * @param string $resource OPTIONAL Name of resource
	 * @param string $algo OPTIONAL Hashing algorithm, defaults to  MD5
	 * @return string Cryptographically-secure token
	 */
	public function create_action_token($action, $resource = false, $algo = 'md5') {
		if ($resource) $action = "$resource:$action";
		return create_token($action, $this->private_key, $algo);
	}

	/**
	 * Validates a random cryptographically-secure token for user and action.
	 * @param string $token Cryptographically-secure token
	 * @param string $action Name of API action
	 * @param string $resource OPTIONAL Name of resource
	 * @param string $algo OPTIONAL Hashing algorithm, defaults to  MD5
	 * @return boolean TRUE if token is valid, FALSE otherwise
	 */
	public function verify_action_token($token, $action, $resource = false, $algo = 'md5') {
		if ($resource) $action = "$resource:$action";
		return verify_token($token, $action, $this->private_key, $algo);
	}
}

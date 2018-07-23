<?php

class database_record implements collectible, JsonSerializable {
	use mutable;

	public function __construct($data) {
		$this->loadArray($data);
	}

	public function jsonSerialize() {
		return $this->toArray();
	}

	public function each($func) {
		if (is_callable($func)) {
			foreach ($this as $key => $value) {
				call_user_func($func, $value, $key);
			}
		}
	}
}

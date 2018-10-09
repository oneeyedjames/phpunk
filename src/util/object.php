<?php

namespace PHPunk\Util;

use JsonSerializable;

class object implements collection, JsonSerializable {
	use mutable;

	public function __construct($data = []) {
		$this->loadArray($data);
	}

	public function jsonSerialize() {
		return $this->toArray();
	}
}
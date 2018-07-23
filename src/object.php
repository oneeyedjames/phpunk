<?php

class object implements collectible, JsonSerializable {
	use mutable;

	public function __construct($data = []) {
		$this->loadArray($data);
	}

	public function jsonSerialize() {
		return $this->toArray();
	}
}

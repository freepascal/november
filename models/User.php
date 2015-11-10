<?php

use BeatSwitch\Lock\Callers\Caller as Caller;

// Registerd users
class User implements Caller {
	protected $id;
	
	public function __contruct($user_id) {
		$this->id = $user_id;
	}
	
	// group of this caller
	public function getCallerType() {
		return "november_users";
	}
	
	public function getCallerId() {
		return $this->id;
	}
	
	public function getCallerRoles() {
		return ['Member'];
	}
}

?>

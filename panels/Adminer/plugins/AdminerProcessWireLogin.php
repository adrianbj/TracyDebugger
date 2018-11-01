<?php

$_GET['username'] = ''; // triggers autologin

class AdminerProcessWireLogin {

	public function __construct($server = false, $name = false, $pass = false) {
		$this->server = $server;
		$this->name = $name;
		$this->pass = $pass;
	}

	function credentials() {
		// server, username and password for connecting to database
		return array($this->server, $this->name, $this->pass);
	}

	function login() {
		return true;
	}

}


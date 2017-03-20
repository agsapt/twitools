<?php

namespace Ags\Helper;

class ErrorObject {
	var $code;
	var $message;
	var $data;
	
	function __construct() {
		$this->code = 0;
		$this->message = array();
	}
	
	function addError($msg) {
		$this->message['error'][] = $msg;
	}
	
	function addSuccess($msg) {
		$this->message['success'][] = $msg;
	}

	function getMessage() {
		$ret = '';
		if (!empty($this->message['error'])) $ret .= implode('<br />', $this->message['error']);
		if (!empty($this->message['success'])) $ret .= implode('<br />', $this->message['success']);
		
		return $ret;
	}
	
	function getMessageHTML() {
		$ret = array();
		if (!empty($this->message['error'])) $ret['error'] = implode('<br />', $this->message['error']);
		if (!empty($this->message['success'])) $ret['success'] = implode('<br />', $this->message['success']);
		
		return $ret;
	}
	
	function setCode($code) { $this->code = $code; }
	function setData($data) { $this->data = $data; }
	function setMessage($message) { $this->message = $message; }
}

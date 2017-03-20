<?php

namespace Ags\Form;

use Zend\Form\Form;

class HolForm extends Form  {
	private function decodeMessage($messages) {
		$retval = '';
		foreach ($messages as $key=>$value) {
			if (is_array($value)) $retval .= $this->decodeMessage($value);
			else $retval .= $value . '<br />';
		}

		return $retval;
	}
	public function getMessages() {
		$messages = $this->getInputFilter()->getMessages();
		return $this->decodeMessage($messages);
	}
}
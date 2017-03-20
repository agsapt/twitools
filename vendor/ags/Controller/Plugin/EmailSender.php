<?php

namespace Ags\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class EmailSender extends AbstractPlugin {
	private $config;

    private function doSend($data) {
		if (!empty($data['template'])) {
			$view = new \Zend\View\Renderer\PhpRenderer();
			$resolver = new \Zend\View\Resolver\TemplateMapResolver();
			$resolver->setMap(array('mailTemplate' => $data['template']));
			$view->setResolver($resolver);

			$viewModel  = new \Zend\View\Model\ViewModel();
			$viewModel->setTemplate('mailTemplate')->setVariables(array('userdata'=>$data['userdata']));

			$bodyPart = new \Zend\Mime\Message();
			$bodyMessage = new \Zend\Mime\Part($view->render($viewModel));
			$bodyMessage->type = 'text/html';
			$bodyPart->setParts(array($bodyMessage));
		}

		$message = new Message();
		$message->addFrom($data['from'], $data['fromname']);
		if (is_array($data['to'])) {
			foreach ($data['to'] as $to) $message->addTo($to);
		} else {
			$message->addTo($data['to']);
		}
		$message->setSubject($data['subject']);
		$message->setBody(isset($bodyPart) ? $bodyPart : $data['body']);
		$message->setEncoding('UTF-8');

		// Setup SMTP transport using LOGIN authentication
		$transport = new SmtpTransport();
		$options   = new SmtpOptions(array(
			'name'              => 'hukumonline.com',
			'host'              => $this->config['smtp']['host'],
			'connection_class'  => 'login',
			'connection_config' => array(
				'username' => $this->config['smtp']['username'],
				'password' => $this->config['smtp']['password'],
			),
		));
		$transport->setOptions($options);
		$transport->send($message);
	}

	private function getConfig() {
		$config = $this->getController()->getServiceLocator()->get('Config');
		$this->config = $config['appconfig'];
	}

	function notifyActive($to, $userdata) {
		$this->getConfig();
		$data['from'] = $this->config['smtp']['fromaddress'];
		$data['fromname'] = $this->config['smtp']['fromname'];
		if (is_array($to)) {
			foreach ($to as $t) $data['to'][] = $t;
		} else {
			$data['to'][] = $to;
		}
		$data['subject'] = 'Akun anda telah aktif';
		$data['template'] = __DIR__ . '/../../../../view/application/subscription/email/notifyactive.phtml';
		$data['userdata'] = $userdata;

		return $this->doSend($data);
	}

	function notifyRenewal($to, $userdata) {
		$this->getConfig();
		$data['from'] = $this->config['smtp']['fromaddress'];
		$data['fromname'] = $this->config['smtp']['fromname'];
		if (is_array($to)) {
			foreach ($to as $t) $data['to'][] = $t;
		} else {
			$data['to'][] = $to;
		}
		$data['subject'] = 'Masa aktif anda segera berakhir';
		$data['template'] = __DIR__ . '/../../../../view/application/subscription/email/notifyrenewal.phtml';
		$data['userdata'] = $userdata;

		return $this->doSend($data);
	}
	
	function send($to, $subject, $message) {
		$this->getConfig();
		$data['from'] = $this->config['smtp']['fromaddress'];
		$data['fromname'] = $this->config['smtp']['fromname'];
		if (is_array($to)) {
			foreach ($to as $t) $data['to'][] = $t;
		} else {
			$data['to'][] = $to;
		}
		$data['subject'] = $subject;
		$data['body'] = $message;
		return $this->doSend($data);
	}

	function sendInvoice($to, $userdata) {
		$this->getConfig();
		$data['from'] = $this->config['smtp']['fromaddress'];
		$data['fromname'] = $this->config['smtp']['fromname'];
		if (is_array($to)) {
			foreach ($to as $t) $data['to'][] = $t;
		} else {
			$data['to'][] = $to;
		}
		$data['subject'] = 'Tagihan untuk paket baru';
		$data['template'] = __DIR__ . '/../../../../view/application/subscription/email/invoice.phtml';
		$data['userdata'] = $userdata;

		return $this->doSend($data);
	}
}
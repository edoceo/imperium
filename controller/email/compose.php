<?php
/**
	@file
	@brief Compose a plain text based email, no attachments

*/

$_ENV['title'] = array('Email','Compose');

if (!empty($_SESSION['mail-compose'])) {
	$this->EmailMessage = $_SESSION['mail-compose'];
} else {
	$this->EmailMessage = array();
	$this->EmailMessage['rcpt'] = $_GET['r'];
	$this->EmailMessage['subj'] = null;
	$this->EmailMessage['body'] = null;
}

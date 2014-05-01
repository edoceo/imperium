<?php
/**
	@file
	@brief Sends the Emails
*/

$mail = <<<EOF
From: %head_from%
To: %mail_rcpt%
Reply-To: %head_from%
Content-Transfer-Encoding: 8bit
Content-Type: text/plain; charset="utf-8"
MIME-Version: 1.0
Message-Id: <%head_hash%>
Subject: %head_subj%
X-Mailer: Edoceo Imperium v2013.43

{$_POST['body']}
EOF;

$mail = str_replace('%head_from%', sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']), $mail);
$mail = str_replace('%head_hash%', md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST), $mail);
$mail = str_replace('%head_subj%', $_POST['subj'], $mail);
$mail = str_replace('%mail_rcpt%', $_POST['rcpt'], $mail);


echo '<pre>';
App::sendMail($_POST['rcpt'], $mail);

radix_session::flash('info', 'Email Sent to: ' . implode(',',$mail->getRecipients());

unset($_SESSION['mail-compose']);

// The Application Mail Handler
// $uri = parse_url($_ENV['mail']['smtp']);
// $smtp = new Zend_Mail_Transport_Smtp($uri['host'],array(
// 	'auth' => 'login',
// 	'username' => $uri['user'],
// 	'password' => $uri['pass'],
// 	'ssl'  => 'tls',
// 	'port' => $uri['port'],
// ));

/*
// Add a Part?
$part = new Zend_Mime_Part($req->getPost('text'));
$part->type = 'text/plain';
$part->encoding = Zend_Mime::ENCODING_7BIT;
$part->disposition = Zend_Mime::DISPOSITION_INLINE;
$mail->addAttachment($part);
*/
// Add PDF
/*
$pdf = new InvoicePDF($iv->id);
$part = new Zend_Mime_Part($pdf->render());
$part->filename = 'Invoice-' . $iv->id . '.pdf';
$part->type = 'application/pdf; name="' . $part->filename . '"';
$part->encoding = Zend_Mime::ENCODING_BASE64;
$part->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$mail->addAttachment($part);
*/
//$mail->createAttachment($pdf->render(),'application/pdf',Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,$part->filename);
//         $mail->send($smtp);

$ret = '/email';

if (isset($this->_s->ReturnGood)) {
	$ret = $this->_s->ReturnGood;
	unset($this->_s->ReturnGood);
	$this->_s->ReturnCode = 200;
	$this->_s->ReturnFrom = 'mail';
}

radix::redirect($ret);

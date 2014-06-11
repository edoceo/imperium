<?php
/**
*/

class App_Mail
{

	static function send($rcpt, $mail)
	{
		$uri = parse_url($_ENV['mail']['smtp']);

		if (empty($uri['scheme'])) {
			$uri['scheme'] = 'tcp';
		}

		if (empty($uri['port'])) {
			switch ($uri['scheme']) {
			case 'ssl':
				$uri['port'] = 465;
				break;
			case 'tls':
				$uri['port'] = 587;
				break;
			case 'tcp':
			default:
				$uri['port'] = 25;
				break;
			}
		}

		$mail = str_replace('%head_from%', sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']), $mail);
		$mail = str_replace('%head_hash%', md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST), $mail);

		$smtp = new Net_SMTP2(sprintf('%s://%s', $uri['scheme'], $uri['host']), $uri['port'], $_ENV['application']['host']);
		$smtp->setDebug(true);
		$smtp->connect();
		$smtp->auth($uri['user'], $uri['pass']);
		$smtp->mailFrom($uri['user']);
		$smtp->rcptTo($rcpt);
		$smtp->data($mail);
		// print_r($smtp->getResponse());
		$smtp->disconnect();

	}


}
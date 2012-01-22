<?php

// Amazon Simple Email Service Client
require_once CLASSES_PATH . 'aws-ses/ses.php';

class Email
{	
	public static function send($name, $address, $template, $values)
  	{
		list ($subject, $html) = self::getContent($template, $values);

		$ses = new SimpleEmailService(AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY);

		$email = new SimpleEmailServiceMessage();
		
		$email->addTo($name . ' <' . $address . '>');
		$email->setFrom('Hundemassor.dk <noreply@hundemassor.dk>');
		$email->setSubject($subject);
		$email->setMessageFromString('', $html);

		$ses->sendEmail($email);
	}			

	public static function getContent($template, $values)
	{
		// load template file
		$body = file_get_contents(MAIL_TEMPLATES_PATH . $template . '.html');
		$body = str_replace('\\','',$body);
		
		// substitute values
		foreach ($values as $k => $v)
		{
			$body = str_replace("{{$k}}", $v, $body);		
		}

		// extract subject from meta tag
		preg_match('<meta name="subject" content="(.*)" \/>', $body, $matches);
    	$subject = $matches[1];

		return array($subject, $body);
	}
}

?>
<?php
/**
 * Функция отправки письма
 * @param $title
 * Заголовок письма
 * @param $subject
 * Тема письма
 * @param $to
 * Адресат
 * @param $content
 * Основное содержание
 * @return bool
 */

function send_email($title, $subject, $to, $content)
{
	require_once Config::get('libs').'PhpMailer/class.phpmailer.php';

	try {
		$mail = new PHPMailer(true); //New instance, with exceptions enabled
		
		$body = file_get_contents(Config::get('app').'views/default/email/template.html');
		$body = str_replace(["{:title:}", "{:content:}"], [$title ,$content], $body);
		$body = preg_replace('/\\\\/','', $body); //Strip backslashes

		$mail->IsSMTP();                           // tell the class to use SMTP
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->Port       = 25;                    // set the SMTP server port
		$mail->Host       = "localhost";           // SMTP server
		$mail->Username   = "noreply@piqtune.com"; // SMTP server username
		$mail->Password   = "o42q60e85f94e88X";    // SMTP server password

		$mail->IsSendmail();  // tell the class to use Sendmail

	//	$mail->AddReplyTo("didimoner@gmail.com","First Last");

		$mail->From       = "noreply@piqtune.com";
		$mail->FromName   = "PiqTune Email Service";

		$mail->AddAddress($to);

		$mail->Subject = $subject;

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->WordWrap   = 80; // set word wrap

		$mail->MsgHTML($body);

		$mail->IsHTML(true); // send as HTML
		$mail->CharSet = "utf8"; // set charset

		$mail->Send();

		return true;
	}
	catch (phpmailerException $e) {
		debug($e->errorMessage());
	}

	return false;
}

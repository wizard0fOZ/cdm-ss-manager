<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use App\Core\Env;

Env::load(dirname(__DIR__));

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = Env::get('MAIL_HOST');
$mail->Port = Env::get('MAIL_PORT');
$mail->SMTPAuth = false;

$mail->setFrom(Env::get('MAIL_FROM'), Env::get('MAIL_FROM_NAME'));
$mail->addAddress('test@example.com');
$mail->Subject = 'CDM Test Mail';
$mail->Body = 'Womp Womp';

$mail->send();

echo 'Mail sent';

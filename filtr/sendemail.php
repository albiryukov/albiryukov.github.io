<?php
$name       = @trim(stripslashes($_POST['Ваше имя'])); 
$from       = @trim(stripslashes($_POST['E-mail'])); 
$subject    = @trim(stripslashes($_POST['Контактный телефон'])); 
$message    = @trim(stripslashes($_POST['Текст сообщения'])); 
$to   		= 'xigo@mail.ru';//replace with your email

$headers   = array();
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/plain; charset=UTF-8";
$headers[] = "From: {$name} <{$from}>";
$headers[] = "Reply-To: <{$from}>";
$headers[] = "Subject: {$subject}";
$headers[] = "X-Mailer: PHP/".phpversion();

mail($to, $subject, $message, $headers);

die;
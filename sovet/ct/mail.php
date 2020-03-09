<?php
require_once __DIR__ . '/recaptcha.php';
$secret = "6w1LdB0TAAAAAPoB8GKdbG-XOqq8QaZ-ft2VGQ3n";
$response = null;
 
$reCaptcha = new ReCaptcha($secret);
	if ($_POST["g-recaptcha-response"]) {
		$response = $reCaptcha->verifyResponse(
		$_SERVER["REMOTE_ADDR"],
		$_POST["g-recaptcha-response"]
	);
}
$post = (!empty($_POST)) ? true : false;
if($post) {
	$email = htmlspecialchars(trim($_POST['email']));
	$name = htmlspecialchars(trim($_POST['name']));
	$sub = htmlspecialchars(trim($_POST["sub"]));
	$message = htmlspecialchars(trim($_POST['message']));
	$error = '';
	if(!$response) {$error .= 'Заполните капчу';}
	if(!$name) {$error .= 'Укажите свое имя. ';}
	if(!$email) {$error .= 'Укажите электронную почту. ';}
	if(!$sub) {$error .= 'Укажите тему обращения. ';}
	if(!$message || strlen($message) < 1) {$error .= 'Введите сообщение. ';}
	if(!$error) {
		$address = "biryukov.a.l@gmail.com";
		$mes = "Почта: ".$email."\n\nИмя: ".$name."\n\nТема: " .$sub."\n\nСообщение: ".$message."\n\n";
		$send = mail ($address,$sub,$mes,"Content-type:text/plain; charset = UTF-8\r\nReply-To:$email\r\nFrom:$name <contact>");
		if($send) {echo 'OK';}
	}
	else {echo '<div class="err">'.$error.'</div>';}
}
?>
<?php

$formConfigFile = file_get_contents("rd-mailform.config.json");
$formConfig = json_decode($formConfigFile, true);

date_default_timezone_set('Etc/UTC');

try {
    require './phpmailer/PHPMailerAutoload.php';

    $recipients = $formConfig['vitebskvorota@gmail.com'];

    preg_match_all("/([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)/", $recipients, $addresses, PREG_OFFSET_CAPTURE);

    if (!count($addresses[0])) {
        die('MF001');
    }

    function getRemoteIPAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];

        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    if (preg_match('/^(127\.|192\.168\.|::1)/', getRemoteIPAddress())) {
        die('MF002');
    }

    $template = file_get_contents('rd-mailform.tpl');

    if (isset($_POST['form-type'])) {
        switch ($_POST['form-type']){
            case 'contact':				 
                $subject = 'Сообщение от посетителя вашего сайта';
                break;
            case 'subscribe':
                $subject = 'Запрос на подписку';
                break;
            case 'order':
                $subject = 'Запрос заказа';
                break;
            default:
                $subject = 'Сообщение от посетителя вашего сайта';
                break;
        }
    }else{
        die('MF004');
    }

    if (isset($_POST['email'])) {
        $template = str_replace(
            array("<!-- #{FromState} -->", "<!-- #{FromEmail} -->"),
            array("Email:", $_POST['email']),
            $template);
    }

    if (isset($_POST['message'])) {
        $template = str_replace(
            array("<!-- #{MessageState} -->", "<!-- #{MessageDescription} -->"),
            array("Message:", $_POST['message']),
            $template);
    }

    // In a regular expression, the character \v is used as "anything", since this character is rare
    preg_match("/(<!-- #\{BeginInfo\} -->)([^\v]*?)(<!-- #\{EndInfo\} -->)/", $template, $matches, PREG_OFFSET_CAPTURE);
    foreach ($_POST as $key => $value) {
        if ($key != "counter" && $key != "email" && $key != "message" && $key != "form-type" && $key != "g-recaptcha-response" && !empty($value)){
            $info = str_replace(
                array("<!-- #{BeginInfo} -->", "<!-- #{InfoState} -->", "<!-- #{InfoDescription} -->"),
                array("", ucfirst($key) . ':', $value),
                $matches[0][0]);

            $template = str_replace("<!-- #{EndInfo} -->", $info, $template);
        }
    }

    $template = str_replace(
        array("<!-- #{Subject} -->", "<!-- #{SiteName} -->"),
        array($subject, $_SERVER['SERVER_NAME']),
        $template);

    $mail = new PHPMailer();

    if ($formConfig['useSmtp']) {
        //Сообщаем PHPMailer использовать SMTP
        $mail->isSMTP();

         // Включить отладку SMTP
         // 0 = выкл (для производственного использования)
         // 1 = клиентские сообщения
         // 2 = сообщения клиента и сервера
		 
		   // -------------------------------------
		   // Настройки вашей почты
    	   // $mail->Host       = 'smtp.gmail.com'; // SMTP сервера GMAIL
    	   // mail->Username   = 'YOURLOGIN'; // Логин на почте
    	   // $mail->Password   = 'YOURPASSWORD'; // Пароль на почте
    	   // $mail->SMTPSecure = 'ssl';
    	   // $mail->Port       = 465;
		   // -------------------------------------
		   
        $mail->SMTPDebug = 0;

        $mail->Debugoutput = 'html';

        // Установить имя хоста почтового сервера
        $mail->Host = $formConfig['smtp.gmail.com'];

        // Установить номер порта SMTP - вероятно, будет 25, 465 или 587
        $mail->Port = $formConfig['465'];

        // Использовать ли аутентификацию SMTP
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";

        // Имя пользователя для использования при аутентификации SMTP
        $mail->Username = $formConfig['vitebskvorota@gmail.com'];

        // Пароль для аутентификации SMTP
        $mail->Password = $formConfig['rfrnec1981'];
    }

    $mail->From = $_POST['email'];

    # Attach file
    if (isset($_FILES['file']) &&
        $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $mail->AddAttachment($_FILES['file']['tmp_name'],
            $_FILES['file']['name']);
    }

    if (isset($_POST['name'])){
        $mail->FromName = $_POST['name'];
    }else{
        $mail->FromName = "Site Visitor";
    }

    foreach ($addresses[0] as $key => $value) {
        $mail->addAddress($value[0]);
    }

    $mail->CharSet = 'utf-8';
    $mail->Subject = $subject;
    $mail->MsgHTML($template);
    $mail->send();

    die('MF000');
} catch (phpmailerException $e) {
    die('MF254');
} catch (Exception $e) {
    die('MF255');
}

<?php
/*************************************
 * Вспомогательные функции:
 * - работа с письмами
 * - работа с данными сессии
 * - получение REQUEST-данных
 * - проверка ip и header-ов пользователя
 * - валидация данных
 * - вывод текста в json-формате
 * - работа с url-ми
*************************************/

/*Отправка email-ов*/
function sendMail($subject,$body, $to, $from_email, $from_name, $reply_to, $type = 'plain')  {
	$headers = "X-PHP-Script: ".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]." for ".$_SERVER['SERVER_ADDR']."\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Return-path: <".$from_email.">\r\n";
	$headers .= "Content-type: text/".$type."; format=flowed; charset=utf-8; reply-type=original\r\n";
	$headers .= "Content-Transfer-Encoding: 8bit\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-MSMail-Priority: Normal\r\n";
	$headers .= "X-Mailer: Automatic PHP Script\r\n";
	$headers .= "From:".$from_name."<".$from_email.">\r\n";
    $headers .= "Reply-To: $reply_to\r\n";

	if (mail($to, $subject, $body, $headers)) {
		return true;
	} else {
		return false;
	}
}

/*Формирование списка кому отправлять email-ы*/
function toEmail($data) {
	$to = "";
	$count = count($data);
	for ($i = 0; $i < $count; $i++) {
		$to .= $data[$i]['email'];
		if ($i < ($count - 1)) {
			$to .= ",";
		}
	}
	return $to;
}

function getControlPanelUrl() {
	$path = explode("/", $_SERVER["REQUEST_URI"]);

	// Убираем скрипт из пути
	array_pop($path);

	$path = implode("/", $path);

	return "https://$_SERVER[HTTP_HOST]$path/cp/";
}

/*Проверяем хэдеры на "человечость"*/
function checkHeader() {
	if (	empty($_SERVER['HTTP_ACCEPT']) ||
			empty($_SERVER['HTTP_ACCEPT_ENCODING']) ||
			empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ||
			empty($_SERVER['HTTP_CONNECTION'])
		) {
		return false;
	}
	
	return true;
}

/*Получаем ip*/
function getIp() {
	$ip = '';
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		return false;
	}
	return $ip;
}

/*Выводим массив в json-формате*/
function echoJsonData($data) {
	global $userdata;

	if ($userdata['old_browser'] == 1) {
		echo " <script type='text/javascript'>window.close();</script>";
	} else {
		echo json_encode($data);
	}
	return true;
}

/*Устанавливаем данные в сессию*/
function setSession($key, $value) {
	$_SESSION[strval($key)] = serialize($value);
}

//Получаем данные из сессии
function getSession($key) {
	return (isset($_SESSION[strval($key)])) ? unserialize($_SESSION[strval($key)]) : false;
}

/*Получаем request-данные*/
function getRequest($key = '', $return = false) {
	return (isset($_REQUEST[$key])) ? trim($_REQUEST[$key]) : $return;
}

/*Проверка данных*/
function validate() {
	global $error, $code_language, $_language;

	if (!checkHeader() && !getIp()) {
		$error = $_language[$code_language]["error_header"];
		return false;
	}

	if (strlen(rawurldecode(getRequest('url', ''))) <= 0) {
		$error = $_language[$code_language]["error_url"];
		return false;
	}
	
	if (!checkUrl(getRequest('url', ''))) {
		$error = $_language[$code_language]["error_valid_url"];
		return false;
	}
	
	if (mb_strlen(getRequest('text', '')) < 5 || mb_strlen(getRequest('text', '')) > 30) {
		$error = sprintf($_language[$code_language]["error_text"], 5, 30, mb_strlen(getRequest('text', '')));
		return false;
	}
	
	if (mb_strlen(getRequest('comment', '')) > 30) {
		$error = sprintf($_language[$code_language]["error_text"], 0, 30, mb_strlen(getRequest('comment', '')));
		return false;
	}
	return true;
}

/*Проверка url-а*/
function checkUrl($url = '') {
	//удаление опасных сиволов
	$url = trim(preg_replace("/[^\x20-\xFF]/","",@strval($url)));
	
	//проверяем УРЛ на правильность
	if (!preg_match("~^(?:(?:https?|ftp|telnet)://(?:[a-z0-9_-]{1,32}".
		"(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:com|net|".
		"org|mil|edu|arpa|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?".
		"!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&".
		"?+=\~/-]*)?(?:#[^ '\"&<>]*)?$~i", $url)) {
		return false; 
	}

	return true;
}

function getFormatingUrl($url) {
	//удаление опасных сиволов
	$url = trim(preg_replace("/[^\x20-\xFF]/","",@strval($url)));
	//если нет протокола - добавляет http://
	if (!strstr($url,"://")) {
		$url = "http://".$url;
	}
	
	//заменить протокол на нижний регистр: hTtP -> http
	$url = preg_replace_callback("|^[a-z]+|i", function (array $matches) {
		return strtolower($matches[0]);
	}, $url);
	
	return $url;
}

?>

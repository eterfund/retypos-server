<?php
/*	Цель: Скрипт обработки опечаток
*	@автор: barbass@
* 	@дата: 2012-04-24
*/

header('Access-Control-Allow-Origin: *');
require_once('configuration.php');

/*Начинаем сессию*/
if (!session_id())  {
	session_start();
}

/*Если часто отправляет (более 1 в минуту)*/
$last_activity = get_session('last_activity');
if ($last_activity)  {
	if ((time() - $last_activity) <= 60)  {
		$ajax_mess = "10timeerror";
		print_text($ajax_mess);
		return;
	}
}

try  {
	$DBH = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
}  catch  (PDOException $e)  {
	$ajax_mess = "10servererror";
	print_text($ajax_mess);
	return;
}

////////////////////////////////////////////////////////////////////////
//Проверка данных

if  (!check_header() || !get_ip())  {
	$ajax_mess = "10robot";
	print_text($ajax_mess);
	return;
}

	if  (!isset($_REQUEST['e_typos_url']) || !isset($_REQUEST['e_typos_error_text'])) {
		$ajax_mess = "10dataerror";
		print_text($ajax_mess);
		return;
	}
	
	if  (!isset($_REQUEST['e_typos_comment']))  {
		$comment = '';
	}  else  {
		$comment = trim(htmlspecialchars(substr(rawurldecode($_REQUEST['e_typos_comment']), 0, 50)));
	}
	
	$url = trim(htmlspecialchars(substr($_REQUEST['e_typos_url'], 0, 300)));
	$error_text = trim(htmlspecialchars(substr(rawurldecode($_REQUEST['e_typos_error_text']), 0, 30)));
	
	if  (!isset($_REQUEST['e_typos_oldbrowser']))  {
		$oldbrowser = 0;
	}  else  {
		$oldbrowser = intval($_REQUEST['e_typos_oldbrowser']);
	}

	if  ($url == '' || $error_text == '' || strlen($error_text) < 5)  {
		$ajax_mess = "10dataerror";
		print_text($ajax_mess);
		return;
	}

	$mas_url = parse_url($url);
	if  (!isset($mas_url['host']))  {
		$ajax_mess = "10dataerror";
		print_text($ajax_mess);
		return;
	}

////////////////////////////////////////////////////////////////////////	


////////////////////////////////////////////////////////////////////////
//Достаем номер сайта ?нужно ли? и емайлы пользователей
try  {
	$query_emails =  "SELECT r.id_site AS site, u.email AS email
						FROM users AS u, responsible AS r
						WHERE r.id  IN (
									SELECT r.id
									FROM responsible AS r
									JOIN sites AS s
									WHERE s.site = ?
									AND r.id_site = s.id
									AND r.status = '1')
						AND r.id_user=u.id";
	$STH = $DBH->prepare($query_emails);
	$STH->execute(array($mas_url["host"]));
	if  ($STH->rowCount() != 0)  {
		$i = 0;
		while  ($row = $STH->fetch(PDO::FETCH_ASSOC))  {
			$email_users[$i]['site'] = $row['site'];
			$email_users[$i]['email'] = $row['email'];
			$i++;
		}
	}  else  {
		$email_users = 0;
	}
}  catch  (PDOException $e)  {
	$ajax_mess = "10servererror";
	print_text($ajax_mess);
	return;
}   
if  ($email_users !== 0)  {
	try  {
		$data = array($email_users[0]['site'], $url, $error_text,$comment, 0);
		$STH = $DBH->prepare("INSERT INTO messages (id_site, link, error_text, comment, date, status) VALUES (?, ?, ?, ?, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), ?)");
		if(!$STH->execute($data)){
			// Ошибка insert-запроса
			print_r($STH->errorInfo());
			print_text("10inserterror");
			return;
		}
	}  catch  (PDOException $e)  {
		$ajax_mess = "10inserterror";
		print_text($ajax_mess);
		return;
	}
	
	if  ($comment == '')  {
		$comment = 'Пользователь не оставил комментарий';
	}
	
	$message_email = "<p>Сайт: <a href=".$mas_url["scheme"]."://".$mas_url["host"].">".$mas_url["scheme"]."://".$mas_url["host"]."</a></p>";
	$message_email .= "<p>Ссылка: <a href=$url>нажмите</a>"." (".$url.")"."</p>";
	$message_email .= "<p>Текст с опечаткой: ".$error_text."</p>";
	$message_email .= "<p>Комментарий: ".$comment."</p>";
		
	$subject = '=?utf-8?B?'.base64_encode("Сообщение об опечатке").'?=';
	
	$to = to_email($email_users);
	/*FIXED какая почта?*/
	$from_email = "typos@etersoft.ru";
	$from_name = '=?utf-8?B?'.base64_encode("Служба опечаток Etersoft").'?=';

	$result = sendmail($subject, $message_email, $to, $from_email, $from_name, 'html');
	if  (!$result)  {
		$ajax_mess = "10emailerror";
		print_text($ajax_mess);
		return;
	}  else  {
		set_session('last_activity', time());
		$ajax_mess = "10win";
		print_text($ajax_mess);
		return;
	}
}  else  {
	$ajax_mess = "10siteerror";
	print_text($ajax_mess);
	return;
}

////////////////////////////////////////////////////////////////////////
//Вспомогательные функции

//Отправка email-ов
function sendmail($subject,$body, $to, $from_email, $from_name, $type = 'plain')  {
	$headers = "X-PHP-Script: ".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]." for ".$_SERVER['SERVER_ADDR']."\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Return-path: <".$from_email.">\r\n";
	$headers .= "Content-type: text/".$type."; format=flowed; charset=utf-8; reply-type=original\r\n";
	$headers .= "Content-Transfer-Encoding: 8bit\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-MSMail-Priority: Normal\r\n";
	$headers .= "X-Mailer: Automatic PHP Script\r\n";
	$headers .= "From:".$from_name."<".$from_email.">\r\n";

	if  (mail($to, $subject, $body, $headers))  {
		return true;
	}  else  {
		return false;
	}
}

//Формирование списка кому отправлять email-ы
function to_email($data)  {
	$to = "";
	$count = count($data);
	for  ($i = 0; $i < $count; $i++)  {
		$to .= $data[$i]['email'];
		if  ($i < ($count - 1))  {
			$to .= ",";
		}
	}
	return $to;
}

//Проверяем хэдеры на "человечность"
function check_header()  {
	if  ( ($_SERVER['HTTP_ACCEPT'] == '')  &&
			($_SERVER['HTTP_ACCEPT_ENCODING'] == '') &&  
			($_SERVER['HTTP_ACCEPT_LANGUAGE'] == '') &&
			($_SERVER['HTTP_CONNECTION'] == '')) { 
		return false;
	}  else  {
		return true;
	}
}

//Проверяем ip
function get_ip()  {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))  {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }  elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }  else  {
		$ip = false;
	}
    return $ip;
}

function print_text($text)  {
	global $oldbrowser;
	echo $text;
	if  ($oldbrowser == 1)  {
		echo " <script type='text/javascript'>window.close();</script>";
	}
	return true;
}

function set_session($key, $value)  {
	$_SESSION[strval($key)] = serialize($value);
}

function get_session($key)  {
	if (isset($_SESSION[strval($key)]))  {
		return unserialize($_SESSION[strval($key)]);
	}  else  {
		return false;
	}
}

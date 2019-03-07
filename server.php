<?php

/* 	Цель: Скрипт обработки опечаток
 * 	авторы:
 *      barbass@etersoft.ru
 *      ambulance@etersoft.ru
 *
 * 	дата: 2012-04-24
 */

header('Access-Control-Allow-Origin: *');

require_once('configuration.php');
require_once('functions.php');
require_once('dbfunctions.php');
require_once('language.php');
require_once('constants.php');

/* Начинаем сессию */
if (!session_id()) {
    session_start();
}

/* Определяем переменные */
$error = '';
$userdata = array(
    'url' => '',
    'text' => '',
    'corrected' => '',
    'context' => '',
    'comment' => '',
    'old_browser' => 0
);
$code_language = DEFAULT_LANGUAGE;

/* Определяем какой язык использовать (или же оставляем по-умолчанию) */
if (in_array(getRequest('language', 'ru'), $_language)) {
    $code_language = getRequest('language', 'ru');
}

/* Если часто отправляет (более 1 раза в минуту) */
$last_time_activity = getSession('last_activity');
setSession('last_activity', time());

if ($last_time_activity) {
    if ((time() - $last_time_activity) <= MIN_TIME) {
        echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_time_activity']));
        return;
    }
}

/* Подключение к базе данных */
try {
    $DBH = new PDO(DB_DRIVER . ":host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
} catch (PDOException $e) {
    error_log($e);
    echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_connect_database']));
    return false;
}

if (!validate()) {
    echoJsonData(array('success' => 'false', 'message' => $error));
    return false;
}

//TODO - работу со старыми бразуерами будем подерживать или нет? Если будем, то надо доработать работу на стороне сервера (закрытие окна)
$userdata['old_browser'] = getRequest('old_browser', 0);
$userdata['comment'] = htmlspecialchars(rawurldecode(getRequest('comment', '')));
$userdata['corrected'] = htmlspecialchars(rawurldecode(getRequest('corrected', '')));
$userdata['url'] = getFormatingUrl(rawurldecode(getRequest('url', '')));
$userdata['text'] = htmlspecialchars(rawurldecode(getRequest('text', '')));
$userdata['context'] = htmlspecialchars(rawurldecode(getRequest('context', '')));

$mas_url = parse_url($userdata['url']);
if (!isset($mas_url['host'])) {
    echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]["error_valid_url"]));
    return;
}


/* * ********************************
 * Основная часть работы:
 * - получения данных по сайту
 * - сохранение текста с ошибкой
 * - отправка писем
 * ******************************** */

/* Достаем номер сайта и email-ы пользователей */
try {
/*
    $query_emails = "SELECT r.id_site AS id_site,
	u.email AS email
	FROM users AS u, responsible AS r
        WHERE r.id IN (
            SELECT r.id
            FROM responsible AS r
            JOIN sites AS s
            WHERE s.site REGEXP ?
            AND r.id_site = s.id
            AND r.status = '1')
        AND r.id_user = u.id";
    
    $STH = $DBH->prepare($query_emails);
    
    // 08.06.17: supports every protocol
    // ^(https?://)*(www.)*etersoft.com/?$
    $STH->execute(array("^(https?://)*(www.)*" . $mas_url["host"] . "/?$"));
*/
    $id_site = get_site_id($DBH, $userdata['url']);
    $query_emails = "SELECT r.id_site AS id_site,
	u.email AS email
	FROM users AS u
	INNER JOIN responsible AS r ON r.id_user = u.id
        WHERE r.id_site = $id_site
            AND r.status = '1'";
    //error_log($query_emails);
    $STH = $DBH->prepare($query_emails);
    
    $STH->execute(array($id_site));

    if ($STH->rowCount() > 0) {
        $email_users = array();
        while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
            $email_users[] = array(
                'id_site' => $row['id_site'],
                'email' => $row['email']
            );
        }
    } else {
        $email_users = false;
    }
} catch (PDOException $e) {
    error_log($e);
    echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]["error_support_site"]));
    return;
}

/* Если активных пользователей за сайт нет, то возвращаем сообщение об ошибке */
if ($email_users) {
    try {
        $data = array(null, $email_users[0]['id_site'], $userdata['url'], $userdata['text'], $userdata['context'], $userdata['corrected'], $userdata['comment'], 0, getIp());
        $STH = $DBH->prepare("INSERT INTO messages (id, site_id, link, text, context, corrected, comment, date, status, user_ip) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), ?, ?)");
        $result = $STH->execute($data);

        if ($result !== true) {
            $errorInfo = $STH->errorInfo();
            error_log("Возникла ошибка при добавлении опечатки в базу данных. Код SQLSTATE: {$errorInfo[0]}. 
            Код ошибки драйвера: {$errorInfo[1]}. Текст ошибки: {$errorInfo[2]}");

            echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_database']));
            return;
        }

    } catch (PDOException $e) {
        echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_database']));
        return;
    }

    $controlPanelUrl = getControlPanelUrl();

    $message_email = "";
    $message_email .= "<p>" . $_language[$code_language]['mail_url'] . " <a href=" . htmlspecialchars($userdata['url']) . ">" . $_language[$code_language]['mail_click_url'] . "</a>" . " (" . $userdata['url'] . ")" . "</p>";
    $message_email .= "<p> <a href='{$controlPanelUrl}'>{$_language[$code_language]['mail_cp_link']}</a></p>";
    $message_email .= "<p>{$_language[$code_language]['mail_text']}: <i>{$userdata['text']}</i></p>";
    $message_email .= "<p>{$_language[$code_language]['mail_comment']}: <i>{$userdata['corrected']}</i></p>";
    $message_email .= "<p>{$_language[$code_language]['mail_context']}: \"{$userdata['context']}\"</p>";

    $subject = '=?utf-8?B?' . base64_encode($_language[$code_language]['mail_subject']) . '?=';

    $to = toEmail($email_users);

    $from_email = EMAIL;
    $from_name = '=?utf-8?B?' . base64_encode($_language[$code_language]['mail_from']) . '?=';

    $reply_to = REPLY_TO;

    if (sendMail($subject, $message_email, $to, $from_email, $from_name, $reply_to, 'html')) {
        echoJsonData(array('success' => 'true', 'message' => $_language[$code_language]['text_success']));
        return;
    } else {
        echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['mail_error']));
        return;
    }
} else {
    error_log("No active users for {$mas_url["scheme"]}://{$mas_url["host"]}");
    echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_support_site']));
    return;
}

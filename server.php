<?php

/* 	Цель: Скрипт обработки опечаток
 * 	автор: barbass@
 * 	дата: 2012-04-24
 */

header('Access-Control-Allow-Origin: *');

require_once('configuration.php');
require_once('functions.php');
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
$userdata['url'] = getFormatingUrl(rawurldecode(getRequest('url', '')));
$userdata['text'] = htmlspecialchars(rawurldecode(getRequest('text', '')));

/* Парсим сайт для получения коренного сайта */
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

error_log("Host = {$mas_url["host"]}");

/* Достаем номер сайта и email-ы пользователей */
try {
    $query_emails = "SELECT r.id_site AS id_site,
	u.email AS email
	FROM users AS u, responsible AS r
        WHERE r.id IN (
            SELECT r.id
            FROM responsible AS r
            JOIN sites AS s
            WHERE s.site = ?
            AND r.id_site = s.id
            AND r.status = '1')
        AND r.id_user = u.id";
    
    $STH = $DBH->prepare($query_emails);
    $STH->execute(array($mas_url["scheme"] . "://" . $mas_url["host"]));
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
        $data = array('NULL', $email_users[0]['id_site'], $userdata['url'], $userdata['text'], $userdata['comment'], 0);
        $STH = $DBH->prepare("INSERT INTO messages (id, site_id, link, text, comment, date, status) VALUES (?, ?, ?, ?, ?, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), ?)");
        $STH->execute($data);
    } catch (PDOException $e) {
        echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_database']));
        return;
    }

    $message_email = "<p>" . $_language[$code_language]['mail_site'] . " <a href=" . $mas_url["scheme"] . "://" . $mas_url["host"] . ">" . $mas_url["scheme"] . "://" . $mas_url["host"] . "</a></p>";
    $message_email .= "<p>" . $_language[$code_language]['mail_url'] . " <a href=" . htmlspecialchars($userdata['url']) . ">" . $_language[$code_language]['mail_click_url'] . "</a>" . " (" . $userdata['url'] . ")" . "</p>";
    $message_email .= "<p>" . $_language[$code_language]['mail_text'] . " " . htmlspecialchars($userdata['text']) . "</p>";
    $message_email .= "<p>" . $_language[$code_language]['mail_comment'] . " " . htmlspecialchars($userdata['comment']) . "</p>";

    $subject = '=?utf-8?B?' . base64_encode($_language[$code_language]['mail_subject']) . '?=';

    $to = toEmail($email_users);

    $from_email = EMAIL;
    $from_name = '=?utf-8?B?' . base64_encode($_language[$code_language]['mail_from']) . '?=';

    if (sendMail($subject, $message_email, $to, $from_email, $from_name, 'html')) {
        echoJsonData(array('success' => 'true', 'message' => $_language[$code_language]['text_success']));
        return;
    } else {
        echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['mail_error']));
        return;
    }
} else {
    error_log("No active users for {$mas_url['host']}");
    echoJsonData(array('success' => 'false', 'message' => $_language[$code_language]['error_support_site']));
    return;
}

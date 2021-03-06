<?php

use JsonRPC\Exception\AccessDeniedException;
use JsonRPC\Exception\ConnectionFailureException;
use JsonRPC\Exception\ServerErrorException;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* Работа с опечатками */

class Typo extends CI_Model {

    function filterResults($table, $data) {
        $page = $data['page'];
        $limit = $data['limit'];
        $sord = $data['sord'];
        $sidx = $data['sidx'];

        if ($limit == null) {
            $limit = 10;
        }
        
        $id_site = isset($data["id_site"]) ? $data["id_site"] : 0;
        $login_id = isset($data["login_id"]) ? $data["login_id"] : 0;
        
        if ( $table == "messages") {
            if (!$this->getSiteRights($data)) {
                return array();
            }
        }
        
        $search = $data['search'];
        $searchstring = "";
        if ($search == "true") {
            $searchField = $data['searchField'];
            $searchOper = $data['searchOper'];
            $searchString = $data['searchString'];
            $search_string = $this->mdl_search->search_string($searchField, $searchOper, $searchString);
            if ($search_string != "") {
                $searchstring .= " AND " . $search_string . " ";
            }
        }
        
        $data = array();
        
        $query_count = "";
        
        if ( $table == "sites" ) {
            $query_count = "SELECT COUNT(s.id) AS count
                            FROM sites AS s
                            JOIN users AS u
                            JOIN responsible AS r ON r.id_user=u.id 
                            WHERE u.id = '" . $login_id . "'
                                AND r.id_site = s.id";
        } else {
            $query_count = "SELECT COUNT(m.id) AS count
                            FROM `messages` AS m
                            JOIN users AS u
                            JOIN responsible AS r ON r.id_user=u.id
                            WHERE m.site_id = '" . $id_site . "'
                                AND u.id = '" . $login_id . "'
                                AND r.id_site = m.site_id 
                                AND r.id_user = u.id";
        }
        
        $count = $this->db->query($query_count)->num_rows();

        log_message("error", "messages count = $count");
        
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $data['page'] = $page;
        $data['total'] = $total_pages;
        $data['records'] = $count;
        /**/

        $start = $limit * $page - $limit;
        if ($start < 0) {
            $start = 0;
        }
        
        /* ЗАПРОС */

        if ( $table == "messages" ) {
            $this->db->select("m.id as message_id, m.link as link, m.text as text, "
                    . "m.comment as comment, m.context as context, m.date as message_date, m.status as message_status, u.*");
            $this->db->from("messages as m, users as u");
            $this->db->join("responsible as r", "r.id_user = u.id AND"
                    . " r.id_site = m.site_id AND r.id_user = u.id");
            $this->db->where("m.site_id", $id_site);
            $this->db->where("u.id", $login_id);

//            $query_string = "SELECT m.id AS id, 
//                                m.link AS link, 
//                                m.error_text AS text, 
//                                m.comment AS comment, 
//                                m.date AS date, 
//                                m.status AS status
//                                FROM messages AS m
//                                JOIN users AS u
//                                JOIN responsible AS r ON r.id_user=u.id
//                                WHERE m.id_site = '" . $id_site . "'
//                                    AND u.id = '" . $login_id . "'
//                                    AND r.id_site = m.id_site 
//                                    AND r.id_user = u.id " . $searchstring . "
//                                ORDER BY $sidx $sord 
//                                LIMIT $start , $limit";
        } else {
            $this->db->select("s.id as site_id, s.site as site, s.status as status, u.*");
            $this->db->from("sites as s, users as u");
            $this->db->join("responsible as r", "r.id_user = u.id AND r.id_site = s.id");
            $this->db->where("u.id", $login_id);
            
//            $query_string = "SELECT s.id AS id, s.site AS site, r.status AS status
//                            FROM sites AS s
//                            JOIN users AS u
//                            JOIN responsible AS r ON r.id_user=u.id 
//                            WHERE u.id='" . $login_id . "'
//                                AND r.id_site = s.id " . $searchstring . "
//                            ORDER BY $sidx $sord 
//                            LIMIT $start , $limit";
        }
        
        if ( $search == "true" ) {
            $this->db->where($searchstring);
        }
        
        $this->db->limit($limit, $start);

        if (!empty($sidx) && !empty($sord)) {
            $this->db->order_by($sidx . " " . $sord);
        }

        $results = $this->db->get();
        
        if ( $table == 'sites') {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id']     = $row->site_id;
                $data['rows'][$id]['cell'][] = $row->site_id;
                $data['rows'][$id]['cell'][] = $row->site;
                $data['rows'][$id]['cell'][] = $row->status;
            }
        } else if ( $table == 'messages' ) {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id'] = $row->message_id;
                $data['rows'][$id]['cell'][] = $row->message_id;
                $data['rows'][$id]['cell'][] = $row->message_status;
                $data['rows'][$id]['cell'][] = anchor($row->link, 'ссылка', array('class' => 'typos_link', 'target' => '_blank'));
                $data['rows'][$id]['cell'][] = $row->text;
                $data['rows'][$id]['cell'][] = $row->comment;
                $data['rows'][$id]['cell'][] = anchor("#", 'показать',
                        array('class' => 'typos_context',
                            'onclick'=> 'return Typos.handleLink(this);',
                            'typo' => $row->text, 'context' => $row->context,
                            'correct' => $row->comment));
                $data['rows'][$id]['cell'][] = $row->message_date;
            }
        }
        
        return $data;
    }

    /**
     * Получает список сайтов определенного пользователя
     *
     * @param $userId integer Идентификатор пользователя
     *
     * @return array Массив сайтов пользователя
     */
    function getSitesList($userId) {

        $this->db->select("sites.id as id, sites.site as name, sites.status as status, sites.date as date");
        $this->db->from("sites");
        $this->db->join("responsible", "sites.id = responsible.id_site");
        $this->db->where("responsible.id_user", $userId);
        //$this->db->where("sites.status", 1);

        return $this->db->get()->result();
    }

    /**
     * Получает список опечаток текущего пользователя для данного
     * сайта. Возвращает список.
     *
     * @param $siteId
     * @return array Список опечаток
     */
    function getSiteTypos($siteId) {
        $this->db->select("id, link, text as originalText, context, corrected as correctedText, comment, date, status as isCorrected");
        $this->db->from("messages");
        $this->db->where("site_id", $siteId);
        $this->db->where("status", 0);
        $this->db->order_by("date", "DESC");
        return $this->db->get()->result();
    }

    /* Получаем список сообщений об опечатках */

    function getMessagesList($data) {
       return $this->filterResults("messages", $data);
    }

    /* Обновляем статус для сайта */

    function updateStatus($data) {
        if ($this->getSiteRights($data)) {
            $this->db->set("status", $data['status']);
            $this->db->where("id_user", $data['login_id']);
            $this->db->where("id_site", $data['id_site']);
            $this->db->update("responsible");
        } else {
            return false;
        }

        return true;
    }

    /* Добавляем новое сообщение */

    function addMessage($data) {
        
        $insertData = [
            "id"        => NULL,
            "id_site"   => $data['id_site'],
            "link"      => $data['link'],
            "error_text"   => $data['error_text'],
            "comment"   => $data['comment'],
            "date"      => $data('Y-m-d H:i:s', time()),
            "status"    => $data['status'],
        ];

        if ($this->getSiteRights($data)) {
            $this->db->insert('messages', $insertData);
        }
    }

    /* Удаляем сообщение */

    function deleteMessage($data) {
        
        if ($this->getMessageRights($data)) {
            $this->db->where("id", $data['id_message']);
            $this->db->delete("messages");
        }
    }

    /**
     *
     * @param $data
     * @throws Exception Если не удалось изменить статус сообщения
     */
    function editMessage($data) {
        if (!$this->getMessageRights($data)) {
            return;
        }

        // По умолчанию ошибки исправляются
        if (!isset($data["autoCorrection"])) {
            $data["autoCorrection"] = true;
        }

        if ( $data['status'] && $data["autoCorrection"] ) {
            $this->correctTypo($data["id_message"], $data["corrected"]);
        }

        $this->db->set("status", $data['status']);
        $this->db->set("comment", $data['corrected']);
        $this->db->where("id", $data['id_message']);
        $this->db->where("site_id", $data['id_site']);
        $this->db->update("messages");
    }

    /* Узнать права на сайт */
    function getSiteRights($data) {      
        $this->db->select("r.id_site");
        $this->db->from("responsible as r");
        $this->db->join("users as u", "u.id = r.id_user");
        $this->db->where("u.id", $data['login_id']);
        $this->db->where("r.id_site", $data['id_site']);
        
        $row = $this->db->count_all_results();
        
        if ($row) {
            return true;
        } else {
            return false;
        }
    }

    /* Узнать права пользователя на сообщение */
    function getMessageRights($data) {
        $this->db->select("m.id");
        $this->db->from("messages as m, users as u");
        $this->db->join("sites as s", "m.site_id = s.id");
        $this->db->join("responsible as r", "r.id_user = u.id AND r.id_site = s.id");
        $this->db->where("m.id", $data['id_message']);
        $this->db->where("u.id", $data['login_id']);
        $this->db->where("s.id", $data['id_site']);
        
        $rows = $this->db->count_all_results();
        log_message("error", "getMessageRights: {$this->db->last_query()}");
        if ($rows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * По заданной ссылке на статью возвращает ссылку на адаптер сайта
     *
     * @param string $link Ссылка на статью
     * @return string URL адаптера JSON RPC
     */
private function get_site_id($DBH, $url) {
    //error_log("\nurl: $url\n");
    $mas_url = parse_url($url);
    if (!isset($mas_url['host']))
        return 0;
    $host = $mas_url['host'];

    $query_sites = "SELECT sites.id AS id, sites.site AS url FROM sites
            WHERE sites.site REGEXP ?";

    $query = $DBH->query($query_sites, array("^(https?://)*(www.)*" . $host . "/?"));
//    if ($query->result() $result->rowCount() == 0) {
//        error_log("no host $host");
//        return 0;
//    }

    $max_id = 0;
    $max_len = 0;
    foreach ($query->result_array() as $row) {
        //echo $row['id'] . " " . $row['url'] . " in " . $url . "\n";
        //preg_match('/(foo)(bar)(baz)/', $row['url'] , $matches
        // TODO: сравнивать строки, начиная с ://
        $pos = strpos ($url, $row['url']);
        $len = strlen($row['url']);
        //echo "pos = $pos, len = $len\n";
        if ($pos !== false && $max_len < $len) {
            $max_id = $row['id'];
            $max_len = $len;
        }
    }
    //echo "Result: $max_id, $max_len\n";
    return $max_id;
}


    private function getTyposAdapterUrl(string $link) {
        $correctPath = $this->config->item("correction_path");

        /* Получаем адрес необходимого сайта */
        $parsed_url = parse_url($link);
        //require_once("/home/eterfund/www/eterfund.ru/api/typos/dbfunctions.php");
        $id_site = $this->get_site_id($this->db, $link);

        $this->db->select("s.site as site, s.path as path");
        $this->db->from("sites as s");
        $this->db->where("s.id", $id_site);
        $site = $this->db->get()->row();
        $path = empty($site->path) ? $correctPath : $site->path;
        //error_log("result: " . $site->site . "/" . $correctPath);
        log_message("error", "result: " . $site->site . "/" .  $path);
        return $site->site . "/" . $path;
        // Адрес на который шлем запрос исправления
        //return $parsed_url["scheme"] . "://" . $parsed_url["host"] . "/" . $correctPath;
    }

    /**
     * Creates and returns a connection with adapter by JSON RPC protocol
     *
     * @param string $adapterUrl Url on which an adapter listen
     * @return \JsonRPC\Client Connection with an adapter
     */
    function createConnectionWithAdapter(string $adapterUrl) {
        $user = $this->config->item("typos_user");
        $password = $this->config->item("typos_password");

        $client = new \JsonRPC\Client($adapterUrl);
        $client->getHttpClient()->withDebug()
            ->withUsername($user)
            ->withPassword($password);

        return $client;
    }

    /**
     * Отправляет запрос к адаптеру на получение ссылки на редактирование
     * статьи.
     *
     * @param int $messageId
     *
     * @throws Exception Если что-либо пошло не так
     * @return string URL редактирования статьи или же null в случае ошибки
     */
    public function getArticleEditUrl(int $messageId) {
        $this->db->select("link");
        $this->db->from("messages");
        $this->db->where("id", $messageId);

        $link = $this->db->get()->row()->link;

        $url = $this->getTyposAdapterUrl($link);

        $client = $this->createConnectionWithAdapter($url);
        $editLink = $client->getEditLink($link);

        if (!isset($editLink["errorCode"]) || $editLink["errorCode"] != 200) {
            throw new Exception($editLink["message"], $editLink["errorCode"]);
        }

        return $editLink["message"];
    }

    /**
     * Отправляет запрос к клиентскому серверу на автоматическое
     * исправление опечатки. Сервер клиента должен использовать
     * библиотеку etersoft/typos_client.
     * Сервер должен быть доступен на хосте клиента по пути  
     * $this->config->item("correction_path").
     *
     * @param int $typoId Идентификатор опечатки
     * @param string $corrected Исправленный вариант
     * @throws Exception Если произошла ошибка
     */
    function correctTypo(int $typoId, string $corrected) {
        /* Получаем исправление */
        $this->db->select("m.link as link, m.text as text, m.comment as comment, m.context as context");
        $this->db->from("messages as m");
        $this->db->where("m.id", $typoId);

        $correction = $this->db->get()->row();

        $url = $this->getTyposAdapterUrl($correction->link);

        try {
            $client = $this->createConnectionWithAdapter($url);
            
            $result = $client->fixTypo($correction->text, $corrected, $correction->context, $correction->link);

            if (!isset($result["errorCode"])) {
                throw new Exception("Неправильный ответ сервера", 500);
            }

            if ($result["errorCode"] != 200) {
                throw new Exception($result["message"], $result["errorCode"]);
            }
        } catch(ConnectionFailureException $e) {
            throw new Exception("Не удалось подключиться к серверу исправления опечаток", 503);
        } catch(AccessDeniedException $e) {
            throw new Exception("Не удалось авторизироваться у сервера исправления опечаток", 401);
        } catch(ServerErrorException $e) {
            throw new Exception("Ошибка автоматического исправления опечатки на сервере", 500);
        } catch(Exception $e) {
            log_message("error", "Ошибка при исправлении опечатки: {$e->getMessage()} (код {$e->getCode()})");
            throw new Exception($this->getExceptionStringForCode($e->getCode()), $e->getCode());
        }
    }

    /**
     * Возвращает читабельную строку для отображения пользователю,
     * содержащую описание ошибки
     *
     * @param $errorCode integer Код ошибки
     * @return string Сообщение об ошибке на русском языке
     */
    private function getExceptionStringForCode($errorCode) {
        switch ($errorCode) {
            case 404:
                return "Ошибка в тексте не найдена. Возможно, она уже была исправлена. Проверьте вручную";
            case 405:
                return "Ошибка. Контекст статьи изменился, автоматическое исправление недоступно. Внесите изменения вручную";
            case 208:
                return "Опечатка уже была исправлена автоматически. Изменения применены к тексту";
            default:
                return "Произошла неизвестная ошибка на сервере, попробуйте повторить попытку позже";
        }
    }

    /**
     * Отправляет запрос на исправление ошибки на сервер
     *
     * @param $data array Информация об опечатке
     * @throws Exception Если произошла ошибка
     */
//    function correctTypo($data) {
//        $correctPath = $this->config->item("correction_path");
//        $authToken = $this->config->item("typos_password");
//        $username = $this->config->item("typos_user");
//
//        /* Получаем исправление */
//        $this->db->select("m.link as link, m.text as text, m.comment as comment");
//        $this->db->from("messages as m");
//        $this->db->where("m.id", $data["id_message"]);
//
//        $correction = $this->db->get()->row();
//
//        /* Получаем адрес необходимого сайта */
//        $parsed_url = parse_url($correction->link);
//
//        // Адрес на который шлем запрос исправления
//        $url = $parsed_url["scheme"] . "://" . $parsed_url["host"] . "/" . $correctPath;
//
//        /* Посылаем запрос с помощью cUrl */
//        $curl = curl_init($url);
//
//        curl_setopt_array($curl, array(
//            CURLOPT_USE_SSL => true,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_POST => true,
//            CURLOPT_FAILONERROR => true,
//            CURLOPT_USERNAME => $username,
//            CURLOPT_PASSWORD => $authToken,
//            CURLOPT_POSTFIELDS => array(
//                'text' => $correction->text,
//                'corrected' => $data["corrected"],
//                'link' => $correction->link
//            ),
//        ));
//
//        log_message("debug", "sending request to $url");
//        log_message("debug", "corrected = {$data["corrected"]}");
//
//        if ( !($res = curl_exec($curl)) && curl_errno($curl) != 0 ) {
//            $errorCode = curl_errno($curl);
//            $errorText = curl_error($curl);
//
//            log_message("debug", "CorrectTypo errorCode: " . $errorCode);
//            log_message("debug", "CorrectTypo errorText: " . $errorText);
//
//            throw $this->getExceptionForCurlError($errorCode, $url);
//        }
//
//        log_message("debug", "response taken");
//        log_message("debug", "Response from $url: $res");
//
//        curl_close($curl);
//    }

    /**
     * Создает исключение, которое описывает ошибку запроса curl
     *
     * @param $errorCode  integer   Код ошибки curl
     * @param $url        string    URL сервера
     *
     * @return Exception исключение
     */
    private function getExceptionForCurlError($errorCode, $url)
    {
        if ($errorCode == CURLE_COULDNT_RESOLVE_HOST) {
            return new Exception("\"$url\" не отвечает, попробуйте позже", 503);
        } else {
            return new Exception("Произошла ошибка при попытке исправить опечатку, попробуйте позже", 500);
        }
    }
}

/**/
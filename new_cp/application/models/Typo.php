<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* Работа с опечатками */

class Typo extends CI_Model {

    function filterResults($table, $data) {
        $page = $data['page'];
        $limit = $data['limit'];
        $sord = $data['sord'];
        $sidx = $data['sidx'];
        
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
                    . "m.comment as comment, m.date as message_date, m.status as message_status, u.*");
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
        $this->db->order_by($sidx . " " . $sord);
        
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
                $data['rows'][$id]['cell'][] = anchor($row->link, 'ссылка', array('class' => 'typos_link', 'target' => '_blank'));;
                $data['rows'][$id]['cell'][] = $row->text;
                $data['rows'][$id]['cell'][] = $row->comment;
                $data['rows'][$id]['cell'][] = $row->message_date;
            }
        }
        
        return $data;
    }
    
    //Получаем список сайтов, доступных для пользователя
    function getSitesList($data) {
        return $this->filterResults("sites", $data);
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
    }

    /* Добавляем новое сообщение */

    function addMessage($data) {
        
        $insertData = [
            "id"        => NULL,
            "id_site"   => $data['id_site'],
            "link"      => $data['link'],
            "error_text"   => $data['error_text'],
            "comment"   => $data['comment'],
            "date"      => $date('Y-m-d H:i:s', time()),
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

    /* Обновляем статус сообщения */

    function editMessage($data) {
        if ($this->getMessageRights($data)) {
            if ( $data['status'] ) {
                $this->correctTypo($data["id_message"]);
            }
            
            $this->db->set("status", $data['status']);
            $this->db->where("id", $data['id_message']);
            $this->db->where("site_id", $data['id_site']);
            $this->db->update("messages");
        }
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
     * Отправляет запрос на исправление ошибки на сервер
     * 
     * @param type $message_id 
     *      Номер сообщения в бд
     */
    function correctTypo($message_id) {
        xdebug_break();
        
        /* TODO: брать из конфига */
        $correctPath = $this->config->item("correction_path");
        $authToken = $this->config->item("typos_password");
        $username = $this->config->item("typos_user");
        
        /* Получаем исправление */
        $this->db->select("m.link as link, m.text as text, m.comment as comment");
        $this->db->from("messages as m");
        $this->db->where("m.id", $message_id);
        
        $correction = $this->db->get()->row();
        
        /* Получаем адрес необходимого сайта */
        $parsed_url = parse_url($correction->link);
        
        // Адресс на который шлем запрос исправления
        $url = $parsed_url["scheme"] . "://" . $parsed_url["host"] . "/" . $correctPath;
        
        /* Посылаем запрос с помощью cUrl */
        $curl = curl_init($url);
        
        curl_setopt_array($curl, array(
            CURLOPT_USE_SSL => true, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERNAME => $username,
            CURLOPT_PASSWORD => $authToken,
            CURLOPT_POSTFIELDS => array(
                'text' => $correction->text,
                'corrected' => $correction->comment,
                'link' => $correction->link
            ),
        ));

        log_message("debug", "sending request to $url");
        
        if ( !($res = curl_exec($curl)) ) {
            log_message("debug", "CorrectTypo error: " . curl_error($curl));
            return;
        }
        
        log_message("debug", "response taken");
        log_message("debug", "Response from $url: $res");
        
        curl_close($curl);
    }
}

/**/
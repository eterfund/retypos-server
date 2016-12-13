<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Работа с опечатками*/
class Mdl_typos extends CI_Model {

    //Получаем список сайтов, доступных для пользователя
    function get_list_sites($data)  {
        $login_id = $data['login_id'];
        $page = $data['page'];
        $limit = $data['limit'];
        $sord = $data['sord'];
        $sidx = $data['sidx'];
	
        $search = $data['search'];
        $searchstring = "";
        if  ($search == "true")  {
            $searchField = $data['searchField'];
            $searchOper = $data['searchOper'];		
            $searchString = $data['searchString'];
            $search_string = $this->mdl_search->search_string($searchField, $searchOper, $searchString);
            if  ($search_string != "")  {
                $searchstring .= " AND ".$search_string." ";
            }
        }
        
        $data = array();
        /*Данные для pagination jqGrid*/
        $query_count = "SELECT COUNT(s.id) AS count
                        FROM sites AS s
                        JOIN users AS u
                        JOIN responsible AS r ON r.id_user=u.id 
                        WHERE u.id = '".$login_id."'
                            AND r.id_site = s.id";
        $rows_count = $this->mdl_query->select($query_count);
        if  ($rows_count)  {
            $count = $rows_count[0]['count'];
        }
        if( $count > 0 )  { 
            $total_pages = ceil($count/$limit); 
        }  else  { 
            $total_pages = 0; 
        } 
        if  ($page > $total_pages)  {
            $page = $total_pages;
        }
        $data['page'] = $page;
        $data['total'] = $total_pages;
        $data['records'] = $count;
        /**/
        
        $start = $limit * $page - $limit;
        if  ($start < 0)  {
            $start = 0;
        }
    
        $query_sites = "SELECT s.id AS id, s.site AS site, r.status AS status
                        FROM sites AS s
                        JOIN users AS u
                        JOIN responsible AS r ON r.id_user=u.id 
                        WHERE u.id='".$login_id."'
                            AND r.id_site = s.id ".$searchstring."
                        ORDER BY $sidx $sord 
                        LIMIT $start , $limit";
				
        $rows_sites = $this->mdl_query->select($query_sites);
		if  ($rows_sites)  {
			for ($i=0; $i<count($rows_sites); $i++)  {
				$data['rows'][$i]['id'] = $rows_sites[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_sites[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_sites[$i]['site'];
				$data['rows'][$i]['cell'][] = $rows_sites[$i]['status'];
			}
		}
        return $data;
    }
    
    /*Получаем список сообщений об опечатках*/
    function get_list_messages($data)  {
        $id_site = $data["id_site"];
        $login_id = $data['login_id'];
        if (!$this->get_right_site($data))  {
            return array();
        }
        
        $page = $data['page'];
        $limit = $data['limit'];
        $sord = $data['sord'];
        $sidx = $data['sidx'];
	
        $search = $data['search'];
        $searchstring = "";
        if  ($search == "true")  {
            $searchField = $data['searchField'];
            $searchOper = $data['searchOper'];		
            $searchString = $data['searchString'];
            $search_string = $this->mdl_search->search_string($searchField, $searchOper, $searchString);
            if  ($search_string != "")  {
                $searchstring .= " AND ".$search_string." ";
            }
        }
        $data = array();
        /*Данные для pagination jqGrid*/
        $query_count = "SELECT COUNT(m.id) AS count
                        FROM `messages` AS m
                        JOIN users AS u
                        JOIN responsible AS r ON r.id_user=u.id
                        WHERE m.id_site = '".$id_site."'
                            AND u.id = '".$login_id."'
                            AND r.id_site = m.id_site 
                            AND r.id_user = u.id";
        $rows_count = $this->mdl_query->select($query_count);
        if  ($rows_count)  {
            $count = $rows_count[0]['count'];
        }

        if($count > 0)  { 
            $total_pages = ceil($count/$limit); 
        }  else  { 
            $total_pages = 0; 
        } 
        if  ($page > $total_pages)  {
            $page = $total_pages;
        }
        $data['page'] = $page;
        $data['total'] = $total_pages;
        $data['records'] = $count;
        /**/
        $start = $limit*$page - $limit;
        if  ($start < 0)  {
            $start = 0;
        }

        $query_messages = "SELECT m.id AS id, 
                            m.link AS link, 
                            m.error_text AS text, 
                            m.comment AS comment, 
                            m.date AS date, 
                            m.status AS status
                            FROM messages AS m
                            JOIN users AS u
                            JOIN responsible AS r ON r.id_user=u.id
                            WHERE m.id_site = '".$id_site."'
                                AND u.id = '".$login_id."'
                                AND r.id_site = m.id_site 
                                AND r.id_user = u.id ".$searchstring."
                            ORDER BY $sidx $sord 
                            LIMIT $start , $limit";

        $rows_messages = $this->mdl_query->select($query_messages);
        if  ($rows_messages)  {
            for ($i=0; $i<count($rows_messages); $i++)  {
                $data['rows'][$i]['id'] = $rows_messages[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_messages[$i]['id'];
				$data['rows'][$i]['cell'][] = anchor($rows_messages[$i]['link'], 'ссылка', array('class'=>'typos_link', 'target'=>'_blank'));
				$data['rows'][$i]['cell'][] = $rows_messages[$i]['text'];
				$data['rows'][$i]['cell'][] = $rows_messages[$i]['comment'];
				$data['rows'][$i]['cell'][] = $rows_messages[$i]['date'];
				$data['rows'][$i]['cell'][] = $rows_messages[$i]['status'];
            }
        }
        return $data;	
    }

    /*Обновляем статус для сайта*/
    function update_status($data)  {
        if  ($this->get_right_site($data))  {
            $this->db->query("UPDATE responsible SET status = '".$data['status']."' WHERE id_site = '".$data['id_site']."' AND id_user = '".$data['login_id']."'");
        }  else  {
            return false;
        }
    }
    
    /*Добавляем новое сообщение*/
    function add_message($data)  {
        $data_m[0] = 'NULL'; //Инкремент id
        $data_m[1] = $data['id_site'];
        $data_m[2] = $data['link'];
        $data_m[3] = $data['error_text'];
        $data_m[4] = $data['comment'];
        $data_m[5] = date('Y-m-d H:i:s', time());
        $data_m[6] = $data['status'];

        if  ($this->get_right_site($data))  {
            $this->mdl_query->insert('messages', $data_m);
        }
    }

    /*Удаляем сообщение*/
    function delete_message($data)  {
		if  ($this->get_right_message($data))  {
			$this->mdl_query->delete("messages", "id = '".$data['id_message']."'");
		}
    }

    /*Обновляем статус сообщения*/
    function edit_message($data)  {
        if  ($this->get_right_message($data))  {
			$this->db->query("UPDATE messages SET status = '".$data['status']."' WHERE id = '".$data['id_message']."' AND id_site = '".$data['id_site']."' ");
		}
    }

    /*Узнать права на сайт*/
    function get_right_site($data)  {
        $query = "SELECT r.id_site AS id_site
                    FROM responsible AS r
                    JOIN users AS u ON u.id = r.id_user
                    WHERE u.id = '".$data['login_id']."'
                        AND r.id_site = '".$data['id_site']."' ";
        $row = $this->mdl_query->select($query);
        if  ($row)  {
            return true;
        }  else  {
            return false;
        }
    }

    /*Узнать права пользователя на сообщение*/
    function get_right_message($data)  {
        $query = "SELECT m.id AS id_message FROM messages AS m
                    JOIN sites AS s ON m.id_site = s.id
                    JOIN users AS u
                    JOIN responsible AS r ON r.id_user = u.id
                    WHERE m.id = '".$data['id_message']."'
                        AND u.id = '".$data['login_id']."'
                        AND r.id_site = s.id
                        AND s.id = '".$data['id_site']."' ";
        $rows = $this->mdl_query->select($query);
        if  ($rows)  {
            return true;
        }  else  {
            return false;
        }
    }

}
/**/
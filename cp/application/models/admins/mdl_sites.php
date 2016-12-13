<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с сайтами*/
class Mdl_sites extends CI_Model {
    
    /*Получаем сайты*/
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
                $searchstring .= " WHERE ".$search_string." ";
            }
        }
        
        $data = array();
        /*Данные для pagination jqGrid*/
        $query_count = "SELECT COUNT(s.id) AS count FROM sites AS s";
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
        
        $query_sites = "SELECT s.id AS id, s.site AS site, s.date as date
                        FROM sites AS s 
                        $searchstring
                        ORDER BY $sidx $sord 
                        LIMIT $start , $limit";
        
        $rows_sites = $this->mdl_query->select($query_sites);
        $i = 0;
        if  ($rows_sites)  {
            for ($i=0; $i<count($rows_sites); $i++)  {
                $data['rows'][$i]['id'] = $rows_sites[$i]['id'];
                $data['rows'][$i]['cell'][] = $rows_sites[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_sites[$i]['site'];
				$data['rows'][$i]['cell'][] = $rows_sites[$i]['date'];
            }
        }        
        return $data;
    }
    
    /*Получаем пользователей по сайту*/
    function get_list_users($data)  {
        $login_id = $data['login_id'];
        $id_site = $data['id_site'];
        
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
        $query_count = "SELECT COUNT(u.id) AS count
                        FROM users AS u 
                        JOIN responsible AS r
                        WHERE r.id_user = u.id
                            AND r.id_site = '".$id_site."'";

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
               
        $query_users = "SELECT u.id AS id, 
                                u.type AS type,
                                u.email AS email, 
                                u.date AS date, 
                                u.activity AS activity,
                                u.firstname AS firstname, 
                                u.middlename AS middlename, 
                                u.lastname AS lastname,
                                u.login AS login,
                                r.status AS mail,
                                r.date AS rdate
                                FROM users AS u 
                                JOIN responsible AS r
                                WHERE r.id_user = u.id
                                    AND r.id_site = '".$id_site."'
                                    $searchstring 
                                ORDER BY $sidx $sord 
                                LIMIT $start , $limit ";

        $rows_users = $this->mdl_query->select($query_users);
        if  ($rows_users)  {
            for ($i=0; $i<count($rows_users); $i++)  {
                $data['rows'][$i]['id'] = $rows_users[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['id'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['login'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['type'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['email'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['firstname'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['middlename'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['lastname'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['activity'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['mail'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['rdate'];
            }
        }

        return $data;
    }
    
    /*Добавление сайта*/
    function add_site($data)  {
        if (!$this->check_site($data['site']))  {
            return array('message' => 'Сайт не уникален');
        }
        $data2[0] = 'NULL';
        $data2[1] = $data['site'];
        $data2[2] = date("Y-m-d H:i:s", time());
        $this->mdl_query->insert('sites', $data2);
    }
    
    /*Обновление названия*/
    function edit_site($data)  {
        if (!$this->check_site($data['site']))  {
            return array('message' => 'Сайт не уникален');
        }
        $query = "UPDATE sites SET site='".$data['site']."' WHERE id = '".$data['id_site']."'";
        $this->db->query($query);
    }
    
    /*Удаление сайта*/
    function delete_site($data)  {
        if  ($this->count_responsibles($data) == 0)  {
            $this->mdl_query->delete('sites', "id = '".$data['id_site']."'");
            $this->mdl_query->delete('messages', "id_site = '".$data['id_site']."'");
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Подсчет пользователей сайта*/
    function count_responsibles($data)  {
        $query = "SELECT COUNT(r.id) AS count 
                    FROM responsible AS r
                    WHERE r.id_site = '".$data['id_site']."'";
        $count = $this->mdl_query->select($query);
        return $count[0]['count'];
    }
    
    /*Проверяем сайт на уникальность*/
    function check_site($site)  {
        $query = "SELECT COUNT(id) AS count 
                    FROM sites AS s
                    WHERE site = '$site'";
        $count = $this->mdl_query->select($query);
        if  ($count[0]['count'] == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
}
/**/
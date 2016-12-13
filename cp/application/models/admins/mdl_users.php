<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с пользователями*/
class Mdl_users extends CI_Model {
    
    /*Получаем всех пользователей*/
    function get_list_users($data)  {
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
        $query_count = "SELECT COUNT(id) AS count
                        FROM users AS u";
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
               
        $query_users = "SELECT id, login, type, email, firstname, middlename, lastname, activity, date
                            FROM users AS u 
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
                $data['rows'][$i]['cell'][] = '******';//$rows_users[$i]['password'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['firstname'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['middlename'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['lastname'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['activity'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['date'];
            }
        }

        return $data;
    }
    
    /*Получаем сайты пользователя*/
    function get_user_sites($data)  {
        $id_user = $data['id_user'];
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
        $query_count = "SELECT COUNT(DISTINCT id) AS count
                        FROM responsible AS r
                        WHERE id_user = '$id_user'";
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
               
        $query_users = "SELECT DISTINCT s.id AS id_site,
                                r.status AS status, 
                                s.site AS site,
                                r.date AS date
                                FROM responsible AS r
                                JOIN sites AS s ON s.id = r.id_site
                                WHERE r.id_user = '$id_user'
                                    $searchstring 
                                ORDER BY $sidx $sord 
                                LIMIT $start , $limit ";

        $rows_users = $this->mdl_query->select($query_users);
        if  ($rows_users)  {
            for ($i=0; $i<count($rows_users); $i++)  {
                $data['rows'][$i]['id'] = $rows_users[$i]['id_site'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['id_site'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['site'];
				$data['rows'][$i]['cell'][] = $rows_users[$i]['status'];
                $data['rows'][$i]['cell'][] = $rows_users[$i]['date'];
            }
        }

        return $data;
    }
    
    /*Добавляем пользователя*/
    function add_user($data)  {
        if  (!$this->check_email($data['email']))  {
            return array('message' => 'Email не уникален');
        }
        if  (!$this->check_login($data['login']))  {
            return array('message' => 'Логин не уникален');
        }
        $data2[0] = 'NULL';
        $data2[1] = $data['login'];
        $data2[2] = $data['type'];
        $data2[3] = $data['email'];
        $data2[4] = $this->mdl_authorized->process_pass($data['password']);
        $data2[5] = $data['firstname'];
        $data2[6] = $data['middlename'];
        $data2[7] = $data['lastname'];
        $data2[8] = $data['activity'];
        $data2[9] = date('Y-m-d H:i:s', time());
        $this->mdl_query->insert('users', $data2);
    }
    
    function edit_user($data)  {
        if  (!$this->check_email($data['email'], $data['id_user']))  {
            return array('message' => 'Email не уникален');
        }
        if  (!$this->check_login($data['login'], $data['id_user']))  {
            return array('message' => 'Логин не уникален');
        }
        
        $query = "UPDATE users SET ";
            $query .= " login = '".$data['login']."' , ";
            $query .= " type = '".$data['type']."' , ";
            $query .= " email = '".$data['email']."' , ";
            
            if  ($data['password'] != '******')  {
                $data['password'] = $this->mdl_authorized->process_pass($this->mdl_post->string('password'));
                $query .= " password = '".$data['password']."' , ";
            }
            
            $query .= " firstname = '".$data['firstname']."' , ";
            $query .= " middlename = '".$data['middlename']."' , ";
            $query .= " lastname = '".$data['lastname']."' , ";
            $query .= " activity = '".$data['activity']."' ";
            
            $query .= " WHERE id = '".$data['id_user']."' ";

        $this->db->query($query);
    }
    
    /*Удаляем пользователя*/
    function delete_user($data)  {
        $this->mdl_query->delete('users', " id = '".$data['id_user']."' ");
        $this->mdl_query->delete('responsible', " id_user = '".$data['id_user']."' ");
    }
    
    /*Снимаем ответсвенного*/
    function delete_responsible($data)  {
        $this->mdl_query->delete('responsible', " id_site = '".$data['id_site']."' AND id_user = '".$data['id_user']."' ");
    }
    
    /*Обновляем статус*/
    function edit_responsible($data)  {
        $this->db->query("UPDATE responsible SET status = '".$data['status']."' WHERE id_site = '".$data['id_site']."' AND id_user = '".$data['id_user']."' ");
    }
    
    /*Проверяем логин на уникальность*/
    function check_login($login, $id_user = '')  {
        if  ($this->config->item('typos_admin_login'))  {
            if  ($login == $this->config->item('typos_admin_login'))  {
                return false;
            }
        }
        $query = "SELECT COUNT(id) AS count FROM users WHERE login = '$login' ";
        if ($id_user != '')  {
            $query .= " AND id != '$id_user'";
        }
        $check = $this->mdl_query->select($query);
        if  ($check[0]['count'] == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Проверяем email на уникальность*/
    function check_email($email, $id_user = '')  {
        if  ($this->config->item('typos_admin_email'))  {
            if  ($email == $this->config->item('typos_admin_email'))  {
                return false;
            }
        }
        $query = "SELECT COUNT(id) AS count FROM users WHERE email = '$email' ";
        if ($id_user != '')  {
            $query .= " AND id != '$id_user'";
        }
        $check = $this->mdl_query->select($query);
        if  ($check[0]['count'] == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Получаем сайты для пользователя, кроме уже принадлежащих*/
    function get_sites($id_user)  {
        return $this->mdl_query->select("SELECT id, site
                                            FROM sites
                                            WHERE id NOT IN (SELECT id_site
                                                                FROM responsible
                                                                WHERE id_user = '$id_user'
                                                             ) ");
    }
    
    /*Добавляем сайт в ответственность*/
    function add_responsible($data)  {
        if (!$this->check_user_id($data['id_user']))  {
            return array('message' => "Пользователь не существует");
        }
        if (!$this->check_responsible($data))  {
            return array('message' => "Этот сайт уже назначен");
        }
        if (!$this->check_site_id($data['id_site']))  {
            return array('message' => "Сайт не существует");
        }

        $data2[0] = 'NULL';
        $data2[1] = $data['id_site'];
        $data2[2] = $data['id_user'];
        $data2[3] = $data['status'];
        $data2[4] = date('Y-m-d H:i:s', time());
        $this->mdl_query->insert('responsible', $data2);
    }
    
    /*Проверяем - есть ли у пользователя такой сайт*/
    function check_responsible($data)  {
        $return = $this->mdl_query->select("SELECT COUNT(id) AS count FROM responsible WHERE id_user = '".$data['id_user']."' AND id_site = '".$data['id_site']."'");
        if ($return[0]['count'] == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Проверяем - есть ли пользователь по id*/
    function check_user_id($id_user)  {
        $return = $this->mdl_query->select("SELECT COUNT(id) AS count FROM users WHERE id = '$id_user' ");
        if ($return[0]['count'] > 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    function check_site_id($id_site)  {
        $return = $this->mdl_query->select("SELECT COUNT(id) AS count FROM sites WHERE id = '$id_site' ");
        if ($return[0]['count'] > 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
}
/**/
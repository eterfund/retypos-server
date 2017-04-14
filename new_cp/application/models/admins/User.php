<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с пользователями*/
class User extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        
        // For hashPassword function
        $this->load->model("userHelper");
    }
    
    /*Получаем всех пользователей*/
    function getUsers($data)  {
        return $this->filterResults('users', $data);
    }
    
    /*Получаем сайты пользователя*/
    function getUserSites($data)  {
        return $this->filterResults('responsible', $data);
    }
    
    private function filterResults($table, $data) {
        log_message('error', "data = " . print_r($data, true));
        $this->load->helper("search");
        
        $id_user = isset($data['id_user']) ? $data['id_user'] : 0;
        $page = $data['page'];
        $limit = $data['limit'];
        $sord = isset($data['sord']) ? $data['sord'] : 0;
        $sidx = isset($data['sidx']) ? $data['sidx'] : 0;
        $search = $data['search'];
        $searchstring = "";
        $search_string = "";
        if  ($search == "true")  {
            $searchField = $data['searchField'];
            $searchOper = $data['searchOper'];		
            $searchString = $data['searchString'];
            $search_string = searchString($searchField, $searchOper, $searchString);
            if  ($search_string != "")  {
                $searchstring .= " AND " . $search_string . " ";
            }
        }
        
        $data = array();
        
        
        /*Данные для pagination jqGrid*/
        if ( $table == 'responsible' ) {
            $query_count = "SELECT COUNT(DISTINCT id) AS count
                            FROM responsible AS r
                            WHERE id_user = '$id_user'";
        } else if ( $table == 'users' ) {
            $query_count = "SELECT COUNT(id) AS count
                        FROM users AS u";
        }
        
        $count = $this->db->query($query_count)->num_rows();
        if( $count > 0 )  { 
            $total_pages = ($limit > 0) ? ceil($count / $limit) : 1; 
        }  else  { 
            $total_pages = 0; 
        } 
        if  ($page > $total_pages)  {
            $page = $total_pages;
        }
        $data['page'] = $page;
        $data['total'] = $total_pages;
        $data['records'] = $count;
        
        $join_on = "s.id = r.id_site" .
                    " WHERE r.id_user = '$id_user'";
        /**/
        $start = $limit * $page - $limit;
        if  ($start < 0)  {
            $start = 0;
        }
        
        $this->db->select('*');
        if ( $table == 'users' ) {
            
            $this->db->from('users as u');
        } else {
            $this->db->from('responsible as r');
            $this->db->join('sites as s', $join_on);
        }
        
        if ( $search == "true" ) {
            $this->db->where($search_string);
        }
        
        if ( $limit ) { 
            $this->db->limit($limit, $start);
        }
        
        if ( $sidx ) {
            $this->db->order_by($sidx . " " . $sord);
        }
        
        $results = $this->db->get();
        
        if ( $table == 'users') {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id']     = $row->id;
                $data['rows'][$id]['cell'][] = $row->id;
                $data['rows'][$id]['cell'][] = $row->login;
                $data['rows'][$id]['cell'][] = $row->type;
                $data['rows'][$id]['cell'][] = $row->email;
                $data['rows'][$id]['cell'][] = '******';
                $data['rows'][$id]['cell'][] = $row->firstname;
                $data['rows'][$id]['cell'][] = $row->middlename;
                $data['rows'][$id]['cell'][] = $row->lastname;
                $data['rows'][$id]['cell'][] = $row->activity;
                $data['rows'][$id]['cell'][] = $row->date;
            }
        } else if ( $table == 'responsible' ) {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id'] = $row->id;
                $data['rows'][$id]['cell'][] = $row->id;
                $data['rows'][$id]['cell'][] = $row->site;
                $data['rows'][$id]['cell'][] = $row->status;
                $data['rows'][$id]['cell'][] = $row->date;
            }
        }

        return $data;
    }
    
    /*Добавляем пользователя*/
    function addUser($data)  {
        if  (!$this->checkEmail($data['email']))  {
            return array('message' => 'Email не уникален');
        }
        if  (!$this->checkLogin($data['login']))  {
            return array('message' => 'Логин не уникален');
        }
        
        $insertData = [
            'login'      => $data['login'],
            'type'       => $data['type'],
            'email'      => $data['email'], 
            'password'   => $this->userHelper->hashPassword($data['password']),
            'firstname'  => $data['firstname'],
            'middlename' => $data['middlename'],
            'lastname'   => $data['lastname'],
            'activity'   => $data['activity'],
            'date'       => date('Y-m-d H:i:s', time()),
        ];
                
        $this->db->insert('users', $insertData);
    }
    
    function editUser($data)  {
        if  (!$this->checkEmail($data['email'], $data['id_user']))  {
            return array('message' => 'Email не уникален');
        }
        if  (!$this->checkLogin($data['login'], $data['id_user']))  {
            return array('message' => 'Логин не уникален');
        }
        
        if  ($data['password'] != '******')  {
            $data['password'] = $this->userHelper->hashPassword($this->input->post('password'));
        }
            
         $insertData = [
            'id'         => $data['id_user'],
            'login'      => $data['login'],
            'type'       => $data['type'],
            'email'      => $data['email'], 
            'password'   => $this->userHelper->hashPassword($data['password']),
            'firstname'  => $data['firstname'],
            'middlename' => $data['middlename'],
            'lastname'   => $data['lastname'],
            'activity'   => $data['activity'],
            'date'       => date('Y-m-d H:i:s', time()),
        ];
        
        $this->db->where('id', $data['id_user']);
            
        $this->db->update('users', $insertData);
    }
    
    /*Удаляем пользователя*/
    function deleteUser($data)  {
        $this->db->where('id', $data['id_user']);
        $this->db->delete('users');
        
        $this->db->where('id', $data['id_user']);
        $this->db->delete('responsible');
    }
    
    /*Снимаем ответсвенного*/
    function deleteResponsible($data)  {
        $this->db->where('id_site', $data['id_site']);
        $this->db->where('id_user', $data['id_user']);
        
        $this->db->delete('responsible');
    }
    
    /*Обновляем статус*/
    function editResponsible($data)  {
        $this->db->where('id_site', $data['id_site']);
        $this->db->where('id_user', $data['id_user']);
        
        $this->db->update('responsible', array( 'status' => $data['status']));
    }
    
    /*Проверяем логин на уникальность*/
    function checkLogin($login, $id_user = '')  {
        if  ($this->config->item('typos_admin_login'))  {
            if  ($login == $this->config->item('typos_admin_login'))  {
                return false;
            }
        }
        
        $this->db->where('login', $login);

        if ($id_user != '')  {
            $this->db->where('id !=', $id_user);
        }
        
        $this->db->from('users');
        
        $count = $this->db->count_all_results();
        if  ($count == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Проверяем email на уникальность*/
    function checkEmail($email, $id_user = '')  {
        if  ($this->config->item('typos_admin_email'))  {
            if  ($email == $this->config->item('typos_admin_email'))  {
                return false;
            }
        }
        $this->db->where('email', $email);

        if ($id_user != '')  {
            $this->db->where('id !=', $id_user);
        }
        
        $this->db->from('users');
        
        $count = $this->db->count_all_results();
        if  ($count == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Получаем сайты для пользователя, кроме уже принадлежащих*/
    function getSites($id_user)  {
        
        return $this->db->query("SELECT id, site
               FROM sites
               WHERE id NOT IN (SELECT site_id
               FROM responsible
               WHERE user_id = '$id_user') ")->result();
    }
    
    /*Добавляем сайт в ответственность*/
    function addResponsible($data)  {
        if (!$this->checkUserId($data['id_user']))  {
            return array('message' => "Пользователь не существует");
        }
        if (!$this->checkResponsible($data))  {
            return array('message' => "Этот сайт уже назначен");
        }
        if (!$this->checkSiteId($data['id_site']))  {
            return array('message' => "Сайт не существует");
        }

        $data2[0] = 'NULL';
        $data2[1] = $data['id_site'];
        $data2[2] = $data['id_user'];
        $data2[3] = $data['status'];
        $data2[4] = date('Y-m-d H:i:s', time());
        $this->db->insert('responsible', $data2);
    }
    
    /*Проверяем - есть ли у пользователя такой сайт*/
    function checkResponsible($data)  {
        $this->db->where("user_id", $data['id_user']); 
        $this->db->where("site_id", $data['id_site']);
        $this->db->from("responsible");
        
        $count = $this->db->count_all_results();
        if ($count == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Проверяем - есть ли пользователь по id*/
    function checkUserId($id_user)  {
        $this->db->where("id", $id_user); 
        $this->db->from("users");
        
        $count = $this->db->count_all_results();
        if ($count == 0)  {
            return true;
        }  else {
            return false;
        }
    }
    
    function checkSiteId($id_site)  {
        $this->db->where("id", $id_site); 
        $this->db->from("sites");
        
        $count = $this->db->count_all_results();
        if ($count > 0)  {
            return true;
        }  else  {
            return false;
        }
    }
    
}
/**/
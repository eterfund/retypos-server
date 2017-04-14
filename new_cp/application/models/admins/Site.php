<?php

/**
 * Sites model
 *
 * @author <barbass@etersoft.ru>
 * @author george popoff <ambulance@etersoft.ru>
 */
class Site extends CI_Model {
    
     /*Получаем сайты*/
    function getSites($data) {
        return $this->filterResults('sites', $data);
    }
    
    /*Получаем пользователей по сайту*/
    function getSiteUsers($data)  {
        return $this->filterResults('users', $data);
    }
    
    private function filterResults($table, $data) {
        log_message('error', "data = " . print_r($data, true));
        $this->load->helper("search");
                
        $id_site = isset($data['id_site']) ? $data['id_site'] : 0;
        
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
            $search_string = searchString($searchField, $searchOper, $searchString);
            if  ($search_string != "")  {
                $searchstring .= " AND ".$search_string." ";
            }
        }
        
        $data = array();
        
        /*Данные для pagination jqGrid*/
       
        $users_join_on = "responsible.id_user = users.id" . " AND " .
                       "responsible.id_site = '". $id_site."'";
        
        if ( $table === 'users' ) {         
            $this->db->join('responsible', $users_join_on);
            
        } 

        $count = $this->db->count_all_results($table);
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
        
        $start = $limit * $page - $limit;
        if  ($start < 0)  {
            $start = 0;
        }
        
        $users_join_on = "r.id_user = u.id" . " AND " .
               "r.id_site = '". $id_site."'";
        
        $this->db->select('*');    
        /* Constructing query */
        if ( $table == 'users' ) {
            $this->db->from('users as u');
            $this->db->join('responsible as r', $users_join_on);
        } else if ( $table == 'sites' ) {
            $this->db->from('sites as s');
        }
              
        if ( $search == "true" ) {
            $this->db->where($search_string);
        }
        
        $this->db->limit($limit, $start);
        $this->db->order_by($sidx . " " . $sord);
        
        $results = $this->db->get();
        
        if ( $table == 'sites') {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id']     = $row->id;
                $data['rows'][$id]['cell'][] = $row->id;
                $data['rows'][$id]['cell'][] = $row->site;
                $data['rows'][$id]['cell'][] = $row->date;
            }
        } else if ( $table == 'users' ) {
            foreach( $results->result() as $id => $row ) {
                $data['rows'][$id]['id'] = $row->id;
                $data['rows'][$id]['cell'][] = $row->id;
                $data['rows'][$id]['cell'][] = $row->login;
                $data['rows'][$id]['cell'][] = $row->type;
                $data['rows'][$id]['cell'][] = $row->email;
                $data['rows'][$id]['cell'][] = $row->firstname;
                $data['rows'][$id]['cell'][] = $row->middlename;
                $data['rows'][$id]['cell'][] = $row->lastname;
                $data['rows'][$id]['cell'][] = $row->activity;
                $data['rows'][$id]['cell'][] = $row->email;
                $data['rows'][$id]['cell'][] = $row->date;
            }
        }
        
        return $data;
    }
    
    /*Добавление сайта*/
    function addSite($site)  {
        if (!$this->isSiteUnique($site['site']))  {
            return array('message' => 'Сайт не уникален');
        }
        
        log_message("error", "here");
        
        $data = array(
          'site' => $site['site'],
          'date' => date("Y-m-d H:i:s", time())
        );
        
        $this->db->insert('sites', $data);
    }
    
    /*Обновление названия*/
    function updateSite($data)  {
        if (!$this->isSiteUnique($data['site']))  {
            return array('message' => 'Сайт не уникален');
        }
        
        $this->db->set("site", $data['site']);
        $this->db->where("id", $data['id_site']);
        $this->db->update("sites");
    }
    
    /*Удаление сайта*/
    function deleteSite($site)  {
        if  ($this->countSiteResponsibles($site) == 0)  {
            $this->db->where("id", $site['id_site']);
            $this->db->delete("sites");
            $this->db->where("site_id", $site['id_site']);
            $this->db->delete("messages");
            return true;
        }  else  {
            return false;
        }
    }
    
    /*Подсчет пользователей сайта*/
    function countSiteResponsibles($data)  {
        $this->db->where("id_site", $data['id_site']);
        $this->db->from("responsible");
       
        return $this->db->count_all_results();
    }
    
    /*Проверяем сайт на уникальность*/
    function isSiteUnique($site)  {
        $this->db->where("site", $site);
        $this->db->from("sites");
        
        $count = $this->db->count_all_results();
        if  ($count == 0)  {
            return true;
        }  else  {
            return false;
        }
    }
}

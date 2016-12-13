<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Построение запросов*/
class Mdl_query extends CI_Model {
    
    /*Вставка*/
    function insert($table, $data)  {
        $query = "INSERT INTO `$table` VALUES(";
        $count = count($data);
        if  ($count == 0)  {
            return false;
        }
        for ($i=0; $i<$count; $i++)  {
            $query .= "'".$data[$i]."'";
            if  ($i < (count($data) -1 ))  {
                $query .= ",";
            }
        }
        $query .= ")";
        $this->db->query($query);
    }
    
    function delete($table, $where)  {
        $query = "DELETE FROM `$table` WHERE $where";
        if  ($this->db->query($query))  {
            return 1;
        }
    }

    function update($table, $data, $where)  {
        $query = "UPDATE $table SET ";
        $z = 0; //регистр запятой
        
        $count = count($data);
        if  ($count == 0)  {
            return false;
        }
        
        for ($i = 0; $i < count($data); $i++)  {
            if  (isset($data[$i]['value']) && isset($data[$i]['field']))  {
                if  ($data[$i]['value'])  {
                    if  ($z != 0)  {
                        $query .= ", "; 
                    }
                    $z = 1;
                    $f++;
                    $query .= "`".$data[$i]['field']."` = "."'".$data[$i]['value']."'";    
                }  else  {
                    continue;
                }
            }  else  {
                continue;
            }
        }
        $query .= " WHERE $where";
        
        if  ($f != 0)  {
            $this->db->query($query);
            return true;
        }  else  {
            return false;
        }
    }

    /*Вывод*/
    function select($select)  {
        $query = $this->db->query($select);
        $result = $query->result_array();
        if  (count($result) == 0)  {
            return false;
        }  else  {
            return $result;
        } 
    }
    
}
/**/
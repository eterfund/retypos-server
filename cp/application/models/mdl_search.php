<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Построение поискового запроса*/
class Mdl_search extends CI_Model {

    //Построение части запроса (поле->значение)
    function search_string($field, $type, $string)  {
        $s = " $field ";
        switch ($type)  {
            case 'eq':
                $s .= "="."'".$string."'";
                break;
            case 'ne':
                $s .= "!="."'".$string."'";
                break;
            case 'lt':
                $s .= "<"."'".$string."'";
                break;
            case 'le':
                $s .= "<="."'".$string."'";
                break;
            case 'gt':
                $s .= ">"."'".$string."'";
                break;
            case 'ge':
                $s .= ">="."'".$string."'";
                break;
            case 'bw':
                $s .= "LIKE '".$string."%'";
                break;
            case 'bn':
                $s .= "NOT LIKE '".$string."%'";
                break;	
            case 'ew':
                $s .= "LIKE '%".$string."'";
                break;
            case 'en':
                $s .= "NOT LIKE '%".$string."'";
                break;	
            case 'cn':
                $s .= "LIKE '%".$string."%'";
                break;	
            case 'nc':
                $s .= "NOT LIKE '%".$string."%'";
                break;	
            case 'nu':
                $s .= "IS NULL";
                break;
            case 'nn':
                $s .= "IS NOT NULL";
                break;
            case 'in':
                $s .= "IN ($string)";
                break;
            case 'ni':
                $s .= "NOT IN ($string)";
                break;	
            default:
                $s = "";
			break;
        }
        return $s;
    }

}
/**/
<?php

/**
 * Search helper functions
 *
 * @author george popoff <ambulance@etersoft.ru>
 */

/**
 * Composes search statement for query string
 * 
 * Example of return " name LIKE %'somewhat'%"
 * 
 * @param type $field 
 * @param type $operator
 * @param type $string Pattern or value
 * @return string
 */
function searchString($field, $operator, $string) {
        $s = " $field ";
        
        log_message('debug', $field);
        log_message('debug', $operator);
        log_message('debug', $string);
        
        if ( $field == "" ) {
            return false;
        }
        
        switch ($operator)  {
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
        
        log_message('debug', 'return ' . $s);
        return $s;
}
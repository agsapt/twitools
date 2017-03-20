<?php

namespace Ags\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Utility extends AbstractPlugin {
	static $months = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	static $months_lcase = array('januari', 'pebruari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember');
	
	function _ago($datetime, $full = false) {
	    $now = new \DateTime;
	    $ago = new \DateTime($datetime);
	    $diff = $now->diff($ago);
	
	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;
	
	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }
	
	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
    
	function array2object($array = array()) {
		$obj = new stdClass();
		foreach ($array as $key => $value) $obj->$key = $value;
		return $obj;
	}
	
	function debug($param, $die = false) {
	    echo "<pre>";
	    print_r($param);
	    echo "</pre>";
	    if ($die) die();
	}
	
	function formatDate($thedate, $theformat = 'j F Y H:i') {
		return date($theformat, strtotime($thedate));
	}
	
	function getArticleAttribute($attributes, $attrname) {
		foreach ($attributes as $attr) {
			if ($attr['attributeGuid'] == $attrname) {
				return $attr['value'];
			}
		}
		return false;
	}
	
	/**
	 * convert indonesian date format to mysql format. indonesian date format is something like
	 * 23 September 2005 13:05 WIB
	 */
	function inddate2mysql($inddate) {
		$els = explode(' ', $inddate);
		$month = array_search(strtolower($els[1]), Utility::$months_lcase);
		return sprintf("%d-%d-%d %s", $els[2], $month+1, $els[0], $els[3]);
	}
	
	/**
	 * convert mysql date format to indonesian format
	 */
	function mysqldate2ind($mysqldate) {
		$els = explode(' ', $mysqldate);
		$thedate = explode('-', $els[0]);
		return sprintf("%d %s %d %s", $thedate[2], Utility::$months[$thedate[1]-1], $thedate[0], $els[1]);
	}
	
	function object2array($object) {
		$arr = array();
		foreach (get_object_vars($object) as $key=>$value) {
			$arr[$key] = $value;
		}
		return $arr;
	}
}
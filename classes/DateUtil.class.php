<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class DateUtil
{
	static function toSQL($date)
	{
		$date = DateTime::createFromFormat('d-m-Y', $date);
		return $date->format('Y-m-d');
	}
	
	static function toDatetime($date, $time)
	{
		return DateTime::createFromFormat('d/m/Y H:i', "{$date} {$time}")->format('Y-m-d H:i:s');
	}
	
	static function toDMY($date)
	{
		return date('d-m-Y', strtotime($date));
	}
	
	static function toDMYHMS($date)
	{
		return date('d-m-Y H:i:s', strtotime($date));	
	}

	static function toFull($date)
	{
			
	}
}

?>
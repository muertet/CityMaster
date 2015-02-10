<?php
class Util
{
	public static function formatAmount ($amount) {
		return number_format($amount, 0, ',', '.');
	}
	public static function getNumberOfDays($startDate, $endDate, $hoursPerDay="7.5", $excludeToday=false)
	{
	    // d/m/Y
	    $start = new DateTime($startDate);
	    $end = new DateTime($endDate);
	    $oneday = new DateInterval("P1D");

	    $days = array();

	    /* Iterate from $start up to $end+1 day, one day in each iteration.
	    We add one day to the $end date, because the DatePeriod only iterates up to,
	    not including, the end date. */
	    foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day) {
	        $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
	        if($day_num < 6) { /* weekday */
	            $days[$day->format("Y-m-d")] = $hoursPerDay;
	        } 
	    }

	    if ($excludeToday)
	        array_pop ($days);

	    return $days;       
	}
	function isWeekend($date) {
	    return (date('N', strtotime($date)) >= 6);
	}

	public static function dateDiff($start, $end) {
		$date1 = new DateTime($start);
		$date2 = new DateTime($end);
		$interval = $date1->diff($date2);
		return $interval;
	}
	public static function mail($to, $title, $template, $vars = array()) {
		
		$file = Config::get('root').'/templates/mails/'.$template.'.html';

		//$domain = Config::get('domain');
		$domain = 'gameriso.com';
		
		$headers = "From: noreply@".$domain."\r\n";
		$headers .= "Reply-To: noreply@".$domain."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		
		//http://css-tricks.com/sending-nice-html-email-with-php/
		// http://www.campaignmonitor.com/
		if (!file_exists($file)) {
			throw new Exception("Mail template doesn't exist");
		}
		$html = file_get_contents($file);
		
		$vars['domain'] = Config::get('domain');
		$vars['website'] = 'http://'.Config::get('domain');
		
		foreach ($vars as $k => $v) {
			$html = str_replace('<%='.$k.'%>',$v,$html);
		}
					
		mail($to, $title, $html, $headers);
	}
	/**
	* Compresses an image
	* @param string $src current file route
	* @param string $dest file destination
	* @param integer $quality compression quality
	* 
	* @return mixed (boolean/string)
	*/
	public static function compressImage($src, $dest , $quality=50) 
	{
	    $info = getimagesize($src);
	  
	    if ($info['mime'] == 'image/jpeg') 
	    {
	        $image = imagecreatefromjpeg($src);
	    }
	    elseif ($info['mime'] == 'image/gif') 
	    {
	        $image = imagecreatefromgif($src);
	    }
	    elseif ($info['mime'] == 'image/png') 
	    {
	        $image = imagecreatefrompng($src);
	    }
	    else
	    {
	        return false;
	    }
	  
	    //compress and save file to jpg
	    imagejpeg($image, $dest, $quality);
	  
	    //return destination file
	    return $dest;
	}
    public static function xml2json($fileContents)
	{
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $simpleXml = simplexml_load_string($fileContents);
        return json_encode($simpleXml);
    }

    public static function curl($url, $data = '') {
        $ch = curl_init($url);
        $header = array();
        $header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[]   = "Cache-Control: max-age=0";
        $header[]   = "Connection: keep-alive";
        $header[]   = "Keep-Alive: 300";
        $header[]   = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[]   = "Accept-Language: en-us,en;q=0.5";
        $header[]   = "Pragma: "; // browsers keep this blank.

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if($data!=''){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }



        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        return curl_exec($ch);
    }
    public static function p()
    {
        echo'<pre>';
        $args = func_get_args();

        foreach($args as $var)
        {
            if($var==null || $var==''){
                var_dump($var);
            }elseif(is_array($var) || is_object($var)){
                print_r($var);
            }else{
                echo $var;
            }
            echo '<br>';
        }

        echo'</pre>';
    }
	
	public static function object_to_array($var)
	{
	    $result = array();
	    $references = array();
	 
	    // loop over elements/properties
	    foreach ($var as $key => $value) {
	        // recursively convert objects
	        if (is_object($value) || is_array($value)) {
	            // but prevent cycles
	            if (!in_array($value, $references)) {
	                $result[$key] = object_to_array($value);
	                $references[] = $value;
	            }
	        } else {
	            // simple values are untouched
	            $result[$key] = $value;
	        }
	    }
	    return $result;
	}
	
    public static function friendly_url($str, $replace=array(), $delimiter='-') {
        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }
}

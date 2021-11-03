<?php
function html_2_text($html){
	$str = $html;
	$str = htmlspecialchars($str);
	$str = str_replace("\n", "", $str);
	$str = str_replace("\r", "", $str);
	return $str;
}

function search_str($text, $str){
	$regex_esc = array('[', '^', '$', '.', '|', '?', '*', '+', '(', ')');
	$before = 30;
	$after = 40;
	$str_pos = mb_stripos($text, $str, 0, "UTF-8");
	$str_length = mb_strlen($str, "UTF-8");
	$start = $str_pos < $before ? 0 : $str_pos - $before;
	$length = $before + $str_length + $after;
	$text = mb_substr($text, $start, $length, "UTF-8");
	foreach($regex_esc as $value){
		$str = str_replace($value, '\\'.$value, $str);
	}
	$text = preg_replace('/('.$str.')/i','<font style="color:#CC0033;font-weight:bold">$1</font>', $text);
	return $text;
}


function imageresize($src, $dest, $destW, $destH){
	if(file_exists($src) && isset($dest)){
		$srcsize = getimagesize($src);
		$srcextension = $srcsize[2];
		if($srcsize[0] <= $destW && $srcsize[1] <= $destH){
			$destW = $srcsize[0];
			$destH = $srcsize[1];
		}
		else{
			$srcratio = $srcsize[0] / $srcsize[1];
			if($srcratio > 1){
				$destH = $destW / $srcratio;
			}
			else {
				$destW = $destH * $srcratio;
			}
		}
	}
	$destimage = imagecreatetruecolor($destW,$destH);
	
	switch($srcextension){
		case 1: $srcimage = imagecreatefromgif($src); break;
		case 2: $srcimage = imagecreatefromjpeg($src); break;
		case 3: $srcimage = imagecreatefrompng($src); break;
	}
	imagecopyresampled($destimage, $srcimage, 0, 0, 0, 0, $destW, $destH, imagesx($srcimage), imagesy($srcimage));
	imagejpeg($destimage, $dest, 90); 	
}


function getheight($pic){
	$pic = './pic/thumb_big/'.$pic.'.jpg';
	list($width, $height) = getimagesize($pic);
	$height += 25-$height%25;
	return $height;
}

function word_count($str){
	$str = str_replace("\r",'',$str);
	$str = str_replace("\n",'',$str);
	$str = str_replace("&nbsp;",'',$str);
	$patterns = array('hl','b','i','u','s','code','quote','right','center','url');
	foreach ($patterns as &$patterns) {
		$str = preg_replace('/\['.$patterns.']/', '', $str);
		$str = preg_replace('/\[\/'.$patterns.']/', '', $str);
	}
	$str = preg_replace('/\[icon]([^[]*)\[\/icon]/', '', $str);
	$str = preg_replace('/\[pic]([^[]*)\[\/pic]/', '', $str);
	$str = preg_replace('/\[music]([^[]*)\[\/music]/', '', $str);

	$sum = round((strlen($str) - mb_strlen($str, "utf-8"))/2) + str_word_count($str, 0);
	return $sum;
}

function fmt_rss_time($dateSrc){
    $timeZone = 'Asia/Hong_Kong';
    $dateTime = new DateTime($dateSrc, new DateTimeZone('UTC')); 
    $dateTime->setTimeZone(new DateTimeZone($timeZone));
	return $dateTime->format(DATE_RSS);
}

function fmt_time($dateSrc){
    $timeZone = 'Asia/Hong_Kong';    
    $dateTime = new DateTime($dateSrc, new DateTimeZone('UTC')); 
    $dateTime->setTimeZone(new DateTimeZone($timeZone)); 
	return $dateTime->format('Y-m-d A H:i:s');
}

function fmt_music($src){
	$str = '<audio controls="controls" preload="none">';
//	$str .= '<source src="horse.ogg" type="audio/ogg" />';
	$str .= '<source src="'.$src[1].'" type="audio/mp3" />';
	$str .= '</audio>';

	return $str;
}
function refmt_music($src){
	foreach($src as $m){
		return rawurldecode(rawurldecode($m));
	}
}
function fmt_url($src){
	return '<a href="'.$src[1].'" target="_blank">'.$src[1].'</a>';
}

function fmt_pic($src){
	list($width, $height, $type, $attr) = getimagesize('pic/thumb_big/'.$src[1].'.jpg', $info);
	$max_height = $height - $height%25;
	return '<a href="/photo/'.$src[1].'/" target="_blank"><img class="photo" loading="lazy" style="max-height:'.$max_height.'px" data-width="'.$width.'" data-height="'.$height.'" src="/pic/thumb_big/'.$src[1].'.jpg" alt="" /></a>';
}

function fmt_blog($t){
	$text = $t;
	$text = preg_replace_callback('/\[pic]([^[]*)\[\/pic]/', 'fmt_pic', $text);
	$text = str_replace('[music]', '[music]/music/', $text);
	$text = preg_replace_callback('/\[music]([^[]*)\[\/music]/', 'fmt_music', $text);
	$text = str_replace('[icon]', '<img class="icon" loading="lazy" src="/image/icon/', $text);
	$text = str_replace('[/icon]', '.gif">', $text);
	$text = preg_replace_callback('/\[url]([^[]*)\[\/url]/', 'fmt_url', $text);
	$text = str_replace('[quote]', '<cite>', $text);
	$text = str_replace('[/quote]', '</cite>', $text);
	$text = str_replace('[code]', '<code>', $text);
	$text = str_replace('[/code]', '</code>', $text);
	$text = str_replace('[b]', '<b>', $text);
	$text = str_replace('[/b]', '</b>', $text);
	$text = str_replace('[i]', '<i>', $text);
	$text = str_replace('[/i]', '</i>', $text);
	$text = str_replace('[u]', '<u>', $text);
	$text = str_replace('[/u]', '</u>', $text);
	$text = str_replace('[s]', '<s>', $text);
	$text = str_replace('[/s]', '</s>', $text);
	$text = str_replace('[right]', '<div class="right">', $text);
	$text = str_replace('[/right]', '</div>', $text);
	$text = str_replace('[center]', '<div class="center">', $text);
	$text = str_replace('[/center]', '</div>', $text);
	$text = str_replace('[hl]', '<span class="highlight">', $text);
	$text = str_replace('[/hl]', '</span>', $text);
	$text = nl2br($text);
	$text = str_replace('</div><br />', '</div>', $text);
	
	return $text;
}

?>

<?php
require_once("./config.inc.php");
require_once("./common.func.php");

$sql_sy = mysqli_connect($hostname_sy, $username_sy, base64_decode($password_sy), $database_sy);
if (mysqli_connect_errno($sql_sy)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else{
  mysqli_query($sql_sy, "SET NAMES 'utf8'");
}

ini_set( 'session.cookie_httponly', true);
ini_set( 'session.cookie_secure', true);
session_start();

if(!isset($_SESSION["token"]) && isset($_COOKIE["password"])){
  $query = "SELECT `value` FROM `settings` WHERE `name`='password'";
  $result = mysqli_query($sql_sy, $query);
  $row = mysqli_fetch_assoc($result);
  $password = $row["value"];
  mysqli_free_result($result);
  
  if($_COOKIE["password"] == $password){
    $_SESSION["token"] = sha1("");
  }
}

$servername = $_SERVER['SERVER_NAME'];

$url = explode("/", preg_replace("/([?].*)/","",$_SERVER['REQUEST_URI']));

for($i=0; $i<4; $i++){
  $uri[$i] = isset($url[$i]) ? $url[$i] : "";
}

$content = "";

switch ($uri[1]) {
  default:
    
    $query = "SELECT * FROM `blog_view` ORDER BY `time` DESC LIMIT 0,1";
    $result = mysqli_query($sql_sy, $query);
    $row = mysqli_fetch_assoc($result);
    
    $time = $row["time"];
    $content .= '<div id="blog_head">';
    $content .= '<div id="blog_title">'.$row["title"]."</div>";
    $content .= '<div id="blog_time" class="time" data-time="'.$row["time"].'">'."</div>";
    $content .= '<div id="blog_id">'.$row["id"]."</div>";
    $content .= '<div id="blog_category">'.$row["category_name"]."</div>";
    if(isset($_SESSION["token"]) && $row["private"]){
      $content .= '<div id="blog_private">private</div>';
    }
    elseif(isset($_SESSION["token"])){
      $content .= '<div id="blog_private">public</div>';
    }
    $content .= '</div>';
    if($row["private"]){
      if(isset($_SESSION["token"])){
        $content .= '<div id="blog_content">'.fmt_blog($row["blog"])."</div>";
      }
      $content .= '<div id="blog_content">私人日記</div>';
    }
    else{
      $content .= '<div id="blog_content">'.fmt_blog($row["blog"])."</div>";
    }
    $content .= '<div id="blog_foot">';
    $content .= '</div>';
    
    mysqli_free_result($result);
    
    $query = "SELECT id FROM blog WHERE `time` < '".$time."' ORDER BY `time` DESC LIMIT 0,1";
    $result = mysqli_query($sql_sy, $query);
    $row = mysqli_fetch_assoc($result);
    $prev_blog = $row["id"];
    mysqli_free_result($result);

  break;
  case "blog":

    if($uri[2] == ""){
    }
    elseif($uri[3] == ""){
      $id = intval($uri[2]);
      $query = "SELECT * FROM `blog_view` WHERE id=".$id;
      $result = mysqli_query($sql_sy, $query);
      while($row = mysqli_fetch_assoc($result)){
        $content .= '<div id="blog_head">';
        $content .= '<div id="blog_title">'.$row["title"]."</div>";
        $content .= '<div id="blog_time" class="time" data-time="'.$row["time"].'">'."</div>";
        $content .= '<div id="blog_id">'.$row["id"]."</div>";
        $content .= '<div id="blog_category">'.$row["category_name"]."</div>";
        if(isset($_SESSION["token"]) && $row["private"]){
          $content .= '<div id="blog_private">private</div>';
        }
        elseif(isset($_SESSION["token"])){
          $content .= '<div id="blog_private">public</div>';
        }
        $content .= '</div>';
        if($row["private"]){
          if(isset($_SESSION["token"])){
              $content .= '<div id="blog_content">'.fmt_blog($row["blog"])."</div>";
          }
          else{
            $content .= '<div id="blog_content">私人日記</div>';
          }
        }
        else{
          $content .= '<div id="blog_content">'.fmt_blog($row["blog"])."</div>";
        }
        $content .= '<div id="blog_foot">';
        $content .= '</div>';
        
        $time = $row["time"];
      }
      mysqli_free_result($result);

      $query = "SELECT id FROM blog WHERE `time` < '".$time."' ORDER BY `time` DESC LIMIT 0,1";
      $result = mysqli_query($sql_sy, $query);
      $row = mysqli_fetch_assoc($result);
      $prev_blog = $row["id"];
      mysqli_free_result($result);

      $query = "SELECT id FROM blog WHERE `time` > '".$time."' ORDER BY `time` ASC LIMIT 0,1";
      $result = mysqli_query($sql_sy, $query);
      $row = mysqli_fetch_assoc($result);
      $next_blog = $row["id"];
      mysqli_free_result($result);
    }

  break;
  case "archive":

    if($uri[2] == ""){
      $query = "SELECT * FROM blog_archive ORDER BY id ASC";
      $result = mysqli_query($sql_sy, $query);
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">'."日記封存"."</div>";
      $content .= '</div>';
      $content .= '<div id="blog_content">';

      $content .= '<table cellpadding="0" cellspacing="0" width="100%">';
      $content .= '<tr style="text-decoration:underline"><td>封存</td><td>日記</td><td>字數</td><td>平均</td></tr>';
      $num = 0;
      $total = 0;
      while($row = mysqli_fetch_assoc($result)){
        $query = "SELECT `blog` FROM `blog` WHERE archive=".$row["id"];
        $result2 = mysqli_query($sql_sy, $query);
        $sum = 0;
        $i = 0;
        while($row2 = mysqli_fetch_assoc($result2)){
          $sum += word_count($row2["blog"]);
          $i++;
        }
        mysqli_free_result($result2);
        $content .= "<tr><td><a href=\"/archive/".$row["id"]."/\">".$row["name"]."</a></td><td>".$i."篇</td><td>".$sum."字</td><td>".round($sum/$i)."字</td></tr>";
        $num += $i;
        $total += $sum;
      }
      $content .= "<tr><td><a href=\"/archive/all/\">總數</a></td><td>".$num."篇</td><td>".$total."字</td><td>".round($total/$num)."字</td></tr>";
      $content .= "</table>";
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
      
      mysqli_free_result($result);
    }
    elseif($uri[3] == ""){
      $id = intval($uri[2]);
      $name = "";
      $description = "";
      
      if(intval($uri[2])) $query = "SELECT * FROM `blog_archive` WHERE `id`=".$id;
      elseif($uri[2]='all') $query = "SELECT * FROM `blog_archive`";
      $result = mysqli_query($sql_sy, $query);
      while($row = mysqli_fetch_assoc($result)){
        $name .= $row["name"];
        $description .= $row["description"];
      }
      mysqli_free_result($result);
  
      if(intval($uri[2])) $query = "SELECT * FROM `blog_view` WHERE `archive`=".$id." ORDER BY time ASC";
      elseif($uri[2]='all') $query = "SELECT * FROM `blog_view` ORDER BY time ASC";
      $result = mysqli_query($sql_sy, $query);
      $content .= '<div id="blog_head">';
      if(intval($uri[2])) $content .= '<div id="blog_title">'.$name."</div>";
      elseif($uri[2]='all') $content .= '<div id="blog_title">全部日記</div>';
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      $content .= $description;
      $content .= '<table cellpadding="0" cellspacing="0" width="100%">';
      $content .= '<tr style="text-decoration:underline"><td>日記</td><td>日期</td><td>分類</td><td>字數</td></tr>';
      $sum = 0;
      $i = 0;
      $j = 0;
      while($row = mysqli_fetch_assoc($result)){
        $j = word_count($row["blog"]);
        $sum += $j;
        $i++;
        if($row["private"]) $content .= "<tr style=\"color:#999999\"><td><div class=\"ar_title\"><a href=\"/blog/".$row["id"]."/\">".$row["title"]."</a></div></td><td class=\"shorttime\">".$row["time"]."</td><td>".$row["category_name"]."</td><td>".$j."字</td></tr>";
        else $content .= "<tr><td><div class=\"ar_title\"><a href=\"/blog/".$row["id"]."/\">".$row["title"]."</a></div></td><td class=\"shorttime\">".$row["time"]."</td><td>".$row["category_name"]."</td><td>".$j."字</td></tr>";
      }
      $content .= "</table>";
      $content .= "總共".$i."篇日記，".$sum."字，平均每篇".round($sum/$i)."字";
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';

      mysqli_free_result($result);
    }

  break;
  case "category":

    if($uri[2] == ""){

      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">'."日記分類"."</div>";
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      
      $content .= '<table cellpadding="0" cellspacing="0" width="100%">';
      $content .= '<tr style="text-decoration:underline"><td>封存</td><td>日記</td><td>字數</td><td>平均</td></tr>';

      $query = "SELECT * FROM `blog_category` ORDER BY `id` ASC";
      $result = mysqli_query($sql_sy, $query);
      $num = 0;
      $total = 0;
      while($row = mysqli_fetch_assoc($result)){
        $query = "SELECT `blog` FROM `blog` WHERE `category`=".$row["id"];
        $result2 = mysqli_query($sql_sy, $query);
        $sum = 0;
        $count = 0;
        while($row2 = mysqli_fetch_assoc($result2)){
          $sum += word_count($row2["blog"]);
          $count++;
        }
        mysqli_free_result($result2);
        $content .= "<tr><td><a href=\"/category/".$row["name"]."/\">".$row["name"]."</a></td><td>".$count."篇</td><td>".$sum."字</td><td>".round($sum/$count)."字</td></tr>";
        $num += $count;
        $total += $sum;
      }
      mysqli_free_result($result);
      $content .= "<tr><td>總數</td><td>".$num."篇</td><td>".$total."字</td><td>".round($total/$num)."字</td></tr>";
      $content .= "</table>";
      
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
    }
    elseif($uri[3] == ""){
      $name = htmlentities(urldecode($uri[2]),ENT_QUOTES,"UTF-8");
      $query = "SELECT * FROM `blog_view` WHERE `category_name`='".$name."' ORDER BY `time` ASC;";
      $result = mysqli_query($sql_sy, $query);
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">'.$name."</div>";
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      $content .= '<table cellpadding="0" cellspacing="0" width="100%">';
      $content .= '<tr style="text-decoration:underline"><td>日記</td><td>日期</td><td>字數</td></tr>';
      $sum = 0;
      $i = 0;
      $j = 0;
      while($row = mysqli_fetch_assoc($result)){
        $j = word_count($row["blog"]);
        $sum += $j;
        $i++;
        if($row["private"]) $content .= "<tr style=\"color:#999999\"><td><div class=\"ar_title\"><a href=\"/blog/".$row["id"]."/\">".$row["title"]."</a></div></td><td class=\"shorttime\">".$row["time"]."</td><td>".$j."字</td></tr>";
        else $content .= "<tr><td><div class=\"ar_title\"><a href=\"/blog/".$row["id"]."/\">".$row["title"]."</a></div></td><td class=\"shorttime\">".$row["time"]."</td><td>".$j."字</td></tr>";
      }
      $content .= "</table>";
      $content .= "總共".$i."篇日記，".$sum."字，平均每篇".round($sum/$i)."字";
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
      
      mysqli_free_result($result);
    }

  break;
  case "album":

    if($uri[2] == ""){
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">相簿</div>';
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      
      $dir = "./pic/album/";
      if (is_dir($dir)){
        if ($dh = opendir($dir)){
          
          while (($file = readdir($dh)) !== false){
            if(is_file($dir.$file) && pathinfo($dir.$file, PATHINFO_EXTENSION)=="jpg"){
              $id = pathinfo($dir.$file, PATHINFO_FILENAME);
              $albums[] = $id;
            }
          }
          closedir($dh);
        }
      }
      asort($albums);
      foreach ($albums as $album) {
          $content .= '<div class="thumb"><a href="/album/'.$album.'/"><img src="/pic/album/'.$album.'.jpg" /></a><div>'.$album.'</div></div>';
      }
      
      $content .= '<div style="clear: both"></div>';
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
    }
    elseif($uri[3] == ""){
      $id = intval($uri[2]);
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">'.$id.'年相簿</div>';
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      
      $text = "";
      if(isset($_SESSION["token"])) $query = "SELECT blog FROM blog WHERE archive = $id ORDER BY time ASC";
      else $query = "SELECT blog FROM blog WHERE archive = $id AND private=0 ORDER BY time ASC";
      $result = mysqli_query($sql_sy, $query);
      while ($row = mysqli_fetch_assoc($result)) {
        $text .= $row["blog"];
      }
      mysqli_free_result($result);

      preg_match_all('/\[pic](?P<id>\d+)\[\/pic]/', $text, $matches);
      foreach($matches[1] as &$value) {
        $content .= '<div class="thumb"><a href="/photo/'.$value.'/" target="_blank"><img src="/pic/thumb_small/'.$value.'.jpg" /></a></div>';
      }
      
      if(sizeof($matches[1]) == 0) $content .= '沒有公開的相片';
      /*
      else $content .= sizeof($matches[1]).'張圖';
      */
      
      $content .= '<div style="clear: both"></div>';
      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
    }

  break;
  case "photo":

      $id = intval($uri[2]);
      
      $query = "SELECT blog FROM blog WHERE private=0 AND blog LIKE '%[pic]".$id."[/pic]%' LIMIT 0, 1";
      $result = mysqli_query($sql_sy, $query);
      if (!isset($_SESSION["token"]) && mysqli_num_rows($result) == 0) exit('Private Photo');
      else{
        mysqli_free_result($result);
        
        $query = "SELECT * FROM `photo` WHERE id = $id LIMIT 0, 1";
        $result = mysqli_query($sql_sy, $query);
        $row = mysqli_fetch_assoc($result);
        
        $file_extension = strtolower(pathinfo($row["filename"], PATHINFO_EXTENSION));
        
        switch ($file_extension) {
          case "jpg": $ctype="image/jpeg"; break;
          case "png": $ctype="image/png"; break;
          case "gif": $ctype="image/gif"; break;
          default: $ctype="application/force-download";
        }
        
        $file = './pic/photo/'.$row["id"].'.'.$file_extension;
        if(is_file($file)){        
          header("Content-Type: $ctype");
          header("Content-Disposition: filename=\"".$row["filename"]."\";");
          //header("Content-Length: ".@filesize($file));
          @readfile($file) or die("找不到檔案");
        }
        else{
          header("status: 204");
          header("HTTP/1.0 204 No Response");
        }
      }

  break;
  case "search":

    if($uri[2] == ""){
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">搜尋日記</div>';
      $content .= '</div>';
      $content .= '<div id="blog_content">';
      
      $content .= '<form onsubmit="window.location = \'/search/\' + encodeURIComponent($(\'#search\')[0].value);return false;">';
      $content .= '<input type="text" id="search" autofocus />';
      $content .= '<input type="submit" value="搜尋" />';
      $content .= '</form>';

      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
    }
    elseif($uri[3] == ""){
      $content .= '<div id="blog_head">';
      $content .= '<div id="blog_title">搜尋日記</div>';
      $content .= '</div>';
      $content .= '<div id="blog_content">';

      $content .= '<form onsubmit="window.location = \'/search/\' + encodeURIComponent($(\'#search\')[0].value);return false;">';
      $content .= '<input type="text" id="search" value="'.urldecode($uri[2]).'" />';
      $content .= '<input type="submit" value="搜尋" />';
      $content .= '</form>';
      $k = urldecode($uri[2]);
      $search_str = $k;
      $k = str_replace('\'', '\\\'', $k);
      $k = str_replace('%', '\%', $k);
      $k = str_replace('_', '\_', $k);

      if(isset($_SESSION["token"])){
        $query = "SELECT * FROM blog WHERE blog LIKE '%$k%' ORDER BY time DESC";
      }
      $result = mysqli_query($sql_sy, $query);
      
      if (mysqli_num_rows($result) == 0) $content .= "找不到與「".$k."」相關的資料\n";
      else {
        $res = "";
        while($row = mysqli_fetch_assoc($result)){
          $res .= '<div style="height:125px">'."\n";
          $res .= '<a href="/blog/'.$row["id"].'/" style="font-size:16px;">'.$row["title"]."</a><br />\n";
          $res .= '<font style="color:#093;font-size:12px;">blog.soruly.com/blog/'.$row["id"]."/</font>";
          $res .= ' - <font style="color:#666;font-size:12px;" class="time" data-time="'.$row["time"].'">'."</font><br />\n";
          $res .= '<font style="font-size:12px;">...'.match($row["blog"], $search_str)."...</font><br><br>"."\n";
          $res .=  '</div>'."\n";
        }
        $content .= "總共".mysqli_num_rows($result)."項搜尋結果<br /><br />";
        $content .= $res;
      }
      mysqli_free_result($result);

      $content .= '</div>';
      $content .= '<div id="blog_foot">';
      $content .= '</div>';
    }
    
  break;
  case "about":

    $content .= '<div id="blog_head">';
    $content .= '<div id="blog_title">關於SolarDay</div>';
    $content .= '</div>';
    $content .= '<div id="blog_content">';
      
    $query = "SELECT `value` FROM `settings` WHERE `name` = 'about'";
    $result = mysqli_query($sql_sy, $query);
    $row = mysqli_fetch_assoc($result);
    $content .= $row["value"];
    $content .= '</div>';
    $content .= '<div id="blog_foot">';
    $content .= '</div>';

  break;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>SolarDay</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="icon" type="image/png" href="/favicon.png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="//<?=$servername?>/" />
<link href="/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/turbolinks.min.js"></script>
<script type="text/javascript" src="/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/sha1.min.js"></script>
<style type="text/css">
@media screen and (max-width: 820px){
    #container{
        margin-right:auto;
    }
}
@media screen and (max-width: 770px){
    html,body{
        overflow-y:overlay;
        width:100%;
    }
    #container,#navigationbar_top, #navigationbar_bottom{
        margin:0;
        width:100%;
    }
    #banner,#banner2{
        display:none;
    }
    #blog_content table tr td:nth-child(3), #blog_content table tr td:nth-child(4){
        display:none;
    }
}
@media screen and (max-width: 530px){
    #blog_head, #blog_content, #blog_foot{
        background-position:-80px 0;
    }
    #blog_title{
        font-size:22px;
        margin-top:130px;
        height:38px;
    }
    #blog_title,#blog_time{
        margin-left:20px;
    }
    #blog_category{
        display:none;
    }
    #blog_content{
        padding-left:20px;
    }
    #blog_content table tr td{
        vertical-align:initial;
    }
    #blog_content table tr td:nth-child(2), #blog_content table tr td:nth-child(3), #blog_content table tr td:nth-child(4){
        display:none;
    }
}
</style>
</head>

<body>
<div id="banner2"></div>
<div id="banner"></div>
<div id="container">
<div id="navigationbar_top">
<?php if(isset($prev_blog)) echo '<a href="/blog/'.$prev_blog.'/">&lt;&lt;</a> - '; elseif(isset($next_blog)) echo '&lt;&lt; - '; ?>
<a href="/">Blog</a> - 
<a href="/archive/">Archive</a> - 
<a href="/category/">Category</a> - 
<a href="/album/">Album</a> - 
<a href="/about/">About</a> - 
<a href="/search/">Search</a>
<?php if(isset($next_blog)) echo ' - <a href="/blog/'.$next_blog.'/">&gt;&gt;</a>'; elseif(isset($prev_blog)) echo ' - &gt;&gt;'; ?>
</div>

<div id="content">
<?php echo $content; ?>
</div>
<div id="navigationbar_bottom">
<?php if(isset($prev_blog)) echo '<a href="/blog/'.$prev_blog.'/">&lt;&lt;</a> - '; elseif(isset($next_blog)) echo '&lt;&lt; - '; ?>
<a href="/">Blog</a> - 
<a href="/archive/">Archive</a> - 
<a href="/category/">Category</a> - 
<a href="/album/">Album</a> - 
<a href="/about/">About</a> - 
<a href="/search/">Search</a>
<?php if(isset($next_blog)) echo ' - <a href="/blog/'.$next_blog.'/">&gt;&gt;</a>'; elseif(isset($prev_blog)) echo ' - &gt;&gt;'; ?>
</div>
<br>
</div>
<div id="blind"></div>

<div id="login">
<form>
Password: <input id="pwd" type="password" />
<input type="submit" value="Login" />
</form>
</div>

<div id="emoticon">
<?php
$dir = "./image/icon/";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
      $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      if($ext == "gif") echo '<div class="icon" style="background-image:url('.$dir.$file.');"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onClick="addemoticon(\''.$dir.$file.'\')" alt /></div>'."\n";
        }
        closedir($dh);
    }
}

mysqli_close($sql_sy);
?>
</div>
<div id="messagebox"></div>
<script type="text/javascript" src="/common.js"></script>
<?php
  if(isset($_SESSION["token"])){
?>
<script type="text/javascript" src="/jquery.html5uploader.js"></script>
<script type="text/javascript" src="/index.js"></script>
<?php
}
?>
</body>
</html>

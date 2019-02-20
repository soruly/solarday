<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
require_once("./config.inc.php");
require_once("./common.func.php");

$sql_sy = mysqli_connect($hostname_sy, $username_sy, base64_decode($password_sy), $database_sy);
if (mysqli_connect_errno($sql_sy)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else{
	mysqli_query($sql_sy, "SET NAMES 'utf8'");
}

if(isset($_GET["login"])){
	$query = "SELECT `value` FROM `settings` WHERE `name`='password'";
	$result = mysqli_query($sql_sy, $query);
	$row = mysqli_fetch_assoc($result);
	$password = $row["value"];
	mysqli_free_result($result);
	
	if($_POST["password"] == $password){
		setcookie("password", $password, time()+$cookie_lifespan);
	}
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

if(isset($_GET["logout"])){
		setcookie("password", "", time()-3600);
		session_unset();
}


if(isset($_SESSION["token"])){
	if(isset($_GET["add_blog"])){
		$week = array("日","一","二","三","四","五","六");
		$t = time() + (-1)*60*60*$_GET["timezone"];
		$time = strftime("%Y-%m-%d %H:%M:%S",time());
		$archive = strftime("%Y",$t);
		$title = strftime("%Y",$t)."年".strftime("%m",$t)."月".strftime("%d",$t)."日 (星期".$week[strftime("%w",$t)].")";
		$query = "INSERT INTO `blog`.`blog` (`id`, `archive`, `category`, `private`, `time`, `title` ,`blog`) VALUES (NULL, '$archive', '1', '0', '$time', '$title', NULL);";
		$result = mysqli_query($sql_sy, $query);

		$query = "SELECT `id` FROM `blog_archive` WHERE `id`=".$archive;
		$result = mysqli_query($sql_sy, $query);
		$exist = mysqli_num_rows($result);
		if(!$exist){
			$name = $archive.'年';
			$query = "INSERT INTO `blog`.`blog_archive` (`id`, `name`, `description`) VALUES ('$archive', '$name', '');";
			$result = mysqli_query($sql_sy, $query);
		}

		$query = "SELECT `id` FROM `blog` ORDER BY id DESC LIMIT 0,1";
		$result = mysqli_query($sql_sy, $query);
		$row = mysqli_fetch_assoc($result);
		echo $row["id"];
		mysqli_free_result($result);
	}
	elseif(isset($_GET["delete_blog"])){
		$id = intval($_GET["delete_blog"]);
		$query = "DELETE FROM `blog` WHERE `id`=$id";
		$result = mysqli_query($sql_sy, $query);
		
		$id = intval($_GET["delete_blog"]);
		$query = "ALTER TABLE `blog` AUTO_INCREMENT = 1;";
		$result = mysqli_query($sql_sy, $query);

		$query = "SELECT `id` FROM `blog` ORDER BY id DESC LIMIT 0,1";
		$result = mysqli_query($sql_sy, $query);
		$row = mysqli_fetch_assoc($result);
		echo $row["id"];
		mysqli_free_result($result);
	}
	elseif(isset($_GET["edit_blog"])){
		$id = intval($_POST["id"]);
		$category = intval($_POST["category"]);
		$private = intval($_POST["private"]);
		$time = $_POST["time"];
		$title = addslashes($_POST["title"]);
		$blog = preg_replace_callback('/\[music](.*)\[\/music]/', 'refmt_music', $_POST["blog"]);
		$blog = addslashes($blog);
		$query = "UPDATE blog SET category=$category, private=$private, time='$time',title='$title',blog='$blog' WHERE id=$id";
		$result = mysqli_query($sql_sy, $query);
	}
	elseif(isset($_GET["private_blog"])){
		$id = intval($_GET["private_blog"]);
		$query = "UPDATE blog SET private=1 WHERE id=$id";
		$result = mysqli_query($sql_sy, $query);
	}
	elseif(isset($_GET["public_blog"])){
		$id = intval($_GET["public_blog"]);
		$query = "UPDATE blog SET private=0 WHERE id=$id";
		$result = mysqli_query($sql_sy, $query);
	}
	elseif(isset($_GET["upload"])){
		if($_FILES){
			if ($_FILES["file"]["error"] > 0){
			}
			else{
				$filename = $_FILES["file"]["name"];
				$file_name = addslashes($filename);
				$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
				
				if($ext == "jpg" || $ext == "png" || $ext == "gif"){
					$dir = "./pic/photo/";
					if(!is_dir($dir)) mkdir($dir);
					$dir = "./pic/thumb_big/";
					if(!is_dir($dir)) mkdir($dir);
					$dir = "./pic/thumb_small/";
					if(!is_dir($dir)) mkdir($dir);
					
					$query = "INSERT INTO `blog`.`photo` (`id`,`filename`) VALUES (NULL, '$file_name');";
					$result = mysqli_query($sql_sy, $query);
					
					$query = "SELECT `id` FROM `photo` ORDER BY id DESC LIMIT 0,1";
					$result = mysqli_query($sql_sy, $query);
					$row = mysqli_fetch_assoc($result);
					$photo = "./pic/photo/".$row["id"].".".$ext;
					copy($_FILES["file"]["tmp_name"], $photo);
					
					imageresize($photo,"./pic/thumb_big/".$row["id"].".jpg",640,480);
					imageresize($photo,"./pic/thumb_small/".$row["id"].".jpg",160,120);

					echo $row["id"];

					mysqli_free_result($result);
				}
				elseif($ext == "mp3"){
					$dir = "./music/";
					if(!is_dir($dir)) mkdir($dir);

					$music = "./music/".$filename;
					//$music=iconv("UTF-8","big5",$music);
					copy($_FILES["file"]["tmp_name"], $music);

					echo $filename;
				}
			}
		}
	}
}
else{
//	echo 'please login';
}

?>

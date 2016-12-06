<?php
header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
echo "<rss version=\"2.0\">\n";
echo "<channel>\n";
echo "	<title>SolarDay</title>\n";
echo "	<description>soruly's weblog</description>\n";
echo "	<link>http://blog.soruly.com</link>\n";
echo "	<image>\n";
echo "		<url>http://blog.soruly.com/image/64.jpg</url>\n";
echo "		<title>SolarDay</title>\n";
echo "		<link>http://blog.soruly.com</link>\n";
echo "	</image>\n";

require_once("./config.inc.php");
require_once("./common.func.php");

$sql_sy = mysqli_connect($hostname_sy, $username_sy, base64_decode($password_sy), $database_sy);
if (mysqli_connect_errno($sql_sy)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else{
	mysqli_query($sql_sy, "SET NAMES 'utf8'");
}

$query = "SELECT * FROM `blog_view` WHERE private=0 ORDER BY time DESC LIMIT 0,20";
$result = mysqli_query($sql_sy, $query);
while($row = mysqli_fetch_assoc($result)){
	echo '	<item>'."\n";
	echo '		<guid>http://blog.soruly.com/blog/'.$row["id"].'/</guid>'."\n";
	echo '		<title><![CDATA['.$row["title"].']]></title>'."\n";
	echo '		<link>http://blog.soruly.com/blog/'.$row["id"].'/</link>'."\n";
	echo '		<pubdate>'.fmt_rss_time($row["time"]).'</pubdate>'."\n";
	echo '		<description><![CDATA['.fmt_blog($row["blog"]).']]></description>'."\n";
	echo '	</item>'."\n";
}
mysqli_free_result($result);

echo "</channel>\n";
echo "</rss>\n";
?>

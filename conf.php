<?php 
define("TG_TOKEN", "token");
define("TG_ADMIN_ID", num);
define("TG_CHAT_ID", -num);
define("_IMG", "img_path");

/*==================================================================*/

$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
$timeNow = $now->format('Y-m-d H:i:s');

/*==================================================================*/
/* WebHook already was set sucessfuly! */
/*
$getQuery = array("url" => "");
$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/setWebhook?".http_build_query($getQuery));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$resultQuery = curl_exec($ch);
curl_close($ch);

echo $resultQuery;
*/
/*==================================================================*/

$dbCon = mysqli_connect('', '', '', '');

if (!$dbCon) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "DB connected successfully!";

?>
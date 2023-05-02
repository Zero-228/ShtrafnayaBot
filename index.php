<?php 

ini_set('error_reporting', E_ALL);
ini_set('display_erroros', 1);
ini_set('display_startup_erroros', 1);

define("TG_TOKEN", "5710504168:AAEc_3ZVtVCYo7L7Bc1wOmuD56rSQA7LEwI");
define("TG_USER_ID", 329402353);
define("TG_CHAT_ID", -728267410);

/*---------------------------------*/

$getQuery = array(
     "url" => "https://zerobot.site//index.php",
);
$ch = curl_init("https://api.telegram.org/bot". $token ."/setWebhook?" . http_build_query($getQuery));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$resultQuery = curl_exec($ch);
curl_close($ch);

echo $resultQuery;

/*---------------------------------*/

/*
$text_message = "Тестовое сообщение 11";
$text_message = urlencode($text_message);
//var_dump($text_message);

$urlQuery = "https://api.telegram.org/bot".TG_TOKEN."/sendMessage?chat_id=".TG_USER_ID."&text=".$text_message;

//var_dump($urlQuery);
$result = file_get_contents($urlQuery);
*/

/*-----------------------------------*/

/*
$textMessage = "Reply ";

$getQuery = array(
	'chat_id' => TG_USER_ID, 
	'text' => $textMessage,
	'parse_mode' => "html",
	'reply_to_message_id' => 7,
); 

$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/sendMessage?".http_build_query($getQuery));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$resultQuery = curl_exec($ch);
curl_close($ch);

//$jsonData = json_decode($resultQuery, true); //параиметр true превращает строку (объект) в массив

//var_dump($jsonData["result"]["chat"]["username"]);
//var_dump($jsonData);

/*-----------------------------------*/



/*=========================================================================*/
/*
$data = file_get_contents('php://input');
$data = json_decode($data, true);

if ($data['message']['chat']['id'] != TG_USER_ID || empty($data['message']['chat']['id'])) {
	exit();
}

if (!empty($data['message']['text'])) {
	$text = $data['message']['text'];

	if (mb_stripos($text, 'привет')) {
		send('sendMessage',
			array(
				'chat_id' => $data['message']['chat']['id'],
				'text' => 'Вечерочечекс'
			)
		);
		exit();
	}
}
*/
/*=========================================================================*/

/*
$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/getUpdates");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$resultQuery = curl_exec($ch);
curl_close($ch);

$numRows = mysql_num_rows($resultQuery);

var_dump($numRows);

/*=========================================================================*/



$data = file_get_contents('php://input');
$data = json_decode($data, true);

var_dump($data);

if ($data['message']['chat']['id'] != TG_USER_ID || empty($data['message']['chat']['id'])) {
	exit();
}

function send($method, $response){
	$ch = curl_init('https://api.telegram.org/bot'.TG_TOKEN.'/'.$method);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);
	curl_close($ch);

	return $res;
}

if (!empty($data['message']['text'])) {
	$text = $data['message']['text'];

	if (mb_stripos($text, 'привет')) {
		send('sendMessage',
			array(
				'chat_id' => $data['message']['chat']['id'],
				'text' => 'Вечерочечекс'
			)
		);
		exit();
	}
}

?>
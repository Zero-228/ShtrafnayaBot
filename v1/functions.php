<?php 

function checkUser($id){
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	$checkUser = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = $id");
	$rows = mysqli_num_rows($checkUser);
	if ($rows == 1) {
		return $checkUser;
	}
	elseif($rows == 0){
		return false;
	}
	else return "Very strange shit, call admin!";
}

function send($method, $response){


	$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/".$method."?".http_build_query($response));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);
	curl_close($ch);

	/*$ch = curl_init('https://api.telegram.org/bot'.TG_TOKEN.'/'.$method);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);
	curl_close($ch);*/

	return $res;
}

function sendMessage($text, $id){

	$msg = array(
		'chat_id' => $id,
		'text' => $text,
	);

	$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/sendMessage?" . http_build_query($msg));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$resultQuery = curl_exec($ch);
	curl_close($ch);
	//return $result;
}

function writeLogFile($string, $clear = false){
	$logFileName = __DIR__."/temp/message.txt";
	$now = date("d-m-Y H:i:s");
	if($clear == false){
		file_put_contents($logFileName, $now." ".print_r($string, true)."\r\n", FILE_APPEND);
	}
	else{
		file_put_contents($logFileName, " ");
		file_put_contents($logFileName, $now." ".print_r($string, true)."\r\n", FILE_APPEND);
	}
}

function debug($string, $clear = false){
	$logFileName = __DIR__."/temp/debug.txt";
	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$now = $now->format('Y-m-d H:i:s');
	if($clear == false){
		file_put_contents($logFileName, $now." ".print_r($string, true)."\r\n", FILE_APPEND);
	}
	else{
		file_put_contents($logFileName, " ");
		file_put_contents($logFileName, $now." ".print_r($string, true)."\r\n", FILE_APPEND);
	}
}

function menuButtons($user_id, $text = ''){
	$getQuery = array(
	    "chat_id" 	=> $user_id,
	    'text' => $text,
	    "parse_mode" => "html",
	    'reply_markup' => json_encode(array(
		    'keyboard' => array(
		        array(
				    array(
					'text' => '| Пинг 📣 |',
				    ),
				    array(
					'text' => '| Статус 📜 |',
				    ),
		    	),
		        array(
				    array(
					'text' => '| Выписать штрафную 🚀 |',
				    ),
		    	),
		        array(
				    array(
					'text' => '| Подтвердить ✅ |',
				    ),
				    array(
					'text' => '| Улучшить бота 🔧 |',
				    ),
		    	)),
		    'one_time_keyboard' => TRUE,
		    'resize_keyboard' => TRUE,
		)),
		);
	$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/sendMessage?" . http_build_query($getQuery));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$resultQuery = curl_exec($ch);
	curl_close($ch);
}

function inlineButtons($user_id, array $buttons, $text = ''){
	$getQuery = array(
	    "chat_id" 	=> $user_id,
	    'text' => $text,
	    "parse_mode" => "html",
	    'reply_markup' => json_encode(array(
		    'inline_keyboard' => array_chunk($buttons, 2),
		    'one_time_keyboard' => TRUE,
		    'resize_keyboard' => TRUE,
		)),
		);
	$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/sendMessage?" . http_build_query($getQuery));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$resultQuery = curl_exec($ch);
	curl_close($ch);
}

function shtrafnaya($idTo, $username, $user_id, $message_id, $reason = ""){
	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$timeNow = $now->format('Y-m-d H:i:s');
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	if ($reason == "") {
		$update = mysqli_query($dbCon, "INSERT INTO shtrafniye VALUES ('', '$idTo', '$user_id', '', '$message_id', '$timeNow')");
	}
	else {
		$update = mysqli_query($dbCon, "INSERT INTO shtrafniye VALUES ('', '$idTo', '$user_id', '$reason', '$message_id', '$timeNow')");
	}
	$userInfo = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$idTo'");
	$userInfo = $userInfo->fetch_array();
	if (is_array($userInfo)) {
		(int)$shtraf = $userInfo['shtrafniye'];
	}
	$shtraf++;
	$update = mysqli_query($dbCon, "UPDATE users SET shtrafniye = '$shtraf' WHERE user_id = '$idTo'");
	if ($reason = "") {	
		sendMessage('❗ '.$username.' выписал тебе штрафную', $idTo);
	}
	else{
		sendMessage('❗ '.$username.' выписал тебе штрафную'.$reason, $idTo);
	}
}

function approveShtraf($user_id, $user_to_id, $photo = ''){
	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$timeNow = $now->format('Y-m-d H:i:s');
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');

	$userInfo = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_id'");
	$userInfo = $userInfo->fetch_array();
	if (is_array($userInfo)) {
		$username = $userInfo['username'];
		$myShtrafniye = $userInfo['shtrafniye'];
	}

	if ($user_id != $user_to_id) {
		
		if ($photo == '') {
			$update = mysqli_query($dbCon, "INSERT INTO approves VALUES ('', '$user_to_id', '$user_id', '', '$timeNow')");
		}
		else {
			$update = mysqli_query($dbCon, "INSERT INTO approves VALUES ('', '$user_to_id', '$user_id', '$photo', '$timeNow')");
		}

		$userToInfo = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_to_id'");
		$userToInfo = $userToInfo->fetch_array();
		if (is_array($userToInfo)) {(int)$shtraf = $userToInfo['shtrafniye'];}
		$shtraf--;

		if ($shtraf>0) {
			$updateUser = mysqli_query($dbCon, "UPDATE users SET shtrafniye = '$shtraf' WHERE user_id = '$user_to_id'");
			sendMessage("✅ Штрафная подтверждена", $user_id);
			sendMessage("✅ ".$username." подтвердил тебе штрафную", $user_to_id);
		}
		else {
			sendMessage("❌ У пользователя нет штрафных которые можно подтвердить", $user_id);
		}	

	}
	else{
		$myShtrafniye--;
		if ($myShtrafniye>0) {
			$updateUser = mysqli_query($dbCon, "UPDATE users SET shtrafniye = '$myShtrafniye' WHERE user_id = '$user_id'");
			$update = mysqli_query($dbCon, "INSERT INTO approves VALUES ('', '$user_to_id', '$user_id', '$photo', '$timeNow')");
			sendMessage("✅ Штрафная подтверждена", $user_id);
			sendMessage("✅ ".$username." подтвердил себе штрафную", TG_ADMIN_ID);
		}
		else {
			sendMessage("❌ У тебя нет штрафных", $user_id);
		}
	}
}

function showStatus($user_id, $username, $lastMsgTime, $admin = ''){
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	$dbUserInfo = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_id'");
	$dbUserInfo = $dbUserInfo->fetch_array();
	//$replys = mysqli_query($dbCon, "SELECT * FROM messages WHERE reply_to_id = '$user_id' AND text != '| Пинг'");
	$words = mysqli_query($dbCon, "SELECT * FROM tiShoWords WHERE user_id = '$user_id'");
	$name = '@'.$username;
	$text = "To ".$username." ID: ".$user_id;
	$allPing = mysqli_query($dbCon, "SELECT * FROM messages WHERE type = 'ping' AND text = '$text'");
	$allPingNumRow = mysqli_num_rows($allPing);
	//$replyNumRows = mysqli_num_rows($replys);
	$numWords = mysqli_num_rows($words);


	$status = "=======================\n             ".$dbUserInfo['username']."\n======================="."\n\n🍀Штрафных: ".$dbUserInfo['shtrafniye']."\n\n🤖Сообщ. боту: ".$dbUserInfo['msgToBot']./*"\n\n💬Сообщ. в чат: ".$dbUserInfo['msgToChat'].*/"\n\n📣Тебя пинганули: ".$allPingNumRow." раз"./*"\n\n📬Тебе ответили: ".$replyNumRows." раз".*/"\n\n📄Новых слов в\n       ты шо словарь   : ".$numWords."\n\n📥Последнее смс:\n   ".$lastMsgTime."\n======================="/*."\n".mb_stripos($text, 'ат')*/;
	
	if ($user_id == TG_ADMIN_ID) {
		$sugg = mysqli_query($dbCon, "SELECT * FROM suggestions WHERE hidden = '0'");
		$numSugg = mysqli_num_rows($sugg);
		$status = $status." \n💡 Предложений по \n       улучшению бота : ".$numSugg."\n=======================";
	}
	if ($admin != '') {
		return sendMessage($status, $admin);
	}
	else {
		return sendMessage($status, $user_id);
	}
}

function pingUser($user_to_id, $user_to, $user_from, $username, $message_id){
	
	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$timeNow = $now->format('Y-m-d H:i:s');
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	$text = "To ".$user_to." ID: ".$user_to_id;
	$message = mysqli_query($dbCon, "INSERT INTO messages VALUES ('$message_id', 'ping', '$text', '$user_from', NULL, NULL, NULL, '$timeNow')");

	$msgTxt = "@".$user_to.", ты куда пропал?\n\n@".$username." хочет тебя и твоего внимания 🌚";

	sendMessage("Сшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсшсш", $user_to_id);
	sendMessage($msgTxt, $user_to_id);
}

function tiSho($user_id, $word){
	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$timeNow = $now->format('Y-m-d H:i:s');
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');

	$totalWords = mysqli_query($dbCon, "SELECT * FROM tiShoWords");
	$checkWord = mysqli_query($dbCon, "SELECT * FROM tiShoWords WHERE word = '$word'");
	$numWords = mysqli_num_rows($checkWord);
	$totNumWords = mysqli_num_rows($totalWords);
	$isset = false;

	if ($numWords > 0) {
		$isset = true;
	}

	if ($isset == false) {
		$updateWords = mysqli_query($dbCon, "INSERT INTO tiShoWords VALUES ('', '$word', '$user_id', '', '$timeNow', '0')");
	}

	$rWord = $word; $numUsed = 0;
	$num = rand(1, $totNumWords);
	$allWords = mysqli_query($dbCon, "SELECT * FROM tiShoWords WHERE id ='$num'");
	$allWords = $allWords->fetch_array();
	if (is_array($allWords)) {
		$rWord = $allWords['word'];
		$numUsed = $allWords['used'];
	}

	$numUsed++;
	$updateWord = mysqli_query($dbCon, "UPDATE tiShoWords SET used = '$numUsed' WHERE id = '$num'");

	$responce = "Ты шо ".$rWord."?";

	sendMessage($responce, $user_id);
}

function lastMsg($user_id){
	$res = "";

	$now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	$timeNow = $now->format('Y-m-d H:i:s');
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	$lastMsg = mysqli_query($dbCon, "SELECT lastMsg FROM users WHERE user_id ='$user_id'");
	$lastMsg = $lastMsg->fetch_array();
	$lastMsg = $lastMsg[0];

    return (string)$lastMsg;
}

function prevMsg($message_id){
	$dbCon = mysqli_connect('198.57.247.160', 'zerobots_shtraf', 'Nikita13#', 'zerobots_shtrafnayaBot');
	$new_message_id = $message_id-2;
	$dbMsgInfo = mysqli_query($dbCon, "SELECT * FROM messages WHERE message_id = '$new_message_id'");
	$dbMsgInfo = $dbMsgInfo->fetch_array();
	if (is_array($dbMsgInfo)) {
		return $dbMsgInfo['text'];
	}
	else {
		return $dbMsgInfo;
	}
}

function delMsg($user_id, $message_id){
	$info = array(
		'chat_id' => $user_id,
		'message_id' => $message_id, 
	);
	$ch = curl_init("https://api.telegram.org/bot".TG_TOKEN."/deleteMessage?".http_build_query($info));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);
	curl_close($ch);

	return $res;
}

?>
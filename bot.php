<?php 
header('Content-type: text/plain');

ini_set('error_reporting', E_ALL);
ini_set('display_erroros', 1);
ini_set('display_startup_erroros', 1);


require 'conf.php';
require 'functions.php';

/*========================Перехват данных==========================*/

$data = file_get_contents('php://input');
$data = json_decode($data, true);

writeLogFile($data, true);
//echo file_get_contents(__DIR__."/message.txt");
//echo $data;

$isReply = false;
$reply_to; $reply_to_id; $reply_message_id; 

$whisper = file_get_contents("/home4/zerobots/public_html/temp/whisper.txt", true);

if(!empty($data['message']['text'])){
	$text = $data['message']['text']; 
	$message_id = $data['message']['message_id'];
	$username = $data['message']['from']['username'];
	$type = $data['message']['chat']['type'];
	$user_id = $data['message']['from']['id'];
	if($type == 'group'){
		$chat_id = $data['message']['chat']['id'];
		$chat_title = $data['message']['chat']['title'];
	}
	if(!empty($data['message']['reply_to_message'])){
		$isReply = true;
		$reply_to = $data['message']['reply_to_message']['from']['username'];
		$reply_to_id = $data['message']['reply_to_message']['from']['id'];
		$reply_message_id = $data['message']['reply_to_message']['message_id'];
	}
	if (!empty($data['message']['entities'])) {
		$type = $data['message']['entities']['0']['type'];
	}
	

/*===================Логирование сообщения в БД=====================*/	

	if ($isReply) {
		$message = mysqli_query($dbCon, "INSERT INTO messages VALUES ('$message_id', '$type', '$text', '$username', '$reply_to', '$reply_to_id', '$reply_message_id', '$timeNow')");		
	}
	else{
		$message = mysqli_query($dbCon, "INSERT INTO messages VALUES ('$message_id', '$type', '$text', '$username', NULL, NULL, NULL, '$timeNow')");
	}
	if (checkUser($user_id)!=false) {
		$updateUser = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_id'");
		$updateUser = $updateUser->fetch_array();
		$lastMsgTime = $updateUser['lastMsg'];
		$msgToBot = $updateUser['msgToBot'];
		$msgToBot++;
		$updateDB = mysqli_query($dbCon, "UPDATE users SET msgToBot = '$msgToBot', lastMsg = '$timeNow' WHERE user_id = '$user_id'");
	}

/*========================Обработка запроса=========================*/


	if ($type == 'private' || $type == 'bot_command' || $type = 'mention') {
		if (checkUser($user_id)==false) {
			if ($text == "/start") {
				$insertNewUser = mysqli_query($dbCon, "INSERT INTO users VALUES ('$user_id', '$username', 'user', 0, 1, 0, '$timeNow', 0)");

				if ($insertNewUser == false) {
					sendMessage('Error', $user_id);
				}
				else{
					menuButtons($user_id,"Поздравляю!\nРегистрация успешна!");
				}
			}
			else {
				sendMessage("Давай по людски\nнапиши /start", $user_id);
			}
		}
		else{
			$dbUserInfo = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_id'");
			$dbUserInfo = $dbUserInfo->fetch_array();
			menuButtons($user_id);
			$prevMsg = prevMsg($message_id);

			if ($type == 'bot_command') {
				switch ($text) {
					case '/start':
						delMsg($user_id, $message_id);
						sendMessage("Вечерочечекс", $user_id);
						break;
					case '/help':
						delMsg($user_id, $message_id);
						sendMessage("https://telegra.ph/Spravka-po-botu-01-15", $user_id);
						break;
					case '/shtrafnaya':
						delMsg($user_id, $message_id);
						$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");

						foreach ($users as $user) {
							$callback = " shtraf ".$user['user_id'];
							$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
						}
						$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];
						$buttons = array_merge($buttons, $cancelButton);

						inlineButtons($user_id, $buttons, "Выбери пользователя которому хочешь выписать штрафную :\n");
						exit();
					case '/approve':
						delMsg($user_id, $message_id);
						$users = mysqli_query($dbCon, "SELECT * FROM users");

						foreach ($users as $user) {
							$callback = " approve ".$user['user_id'];
							$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
						}
						$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];
						$buttons = array_merge($buttons, $cancelButton);

						inlineButtons($user_id, $buttons, "Выбери пользователя которому хочешь подтвердить штрафную :\n");
						break;
					default:
						sendMessage("Неизвестная команда", $user_id);
						break;
				}
				exit();
			}
			/*if ($prevMsg == "/shtrafnaya") {

				$un = mb_strcut($text, 1);
				$unId = mysqli_query($dbCon, "SELECT user_id FROM users WHERE username = '$un'");
				$unIdNumRow = mysqli_num_rows($unId);
				delMsg($user_id, $message_id);
				if ($unIdNumRow == 1) {
					$unId = $unId->fetch_array();
					$unId = $unId[0];
					shtrafnaya($unId, $username, $user_id, $message_id);
					sendMessage("Ты выписал штрафную ".$un, $user_id);
				}
				else {
					sendMessage("Данный пользователь не зарегистрирован, пригласи его в бот и попробуй снова)",$user_id);
				}
			}*/
			if ($text == "update") {
				delMsg($user_id, $message_id);
				menuButtons($user_id, "updated");
				exit();
			}
			if ($text == "| Статус 📜 |") {
				delMsg($user_id, $message_id);
				showStatus($user_id, $username, $lastMsgTime);
				exit();
			}
			if ($text == "| Выписать штрафную 🚀 |") {
				$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");

				foreach ($users as $user) {
					$callback = " shtraf ".$user['user_id'];
					$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
				}
				$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];
				$buttons = array_merge($buttons, $cancelButton);

				inlineButtons($user_id, $buttons, "Выбери пользователя которому хочешь выписать штрафную :\n");
				exit();
			}
			if ($text == "| Подтвердить ✅ |") {
				$users = mysqli_query($dbCon, "SELECT * FROM users");

				foreach ($users as $user) {
					$callback = " approve ".$user['user_id'];
					$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
				}
				$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];
				$buttons = array_merge($buttons, $cancelButton);

				inlineButtons($user_id, $buttons, "Выбери пользователя которому хочешь подтвердить штрафную :\n");
				exit();
			}
			if ($text == "| Пинг 📣 |") {
				$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");

				foreach ($users as $user) {
					$callback = " ping ".$user['user_id'];
					$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
				}
				$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];
				$buttons = array_merge($buttons, $cancelButton);
				inlineButtons($user_id, $buttons, "Выбери пользователя которого хочешь пингануть :\n");
				exit();
			}
			if ($text == "| Улучшить бота 🔧 |") {

				$cancelButton[] = ['text' => "❌ Отмена ❌", 'callback_data' => " cancel "];

				inlineButtons($user_id, $cancelButton, "Напиши своё предложение или пожелание : ");
				exit();
			}
			if ($prevMsg == "| Улучшить бота ") {
				if ($text != "Отмена" || $text != "отмена") {
					$insert = mysqli_query($dbCon, "INSERT INTO suggestions VALUES ('', '$text', '$username', '','$timeNow')");
					delMsg($user_id, $message_id-2);
					delMsg($user_id, $message_id-1);
					delMsg($user_id, $message_id);
					sendMessage("✅ Предложение успешно внесено, команда благодарит вас за улучшение бота)", $user_id);
					$currentNum = mysqli_query($dbCon, "SELECT * FROM suggestions WHERE hidden = '0'");
					$numRow = mysqli_num_rows($currentNum);
					sendMessage("✅ Поступило 1 новое предложение\nВсего предложений : ".$numRow, TG_ADMIN_ID);
				}
				exit();
			}
			if (mb_stripos($text, 'fcsakpjhjgb[aergvd')!==false) {
				sendMessage("Так а ты там так шо?", $user_id);
				exit();
			}
			if (mb_stripos($text, 'test')!==false) {
				sendMessage("Введи ник польгователя которому хочешь отправить запрос дружбы в формате '@username'", $user_id);
				exit();
			}
			if ($prevMsg = "test") {

				$checkUser = mb_strcut($text, 1);
				$checkUserId = mysqli_query($dbCon, "SELECT user_id FROM users WHERE username = '$checkUser'");
				$checkUserIdNumRow = mysqli_num_rows($checkUserId);

				delMsg($user_id, $message_id-2);
				delMsg($user_id, $message_id-1);
				delMsg($user_id, $message_id);

				if ($checkUserIdNumRow == 1) {
					$unId = $checkUserId->fetch_array();
					$unId = $unId[0];

					if ($unId != $user_id) {
						$checkIfFriend = mysqli_query($dbCon, "SELECT * FROM friends WHERE user_from = '$user_id' AND user_to = '$unId'");
						$friendNumRow = mysqli_num_rows($checkIfFriend);
						if ($friendNumRow == 1) {
							$checkIfFriend = $checkIfFriend->fetch_array();
							$friendshipStatus = $checkIfFriend['status'];
							switch ($friendshipStatus) {
								case 'no':
									sendMessage("🔴 Вы уже не друзья. Пока это никак не исправить", $user_id);
									delMsg($user_id, $message_id-1);
									delMsg($user_id, $message_id);
									break;
								case 'req':
									sendMessage("🟡 Заявка в друзья уже отправлена", $user_id);
									delMsg($user_id, $message_id-1);
									delMsg($user_id, $message_id);
									break;
								case 'yes':
									delMsg($user_id, $message_id-1);
									delMsg($user_id, $message_id);
									sendMessage("🟢 Вы уже друзья", $user_id);
									break;
								default:
									delMsg($user_id, $message_id-1);
									delMsg($user_id, $message_id);
									sendMessage("❌ ERROR ❌", $user_id);
									break;
							}
						}
						elseif ($friendNumRow == 0) {
							$friendReqText = " @".$username." хочет добавить тебя в друзья";
							$friendButtons = [
								['text' => "✅ Принять ✅", 'callback_data' => " acceptFriend ".$user_id],
								['text' => "❌ Отклонить ❌", 'callback_data' => " denyFriend ".$user_id],
							];
							inlineButtons($unId, $friendButtons, $friendReqText);

							$friendUpdate = mysqli_query($dbCon, "INSERT INTO friends VALUES ('', '$unId', '$user_id', 'req', '$timeNow')");
							sendMessage("🟡 Запрос в друзья отправлен", $user_id);
						}
					}
					else {
						sendMessage("Ты и так себе друг, что ты вот это вот начинаешь", $user_id);
					}
				}
				else {
					sendMessage("🔴 Пользователь не найден, проверьте правильность написания ника", $user_id);
				}
				exit();
			}
			/*if ($text == "test") {
				$textO = "Ниже должен быть список кнопок из пользователей";
				$users = mysqli_query($dbCon, "SELECT * FROM users /*WHERE user_id != '$user_id'");
				$buttons = array();

				foreach ($users as $user) {
					$callback = " shtraf ".$user['user_id'];
					$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
				}

				inlineButtons($user_id, $buttons, $textO);
				exit();
			}*/
			if (mb_stripos($text, 'ты шо') !== false) {
				$cutIn = stripos($text, 'ты шо')+10;
				$word = mb_strcut($text, $cutIn);
				if ($word{strlen($word)-1} == '?') {
				   $word = substr($word,0,-1);
				}
				tiSho($user_id, $word);
			}

			/*============================Admin=================================*/

			if ($user_id == TG_ADMIN_ID) {
				if ($text == "Помощь" || $text == "помощь" || $text == "/help") {
					sendMessage("Статус - посмотреть чужой статус\n. шепнуть [сообщение] - отправить анон смс через бот\n. всем [сообщение] - массовая рассылка\n", $user_id);
				}
				if (mb_stripos($text, "шепнуть")) {
					$whisper = mb_strcut($text, 16);
					$logFileName = __DIR__."/temp/whisper.txt";
					file_put_contents($logFileName, " ");
					file_put_contents($logFileName, print_r($whisper, true)."\r\n", FILE_APPEND);
					$textO = "Выбери пользователя которому хочешь отправить сообшение :";
					$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");

					foreach ($users as $user) {
						$callback = " msgTo ".$user['user_id'];
						$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
					}

					inlineButtons($user_id, $buttons, $textO);
					exit();
				}
				if (mb_stripos($text, "всем")) {
					$broadcast = mb_strcut($text, 10);
					delMsg($user_id, $message_id);

					$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");
					foreach ($users as $user) {
						sendMessage("✉ Сообщение : \n=======================\n\n".$broadcast, $user['user_id']);
					}

					sendMessage("✉ Ты сказал : \n=======================\n".$broadcast."\n=======================\n\nВсем пользователям.", $user_id);
					exit();
				}
				if ($text == "Статус"){
					$textO = "Чей статус будем смотреть?";
					$users = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id != '$user_id'");

					foreach ($users as $user) {
						$callback = " status ".$user['user_id'];
						$buttons[] = ['text' => $user['username'], 'callback_data' => $callback];
					}

					inlineButtons($user_id, $buttons, $textO);
					exit();
				}


			/*==================================================================*/
			}
		}

		exit();
	}
}
elseif (!empty($data['callback_query'])) {
	//debug('callback_query');
	$text = $data['callback_query']['data']; 
	$message_id = $data['callback_query']['message']['message_id'];
	$username = $data['callback_query']['from']['username'];
	$user_id = $data['callback_query']['from']['id'];
	$type = $data['callback_query']['message']['chat']['type'];

	$dbCon = mysqli_connect('', '', '', '');
	

	if ($type == 'private') {
		if (mb_stripos($text, "shtraf") != 0) {
			(int)$user_to = mb_strcut($text, 8);
			$userToUn = mysqli_query($dbCon, "SELECT username FROM users WHERE user_id = '$user_to'");
			$userToUn = $userToUn->fetch_array();
			if (is_array($userToUn)) {
				$to_username = $userToUn['username'];
			}
			else {
				$to_username = $userToUn;
			}
			shtrafnaya($user_to, $username, $user_id, $message_id);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			sendMessage("🚀 Ты выписал штрафную ".$to_username, $user_id);
		}

		if (mb_stripos($text, "msgTo") != 0) {
			(int)$user_to = mb_strcut($text, 7);
			$userToUn = mysqli_query($dbCon, "SELECT username FROM users WHERE user_id = '$user_to'");
			$userToUn = $userToUn->fetch_array();
			if (is_array($userToUn)) {
				$to_username = $userToUn['username'];
			}
			else {
				$to_username = $userToUn;
			}
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			sendMessage($whisper, $user_to);
			sendMessage("✉ Ты шепнул \n=======================\n".$whisper."=======================\n\nПользователю ".$to_username, $user_id);
		}

		if (mb_stripos($text, "status") != 0) {
			(int)$user_to = mb_strcut($text, 8);
			$userToUn = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_to'");
			$userToUn = $userToUn->fetch_array();
			if (is_array($userToUn)) {
				$to_username = $userToUn['username'];
				$lastMsg = $userToUn['lastMsg'];

			}
			else {
				debug("Error");
			}
			showStatus($user_to, $to_username, $lastMsg, $user_id);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
		}

		if (mb_stripos($text, "approve") != 0) {
			(int)$user_to = mb_strcut($text, 9);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			approveShtraf($user_id, $user_to);
		}

		if (mb_stripos($text, "cancel") != 0) {
			delMsg($user_id, $message_id-2);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			menuButtons($user_id, "Операция отменена, возвращение в главное меню. 🔄");
		}

		if (mb_stripos($text, "ping") != 0) {
			(int)$user_to = mb_strcut($text, 6);
			$userToUn = mysqli_query($dbCon, "SELECT * FROM users WHERE user_id = '$user_to'");
			$userToUn = $userToUn->fetch_array();
			if (is_array($userToUn)) {
				$to_username = $userToUn['username'];
			}
			else {
				debug("Error");
			}
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			pingUser($user_to, $to_username, $user_id, $username, $message_id);
			sendMessage("Пингуем ".$to_username." 🌚", $user_id);
		}

		if (mb_stripos($text, "acceptFriend") != 0) {
			(int)$user_to = mb_strcut($text, 14);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			$friendUpdate = mysqli_query($dbCon, "UPDATE friends SET status = 'yes' WHERE user_from = '$user_to' AND user_to = '$user_id'");
			sendMessage("🟢 @".$username." принял твой запрос в друзья", $user_to);
		}

		if (mb_stripos($text, "denyFriend") != 0) {
			(int)$user_to = mb_strcut($text, 12);
			delMsg($user_id, $message_id-1);
			delMsg($user_id, $message_id);
			$friendUpdate = mysqli_query($dbCon, "UPDATE friends SET status = 'no' WHERE user_from = '$user_to' AND user_to = '$user_id'");
			sendMessage("🔴 @".$username." отклонил запрос дружбы", $user_to);
		}
	}
}
?>
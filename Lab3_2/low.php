<?php
$html .= "<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>"; // подключаем капчу

if( isset( $_GET[ 'Login' ] ) ) {
	// Получить пользователя
	$user = ($_GET[ 'username' ]);

	// Получение запроса
	$pass = ($_GET[ 'password' ]);

	// Выполняем запрос в БД: найти пользователя с никнеймом $user
	$query  = "SELECT * FROM `users` WHERE user = '$user';";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$account_locked = False;
		$captcha = False; 
		$last_login = strtotime( $row["last_login"] );
		if ($row["failed_login"] > 0){
			$fail = $row["failed_login"];
			for ($i = 0; $i < 3; $i++){
				if ($row["failed_login"] > $fail){
					if (!$account_locked){
						$query  = "SELECT * FROM `forbidden_passws` WHERE password='$pass';";
						$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
						if( $result && mysqli_num_rows( $result ) == 1 ) {
							$account_locked = True; 
							$query  = "UPDATE `users` SET failed_login = "1 + $i", last_login = now() WHERE user = '$user';";
							$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
							break;
						}
					}
	 				if ($i = 2){
						$captcha = True;
						break; 
					}
					if (time() < $last_login + 1 + $i) 
						$account_locked = True; 
				}
				
			}
		}
	
		$captcha_failed = false; // обьявили переменную капча зафейлилась или нет
		if ($captcha) // если нужна капча
		{
            // посылаем запрос в гугл и он отвечает успешно капча прошла или нет
			$response = $_GET["g-recaptcha-response"];
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$data = [
			'secret' => '6LeRSgYjAAAAAInmUFMNWoVLMPcBZzDfbgGESi3l',
			'response' => $response
			];
			$options = [
		        'http' => [
		            'method' => 'POST',
		            'content' => http_build_query($data)
						
		        ]
			];
			$context  = stream_context_create($options);
			$verify = file_get_contents($url, false, $context);
			$captcha_success=json_decode($verify); // ответ от сервера гугл
			// ответ от сервера гугл
			if ($captcha_success->success==false){ // если зафейлилась то ставим true
				$captcha_failed = true;
				$html .= "<script>document.getElementById(\"captcha\").style.display = \"block\";</script>"; // отображаем капчу на экране
			}
		}

		if ($row["password"] == md5( $pass ) && !$account_locked && !$captcha_failed){
			$avatar = $row["avatar"];

			// Login successful
			$html .= "<p>Welcome to the password protected area {$user}</p>";
			$html .= "<img src=\"{$avatar}\" />";
			$query  = "UPDATE `users` SET failed_login=0, last_login = now() WHERE user = '$user';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
		}
		
		else{
			// Login failed
			if($captcha_failed) // если капча зафейлилась то выводим Введите капчу
				$html .= "<pre><br />Enter captcha.</pre>";
			else($account_locked)
				$html .= "<pre><br />Account locked.</pre>";
			else
				$html .= "<pre><br />Username and/or password incorrect.</pre>";
			$query  = "UPDATE `users` SET failed_login = (failed_login + 1), last_login = now() WHERE user = '$user';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
		}
	}
	else {
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res); // закрывает соединение
}

?>
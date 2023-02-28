<?php
$html .= "<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>"; // подключаем капчу 
if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = ($_GET[ 'username' ]);

	// Get password
	$pass = ($_GET[ 'password' ]);
	$pass = md5( $pass );

	// Выполняем запрос в БД: найти пользователя с никнеймом $user
	$query  = "SELECT * FROM `users` WHERE user = '$user';";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$account_locked = False; 
		$captcha_required = False;
		$last_login = strtotime( $row["last_login"] ); 

		if ($row["failed_login"] >= 5) { 
			if (time() < $last_login + (5 * 60)) 
				$account_locked = True; 
		}
		else if ($row["failed_login"] >= 2) { // если попыток больше двух 
			if (time() < $last_login + (5 * 60)) // и 5 мин не прошло 
			{
				$captcha_required = true; // ставим нужна капча
			}
		}
		$captcha_failed = false; // обьявили переменную капча зафейлилась или нет
		if ($captcha_required) // если нужна капча
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
			$captcha_success=json_decode($verify);
            // ответ от сервера гугл
			if ($captcha_success->success==false){ // если зафейлилась то ставим true
				$captcha_failed = true;
				$html .= "<script>document.getElementById(\"captcha\").style.display = \"block\";</script>"; // отображаем капчу на экране
			}
		}
		if ($row["password"] == $pass && !$account_locked && !$captcha_failed){
			$avatar = $row["avatar"];

			// Login successful

			$html .= "<p>Welcome to the password protected area {$user}</p>";
			$html .= "<img src=\"{$avatar}\" />";
			$query  = "UPDATE `users` SET failed_login=0, last_login = now() WHERE user = '$user';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
		}
		else{
			// Login failed
			if ($captcha_failed) // если капча зафейлилась то выводим введите капчу
				$html .= "<pre><br />Enter captcha.</pre>";
		  	else if ($account_locked)
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
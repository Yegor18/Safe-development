<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = stripslashes($_GET[ 'username' ]); // добавлена проверка ввода

	// Get password
	$pass = stripslashes($_GET[ 'password' ]);
	$pass = md5( $pass );

	// Выполняем запрос в БД: найти пользователя с никнеймом $user
	$query  = "SELECT * FROM `users` WHERE user = '$user';";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );

		$account_locked = False; // объявили переменную
		if ($row["failed_login"] >= 5) { // количество неправильных попыток больше или равно 5
			$last_login = strtotime( $row["last_login"] ); // берем время последней попытки
			if (time() < $last_login + (5 * 60)) // пока не прошло 5 минут, ты залочен
				$account_locked = True; // залочили аккаунт
		}
		if ($row["password"] == $pass && !$account_locked){
			$avatar = $row["avatar"];

			// Login successful
			$html .= "<p>Welcome to the password protected area {$user}</p>";
			$html .= "<img src=\"{$avatar}\" />";

			// обнуляем количество попыток ввода неправильного пароля (failed_login=0)
			$query  = "UPDATE `users` SET failed_login=0, last_login = now() WHERE user = '$user';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
		}
		// в else входим если неправильный пароль ИЛИ аккаунт залочен
		else{
			// Login failed

			// смотрим что вывести пользователю: если аккаунт залочен, выводим 'account locked'
			// иначе просто пишем, что неправильный пароль
			if ($account_locked)
				$html .= "<pre><br />Account locked.</pre>";
			else
				$html .= "<pre><br />Username and/or password incorrect.</pre>";

			// увеличиваем количество неудавшихся попыток входа (failed_login = failed_login + 1)
			// last_login - время последней попытки
			// если аккаунт уже был залочен и вводим шестой раз, то 5 минут будут идти уже с текущего момента

			$query  = "UPDATE `users` SET failed_login = (failed_login + 1), last_login = now() WHERE user = '$user';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
		}
	}
	else {
		// пользователь был не найден
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res); // закрывает соединение
}

?>
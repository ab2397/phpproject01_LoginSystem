<?php

// Check for empty input signup
function emptyInputSignup($name, $email, $username, $pwd, $pwdRepeat) {
	$result;
	if (empty($name) || empty($email) || empty($username) || empty($pwd) || empty($pwdRepeat)) {
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

// Check invalid username
function invalidUid($username) {
	$result;
	if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

// Check invalid email
function invalidEmail($email) {
	$result;
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

// Check if passwords matches
function pwdMatch($pwd, $pwdrepeat) {
	$result;
	if ($pwd !== $pwdrepeat) {
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

// Check if username is in database, if so then return data
function uidExists($conn, $username) {
  $sql = "SELECT * FROM users WHERE usersUid = ? OR usersEmail = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
	 	header("location: ../signup.php?error=stmtfailed");
		exit();
	}

	mysqli_stmt_bind_param($stmt, "ss", $username, $username);
	mysqli_stmt_execute($stmt);

	// "Get result" returns the results from a prepared statement
	$resultData = mysqli_stmt_get_result($stmt);

	if ($row = mysqli_fetch_assoc($resultData)) {
		return $row;
	}
	else {
		$result = false;
		return $result;
	}

	mysqli_stmt_close($stmt);
}

// Insert new user into database
function createUser($conn, $name, $email, $username, $pwd) {
  $sql = "INSERT INTO users (usersName, usersEmail, usersUid, usersPwd) VALUES (?, ?, ?, ?);";

	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
	 	header("location: ../signup.php?error=stmtfailed");
		exit();
	}

	$hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

	mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $username, $hashedPwd);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
	header("location: ../signup.php?error=none");
	exit();
}

// Check for empty input login
function emptyInputLogin($username, $pwd) {
	$result;
	if (empty($username) || empty($pwd)) {
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

// Log user into website
function loginUser($conn, $username, $pwd) {
	$uidExists = uidExists($conn, $username);

	if ($uidExists === false) {
		header("location: ../login.php?error=wronglogin");
		exit();
	}

	$pwdHashed = $uidExists["usersPwd"];
	$checkPwd = password_verify($pwd, $pwdHashed);

	if ($checkPwd === false) {
		header("location: ../login.php?error=wronglogin");
		exit();
	}
	elseif ($checkPwd === true) {
		session_start();
		$_SESSION["userid"] = $uidExists["usersId"];
		$_SESSION["useruid"] = $uidExists["usersUid"];
		header("location: ../index.php?error=none");
		exit();
	}
}

// doLogin v2 code 
function doLogin($username, $password)
{
    $query = "SELECT * FROM users WHERE usersUid='$username' AND usersPwd='$password'";
    $result = mysqli_query(dbConnection(), $query);
    if($result){
		if($result->num_rows == 0){
			echo("No users in table.");
			// $event = date("Y-m-d") . "  " . date("h:i:sa") . " [ DB ] " . "ERROR: this user does not exist: $username" . "\n";
	                // log_event($event);
			return false;
		}
		else {
			while($row = $result->fetch_assoc()){
				// $h_password = generateHash($password);
				if ($row['usersPwd'] == $password){
					echo "User Authenicated".PHP_EOL;
					// $cookie = setcookie($row['usersUid'], hash("sha256",$row['usersPwd']), time()+60*60);
					// Above code is incorrect. You need to use Javascript in Browser side
					
					$sessionId = hash("sha256",$row['usersPwd']),
					$queryy = "INSERT INTO users_session(Sessionid, userName) VALUES ($sessionId, $username);";
					$resultt = mysqli_query(dbConnection(), $queryy);

					return $sessionId;
				}
				else{
					// $event = date("Y-m-d") . "  " . date("h:i:sa") . " [ DB ] " . "ERROR: Username & Password do not match" . "\n";
			                // log_event($event);
					return 0;
				}
			}
		}
	}
}

    

	

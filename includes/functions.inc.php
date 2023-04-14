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
	 	// header("location: ../signup.php?error=stmtfailed");
		// exit();
		$result = false;
		return $result;
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
        // header("location: ../signup.php?error=stmtfailed");
        // exit();
		$myNum= 0;
		$myJSON = json_encode($myNum);
		return $myJSON;
    }

    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $username, $hashedPwd);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
	$myNum= 1;
	$myJSON = json_encode($myNum);
	return $myJSON;
    // header("location: ../signup.php?error=none");
    // exit();
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
	    $myNum= 0;
	    $myJSON = json_encode($myNum);
	    return $myJSON;
	}

	$pwdHashed = $uidExists["usersPwd"];
	$checkPwd = password_verify($pwd, $pwdHashed);

	if ($checkPwd === false) {
	    $myNum= 0;
	    $myJSON = json_encode($myNum);
	    return $myJSON;
	}
	else{
		$myObj = new stdClass();
		$myObj->username = $uidExists['usersUid'];
		$queryy = "SELECT session_id FROM user_session WHERE user_id='$username'";
    		$resultt = mysqli_query(dbConnection(), $queryy);
    		if($resultt){
			    if($resultt->num_rows == 0){
				    $sessionId = hash("sha256",$row['usersPwd']);
				    $queryyy = "INSERT INTO user_session(user_id,session_id) VALUES ('$username','$sessionId');";
				    $resulttt = mysqli_query(dbConnection(), $queryyy);
				    $myNum = 1;
				    $myJSON = json_encode($myNum);
				    return $myJSON;
				    }
			    else{
				    while($roww = $resultt->fetch_assoc()){
					    $myObj->sessionId = $roww['session_id'];
					    $myObj->expTime = $roww['loginTime'];
					    $myJSON = json_encode($myObj);
					    return $myJSON;
					    }
				    }
			    }
			else{
				    // $event = date("Y-m-d") . "  " . date("h:i:sa") . " [ DB ] " . "ERROR: Username & Password do not match" . "\n";
			        // log_event($event);
				    $myNum= 0;
				    $myJSON = json_encode($myNum);
				    return $myJSON;
			}
	}
}

    

	

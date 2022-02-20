<?php
header("Content-Type:application/json");
$request = json_decode(file_get_contents('php://input'));

if($request->route == "api/testword")
{
	$result = testWord($request->word);
	response(200,"todo ok", $result);
}

if($request->route == "api/login")
{
	$result = login($request->userid);
	response(200,"todo ok", $result);
}

if($request->route == "api/adduser")
{
	$result = addUser($request->userid,$request->name,$request->mail);
	response(200,"todo ok", $result);
}

function login($userid)
{
	$result['state'] = true;
	$result['userid'] = $userid;
	//$result['message'] = $message;
	return $result;
}

function testWord($testWord)
{
	$testWord = strtoupper($testWord);
	$realWord = getRealWord();
	$len = strlen($realWord);
	$text = "";
	$state = false; 
	$message = "";

	if(strlen($testWord) != $len)
	{
		$message = 'La palabra debe tener '.$len.' letras';
	}
	else
	{
		for ($i = 0; $i < $len; $i++) 
		{
			if($testWord[$i] == $realWord[$i])
			{
	    		$text = $text."1";
			}
	    	else
	    	{
	    		if (strpos($realWord, $testWord[$i]) !== false)
	    			$text = $text."2";
	    		else
	    			$text= $text."0";
	    	}
		}

		if($testWord == $realWord)
		{
			$state = true;
			$message = "Encontraste la palabra!";
		}
	}

	$result['text'] = $text;
	$result['state'] = $state;
	$result['message'] = $message;
	return $result;
}

function getRealWord()
{
	return strtoupper("movil");
}


function addUser($userid,$name,$mail)
{
	$query = "INSERT INTO `users` (`id`, `name`, `mail`) VALUES ('%d', '%s', '%s')";
	$query = sprintf($query, $userid,$name,$mail);
	$res = dbquery($query);

	$result['state'] = $res;
	//$result['message'] = $res;
	return $result;

}



function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);
	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']= $data;
	
	$json_response = json_encode($response);
	echo $json_response;
}

function dbquery($sql){
	$host="localhost";        # host name or ip address
	$user="root";            # database user name
	$pass="";    # database password
	$database="wordle";        # dateabase name with which you want to connect
	$conn = new mysqli($host, $user, $pass, $database);
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}
	$result = $conn->query($sql);
	$conn->close();
	return $result;
}

/*
//"SELECT id, firstname, lastname FROM MyGuests";
if ($result->num_rows > 0) {
	  // output data of each row
	  while($row = $result->fetch_assoc()) {
	    echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
	  }
	} else {
	  echo "0 results";
	}

*/


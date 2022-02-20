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

if($request->route == "api/addword")
{
	$result = addWord($request->word);
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
	$realWord = strtoupper(getRealWord());
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
		$message = " la palabra es ".$realWord;
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
	$date= date('Y-m-d');
	$result= dbSelectQuery("select word,DATE_FORMAT(date, '%Y-%m-%d') as date from words order by date DESC LIMIT 1");
	$wordDate=$result[0]->date;
   
    if($wordDate!=$date)
    {
    	$ch = curl_init('https://palabras-aleatorias-public-api.herokuapp.com/random-by-length?length=5');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($ch);
		curl_close($ch);
		$data=  json_decode($json);
		$word= $data->body->Word;
		addWord($word);
    }
    else{
    	$word = $result[0]->word;
    }

  return $word;
}


function addWord($word)
{
	$date= date('Y-m-d');
	$query = "INSERT INTO `words` ( `word`, `date`) VALUES ('%s','".$date."')";
	$query = sprintf($query, $word);
	$res = dbExecuteQuery($query);

	$result['state'] = $res;
	//$result['message'] = $res;
	return $result;
}

function addUser($userid,$name,$mail)
{
	$query = "INSERT INTO `users` (`id`, `name`, `mail`) VALUES ('%d', '%s', '%s')";
	$query = sprintf($query, $userid,$name,$mail);
	$res = dbExecuteQuery($query);

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


function dbExecuteQuery($sql){
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


function dbSelectQuery($sql){
	$host="localhost";        # host name or ip address
	$user="root";            # database user name
	$pass="";    # database password
	$database="wordle";        # dateabase name with which you want to connect
	$conn = new mysqli($host, $user, $pass, $database);
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}
	
	$result = $conn->query($sql);

	if(mysqli_num_rows($result)>0){
	     // Cycle through results
	    while ($row = $result->fetch_object()){
	        $user_arr[] = $row;
	    }
	    // Free result set
	    $result->close();
	    $conn->next_result();
	}

	$conn->close();
	return $user_arr;
}
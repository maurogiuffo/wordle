<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Content-Type:application/json");

include 'vars.php';
session_start();

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
	$result = addUser($request->userid,$request->name,$request->mail,$request->pass);
	response(200,"todo ok", $result);
}

if($request->route == "api/addword")
{
	$result = addWord($request->word);
	response(200,"todo ok", $result);
}

if($request->route == "api/gettestwords")
{
	$result = getTestWords();
	response(200,"todo ok", $result);
}


function login($userid)
{
	$result = dbSelectQuery("select * from users where id=".$userid);
	if(count($result) == 0)
	{
		$result['state'] = false;
		return $result;
	}

	$_SESSION["userid"] = $userid;
	$result['state'] = true;
	$result['userid'] = $userid;
	$result['name'] = $result[0]->name;
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
		//$text= getCompareWords($testWord,$realWord )

		if($testWord == $realWord)
		{
			$state = true;
		}

		addWordTest($testWord);
	}

	$result['state'] = $state;
	return $result;
}

function getCompareWords($testWord,$realWord)
{
	$len = strlen($realWord);
	$text ="";

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

	return $text;
}


function getTestWords()
{
	$query = "select wordtest from wordtests where userid = '%d' and wordid = '%d' order by id";
	$query = sprintf($query, $_SESSION["userid"],$_SESSION["wordid"]);
	$qresult = dbSelectQuery($query);
	
	$realWord = getRealWord();
	$finished= false;
	for ($i = 0; $i < count($qresult); $i++) 
	{
		$list[$i]['wordtest'] = $qresult[$i]->wordtest;
		$list[$i]['compare'] = getCompareWords($qresult[$i]->wordtest,$realWord);
		
		if($list[$i]['wordtest'] == $realWord)
			$finished = true;
	}

	$result['testwords'] = $list;
	$result['finished'] = $finished;
	return $result;
}

function getRealWord()
{
	$date = date('Y-m-d');
	$result = dbSelectQuery("select id,word,DATE_FORMAT(date, '%Y-%m-%d') as date from words order by date DESC LIMIT 1");
	$wordDate = $result[0]->date;
   
    if($wordDate != $date)
    {
    	$ch = curl_init('https://palabras-aleatorias-public-api.herokuapp.com/random-by-length?length=5');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($ch);
		curl_close($ch);
		$data =  json_decode($json);
		$word = $data->body->Word;
		addWord($word);
		return getRealWord();
    }
    else{
    	$word = $result[0]->word;
    	$_SESSION["wordid"] = $result[0]->id;
    }

  return strtoupper($word);
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

function addWordTest($word)
{
	$query = "INSERT INTO `wordtests` ( `wordtest`, `userid`, `wordid`) VALUES ('%s','%d','%d')";
	$query = sprintf($query, $word,$_SESSION["userid"],$_SESSION["wordid"]);
	$res = dbExecuteQuery($query);
	$result['state'] = $res;
	//$result['message'] = $res;
	return $result;
}

function addUser($userid,$name,$mail,$pass)
{
	if($pass != ADMIN_PASS)
	{
		$result['state'] = false;
		return $result;
	}

	$query = "INSERT INTO `users` (`id`, `name`, `mail`) VALUES ('%d', '%s', '%s')";
	$query = sprintf($query, $userid,$name,$mail);
	$res = dbExecuteQuery($query);
	$result['state'] = $res;
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
	$conn = new mysqli(HOST, BD_USER, BD_PASS, BD_DATABASE);
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}
	$result = $conn->query($sql);
	$conn->close();
	return $result;
}


function dbSelectQuery($sql){
	$conn = new mysqli(HOST, BD_USER, BD_PASS, BD_DATABASE);
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
<?php
header("Content-Type:application/json");
$request = json_decode(file_get_contents('php://input'));

if($request->type == "testword")
{
	$result = testWord($request->word);
	response(200,"todo ok",$result);
}

function testWord($testWord)
{
	$testWord = strtoupper($testWord);
	$realWord = getRealWord();
	$len = strlen($realWord);
	$text = "";
	$state = false; 
	
	if(strlen($testWord) != $len)
	{
		$text = 'La palabra debe tener '.$len.' letras';
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
			$state = true;
	}

	$result['text'] = $text;
	$result['state'] = $state;
	return $result;
}

function getRealWord()
{
	return strtoupper("movil");
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
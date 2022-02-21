<?php

    define("ADMIN_PASS","123654");

	if($_SERVER['HTTP_HOST']=='localhost')
	{
		define("HOST","localhost" );
		define("BD_USER","root" );
		define("BD_PASS","" );
		define("BD_DATABASE","wordle" );
	}
	else
	{
		define("HOST","localhost" );
		define("BD_USER","id18493342_user" );
		define("BD_PASS","ttGXh1d>Rf07DvVR" );
		define("BD_DATABASE","id18493342_wordle" );
	}

?>
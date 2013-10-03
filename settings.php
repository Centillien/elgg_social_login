<?php
	$HA_SOCIAL_LOGIN_PROVIDERS_CONFIG = ARRAY(
		ARRAY( 
			"provider_id"       => "Facebook",
			"provider_name"     => "Facebook",
			"require_client_id" => TRUE, 
			"callback"          => TRUE,
			"new_app_link"      => "https://www.facebook.com/developers/apps.php", 
		)
		,
		ARRAY(
                        "provider_id"       => "LinkedIn",
                        "provider_name"     => "LinkedIn",
                        "callback"          => TRUE,
                        "new_app_link"      => "https://www.linkedin.com/secure/developer?newapp=",
                )
                ,
		ARRAY(
			"provider_id"       => "Google",
			"provider_name"     => "Google",
			"callback"          => TRUE,
			"require_client_id" => TRUE, 
			"new_app_link"      => "https://code.google.com/apis/console/", 
		) 
		,
		ARRAY( 
			"provider_id"       => "Twitter",
			"provider_name"     => "Twitter", 
			"new_app_link"      => "https://dev.twitter.com/apps", 
		)
		,
		ARRAY( 
			"provider_id"       => "Foursquare",
			"provider_name"     => "Foursquare",
			"callback"          => TRUE,
			"require_client_id" => TRUE, 
			"new_app_link"      => "https://developer.foursquare.com/", 
		)
		,
		ARRAY( 
			"provider_id"       => "Yahoo",
			"provider_name"     => "Yahoo", 
			"callback"          => TRUE,
			"new_app_link"      => "https://developer.apps.yahoo.com/projects", 
		)
		,
		ARRAY( 
			"provider_id"       => "AOL",
			"provider_name"     => "AOL", 
			"new_app_link"      => NULL, 
		) 
	);

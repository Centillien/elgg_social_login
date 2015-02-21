<?php
	elgg_register_event_handler( 'init', 'system', 'elgg_social_login_init' );

	function elgg_social_login_init()
	{
		elgg_extend_view( 'forms/login'   , 'elgg_social_login/login' );
		elgg_extend_view( 'forms/register', 'elgg_social_login/login' );
	}

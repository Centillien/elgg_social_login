<?php
	require_once (dirname(dirname(dirname(__FILE__))) . "/engine/start.php");
	// load hybridauth
        require_once( dirname(__FILE__) . "/vendors/hybridauth/Hybrid/Auth.php" );

	// well, dont need theses
	//restore_error_handler();
	//restore_exception_handler();

	$assets_base_url  = elgg_get_site_url() . "mod/elgg_social_login/";
	$forward = elgg_get_plugin_setting("ha_settings_forward_page","elgg_social_login");
	if(empty($forward)) { 
	$forward = "/";
	}

	// let display a loading message. should be better than a white screen
	if( isset( $_GET["provider"] ) && ! isset( $_GET["redirect_to_provider"] )){
		// selected provider 
		$provider = @ trim( strip_tags( $_GET["provider"] ) ); 
		
		$_SESSION["HA::STORE"] = ARRAY(); 
?>
<table width="100%" border="0">
  <tr>
    <td align="center" height="200px" valign="middle"><img src="<?php echo $assets_base_url ; ?>graphics/loading.gif" /></td>
  </tr>
  <tr>
    <td align="center"><br /><h3>Loading...</h3><br /></td> 
  </tr>
  <tr>
    <td align="center">Contacting <b><?php echo ucfirst( $provider ) ; ?></b>, please wait...</td> 
  </tr> 
</table>
<script> 
	setTimeout( function(){window.location.href = window.location.href + "&redirect_to_provider=true"}, 750 );
</script>
<?php
		die();
	} // end display loading 

	// if user select a provider to login with 
	// and redirect_to_provider eq ture
	if( isset( $_GET["provider"] ) && isset( $_GET["redirect_to_provider"] )){
		try{ 
			// selected provider name 
			$provider = @ trim( strip_tags( $_GET["provider"] ) );

			// build required configuratoin for this provider
			if( ! get_plugin_setting( 'ha_settings_' . $provider . '_enabled', 'elgg_social_login' ) ){
				throw new Exception( 'Unknown or disabled provider' );
			}

			$config = array();
			$config["base_url"]  = $assets_base_url . 'vendors/hybridauth/';
			$config["providers"] = array();
			$config["providers"][$provider] = array();
			$config["providers"][$provider]["enabled"] = true;

			// provider application id ?
			if( get_plugin_setting( 'ha_settings_' . $provider . '_app_id', 'elgg_social_login' ) ){
				$config["providers"][$provider]["keys"]["id"] = get_plugin_setting( 'ha_settings_' . $provider . '_app_id', 'elgg_social_login' );
			}

			// provider application key ?
			if( get_plugin_setting( 'ha_settings_' . $provider . '_app_key', 'elgg_social_login' ) ){
				$config["providers"][$provider]["keys"]["key"] = get_plugin_setting( 'ha_settings_' . $provider . '_app_key', 'elgg_social_login' );
			}

			// provider application secret ?
			if( get_plugin_setting( 'ha_settings_' . $provider . '_app_secret', 'elgg_social_login' ) ){
				$config["providers"][$provider]["keys"]["secret"] = get_plugin_setting( 'ha_settings_' . $provider . '_app_secret', 'elgg_social_login' );
			}

			// if facebook
			if( strtolower( $provider ) == "facebook" ){
				$config["providers"][$provider]["display"] = "popup";
			}

			// create an instance for Hybridauth
			$hybridauth = new Hybrid_Auth( $config );

			// try to authenticate the selected $provider
			$adapter = $hybridauth->authenticate( $provider );

			$user_profile = $adapter->getUserProfile();

			$user_uid = $provider . "_" . $user_profile->identifier;

			// attempt to find user 
			/**
			 * !!! taken from Elgg Facebook Services plugin by anirupdutta 
			 */ 
			$options = array(
				'type' => 'user',
				'plugin_id' => 'elgg_social_login',
				'plugin_user_setting_name_value_pairs' => array(
					'uid' => $user_uid,
					'provider' => $provider,
				),
				'plugin_user_setting_name_value_pairs_operator' => 'AND',
				'limit' => 0
			);
			
			$users = elgg_get_entities_from_plugin_user_settings($options);

			if ( ! $users ) { 
				$userlogin = str_replace( ' ', '-', $user_profile->displayName ); 

				if ( ! $userlogin ){
					$userlogin = 'user-' . rand( 10000, 99999 );
				}

				while ( get_user_by_username( $userlogin ) ){
					$userlogin = str_replace( ' ', '-', $user_profile->displayName ) . '-' . rand( 1000, 9999 );
				}

				$password = generate_random_cleartext_password();

				$username = $user_profile->displayName;

				$useremail = $user_profile->email;
 
				$user = new ElggUser();
				$user->username = $userlogin;
				$user->name = $username;
				$user->access_id = ACCESS_PUBLIC;
			 	$user->email = $user_profile->email;
				$user->salt = generate_random_cleartext_password();
				$user->password = generate_user_password($user, $password);
				$user->owner_guid = 0;
				$user->container_guid = 0;
	 
				if ( ! $user->save() ) {
					register_error( elgg_echo('registerbad') ); 
				}
 
				// register user && provider
				elgg_set_plugin_user_setting( 'uid', $user_uid, $user->guid, 'elgg_social_login' ); 
				elgg_set_plugin_user_setting( 'provider', $provider, $user->guid, 'elgg_social_login' ); 

				// notice && login
				system_message( elgg_echo('A new user account has been created from your ' . $provider . ' account.') );
				login( $user ); 
				
			# {{{ update user profile
				// access_id 1 => Logged in users


				// 1. About me
				create_metadata( $user->guid, "description", html_entity_decode( $user_profile->description, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				// 2. Brief description
				create_metadata( $user->guid, "briefdescription", html_entity_decode( substr ($user_profile->briefdescription,0, 249), ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );
				// 3. Location
				create_metadata( $user->guid, "location", html_entity_decode( $user_profile->region, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				// 4. contactemail
				create_metadata( $user->guid, "email", html_entity_decode( $user_profile->email, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				// 5. website
				create_metadata( $user->guid, "website", html_entity_decode( $user_profile->profileURL, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				//6. Username 
				create_metadata( $user->guid, "username", html_entity_decode( $user_profile->displayName, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );
			
				//7. Phone
				create_metadata( $user->guid, "phone", html_entity_decode( $user_profile->phone, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				//8. Twitter
				create_metadata( $user->guid, "twitter", html_entity_decode( $user_profile->twitter, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

				//9. Specialties
				create_metadata( $user->guid, "skills", html_entity_decode( $user_profile->skills, ENT_COMPAT, 'UTF-8'), "text", $user->guid, 1 );

			

			# }}} update user profile

			# {{{ user image
				if( $user_profile->photoURL ){ 
					$sizes = array(
						'topbar' => array(16, 16, TRUE),
						'tiny' => array(25, 25, TRUE),
						'small' => array(40, 40, TRUE),
						'medium' => array(100, 100, TRUE),
						'large' => array(200, 200, FALSE),
						'master' => array(550, 550, FALSE),
					);

					$filehandler = new ElggFile();
					$filehandler->owner_guid = $user->guid;
					foreach ($sizes as $size => $dimensions) {
						$image = get_resized_image_from_existing_file(
							$user_profile->photoURL,
							$dimensions[0],
							$dimensions[1],
							$dimensions[2]
						);

						$filehandler->setFilename("profile/{$user->guid}{$size}.jpg");
						$filehandler->open('write');
						$filehandler->write($image);
						$filehandler->close();
					}
 
					$user->icontime = time(); 
				}
			# }}} user image
			}
			elseif (count($users) == 1) { 
				// login user
				login( $users[0] );

				// notice
				system_message( elgg_echo( 'You have signed in with ' . $provider ) );
			}
			else{
				throw new Exception( 'Unable to login with ' . $provider );
			} 

?>
<html>
<head>
<script>
function init() {

	window.opener.location ='<?php echo $forward;?>';

	window.close();
}
</script>
</head>
<body onload="init();">
</body>
</html>
<?php
		}
		catch( Exception $e ){
			$message = "Unspecified error!"; 

			switch( $e->getCode() ){
				case 0 : $message = "Unspecified error."; break;
				case 1 : $message = "Hybriauth configuration error."; break;
				case 2 : $message = "Provider not properly configured."; break;
				case 3 : $message = "Unknown or disabled provider."; break;
				case 4 : $message = "Missing provider application credentials."; break;
				case 5 : $message = "Authentification failed. The user has canceled the authentication or the provider refused the connection."; break; 
			}  
?>
<style> 
HR {
	width:100%;
	border: 0;
	border-bottom: 1px solid #ccc; 
	padding: 50px;
}
</style>
<table width="100%" border="0">
  <tr>
    <td align="center"><br /><img src="<?php echo $assets_base_url ; ?>graphics/alert.png" /></td>
  </tr>
  <tr>
    <td align="center"><br /><h3>Something bad happen!</h3><br /></td> 
  </tr>
  <tr>
    <td align="center">&nbsp;<?php echo $message ; ?></td> 
  </tr>

<?php 
	if( get_plugin_setting( 'ha_settings_test_mode', 'elgg_social_login' ) ){
?>
  <tr>
    <td align="center"> 
		<div style="padding: 5px;margin: 5px;background: none repeat scroll 0 0 #F5F5F5;border-radius:3px;">
			<br /> 
			&nbsp;<b>This code is still in alpha</b><br /><br /><b style="color:#cc0000;">But you can make it better by sending the generated error report to the developer!</b>
			<br />
			<br />

			<div id="bug_report">
				<form method="post" action="http://hybridauth.sourceforge.net/reports/index.php?product=elgg-plugin-1.8&v=1.0.2">
					<table width="90%" border="0">
						<tr>
							<td align="left" valign="top">
								Your email (recommended)
								<input type="text" name="email" style="width: 98%;border: 1px solid #CCCCCC;border-radius: 5px;padding: 5px;" />
							</td> 
						</tr>
						<tr>
							<td align="left" valign="top"> 
								A comment? how it did happen? (optional)
								<textarea name="comment" style="width: 98%;border: 1px solid #CCCCCC;border-radius: 5px;padding: 5px;"></textarea>
							</td> 
						</tr>
						<tr>
							<td align="center" valign="top"> 
								<input type="submit" style="width: 300px;height: 33px;" value="Send the error report" /> 
							</td> 
						</tr>
					</table> 
					
					<textarea name="report" style="display:none;"><?php echo base64_encode( print_r( array( $e, $_SERVER ), TRUE ) ) ?></textarea>
				</form> 
				<small>
					Note: This message can be disabled from the plugin settings by setting <b>test mode</b> to <b>NO</b>.
				</small>
			</div>
		</div>
	</td> 
  </tr>
<?php
	} // end test mode
?>
</table>  
<?php 
			// diplay error and RIP
			die();
		}
    }

<?php
global $CONFIG;

require_once( elgg_get_plugins_path() . "elgg_social_login/settings.php");

$plugin_base_url     =  elgg_get_site_url() . "mod/elgg_social_login/";
$hybridauth_base_url = elgg_get_site_url() . "mod/elgg_social_login/vendors/hybridauth/";
$assets_base_url     = elgg_get_site_url() . "mod/elgg_social_login/graphics/";


echo '<div id="elgg_social_login_site_settings">';

if
(
   ! session_id() 
|| ! version_compare( PHP_VERSION, '5.2.0', '>=' )
|| ! function_exists ( 'curl_version' )
||   class_exists('OAuthException')
||   extension_loaded('oauth') 
)
{ 
?>
<p style="font-size: 14px;margin-left:10px;"> 
	<br />
	<b style='color:red;'>Warning:<br />Unfortunately Your server failed the requirements check for this plugin and most likely it won't work correctly!</b>
	<br /> 
</p>
<?php
} 
?> 
	<p style="font-size: 14px;margin-left:10px;"> 
		<br /> 
		<div style="background: none repeat scroll 0 0 #E6EFC2;color: #264409;padding: 10px;border: 2px solid #C6D880;">
		We highly recommend to first run the plugin requirements test or read the plugin user manual. 
		</div>
		<br />
		<br />
		<br />
		<div align="center">
		<b><a href="<?php echo $plugin_base_url ?>diagnostics.php?url=http://www.example.com" target="_blank"  style="border: 1px solid #CCCCCC;border-radius: 5px;padding: 7px;text-decoration: none;"> Run the plugin requirements test </a></b>
		&nbsp;
		<b><a href="<?php echo $plugin_base_url ?>help/index.html#settings" target="_blank"  style="border: 1px solid #CCCCCC;border-radius: 5px;padding: 7px;text-decoration: none;"> Read the plugin user guide </a></b>
		</div>
		<br /> 
	</p>
 
	<br />
	<h2 style="border-bottom: 1px solid #CCCCCC;margin:10px;">General Settings</h2>

		<div style="padding: 5px;margin: 5px;background: none repeat scroll 0 0 #F5F5F5;border-radius:3px;">
			<table>
			<tr>
			<td>
				<b>Plugin Test Mode Active?</b>
				<select style="height:22px;margin: 3px;" name="params[ha_settings_test_mode]">
					<option value="1" <?php if(   $vars['entity']->ha_settings_test_mode ) echo "selected"; ?> >YES</option>
					<option value="0" <?php if( ! $vars['entity']->ha_settings_test_mode ) echo "selected"; ?> >NO</option>
				</select> 
			</td>
			<td> 
				&nbsp;&nbsp; We recommend to set <b>test mode</b> to <b style="color:green">YES</b> until you are sure you want to go live. 
			</td>
			</tr>
			</table>
		</div> 
 
		<div style="padding: 5px;margin: 5px;background: none repeat scroll 0 0 #F5F5F5;border-radius:3px;">
			<table>
			<tr>
			<td>
				<b>Do you have privacy web page?</b>
				
			</td>
			<td> 
				<input type="text" style="width: 350px;margin: 3px;" 
					value="<?php echo $vars['entity']->ha_settings_privacy_page; ?>"
					name="params[ha_settings_privacy_page]" 
				> leave it blank if you dont.
			</td>
			</tr>
			</table>
		</div>
		                <div style="padding: 5px;margin: 5px;background: none repeat scroll 0 0 #F5F5F5;border-radius:3px;">
                        <table>
                        <tr>
                        <td>
                                <b>Forward users after login ?</b>

                        </td>
                        <td>
                                <input type="text" style="width: 350px;margin: 3px;"
                                        value="<?php echo $vars['entity']->ha_settings_forward_page; ?>"
                                        name="params[ha_settings_forward_page]"
                                > No value = homepage
                        </td>
                        </tr>
                        </table>
                </div>


 
	<br />
	<h2 style="border-bottom: 1px solid #CCCCCC;margin:10px;">Providers setup</h2>
	<p style="margin:10px;">
	Except for OpenID providers, each social network and identities provider will require that you create an external application linking your Web site to theirs apis. These external applications ensures that users are logging into the proper Web site and allows identities providers to send the user back to the correct Web site after successfully authenticating their Accounts.
	</p>
	<ul style="list-style:circle inside;margin-left:30px;">
		<li>To correctly setup these Identity Providers please carefully follow the help section of each one.</li>
		<li>If <b>Provider Satus</b> is set to <b style="color:red">NO</b> then users will not be able to login with this provider on you website.</li>
	</ul>
	<br />
<?php   
	foreach( $HA_SOCIAL_LOGIN_PROVIDERS_CONFIG AS $item ){
		$provider_id                = @ $item["provider_id"];
		$provider_name              = @ $item["provider_name"];

		$require_client_id          = @ $item["require_client_id"];
		$provide_email              = @ $item["provide_email"];
		
		$provider_new_app_link      = @ $item["new_app_link"];
		$provider_userguide_section = @ $item["userguide_section"];

		$provider_callback_url      = elgg_get_site_url() ;

		if( isset( $item["callback"] ) && $item["callback"] ){
			//$provider_callback_url  = '<span style="color:green">' . $hybridauth_base_url . '?hauth.done=' . $provider_id . '</span>';
			$provider_callback_url  = '<span style="color:green">' . $hybridauth_base_url . '?hauth.done=' . $provider_id . '</span>';
		}
		
		$setupsteps = 0;
	?> 
	<div> 
		<div style=" border-radius:3px; border: 1px solid #999999;">
			<div style="padding: 5px;margin: 5px;background: none repeat scroll 0 0 #F5F5F5;border-radius:3px;">
				<h2><img alt="<?php echo $provider_name ?>" title="<?php echo $provider_name ?>" src="<?php echo $assets_base_url . "16x16/" . strtolower( $provider_id ) . '.png' ?>" /> <?php echo $provider_name ?></h2> 
				<ul>
					 <li><b>Allow users to sign on with <?php echo $provider_name ?>?</b>
						<select name="params[<?php echo 'ha_settings_' . $provider_id . '_enabled' ?>]" style="height:22px;margin: 3px;" >
							<option value="1" <?php $entitykey = 'ha_settings_' . $provider_id . '_enabled'; if( $vars['entity']->$entitykey == 1 ) echo "selected"; ?> >YES</option>
							<option value="0" <?php $entitykey = 'ha_settings_' . $provider_id . '_enabled'; if( $vars['entity']->$entitykey == 0 ) echo "selected"; ?> >NO</option>
						</select>
					</li>
					
					<?php if ( $provider_new_app_link ){ ?>
						<?php if ( $require_client_id ){ // key or id ? ?>
							<li><b>Application ID</b>
							<input type="text" style="width: 350px;margin: 3px;"
							value="<?php $entitykey = 'ha_settings_' . $provider_id . '_app_id'; echo $vars['entity']->$entitykey; ?>"
							name="params[<?php echo 'ha_settings_' . $provider_id . '_app_id' ?>]" ></li>
						<?php } else { ?>
							<li><b>Application Key</b>
							<input type="text" style="width: 350px;margin: 3px;"
								value="<?php $entitykey = 'ha_settings_' . $provider_id . '_app_key'; echo $vars['entity']->$entitykey; ?>"
								name="params[<?php echo 'ha_settings_' . $provider_id . '_app_key' ?>]" ></li>
						<?php }; ?>	 

						<li><b>Application Secret</b>
						<input type="text" style="width: 350px;margin: 3px;"
							value="<?php $entitykey = 'ha_settings_' . $provider_id . '_app_secret'; echo $vars['entity']->$entitykey; ?>"
							name="params[<?php echo 'ha_settings_' . $provider_id . '_app_secret' ?>]" ></li>
					<?php } // if require registration ?>
				</ul> 
			</div>
			<div style="padding: 12px;margin: 5px;background: none repeat scroll 0 0 white;border-radius:3px;">
				<p><b>How to setup <?php echo $provider_name ?>:</b></p>

				<?php if ( $provider_new_app_link  ) : ?> 
					<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Go to <a href="<?php echo $provider_new_app_link ?>" target ="_blanck"><?php echo $provider_new_app_link ?></a> and <b>create a new application</b>.</p>

					<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Fill out any required fields such as the application name and description.</p>

					<?php if ( $provider_id == "Google" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> On the <b>"Create Client ID"</b> popup switch to advanced settings by clicking on <b>(more options)</b>.</p>
					<?php endif; ?>	

					<?php if ( $provider_callback_url ) : ?>
						<p>
							<?php echo "<b>" . ++$setupsteps . "</b>." ?> Provide this URL as the Callback URL for your application:
							<br />
							<?php echo $provider_callback_url ?>
						</p>
					<?php endif; ?> 

					<?php if ( $provider_id == "MySpace" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Put your website domain in the <b>External Url</b> and <b>External Callback Validation</b> fields. This should match with the current hostname <em style="color:#CB4B16;"><?php echo $_SERVER["SERVER_NAME"] ?></em>.</p>
					<?php endif; ?> 

					<?php if ( $provider_id == "Live" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Put your website domain in the <b>Redirect Domain</b> field. This should match with the current hostname <em style="color:#CB4B16;"><?php echo $_SERVER["SERVER_NAME"] ?></em>.</p>
					<?php endif; ?> 

					<?php if ( $provider_id == "Facebook" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Put your website domain in the <b>Site Url</b> field. This should match with the current hostname <em style="color:#CB4B16;"><?php echo $_SERVER["SERVER_NAME"] ?></em>.</p> 
					<?php endif; ?>	

					<?php if ( $provider_id == "LinkedIn" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Put your website domain in the <b>Integration URL</b> field. This should match with the current hostname <em style="color:#CB4B16;"><?php echo $_SERVER["SERVER_NAME"] ?></em>.</p> 
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Set the <b>Application Type</b> to <em style="color:#CB4B16;">Web Application</em>.</p> 
					<?php endif; ?>	

					<?php if ( $provider_id == "Twitter" ) : ?>
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Put your website domain in the <b>Application Website</b> and <b>Application Callback URL</b> fields. This should match with the current hostname <em style="color:#CB4B16;"><?php echo $_SERVER["SERVER_NAME"] ?></em>.</p> 
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Set the <b>Application Type</b> to <em style="color:#CB4B16;">Browser</em>.</p> 
						<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Set the <b>Default Access Type</b> to <em style="color:#CB4B16;">Read</em>.</p> 
					<?php endif; ?>	
					
					<p><?php echo "<b>" . ++$setupsteps . "</b>." ?> Once you have registered, copy and past the created application credentials into this setup page.</p>  
				<?php else: ?>	
					<p>No registration required for OpenID based providers</p> 
				<?php endif; ?> 
		   </div>
		</div>   
	</div> 
	<br />  
	<?php 
}

echo '</div>';

<?php
	global $CONFIG;
	global $HA_SOCIAL_LOGIN_PROVIDERS_CONFIG;
	$site_name =  elgg_get_site_entity()->name;


	require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/settings.php");

	// display "Or connect with" message, or not.. ?
	echo "<div style='padding:10px;padding-top:0px;margin-top:0px;'><div style='padding:10px;padding-left:0px;'><b>";
	echo elgg_echo('social:login'); 
	echo "</b></div>";

	// display provider icons
	foreach( $HA_SOCIAL_LOGIN_PROVIDERS_CONFIG AS $item ){
		$provider_id     = @ $item["provider_id"];
		$provider_name   = @ $item["provider_name"];
	
		$assets_base_path  = elgg_get_plugins_path() . "elgg_social_login/";
		$assets_base_url  = elgg_get_site_url() . "mod/elgg_social_login/";


		if( elgg_get_plugin_setting( 'ha_settings_' . $provider_id . '_enabled', 'elgg_social_login' ) ){
			?>
			<a href="javascript:void(0);" title="Login to <?php echo $site_name ?> with <?php echo $provider_name ?>" class="ha_connect_with_provider" provider="<?php echo $provider_id ?>">
                                <img alt="<?php echo $provider_name ?>" title="<?php echo "Login to ". $site_name ." using your ". $provider_name." account."?>" src="<?php echo $assets_base_url . "graphics/32x32/" . strtolower( $provider_id ) . '.png' ?>" />
			<?php
		} 
	} 

	// provide popup url for hybridauth callback
	?>
		<input id="ha_popup_base_url" type="hidden" value="<?php echo $assets_base_url; ?>authenticate.php?" />
		<input id="ha_popup_base_path" type="hidden" value="<?php echo $assets_base_path; ?>authenticate.php?" />
	<?php

	// link attribution && privacy page 
	?>
	<p style="border-top:2px dotted #999;font-size: 12px;">
	</p>
	<?php   
	echo "</div>";
?>
<script type="text/javascript">
	$(function(){
		$(".ha_connect_with_provider").click(function(){
			popupurl = $("#ha_popup_base_url").val();
			popuppath = $("#ha_popup_base_path").val();
			provider = $(this).attr("provider");

			window.open(
				popupurl+"provider="+provider,
				"hybridauth_social_sing_on", 
				"location=1,status=0,scrollbars=0,width=800,height=570"
			); 
		});
	});  
</script> 

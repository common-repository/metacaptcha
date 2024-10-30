<?php
/*
 Plugin Name: WP MetaCaptcha plugin
 Plugin URI: http://metacaptcha.com
 Description: MetaCaptcha Anti-spam
 Author: Tien Le, Nhan Huynh, Thai Bui, Wuchang Feng
 Author URI: http://metacaptcha.com
 Version: 2.7
*/

add_action('wp_enqueue_scripts', 'metacaptchaScript');

function metacaptchaScript(){
	if(!is_admin()){
		if(is_single()){
		  wp_enqueue_script('ajax_js', WP_PLUGIN_URL.'/metacaptcha/ajax.js',array('jquery'),'',true);
		}
	}
}

function wpkp_add_commentform(){
    $verb = 'delete';
	echo '<noscript><input type="hidden" name="js_disabled" value="1"/>
	    <div><small>Wordpress MetaCAPTCHA needs javascript to work, but your browser has javascript disabled. Your comment will be '.$verb.'!</small></div></noscript>';

	include_once "metacaptcha_lib.php"; //Link to metacaptcha_lib.php
	$processUrl= WP_PLUGIN_URL.'/metacaptcha/metacaptcha.php';      //Link to metacaptcha.php created in step 5
	$formID="commentform";

	echo initialize_metacaptcha($processUrl, $formID);
}

add_action('comment_form', 'wpkp_add_commentform');

function wpkp_check_hidden_tag($comment) {
    // client browser disable js --> pending queue
    $browser_check = 0;
    if(isset($_POST['js_disabled']))
	$browser_check = $_POST['js_disabled'];

    if ($browser_check == 1) {
    $comment['comment_content'] .= "\n\n[WP MetaCAPTCHA] Javascript has been disable in the client browser";
    add_filter('pre_comment_approved', create_function('$a', 'return 0;'));
    }
    return $comment;
}
add_filter('preprocess_comment', 'wpkp_check_hidden_tag');

function metacaptcha_verifying_new($comment) {
	include_once "metacaptcha_lib.php";
	$message = preg_replace('/\s+/',' ', $_POST['comment']);  //content of the message

	$verify = metacaptcha_verify(trim(stripslashes($_REQUEST['metacaptchaField'])), stripslashes($message));

	if($verify){
		return $comment;
	}
}
add_filter('pre_comment_approved', 'metacaptcha_verifying_new');

// more more

// If you hardcode a MetaCAPTCHA API key here, all key config screens will be hidden
$metacaptcha_api_key = '';
# Base hostname for API requests (API key is always prepended to this)
$metacaptcha_service_host = 'metacaptcha.com';
# URL for the home page for the AntiSpam service
$metacaptcha_service_url = 'http://rabbit.cs.pdx.edu/MetaCAPTCHA/';
# URL for the page where a user can obtain an API key
$metacaptcha_apikey_url = 'http://metacaptcha.com/key/availableKey.php';
# Plugin version
$metacaptcha_plugin_ver = '1.0';
# API Protocol version
$metacaptcha_protocol_ver = '1.0';
# Port for API requests to service host
$metacaptcha_api_port = 80;


function metacaptcha_init() {
	global $metacaptcha_api_key, $metacaptcha_api_host, $metacaptcha_api_port, $metacaptcha_service_host;

	if ( $metacaptcha_api_key )
		$metacaptcha_api_host = $metacaptcha_api_key . '.' . $metacaptcha_service_host;
	else
		$metacaptcha_api_host = get_option('metacaptcha_api_key') . '.' . $metacaptcha_service_host;

	$metacaptcha_api_port = 80;
	add_action('admin_menu', 'metacaptcha_config_page');
}
#add_action('init', 'metacaptcha_init');

if ( !function_exists('wp_nonce_field') ) {
	function metacaptcha_nonce_field($action = -1) { return; }
	$metacaptcha_nonce = -1;
} else {
	function metacaptcha_nonce_field($action = -1) { return wp_nonce_field($action); }
	$metacaptcha_nonce = 'metacaptcha-update-key';
}

if ( !function_exists('number_format_i18n') ) {
	function number_format_i18n( $number, $decimals = null ) { return number_format( $number, $decimals ); }
}

function metacaptcha_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('metacaptcha Configuration'), __('metacaptcha Configuration'), 'manage_options', 'metacaptcha-key-config', 'metacaptcha_conf');

}

function metacaptcha_conf() {
	global $metacaptcha_nonce, $metacaptcha_api_key,
	    $metacaptcha_service_host, $metacaptcha_apikey_url,
	    $metacaptcha_service_url;

	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		check_admin_referer( $metacaptcha_nonce );
		$key = preg_replace( '/[^a-h0-9]/i', '', $_POST['key'] );

		if ( empty($key) ) {
			$key_status = 'empty';
			$ms[] = 'new_key_empty';
			delete_option('metacaptcha_api_key');
		} else {
			$key_status = metacaptcha_verify_key( $key );
		}

		if ( $key_status == 'valid' ) {
			update_option('metacaptcha_api_key', $key);
			$ms[] = 'new_key_valid';
		} else if ( $key_status == 'invalid' ) {
			$ms[] = 'new_key_invalid';
		} else if ( $key_status == 'failed' ) {
			$ms[] = 'new_key_failed';
		}

		if ( isset( $_POST['metacaptcha_discard_month'] ) )
			update_option( 'metacaptcha_discard_month', 'true' );
		else
			update_option( 'metacaptcha_discard_month', 'false' );
	}

	if ( $key_status != 'valid' ) {
		$key = get_option('metacaptcha_api_key');
		if ( empty( $key ) ) {
			if ( $key_status != 'failed' ) {
				if ( metacaptcha_verify_key( '1234567890ab' ) == 'failed' )
					$ms[] = 'no_connection';
				else
					$ms[] = 'key_empty';
			}
			$key_status = 'empty';
		} else {
			$key_status = metacaptcha_verify_key( $key );
		}
		if ( $key_status == 'valid' ) {
			$ms[] = 'key_valid';
		} else if ( $key_status == 'invalid' ) {
			delete_option('metacaptcha_api_key');
			$ms[] = 'key_empty';
		} else if ( !empty($key) && $key_status == 'failed' ) {
			$ms[] = 'key_failed';
		}
	}

	$messages = array(
		'new_key_empty' => array('color' => 'aa0', 'text' => __('Your key has been cleared.')),
		'new_key_valid' => array('color' => '2d2', 'text' => __('Your key has been verified. Happy blogging!')),
		'new_key_invalid' => array('color' => 'd22', 'text' => __('The key you entered is invalid. Please double-check it.')),
		'new_key_failed' => array('color' => 'd22', 'text' => sprintf(__('The key you entered could not be verified because a connection to %s could not be established. Please check your server configuration.'), $metacaptcha_service_host)),
		'no_connection' => array('color' => 'd22', 'text' => __('There was a problem connecting to the MetaCAPTCHA server. Please check your server configuration.')),
		'key_empty' => array('color' => 'aa0', 'text' => sprintf(__('Please enter an API key. (<a href="%s" style="color:#fff">Get your key.</a>)'), $metacaptcha_apikey_url)),
		'key_valid' => array('color' => '2d2', 'text' => __('This key is valid.')),
		'key_failed' => array('color' => 'aa0', 'text' => __('The key below was previously validated but a connection to %s can not be established at this time. Please check your server configuration.', $metacaptcha_service_host)));
?>
        
<?php if ( !empty($_POST ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('MetaCAPTCHA Configuration'); ?></h2>
<div class="narrow">
<form action="" method="post" id="metacaptcha-conf" style="margin: auto; width: 400px; ">
<?php if ( !$metacaptcha_api_key ) { ?>
	<p><?php printf(__('<a href="%1$s">MetaCAPTCHA</a> is a free service from Portland State University that helps protect your blog from spam. The MetaCAPTCHA plugin will send every comment or Pingback submitted to your blog to the service for evaluation, and will return the high spam score if MetaCAPTCHA determines it is spam. MetaCAPTCHA plugin uses the spam score to generate Proof-of-Work (PoW) or client puzzles and supply clients with a JavaScript solver. The client-browser uses the solver to generate a solution and submit it to WP server for verification. If you don\'t have a MetaCAPTCHA key yet, you can get one at <a href="%2$s">metacaptcha.cs.pdx.edu</a>.'), $metacaptcha_service_url, $metacaptcha_apikey_url); ?></p>

<?php metacaptcha_nonce_field($metacaptcha_nonce) ?>
<h3><label for="key"><?php _e('MetaCAPTCHA API Key'); ?></label></h3>
<?php foreach ( $ms as $m ) : ?>
	<p style="padding: .5em; background-color: #<?php echo $messages[$m]['color']; ?>; color: #fff; font-weight: bold;"><?php echo $messages[$m]['text']; ?></p>
<?php endforeach; ?>
<p><input id="key" name="key" type="text" size="15" maxlength="64" value="<?php echo get_option('metacaptcha_api_key'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<?php _e('<a href="http://metacaptcha.cs.pdx.edu/">What is this?</a>'); ?>)</p>


<?php if ( isset( $invalid_key) && $invalid_key ) { ?>
<h3><?php _e('Why might my key be invalid?'); ?></h3>
<p><?php _e('This can mean one of two things, either you copied the key wrong or that the plugin is unable to reach the metacaptcha servers, which is most often caused by an issue with your web host around firewalls or similar.'); ?></p>
<?php } ?>
<?php } ?>
<p><label><input name="metacaptcha_discard_month" id="metacaptcha_discard_month" value="true" type="checkbox" <?php if ( get_option('metacaptcha_discard_month') == 'true' ) echo ' checked="checked" '; ?> /> <?php _e('Automatically discard spam comments on posts older than a month.'); ?></label></p>
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
</form>
</div>
</div>
<?php
}

function metacaptcha_verify_key( $key ) {
    // call verify key
    $array_key = array('321492', '321417', '321456', '321437', '321412');
    if(in_array($key, $array_key))
    {
        return 'valid';
    }
    else
    {
        return 'invalid';
    }
}
/*
if ( !get_option('metacaptcha_api_key') && !$metacaptcha_api_key && !isset($_POST['submit']) ) {
	function metacaptcha_warning() {
		echo "
		<div id='metacaptcha-warning' class='updated fade'><p><strong>".__('MetaCAPTCHA is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your MetaCAPTCHA API key</a> for it to work.'), "plugins.php?page=metacaptcha-key-config")."</p></div>
		";
	}
	add_action('admin_notices', 'metacaptcha_warning');
	return;
}
*/

?>

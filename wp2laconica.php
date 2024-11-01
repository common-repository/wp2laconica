<?php
/*
Plugin Name: WP2Laconica
Plugin URI: http://nob.hct.pl/2009/07/04//wp2laconica-wordpress-plugin-for-laconica
Description: Generates Laconica Updates when a new Post is Published.
Author: Original author Gary Jones mod by bm9ib2r5
Version: 1.0
Author URI: http://nob.hct.pl/
*/



$wp2laconica_plugin_name = 'WP2Laconica';
$wp2laconica_plugin_prefix = 'wp2laconica_';

add_action('publish_post', 'laconica_post_now_published');

function laconica_update_status($username, $password, $iservice, $new_status)
{


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://'.$iservice.'/api/statuses/update.xml');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    
        curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'status='.$new_status);
        $output = curl_exec($curl);
        $info = curl_getinfo($curl);
                                                                        
    //  echo $output;
    //  echo $info['url'];
                                                                                        
                                                                                        
        curl_close($curl);
                                                                                                

}

function laconica_post_now_published($post_id)
{
	global $wp2laconica_plugin_prefix;

	$has_been_laconicaed = get_post_meta($post_id, 'has_been_laconicaed', true);
	if (!($has_been_laconicaed == 'yes')) {
		query_posts('p=' . $post_id);

		if (have_posts()) {
			the_post();
			$post_url = file_get_contents('http://tinyurl.com/api-create.php?url=' . get_permalink());
			$title = get_the_title();
			if (strlen($title) > 110) {
				$title = substr_replace($title, '...', 107);
			}
			$i = '\'' . $title . '\' - ' . $post_url;
			
			$laconica_username = get_option($wp2laconica_plugin_prefix . 'username', 0);
			$laconica_password = get_option($wp2laconica_plugin_prefix . 'password', 0);
			$laconica_iservice = get_option($wp2laconica_plugin_prefix . 'iservice', 0);			
			laconica_update_status( $laconica_username, $laconica_password, $laconica_iservice, $i);
	
			add_post_meta($post_id, 'has_been_laconicaed', 'yes');
		}
	}
}

function word2laconica_plugin_url($str = '')
{
	$dir_name = '/wp-content/plugins/wp2laconica';
	bloginfo('url');
	echo($dir_name . $str);
}

function word2laconica_options_subpanel()
{
	global $wp2laconica_plugin_name;
	global $wp2laconica_plugin_prefix;

  	if (isset($_POST['info_update'])) 
	{

		if (isset($_POST['username'])) {
			$username = $_POST['username'];
		} else {
			$username = '';
		}

		if (isset($_POST['password'])) {
			$password = $_POST['password'];
		} else {
			$password = '';
		}
		
		if (isset($_POST['iservice'])) {
			$iservice = $_POST['iservice'];
		} else {
			$iservice = '';
		}
		


		update_option($wp2laconica_plugin_prefix . 'username', $username);
		update_option($wp2laconica_plugin_prefix . 'password', $password);
		update_option($wp2laconica_plugin_prefix . 'iservice', $iservice);		
	} else {

		$username = get_option($wp2laconica_plugin_prefix . 'username');
		$password = get_option($wp2laconica_plugin_prefix . 'password');
		$iservice = get_option($wp2laconica_plugin_prefix . 'iservice');		
	}

	echo('<div class=wrap><form method="post">');
	echo('<h2>' . $plugin_name . ' Options</h2>');

	?>
	<p><h3>General Options</h3>
		You can find out more information about this plugin at <a href="http://blog.bluefur.com/word2laconica">the word2laconica plugin page</a>.
		<p><br />

		Laconica Username: <input type="text" name="username" value="<?php echo($username); ?>"><br />
		Laconica Password: <input type="password" name="password" value="<?php echo($password); ?>"><br />
		Laconica Service: <input type="text" name="iservice" value="<?php echo($iservice); ?>"><br />		
		<div class="submit"><input type="submit" name="info_update" value="Update Options" /></div></form>

	<?php

	echo('</div>');
}

function word2laconica_add_plugin_option()
{
	global $wp2laconica_plugin_name;
	if (function_exists('add_options_page')) 
	{
		add_options_page($wp2laconica_plugin_name, $wp2laconica_plugin_name, 0, basename(__FILE__), 'word2laconica_options_subpanel');
    	}	
}

add_action('admin_menu', 'word2laconica_add_plugin_option');

?>

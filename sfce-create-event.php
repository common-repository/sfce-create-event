<?php
/*
Plugin Name: SFCe - Graph API Create Event
Version: 4.00.2
Author: Roger Howorth
Author URI: http://www.thehypervisor.com
Plugin URI: http://www.thehypervisor.com/simple-facebook-connect-extensions
Description: Adds a PHP function to create a Facebook event. 
License: http://www.gnu.org/licenses/gpl.html

Copyright (c) 2011 Roger Howorth

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

add_action("plugins_loaded", "sfce_graph_create_event_init");
add_action("admin_menu", "sfce_graph_create_event_init");

function sfce_graph_create_event_init() {
	$plugin_dir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain( 'sfce-create-event', null, $plugin_dir . '/languages/');
	return;
}

include_once('sfce_create_event_post.php');
include_once('sfce-settings-page.php');

function sfce_create_event() {
	// See Notes section for possible arguments - http://wiki.developers.facebook.com/index.php/Events.create
	$num_args = func_num_args();
	if ( $num_args <> '1' ) wp_die(__('You must pass the correct arguments as an array to sfce_create_event when you invoke the function. See plugin readme for more details.', 'sfce-create-event'));
	$args = func_get_arg(0);

	// Make Facebook event
	// Check if the post is published before creating fb event, disable this line for testing
	//if ( $args['post_id']->post_status == "draft" ) return $args['post_id'];

	// load Facebook ID etc
	$fboptions = get_option('sfc_options');

	$start_time=mktime($args['start_hour'],$args['start_min'],"00",$args['month'],$args['day'],$args['year']);
	$start_time = substr(date('c',$start_time),0,16);

	$end_time=mktime($args['end_hour'],$args['end_min'],"00",$args['month'],$args['day'],$args['year']);
	$end_time = substr(date('c',$end_time),0,16);

	// Add promo link if ok
	$options = get_option('sfce_event_options');
	if ( $options['sfce_show_promo'] ) $args['description'] .= "\nAuto event creation by http://www.thehypervisor.com";

	$event_data = array(
		'name' => $args['name'],
		'start_time' => $start_time,
		'end_time' => $end_time,
		'description' => $args['description'],
		'tagline' => $args['tagline'],
	);
	if ( isset($args['privacy']) ) $event_data['privacy_type'] = $args['privacy'];

	if ( $args['is_fanpage'] == 'TRUE' ){
		$event_data['page_id'] = $fboptions['fanpage'];
		$url = "https://graph.facebook.com/me/events?";
		$event_data['access_token'] = $fboptions['page_access_token'];
	}
	else if ( $args['is_fanpage'] == 'FALSE' )
	{
		$url = "https://graph.facebook.com/me/events?";
		$event_data['access_token'] = $fboptions['access_token'];
	}

	$data = wp_remote_post($url, array('body'=>$event_data));

	if (!is_wp_error($data)) {
		$resp = json_decode($data['body'],true);
		if ($resp['id']) $event_id = $resp['id']; 
		else
		{
			foreach ( $resp as $res ) foreach( $res as $re ) $tmptxt .= $re . '<br>';
			wp_die( 'Error: ' . $tmptxt . '<br>If this is an OAuth error, go to SFCe settings page and re-save settings to grant this plugin the correct Facebook permissions. Otherwise, use your browser\'s Back button to go back to your post and correct the problem then try again.');
		}
	}

	return $event_id;
}
//end sfce_create_event



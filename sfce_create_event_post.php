<?php
/*
Author URI: http://www.thehypervisor.com
Plugin URI: http://www.thehypervisor.com/simple-facebook-connect-extensions
Description: Adds a PHP function to create a Facebook event. Requires Simple Facebook Connect plugin by Otto.
License: http://www.gnu.org/licenses/gpl.html

Copyright (c) 2010 Roger Howorth

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


// array of HTML inputs
$sfce_text_inputs = array( 	array( 'name' => 'sfce_event_name', 'maxlength' => 30, 'label' =>'Name of Event', 'title' => 'Type the name of the event here. Or [TITLE] for post title; [[name_of_custom_field]] for custom field content', 'required' => 1, 'default' => '[TITLE]'),
					array( 'name' => 'sfce_event_description', 'maxlength' => 100, 'title' => 'Type a description of the event here. Or [TITLE] for post title; [[name_of_custom_field]] for custom field content', 'label' =>'Description'), 
					array( 'name' => 'sfce_event_host', 'maxlength' => 30, 'label' =>'Host'), 
					array( 'name' => 'sfce_event_tagline', 'maxlength' => 30, 'label' =>'Tagline'), 
				//	array( 'name' => 'sfce_event_photo', 'maxlength' => 255, 'size' => 60, 'label' =>'Local path to photo', 'title' => 'e.g. /var/www/wordpress/uploads/photo.jpg'), 
					array( 'name' => 'sfce_event_is_fanpage', 'default' => 'TRUE', 'type' => 'select', 'values' => array('TRUE', 'FALSE'), 'label' => 'Use Fan Page?', 'title' => 'e.g. TRUE or FALSE'), 
					array( 'name' => 'sfce_event_privacy', 'maxlength' => 6, 'type' => 'select', 'values' => array( 'OPEN', 'CLOSED', 'SECRET'), 'size' => 6, 'label' =>'Privacy setting', 'default' =>'OPEN', 'title' => 'e.g. OPEN, CLOSED or SECRET' ), 
					array( 'name' => 'sfce_event_start_hour', 'type' => 'select', 'values' => array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24'),  'label' =>'Start hour', 'required' => 1, 'no-end-br' => '1', 'title' => 'Time when the event will start.'), 
					array( 'name' => 'sfce_event_start_min', 'type' => 'select', 'values' => array('00','15','30','45'), 'label' =>'minute', 'required' => 1, 'no-start-br' => '1', 'title' => 'Time when the event will start.'), 
					array( 'name' => 'sfce_event_end_hour', 'type' => 'select', 'values' => array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24'),  'label' =>'End hour', 'required' => 1, 'no-end-br' => '1', 'title' => 'Time when the event will end.'), 
					array( 'name' => 'sfce_event_end_min', 'type' => 'select', 'values' => array('00','15','30','45'), 'label' =>'minute', 'required' => 1, 'no-start-br' => '1', 'title' => 'Time when the event will end.')
					);

$options = get_option('sfce_event_options');
if ( !$options['sfce_hide_postpanel'] ) add_action('edit_form_advanced', 'sfce_create_event_post_hook');

add_action('edit_post', 'sfce_create_event_post');

function sfce_create_event_post_hook() {
	/* Must be author or above to see this form */
	if ( !current_user_can('2') ) return;

	$fboptions = get_option('sfc_options');

	global $sfce_text_inputs;
	$options = get_option('sfce_event_options');

	global $current_user;
	get_currentuserinfo();
	$fbuid = get_user_meta($current_user->ID, 'fbuid', true);



	$post_id = $_GET['post'];
	$eeventdate = get_post_meta ($post_id, 'sfce_event_date', true) ;
	if ( !$eeventdate ) $eeventdate = date('Y-m-d');

	list ($sfce_event_year, $sfce_event_month, $sfce_event_day) = sscanf($eeventdate, "%d-%d-%d");                                                                                                       

	echo '<div class="postbox"><h3>'; _e('Create Facebook Event', 'sfce-create-event'); echo '</h3><div class="inside">';
	if ( empty($fbuid) ) {
		echo 'To create Facebook events you must:<br>';
		echo '<li>Be logged into Facebook using the same browser - use a different tab.</li>';
		echo '<li>Connect your Facebook account to your Wordpress account using the button on your <a href="/wp-admin/profile.php">Wordpress Profile page</a>.</li></div></div>';
		return;
	}


	echo '<h2><b>'; _e('Event details', 'sfce-create-event') ; echo ':</b></h2>';
	_e('Insert text; [TITLE] for post title; [[name_of_custom_field]] for data from Custom field.');
	echo '<hr><ul>';
	echo '<li><input type="hidden" name="edit-verify-key" id="edit-verify-key" value="' . wp_create_nonce('edistance') . '" />';
	echo '<ul>';
	foreach ( $sfce_text_inputs as $input)
	{
		// this sets defaults hardcoded into above array
		$$input['name'] = $input['default'];

		// Load default value from options if present
		$temp = $options[$input['name']];
		if ( $temp <> '' ) $$input['name'] = $temp;

		// Now update with saved value from this form if present
		$temp = stripslashes(get_post_meta($_GET['post'], $input['name'], true ));
		if ( $temp <> '' ) $$input['name'] = $temp;

		// Convert fields hidden in options panel to hidden fields...
		if ( $options[$input['name'] . '_visible'] ) $input['type'] = 'hidden';

		switch( $input['type'] )
		{
			case 'hidden':
			echo '<input type="hidden" name="' . $input['name'] . '" value="' . $$input['name'] .'" />';
			break;

			case 'select':
			if ( !$input['no-start-br'] ) echo '<li>';
			printf(__('%s', 'sfce-create-event'),$input['label']);
			echo '&nbsp;<select name="' . $input['name'] . '" title="';
			printf(__('%s', 'sfce-create-event'), $input['title']);
			echo '" >';
			foreach ( $input['values'] as $val ){
				echo '<option value="' . $val .'" ';
				if ( substr($input['name'],-4) == "hour" && $val == date('H')) echo 'selected="selected" ';
				if ( substr($input['name'],-3) == "min" && $val > date('m')) echo 'selected="selected" ';
				if ( $val == $$input['name'] ) echo 'selected="selected" ';
				echo '>' . $val . '</option>';
			}
			echo '</select>';
			if ( $input['no-end-br'] ) echo '&nbsp;'; else echo '</li>';
			break;

			default:
			echo '<li>';
			printf(__('%s', 'sfce-create-event'),$input['label']);
			echo ': <input type="text" name="' . $input['name'] . '" size="' . $input['size'] . '" maxlength="' . $input['maxlength'] . '" title="';
			printf(__('%s', 'sfce-create-event'), $input['title']);
			echo '" value="';
			echo $$input['name']; 
			echo '" />';
			if ( $input['required'] )
			{
				echo '<small>(';
				_e('required', 'sfce-create-event');
				echo ')</small>';
			}
			echo '</li>';
			break;
		}
	}

	echo '<li>';
	_e('Event date', 'sfce-create-event');
	echo '&nbsp;<select type="text" name="sfce_event_day">';
	for ( $i = 1; $i < 32; $i++)
	{
		$day = sprintf("%02d", $i);
		echo '<option value="' . $day . '"';
		if ( $sfce_event_day == $day ) echo "selected";
		echo '>' . $i;
		echo '</option>';
	}
	echo '</select>';

	echo '<select type="text" name="sfce_event_month">';
	for ( $i = 1; $i < 13; $i++)
	{
		$month = sprintf("%02d", $i);
		echo '<option value="' . $month . '"';
		if ( $sfce_event_month == $month ) echo "selected";
		echo '>';
		printf(__('%s','sfce-create-event'), date('M',mktime(0,0,0,$i,1,2011)));
		echo '</option>';
	}
	echo '</select>';

	echo '<select type="text" name="sfce_event_year">';
	for ( $i = date('Y') ; $i < date('Y') +3; $i++ )
	{
		echo '<option value="' . $i . '"';
		if ( $sfce_event_year == $i ) echo "selected";
		echo '>' . $i;
		echo '</option>';
	}
	echo '</select></li><br />';

	$sfce_fb_event_status = get_post_meta($_GET['post'], 'sfce_fb_event_status', true );
	echo '<li>';
	_e('Create Public Facebook Event', 'sfce-create-event');
	echo ':<input type="checkbox" name="sfce_create_fb_event" ';
	if ( $sfce_fb_event_status == "created" ) echo 'checked="yes" ';
	echo 'value="1"  />';


	echo '<p /><small>';
	_e('Tick this box to create a Facebook event the next time this post is updated. Your Wordpress Blog and Facebook accounts must be linked to use this feature.', 'sfce-create-event');
	echo '</small><p />';

	$existing_events = get_post_meta ($post_id, 'fb_event_id') ;
	$j=1;
	if ( $existing_events ) foreach ( $existing_events as $event )
	{
		if ( $event == '' ) continue;
		echo '<br /><a href="http://www.facebook.com/event.php?eid=' . $event . '&index=1">';
		echo addOrdinalNumberSuffix($j++) . ' ';
		_e('Facebook event for this post','sfce-create-event');
		echo '</a><br />';
	}

	echo '<small><br />';
	_e('After using this option go to your Facebook Page and invite your friends to this event.', 'sfce-create-event');
	echo '<p />';
	_e('Note: It takes about 10 seconds to create a Facebook event, don\'t navigate away from this page until the update has completed.', 'sfce-create-event');

	echo '</small></ul>';
	echo '<a href="../wp-admin/tools.php?page=sfce_event_settings_page">';
	_e('Settings Page', 'sfce-create-event');
	echo '</a>';
	echo '</div></div>';
}


function sfce_create_event_post($post_id)
{
	$options = get_option('sfce_event_options');
	if ( !$options['sfce_hide_postpanel'] && $_POST['sfce_create_fb_event'] ) 
	{
		$my_post = array();

		// authorization
		if ( !current_user_can('edit_post', $post_id) ) return $post_id;

		// origination and intention
		if ( !wp_verify_nonce($_POST['edit-verify-key'], 'edistance') ) return ($post_id);

		global $wpdb, $sfce_text_inputs;

		$sfce_event_date = $_POST['sfce_event_year'].'-'.$_POST['sfce_event_month'].'-'.$_POST['sfce_event_day'];
		if ( date('Y-m-d') > $sfce_event_date ) 
		{
			$my_post['ID'] = $post_id;
			wp_die(_e('You cannot create an event in the past. Please pick a future date for your event. You post has been saved but no event created. Press the browser\'s back button to go back and edit your post', 'sfce-create-event'));
		}

		foreach ( $sfce_text_inputs as $input )
			if ( $input['required'] && $_POST[$input['name']] == '' ) $error .= sprintf(__('You must enter a value for %s', 'sfce-create-event'), $input['label']) . '.<br />';

		if ( $error ) wp_die( $error . '<p>' . __('Press your browser\'s Back button and try again.','sfce-create-event' ));

		// By now all is well so do the actual update here
		// Save values in array
		$my_post['ID'] = $post_id;

		// Now add meta data to post  
		update_post_meta ( $post_id, 'sfce_event_date', $sfce_event_date );

		if ( $_POST['sfce_create_fb_event'] )
		{
			foreach ( $sfce_text_inputs as $input)
			{
				$_POST[$input['name']] = stripslashes($_POST[$input['name']]);
				update_post_meta ( $post_id, $input['name'] , $_POST[$input['name']] );
				// Replace [TITLE] with post title...
				$title = get_the_title($post_id);
				$_POST[$input['name']] = str_replace('[TITLE]',$title, $_POST[$input['name']]);

				//Now replace anything else in double square brackets
				// with data from meta data e.g [[meta_tag_here]]
				// Find custom field names, store results in matchesa
				preg_match_all('/\[\[[a-zA-Z0-9_-]+\]\]/', $_POST[$input['name']], $matchesa );
				$count =0;
				foreach ( $matchesa as $matches )
				{
					// Now get array of field names and their data
					foreach ( $matches as $match )
					{
						$length = strlen( $match );
						$match = substr( $match, 2, $length - 4);
						$data = get_post_meta ($post_id, $match, true);
						$res[$count] = array( 'key' => $match, 'data' => $data);
						$count++;
					}
					// Replace matches with the data
					if ( isset($res)) foreach ($res as $ras) $_POST[$input['name']] = str_replace( '[[' . $ras['key'] . ']]', $ras['data'], $_POST[$input['name']]);
					unset($res);
				}
			}
		}

		// Call the facebook event creation...
		if (function_exists('sfce_create_event') && $_POST['sfce_create_fb_event'] )
		{
			$event_id = sfce_create_event( array(
			'name' => $_POST['sfce_event_name']
			,'description' => $_POST['sfce_event_description']
			,'host' => $_POST['sfce_event_host']
			,'post_id' => $post_id
			,'tagline' => $_POST['sfce_event_tagline']
			,'is_fanpage' => $_POST['sfce_event_is_fanpage']
			,'privacy' => $_POST['sfce_event_privacy']
			,'day' => $_POST['sfce_event_day']
			,'month' => $_POST['sfce_event_month']
			,'year' => $_POST['sfce_event_year']
			,'start_hour' => $_POST['sfce_event_start_hour']
			,'start_min' => $_POST['sfce_event_start_min']
			,'end_hour' => $_POST['sfce_event_end_hour']
			,'end_min' => $_POST['sfce_event_end_min']
		//	,'photo' => $_POST['sfce_event_photo']
			));

			if ( $event_id ) add_post_meta( $post_id, 'fb_event_id', $event_id );
		}
	}
}

function addOrdinalNumberSuffix($num) {
	if (!in_array(($num % 100),array(11,12,13))){
		switch ($num % 10) {
		// Handle 1st, 2nd, 3rd
		case 1:  return $num.'st';
		case 2:  return $num.'nd';
		case 3:  return $num.'rd';
		}
	}
	return $num.'th';
}



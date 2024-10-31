=== Plugin Name ===
Plugin name: SFCe Create Event
Contributors: roggie
Donate link: http://www.rogerh.com/donate.html
Tags: Simple Facebook Connect, Facebook, event, SFC, create
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag:4.00.2

Create Facebook events automatically when you create Wordpress posts. This plugin requires the Simple Facebook Connect plugin by Otto.

== Description ==

This plugin creates events in personal Facebook profiles and Facebook Pages.

Version 4.x is a major re-write to work with Facebook's new Graph API. It requires Otto's Simple Facebook Connect plugin v 1.x. You must configure SFC's Publisher module, which is used by SFCe to publish events to Facebook.

To create a Facebook event with this plugin, create or edit a post, scroll down the page to the Create Facebook Event section.

Provided the Wordpress user account has been "Connected to Facebook" using the SFC plugin, they will see a form that will allow them to create an event in their Facebook profile or your Facebook Page. Fill in the event title and start time, tick the Create Public Facebook Event box and Update or Publish the post. If the Wordpress user is not "Connected to Facebook" they will see a warning message and not see the form. The plugin handles Facebook security properly. The first time someone uses this plugin on your Wordpress site, Facebook may ask the Wordpress user to authorize this plugin.

SFCe can populate the event with data from the Wordpress post - e.g. the post title etc, and SFCe can be configured with default values for many Facebook event parameters. Wordpress site admins can configure SFCe to enforce default values or let users override them.

Example: We use this plugin to automatically create public Facebook events for our fan page when certain types of post are published. We also use it to create private Facebook events when other types of post are published.

 
<a href="http://www.thehypervisor.com/simple-facebook-connect-extensions">Changelog</a>

<a href="http://www.facebook.com/home.php?sk=lf#!/pages/The-Hypervisor/114689115238103">Follow me on Facebook</a href>

<a href="http://twitter.com/thehypervisor">Follow me on Twitter</a href>

== Installation ==

This plugin requires Otto's Simple Facebook Connect (SFC) plugin is also installed in your WordPress site. Otto's SFC is an excellent set of plugins for integrating Facebook with your Wordpress site. Unfortunately Otto's SFC does not create Facebook Events. However, this plugin is designed and tested to work with Otto's SFC, so if you want to create Facebook events from your Wordpress blog, install Otto's SFC and this plugin.

1. Install and configure Simple Facebook Connect by Otto.
2. Ensure your Wordpress account is 'connected' to your Facebook account using Otto's Simple Facebook Connect.
3. Unzip the sfce-create-event.zip in your Wordpress plugins directory.
4. Login to WordPress as an administrator, go to Plugins and Activate SFCe Create Events.
5. Configure Otto's SFC to publish events to Facebook.
6. Create or edit a Post, scroll down and use the Create Facebook Events section to supply information about your Facebook event.

== Frequently Asked Questions ==

<strong>I can't create events with this plugin.</strong> <br>You must install and configure Simple Facebook Connect (SFC), a separate Wordpress plugin by Otto. For version 4.x of SFCe you need version v1.x of SFC.<br><br>
<strong>I get an error message about no Access Token.</strong><br>You must configure Otto's SFC plugin's Publisher module to allow it to publish events to Facebook. Make sure you have granted SFC permission "Automatic Publishing" and "Extended Permissions" in the "Publish Settings" section of the SFC settings page.<br><br>
<strong>"Warning: include_once() [function.include]: Failed opening '[path]/wp-content/plugins/simple-facebook-connect/facebook-platform/facebook.php'</strong><br>You are using version 1.x of Otto's SFC plugin with version 3.x of SFCe. Either downgrade SFC or upgrade SFCe.<br><br>
<strong>I can't add a photo to my events.</strong><br>The old Facebook API allowed you to upload a photo for your event, the new Graph API does not. Please contact Facebook and ask them to provide this feature and documentation. You could also downgrade to SFCe 3.x and SFC 0.x, which use the old Rest API.<br><br>
<strong>I want to create an event using this plugin but without filling in the form on my Edit Post page. How do I create an array containing the event date/time etc?</strong><br>
There are several methods of doing this. We use the one below. Please note, in this example we pass some parameters as literal text (e.g. end_min). We pass other parameters as PHP variables (e.g. end_hour). And others we pass as data obtained from an HTML form (e.g. month):<br>
<br>
if (function_exists('sfce_create_event')) sfce_create_event( array(<br>
				'name' => $name,<br>
				'description' => $fbdescription,<br>
				'host' => $host,<br>
				'post_id' => $post_id,<br>
				'tagline' => 'Let\'s Skate Together!',<br>
				'is_fanpage' => TRUE,<br>
				'privacy' => 'OPEN',<br>
				'timezone' => 'Europe/London',<br>
				'day' => $_POST['eday'],<br>
				'month' => $_POST['emonth'],<br>
				'year' => $_POST['eyear'],<br>
				'start_hour' => $start_hour,<br>
				'start_min' => '45',<br>
				'end_hour' => $end_hour,<br>
				'end_min' => '00')<br>
				);<br>
<br>
Note: Many parameters are optional, see the Facebook documentation (below) for a list of the required parameters.<br>

<strong>Where is the Facebook documentation for creating events in this way?</strong>
http://wiki.developers.facebook.com/index.php/Events.create<br>

== Changelog ==

Click this link to see the <a href="http://www.thehypervisor.com/simple-facebook-connect-extensions">changelog</a>.

== Screenshots ==

1. Section added to "New Post" and "Edit Post" to supply details about your event.
2. Settings page.

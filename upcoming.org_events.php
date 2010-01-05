<?php
/*
Plugin Name: Upcoming.org Public Events by Location
Plugin URI: http://priedel.com/wpplugins/upcoming.org_events
Description: Very simple: Fetches events from Upcoming.org by location via shortcode: [upcoming.org_events]San Francisco, CA[/upcoming.org_events]. Check the "Upcoming.org Events"-settings page for some options.
Author: Paul Riedel
Version: 1.0
Author URI: http://priedel.com
*/

require_once(ABSPATH . 'wp-includes/class-snoopy.php');

function get_upcoming_events_url($url, $cachetime = '3600') {
	$snoopy = new Snoopy;
	$snoopy->fetch($url);
	$newcache 				= array();
	$newcache['xml'] 		= $snoopy->results;
	$newcache['expires'] 	= time() + $cachetime;
	$cache[$cacheid] 		= $newcache;
	
	return $cache[$cacheid]['xml'];
}

function shorten_this($string, $maxchars = '25'){
	if(strlen($string) >= $maxchars+1){
return $shortenedstring = substr($string, 0, $maxchars-3) . '...';
	}
}

function set_upcoming_events_options(){
	add_option('show_images','yes');
	add_option('show_upcoming_logo','yes');
	add_option('api_key');
	add_option('max_events','20');
	add_option('radius','50');
}

function unset_upcoming_events_options(){
	delete_option('show_images');
	delete_option('show_upcoming_logo');
	delete_option('api_key');
	delete_option('max_events');
	delete_option('radius');
}

register_activation_hook(__FILE__,'set_upcoming_events_options');
register_deactivation_hook(__FILE__,'unset_upcoming_events_options');

function admin_upcoming_events_options(){
	echo '<div class="wrap">';
	//echo '<p>Existing options (raw):</p><br/>';
	//echo get_option('show_images');
	echo '<p>Set the options for upcoming.org events</p>';

	if($_POST['submit']){
		update_upcoming_events_options();
	}

	print_upcoming_events_form();

	echo '</div>';
}

function update_upcoming_events_options(){
	
	$ok = false;
	
	if($_POST['show_images']){
	update_option('show_images',$_POST['show_images']);	
	$ok = true;	
	}
	
	if($_POST['show_upcoming_logo']){
	update_option('show_upcoming_logo',$_POST['show_upcoming_logo']);	
	$ok = true;	
	}
	
	if($_POST['api_key']){
	update_option('api_key',$_POST['api_key']);	
	$ok = true;	
	}
	
	if($_POST['max_events']){
	update_option('max_events',$_POST['max_events']);	
	$ok = true;	
	}
	
	if($_POST['radius']){
	update_option('radius',$_POST['radius']);	
	$ok = true;	
	}
	
	if($ok){
		echo '<div class="updated"><p><strong>Options Saved.</strong></p></div>';
/*		
		echo 'debug:<pre>';
		print_r($_POST);
		echo '</pre>';
*/
	}
	else{
		echo "<div id=\"message\" class=\"error\"><p>Error:<pre>";
		print_r($_POST);	
		echo "</pre></p></div>\n";
	}
	
}

function print_upcoming_events_form(){
	$show_images = get_option('show_images');
	$show_upcoming_logo = get_option('show_upcoming_logo');
	$api_key = get_option('api_key');
	$max_events = get_option('max_events');
	$radius = get_option('radius');
	?>
	
	<form method="POST">
	<label for="api_key"><a href="http://upcoming.yahoo.com/services/api/keygen.php">Upcoming.org API key</a>: </label><input type="text" name="api_key" value="<?php echo $api_key; ?>"/><br/>
<label for="max_events">Number of events to show: </label><input type="text" name="max_events" value="<?php echo $max_events; ?>"/><br/>
<label for="radius">Radius around location (e.g. "50"): </label><input type="text" name="radius" value="<?php echo $radius; ?>"/><br/>
Show Images next to events: Yes <INPUT type="radio" name="show_images" value="yes"<?php if($show_images == 'yes'){ echo 'checked'; } ?>> No <INPUT type="radio" name="show_images" value="no" <?php if($show_images == 'no'){ echo 'checked';} ?>><br/> 
Show Upcoming.org Logo: Yes <INPUT type="radio" name="show_upcoming_logo" value="yes"<?php if($show_upcoming_logo == 'yes'){ echo 'checked'; } ?>> No <INPUT type="radio" name="show_upcoming_logo" value="no" <?php if($show_upcoming_logo == 'no'){ echo 'checked';} ?>><br/> 
		
	<br/><br/><input type="submit" name="submit" value="save"/>
	</form>
	<?php
}

function modify_menu(){
	add_options_page(
		'Upcoming.org Events',
		'Upcoming.org Events',
		'manage_options',
		__FILE__,
		'admin_upcoming_events_options'
		);
}

add_action('admin_menu','modify_menu');


function show_upcoming_events( $atts, $content = null ) {
	
	$show_images = get_option('show_images');
	$show_upcoming_logo = get_option('show_upcoming_logo');
	$api_key = get_option('api_key');
	$max_events = get_option('max_events');
	$radius = get_option('radius');
	
	if($radius == ''){ $radius = '50'; }
	if($max_events == ''){ $max_events = '20'; }
	
	if($api_key == ''){
		echo '<br/><font color="red"><b>YOU NEED TO ENTER YOUR UPCOMING.ORG API KEY IN THE PLUGIN SETTINGS. GET IT <a href="http://upcoming.yahoo.com/services/api/">HERE</a>.</b></font>';	   
	}
		
	if($content == null){
		return '<br/><font color="red"><b>YOU NEED TO DEFINE THE LOCATION BY USING e.g. [upcoming.org_events]San Francisco, CA[/upcoming.org_events].</b></font>';	   
	}elseif($content != null && $api_key != ''){
		$outputstring = '';
		$loc = urlencode($content);
		$key = $api_key;		
		$venuecutoff = 25;
				
		$upcomingeventspluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';
		
		if($show_upcoming_logo == 'yes'){
			$outputstring = '<div style="float:right;"><img src="'.$upcomingeventspluginpath.'upcoming_logo2.gif"/></div>';
		}
		
		$url = 'http://upcoming.yahooapis.com/services/rest/?method=event.search&api_key='.$key.'&location='.$loc.'&radius='.$radius;
		
		$fxml = get_upcoming_events_url($url);
		
		$xml = new SimpleXMLElement($fxml);
		
		if($xml['stat'] == "fail"){
			
			$error = $xml->error['msg'];
			echo '<br/><font color="red"><b>ERROR.<br/>This is the error message from upcoming.org:<br/>';
			echo "\"$error\"";
			echo "<br/>URL called: $url</b></font>";	   
			
		}
		
		$events = $xml->event;

if (count($events) >= 1):

		for ($i = 0; $i <= count($events); $i++) {
			
			
			$eventdate = date("M d", strtotime($events[$i]['start_date']));
			$eventtime = date("g:ia", strtotime($events[$i]['start_time']));
			$eventname = $events[$i]['name'];
			$eventlink = $events[$i]['url'];
			$eventvenuename = $events[$i]['venue_name'];
			$eventvenueloc = $events[$i]['venue_address'] .', '. $events[$i]['venue_city'] .', ' . strtoupper($events[$i]['venue_state_code']);
			$eventdescription = shorten_this(strip_tags($events[$i]['description']), 180);
			$eventpic = $events[$i]['photo_url'];
			
			shorten_this($eventname, 10);			
		
		
		
		$outputstring .= '
		<div style="margin-top:10px;">';
		
			if($eventpic != '' && $show_images == 'yes'){
				
				$outputstring .= '<img class="eventimage" src="'.$eventpic.'"/>';
			}
			
		  $outputstring .= '
		    <div><a href="'.$eventlink.'" class="upcoming_title">'.$eventname.'</a></div>
		    <div class="upcoming_day">'.$eventdate.', '.$eventtime.' at '.$eventvenuename.', '.$eventvenueloc.'</div>';
	
		if($eventdescription){	
			$outputstring .= '<div style="padding-top:5px;">'.$eventdescription.'</div>';
		}
		$outputstring .=  '<br style="clear:both;"/>
			</div>';
		
		if($i >= $max_events-1 || $i >= count($events)-1){
			break;
		}
		
	}
endif;
		return $outputstring;
	}
}

add_shortcode('upcomingorg_events', 'show_upcoming_events');



    add_action('wp_print_styles', 'add_upcoming_events_css');


    function add_upcoming_events_css() {
	
        $myStyleUrl = WP_PLUGIN_URL . '/upcomingorg-events/upcoming.org_events.css';
        $myStyleFile = WP_PLUGIN_DIR . '/upcomingorg-events/upcoming.org_events.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('upcoming.org_events_css', $myStyleUrl);
            wp_enqueue_style('upcoming.org_events_css');
        }
    }

?>

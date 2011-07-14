<?php
/*
Plugin Name: WP-TagThis
Plugin URI: http://oinam.com/wordpress/plugins/tag-this/
Description: An idea conceived by <a href="http://www.brajeshwar.com/">Brajeshwar</a>, "TagThis" allow users to tag your Published Post. You can control, de-spam or even blacklist users. "TagThis" will be particularly useful if you've a big blog and it becomes difficult for you to be able to tag all your Posts in an effective way. This will also be useful to older blogs which do not have tags in their old Posts (yes, tags were not so famous back then when we started blogging).
Version: 0.9.2
Author: Anirudh Sanjeev
Author URI: http://anirudhsanjeev.org/
Requires: WordPress Version 2.3 or above
*/

/*  Copyright 2007  Andy Staines  (email: andy@yellowswordfish.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
	require_once('../../../wp-config.php');
}

//Create Text Domain For Translations
add_action('init', 'tagthis_textdomain');
function tagthis_textdomain() {
	load_plugin_textdomain('wp-tagthis', 'wp-content/plugins/tagthis');
}

$wpdb->tagthis = $table_prefix . 'tagthis';
$wpdb->spamlist= $table_prefix . 'spamlist';
// Function: Tagthis Administration Menu
// This section adds the various administrative menus that are required for managing the plugin's functions
add_action('admin_menu', 'tagthis_menu');
function tagthis_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Tag This', 'tagthis'), __('Tag This', 'tagthis'), 8, 'tagthis/frontend.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('tagthis/frontend.php', __('Manage Tags', 'tagthis'), __('Manage Tags', 'tagthis'), 'manage_tagthis', 'tagthis/frontend.php');
		add_submenu_page('tagthis/frontend.php', __('Options', 'tagthis'), __('Options', 'tagthis'), 'manage_tagthis', 'tagthis/options_frontend.php');
		add_submenu_page('tagthis/frontend.php', __('Spam Blacklist', 'tagthis'), __('Spam Blacklist', 'tagthis'), 'manage_tagthis', 'tagthis/spam_frontend.php');
		add_submenu_page('tagthis/frontend.php', __('Update', 'tagthis'), __('Update', 'tagthis'), 'manage_tagthis', 'tagthis/update_frontend.php');		
	}
}

//Function: Displays TagThis Header along with custom CSS
add_action('wp_head', 'tagthis_header');
function tagthis_header() {
	echo "\n".'<!-- Start Of Script Generated By Tagthis -->'."\n";
	wp_register_script('tagthis', '/wp-content/plugins/tagthis/tagthis-js.php', false);
	wp_print_scripts(array('sack','tagthis'));
	//echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-content/plugins/tagthis/tagthis-css.php" type="text/css" media="screen" />'."\n";
	echo '<!-- End Of Script Generated By Tagthis -->'."\n";
}


add_action('admin_menu', 'create_tagthis_table');
function create_tagthis_table() {
    if(!get_option("tt_customcss"))
    {
    update_option("tt_display",1);             
    update_option("tt_nfrontpage",2);
    update_option("tt_spamstrength",0);
    update_option("tt_customcss","padding:10px 10px 10px 10px;");
    update_option("tt_displaytags",1);
    update_option("tt_manualmod",0);
    update_option("tt_secret","34231");
    }
	global $wpdb;
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
	// Create Post Ratings Table
	$create_tagthis_sql = "CREATE TABLE $wpdb->tagthis (".
			"id INT(11) NOT NULL auto_increment,".
			"postid INT(11) NOT NULL ,".
			"tagid INT(11) NOT NULL ,".
			"posttitle TEXT NOT NULL,".
			"status INT(2) NOT NULL,".
			"tag text NOT NULL ,".
			"timestamp VARCHAR(15) NOT NULL ,".
			"ip VARCHAR(40) NOT NULL ,".			
			"PRIMARY KEY (id))";
	maybe_create_table($wpdb->tagthis, $create_tagthis_sql);
	$create_tagthis_sql = "CREATE TABLE $wpdb->spamlist (".
			"id INT(11) NOT NULL auto_increment,".
			"type INT(2) NOT NULL,".
			"value VARCHAR(40) NOT NULL,".
	"PRIMARY KEY (id))";			
			
	maybe_create_table($wpdb->spamlist, $create_tagthis_sql);
	$role = get_role('administrator');
	if(!$role->has_cap('manage_tagthis')) {
		$role->add_cap('manage_tagthis');
	}
}

function WidgetPrinter($content)
{
	
	global $post;
	$postid=$post->ID;
	echo $content;//print the post        
    if(get_option("tt_display"))
    {
    ?>
     
    <div id="tagthis<?php echo $postid;?>" style="<?php echo get_option("tt_customcss"); ?>">
	 <?php
     if(get_option("tt_displaytags"))
     {
          echo '<p>';the_tags('Tags for this article: ',' , ');echo '</p>';
     }
     
     ?>           
	<input type="text" name="tag" id="tagtext<?php echo $postid; ?>"> 
	<input type="button" onclick="ajaxAddTag(<?php echo $postid; ?>)" value="Tag This!">  <a onclick="toggle(<?php echo $postid;?>);">[?]</a>
    <div id="tt-help<?php echo $postid;?>" style="background-color:#FFFF99;width:0px;height:0px;border:thin dotted;visibility:hidden;font-size:smaller;">
    Type in a relevant tag, and click the button, and help organize this blog's information. <br><a href="http://dailyusability.com/tagthis/help/">[More Help]</a>
    </div>
    <div id="tt-finished<?php echo $postid;?>" style="background-color:#FFFF99;width:0px;height:0px;border:thin dotted;visibility:hidden;font-size:smaller;">
    <?php if(get_option('tt_manualmod')){echo "Thanks for the tag. The author has chosen for all tags to be moderated and it may take a while before it appears on the front page.<br><a href=\"http://dailyusability.com/tagthis/help/\">[More Help]</a>";} else { echo "Thanks for the tag! You may need to refresh the page to see changes"; }?><br><a href="http://dailyusability.com/tagthis/help/">[More Help]</a>
    </div></div>
    	
        
	<?
    }
}
function wp_tagthis()
{
    
    global $post;
    $postid=$post->ID;
    if(get_option("tt_display"))
    {
    ?>
     
      
    <div id="tagthis<?php echo $postid;?>" style="<?php echo get_option("tt_customcss"); ?>">
     <?php
     if(get_option("tt_displaytags"))
     {
          echo '<p>';the_tags('Tags for this article: ',' , ');echo '</p>';
     }
     
     ?>           
    <input type="text" name="tag" id="tagtext<?php echo $postid; ?>"> 
    <input type="button" onclick="ajaxAddTag(<?php echo $postid; ?>)" value="Tag This!">  <a onclick="toggle(<?php echo $postid;?>);">[?]</a>
    <div id="tt-help<?php echo $postid;?>" style="background-color:#FFFF99;width:0px;height:0px;border:thin dotted;visibility:hidden;font-size:smaller;">
    Type in a relevant tag, and click the button, and help organize this blog's information. <br><a href="http://dailyusability.com/tagthis/help/">[More Help]</a>
    </div>
    <div id="tt-finished<?php echo $postid;?>" style="background-color:#FFFF99;width:0px;height:0px;border:thin dotted;visibility:hidden;font-size:smaller;">
    
    </div>
    </div>
    
    <?
    }
}
function printscripts()
{
	echo "<script type=\"text/javascript\" src=\"".get_option('home')."/wp-content/plugins/tagthis/jquery.js\">";
	echo "<script type=\"text/javascript\" src=\"".get_option('home')."/wp-content/plugins/tagthis/tagthis.js.php?".get_option('home')."\">";
}

//add_action('the_content', 'WidgetPrinter');

?>
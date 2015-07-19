<?php 
/*
Plugin Name: LH Archived Post Status
Plugin URI: http://lhero.org/plugins/lh-archived-post-status/
Description: Creates an archived post status. Content can be excluded from the main loop and feed (but visible with a message), or hidden entirely
Version: 1.3
Author: Peter Shaw
Author URI: http://shawfactor.com/

== Changelog ==

= 0.01 =
* Initial release

= 0.02 =
* Added public/private option

= 1.0 =
* Added icons

= 1.1 =
* Added nonces

= 1.2 =
* Added settings

License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html

Copyright 2013  Peter Shaw  (email : pete@localhero.biz)


This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published bythe Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class LH_archived_post_status_plugin {

	      var $newstatusname = 'archive';
               var $newstatuslabel = 'archived';
               var $newstatuslabel_count = 'Archived <span class="count">(%s)</span>';
	      var $message_field_name = 'lh_archive_post_status_message';
	      var $posttypes_field_name = 'lh_archive_post_status_posttypes';
		var $publicly_available = 'public';
               var $options_name = 'lh_archive_post_status_options';
		var $filename;



function plugin_menu() {
add_options_page('Archive ptions', 'LH Archive', 'manage_options', $this->filename, array($this,"plugin_options"));
}


function current_user_can_view() {
	/**
	 * Default capability to grant ability to view Archived content (if the status is set to non public)
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	$capability = 'read_private_posts';

	return current_user_can( $capability );
}



function plugin_options() {

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

    // variables for the field and option names 


    $hidden_field_name = 'lh_archive_submit_hidden';
   

 // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value

$lh_archive_options[ $this->publicly_available ] = $_POST[ $this->publicly_available ];
$lh_archive_options[ $this->message_field_name ] = $_POST[ $this->message_field_name ];
$lh_archive_options[ $this->posttypes_field_name ] = $_POST[ $this->posttypes_field_name ];


        // Save the posted value in the database
	update_option( $this->options_name,  $lh_archive_options  );


        // Put an settings updated message on the screen



?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
<?php

    } else {

$lh_archive_options = get_option($this->options_name);



}

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'LH Archive Post Type Settings', 'menu-test' ) . "</h2>";

    // settings form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Can Archived Posts be read publicly:", 'lh-archived-post-status'); ?>
<select name="<?php echo $this->publicly_available; ?>" id="<?php echo $this->publicly_available; ?>">
<option value="1" <?php  if ($lh_archive_options[$this->publicly_available] == 1){ echo 'selected="selected"'; }  ?>>Yes - But not on the frontpage or feed</option>
<option value="0" <?php  if ($lh_archive_options[$this->publicly_available] == 0){ echo 'selected="selected"';}  ?>>No - only logged in users can view archived posts</option>
</select>
</p>



<p><strong><?php _e("Archive Message:", 'menu-test' ); ?></strong><br/>
This message will appear at the top of any archived post or page when viewed.<br/>
<textarea name="<?php echo $this->message_field_name; ?>" rows="20" cols="50">
<?php echo $lh_archive_options[$this->message_field_name] ; ?>
</textarea>
</p>

<p><strong>Set a list of default post types that can be archived.</strong><br/>
<?php

$posttypes = get_post_types(array('public'   => true ));

foreach ( $posttypes as $posttype ) {

echo "<input type=\"checkbox\" name=\"".$this->posttypes_field_name."[]\" value=\"".$posttype."\"";


if (in_array($posttype, $lh_archive_options[$this->posttypes_field_name])) {

echo "checked=\"checked\"";

}



echo " />".$posttype." ,";


}

?>
</p>



<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>



</div>

<?php
}




function create_archived_custom_post_status(){

$lh_archive_post_status_options = get_option($this->options_name);

if ($lh_archive_post_status_options[ $this->publicly_available ]){

$public = $lh_archive_post_status_options[ $this->publicly_available ];

} else {

$public = $this->current_user_can_view();

}



foreach (  $lh_archive_post_status_options[ $this->posttypes_field_name ] as $posttype ) {

     register_post_status( $this->newstatusname, array(
          'label'                     => _x( $this->newstatuslabel, $posttype ),
          'public'                    => $public,
          'show_in_admin_all_list'    => true,
          'show_in_admin_status_list' => true,
          'label_count'               => _n_noop( $this->newstatuslabel_count, $this->newstatuslabel_count) ) );

}

}

function add_archived_message($content){

global $post;

if (is_singular()){

if ($post->post_status == $this->newstatusname){

$lh_archive_post_status_options = get_option($this->options_name);

$content = $lh_archive_post_status_options[$this->message_field_name].$content;

}

}

return $content;

}

function add_posts_rows($actions,$post) {

if ($post->post_status == "publish"){

if ( current_user_can('edit_post', $post->ID) ) {

if ( current_user_can('publish_posts') ) {

$lh_archive_options = get_option($this->options_name);

if (in_array($post->post_type, $lh_archive_options[$this->posttypes_field_name])) {

$url = add_query_arg( 'lh_archived_post_status-archive_post', $post->ID );

$url = add_query_arg( 'lh_archived_post_status-archive_nonce', wp_create_nonce( 'lh_archived_post_status-archive_post'.$post->ID ), $url );


$actions['archive_link']  = '<a href="'.$url.'" title="' . esc_attr( __( 'Archive this post' ) ) . '">' . __( 'Archive' ) . '</a>';

}

}

return $actions;

}

} elseif ($post->post_status == $this->newstatusname){

unset($actions['edit']);

unset($actions['trash']);


}

return $actions;

}



function exclude_archive_post_status_from_main_query( $query ) {
	if ( $query->is_main_query() && $query->is_home() && !$query->is_search()) {

if ( current_user_can('read_private_posts') ) {

$post_status = array( 'publish', 'private' );

} else {

$post_status = array( 'publish');

}
		$query->set( 'post_status', $post_status );
	}
}


function exclude_archive_post_status_from_feed( $query ) {
	if ($query->is_feed){

if ( current_user_can('read_private_posts') ) {

$post_status = array( 'publish', 'private' );

} else {

$post_status = array( 'publish');

}
		$query->set( 'post_status', $post_status );
	}
}



function display_archive_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != $this->newstatusname){
          if($post->post_status == $this->newstatusname){
               return array(ucwords($this->newstatuslabel));
          }
     }
    return $states;
}

function handle_archiving(){

if($_GET['lh_archived_post_status-archive_post']){

if ( current_user_can('publish_posts') ) { 

if (wp_verify_nonce( $_GET['lh_archived_post_status-archive_nonce'], 'lh_archived_post_status-archive_post'.$_GET['lh_archived_post_status-archive_post'])){

  $my_post = array(
	'ID'           =>  $_GET['lh_archived_post_status-archive_post'],
	'post_status' => $this->newstatusname
  );

// Update the post into the database
  wp_update_post( $my_post );

}


}

}


}


function append_post_status_list(){
     global $post;
     $complete = '';
     $label = '';

$lh_archive_options = get_option($this->options_name);

if (in_array($post->post_type, $lh_archive_options[$this->posttypes_field_name])) {
          if($post->post_status == $this->newstatusname){
          echo '
          <script>
          jQuery(document).ready(function($){
$("select#post_status").append("<option value=\"'.$this->newstatusname.'\" selected=\"selected\">'.ucwords($this->newstatuslabel).'</option>");
$(".misc-pub-section label").append("<span id=\"post-status-display\"> '.ucwords($this->newstatuslabel).'</span>");
          });
          </script>
          ';
          } elseif ($post->post_status == "publish"){


          echo '
          <script>
          jQuery(document).ready(function($){
$("select#post_status").append("<option value=\"'.$this->newstatusname.'\" >'.ucwords($this->newstatuslabel).'</option>");
          });
          </script>
          ';

}

     }
} 

// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}


function __construct() {

$this->filename = plugin_basename( __FILE__ );

add_action( 'init', array($this,"create_archived_custom_post_status"));

add_filter( 'the_content', array($this,"add_archived_message"));

add_action('admin_menu', array($this,"plugin_menu"));

add_filter('page_row_actions',array($this,"add_posts_rows"),10,2);

add_filter('post_row_actions',array($this,"add_posts_rows"),10,2);

add_action( 'pre_get_posts', array($this,"exclude_archive_post_status_from_main_query"));

add_action( 'pre_get_posts', array($this,"exclude_archive_post_status_from_feed"));

add_filter( 'display_post_states', array($this,"display_archive_state"));

add_action( 'plugins_loaded', array($this,"handle_archiving"));

add_action('admin_footer-post.php', array($this,"append_post_status_list"));

add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);

}


}


$lh_locked_post_status = new LH_archived_post_status_plugin();

?>
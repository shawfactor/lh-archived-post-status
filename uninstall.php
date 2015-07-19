<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$option_name = 'lh_archive_post_status_options';

delete_option( $option_name );

?>

<?php

/*********** FUNCTION TO GET PAGE ID FROM TEMPLATE NAME ***********/
// IF TEMPLATES ARE CREATED IN page-templates DIRECTORY, PASS 'page-templates/your-template-name' AS PARAMETER AT THE TIME OF CALLING THIS FUNCTION
function get_page_id_by_template_name($template_name){
	$page_array = get_pages(array(
	    'meta_key' => '_wp_page_template',
	    'meta_value' => $template_name
	));
	if($page_array){
		$page_id = $page_array[0]->ID;	
	}else{
		$page_id = get_option( 'page_on_front' );
	}	
	return $page_id;
}

/******** TO CHANGE GIVEN POST TYPE THUMBNAIL METABOX TITLE & OTHER DETAILS ********/
function textdomain_featured_metabox_title_change( $post_type, $context ) {
	if ( $post_type == 'yourposttype' && 'side' == $context ) {
		remove_meta_box( 'postimagediv', 'yourposttype', 'side' );
		add_meta_box( 'postimagediv', __( 'Title to replace' ), 'post_thumbnail_meta_box', 'yourposttype', 'side', 'low' );	
	}
} 
add_action( 'do_meta_boxes', 'textdomain_featured_metabox_title_change', 10, 2 );

function textdomain_update_media_view_featured_image_title( $settings, $post ) {
	if($post) {
		if ( 'yourposttype' == $post->post_type ) {
			$settings['setFeaturedImageTitle'] = __( "Title to replace" , 'textdomain' );
			$settings['setFeaturedImage']      = __( "Upload your media name" , 'textdomain' );
		}
	}	
	return $settings;
} 
add_filter( 'media_view_strings', 'textdomain_update_media_view_featured_image_title', 10, 2 );

function textdomain_change_fetured_image_link_text( $content, $post_id ) {
	$post = get_post($post_id);
	if ( 'yourposttype' == get_post_type($post) ) {
		$content = str_replace( 'Set featured image', __( "Upload your media name" , 'textdomain' ), $content );
		$content = str_replace( 'Remove featured image', __( "Remove your media name" , 'textdomain' ), $content );
	}
	return $content;
}
add_filter( 'admin_post_thumbnail_html', 'textdomain_change_fetured_image_link_text', 10, 2 );
/******** TO CHANGE GIVEN POST TYPE THUMBNAIL METABOX TITLE & OTHER DETAILS ********/

/********* POST THUMBNAIL UPLOADING FROM THE FRONT END *********/
function textdomain_thumbnail_uploading_function( $file, $post_id, $set_as_featured ) {
	if ( !function_exists('wp_handle_upload') ) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	}
    $upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );
    $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );
    $wp_upload_dir = wp_upload_dir();    
    $attachment = array(
        'guid'           => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ),
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename( $upload['file'] )),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    wp_update_attachment_metadata( $attachment_id, $attach_data );
    if( $set_as_featured == true ) {
    	set_post_thumbnail( $post_id, $attachment_id );
    }
}
/********* POST THUMBNAIL UPLOADING FROM THE FRONT END *********/

/********* WPML SHOW COMMENTS FROM ALL LANGUAGES START *********/
global $sitepress;
remove_filter( 'comments_clauses', array( $sitepress, 'comments_clauses' ), 10, 2 );
add_action('pre_get_comments', function($c){
    $id = [];
    $languages = apply_filters('wpml_active_languages', '');
    if( 1 < count($languages) ){
        foreach( $languages as $l ){
            $id[] = apply_filters( 'wpml_object_id', $c->query_vars['post_id'], 'page', FALSE, $l['code']);
        }
    }
    $c->query_vars['post_id'] = '';
    $c->query_vars['post__in'] = $id;
    return $c;
});
/********* WPML SHOW COMMENTS FROM ALL LANGUAGES END *********/

?>
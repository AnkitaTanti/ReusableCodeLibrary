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
?>
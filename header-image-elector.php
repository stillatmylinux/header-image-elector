<?php
/*
Plugin Name: Header Image Elector
Plugin URI: https://github.com/stillatmylinux/header-image-elector
Description: Modify your header image
Text Domain: header-image-elector
Domain Path: /languages
Version: 1.0
Author: Matt Thiessen
Author URI: https://matt.thiessen.us/
License: MIT
*/

class HeaderImageElector {

	public function hooks() {
		add_filter( 'theme_mod_header_image', array( $this, 'maybe_remove_header_image' ) );
		
		add_action( "add_meta_boxes_post", array( $this, 'add_meta_box' ) );
		add_action( "add_meta_boxes_page", array( $this, 'add_meta_box' ) );

		add_action( "save_post", array( $this, 'save' ), 10, 2 );
		add_action( "save_page", array( $this, 'save' ), 10, 2 );
	}

	/**
	 * When the get_header_image() is used in the template,
	 * we get the post meta for the option to hide the header
	 * image.  The theme_mod hooks will hide the image if it 
	 * receives 'remove-header' in the theme_mod filter.
	 * 
	 * @param $img_url The image URL if it exists
	 * 
	 * @return $url_img The URL or 'remove-header'
	 */
	public function maybe_remove_header_image( $img_url ) {

		global $post;

		if( isset( $post, $post->ID ) ) {

			$meta_key = 'header_img_elector';

			$remove_header_img = get_post_meta( $post->ID, $meta_key, true );
			if( $remove_header_img ) {
				return 'remove-header';
			}
		}

		return $img_url;
	}

	/**
	 * Add the meta box to a post edit page
	 */
	public function add_meta_box( $post ) {
		add_meta_box(
			'headerimgelector',
			'Header Image Elector',
			array( $this, 'elector_metabox' ),
			$post->post_type,
			'side',
			'high'
		);
	}

	/**
	 * Add the content of the meta box
	 */
	public function elector_metabox( $post ) {

		$meta_key = 'header_img_elector';

		$checked = get_post_meta( $post->ID, $meta_key, true );

		echo '<table id="header-img-elector-options" class="widefat">';

		echo '<tr><td><input type="checkbox" name="'.$meta_key.'" value="1" '.checked( $checked, "1", false ).'></td><td>';
		echo 'Remove the header image from this post';
		echo wp_nonce_field( 'mrhi_text_description', 'mrhi_nonce' ) . '</td></tr>';
		
		echo '</table>';
	}

	/**
	 * Hook in the post save function to save or delete the 
	 * value from our meta box checkbox
	 */
	public function save( $post_id, $post ) {

		$meta_key = 'header_img_elector';

		if( isset( $_POST[$meta_key] ) && $_POST[$meta_key] == '1' ) {
			update_post_meta( $post_id, $meta_key, '1' );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}

}

$header_image_elector = new HeaderImageElector();
$header_image_elector->hooks();
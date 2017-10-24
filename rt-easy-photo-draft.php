<?php
/*
Plugin Name: RT Easy Photo Draft
Plugin URI: https://photography.roytanck.com
Description: Autmatically creates draft posts for image uploads
Version: 0.1
Author: Roy Tanck
Author URI: http://www.this-play.nl
Text Domain: rt-easy-photo-draft
Domain Path: /languages
*/

/*
Copyright Roy Tanck (email: roy.tanck@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


/*
	Create a draft post for every image uploaded
	Based on: https://wordpress.stackexchange.com/a/205400
*/
if( !function_exists('rtepd_create_draft_from_image') ){

	function rtepd_create_draft_from_image( $id ) {
		// check if PHP support for EXIF data is enabled
		if( function_exists( 'exif_read_data' ) ){
			// check if the attachment is an image
			if( wp_attachment_is_image( $id ) ){
				// get the attachement's post object
				$image = get_post( $id );
				// read all the EXIF data
				$exif = exif_read_data( $image->guid );
				// check if the original date is set (capture time)
				if( isset( $exif ) && !empty( $exif ) && !empty( $exif['DateTimeOriginal'] ) ){
					// create a new post object
					$post = array(
						// Set image title as post title
						'post_title'   => $image->post_title,
						// Set post to draft for details
						'post_status'  => 'draft',
						// Placeholder content
						'post_content' => 'Insert image description here...',
						// set the post's date to the EXIF original date
						'post_date'    => $exif['DateTimeOriginal']
					);
					// insert the post into WP's database
					$postid = wp_insert_post( $post );
					// check if successful
					if ( $postid ) {
						// set image as post featured image
						set_post_thumbnail( $postid, $image->ID );
						// attach image to the post
						wp_update_post( array(
							'ID'          => $id,
							'post_parent' => $postid
						));
						// store the image's EXIF date in a post meta field for use later
						update_post_meta( $postid, 'rt-exif-date', $exif['DateTimeOriginal'] );
					}
				}
			}
		}
	}

	add_action( 'add_attachment', 'rtepd_create_draft_from_image' );
}


/*
	Use 'wp_insert_post_data' to set the post's date each time it is saved
*/
if( !function_exists('rtepd_pre_post_update') ){

	function rtepd_pre_post_update( $data, $postarr ){
		// check if the post type is 'post'
		if( $data['post_type'] != 'post' ){
			return $data;
		}
		// get the post's ID
		$postid = $postarr['ID'];
		// get the EXIF date we stored in 'rtepd_create_draft_from_image'
		$exifdate = get_post_meta( $postid, 'rt-exif-date', true );
		// if a date is avaiable, set it.
		if( !empty( $exifdate ) ){
			$data['post_date'] = $exifdate;
		}
		// this is a filter action, so return the modified(?) post data
		return $data;
	}; 

	add_action( 'wp_insert_post_data', 'rtepd_pre_post_update', 10, 2 ); 
}

?>
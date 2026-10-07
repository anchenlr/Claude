<?php
/**
 * Plugin Name:       PBS Featured Images for MCP
 * Description:       Adds three MCP abilities so Claude can find posts, find media and set a post's featured image. Requires the MCP Adapter plugin and WordPress 6.9+.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_abilities_api_categories_init', function () {
	wp_register_ability_category(
		'pbs-content',
		array(
			'label'       => 'Content & Media',
			'description' => 'Find posts and media, and manage featured images.',
		)
	);
} );

add_action( 'wp_abilities_api_init', function () {

	// 1. Find posts by title/keyword, showing each post's current featured image.
	wp_register_ability(
		'pbs/find-posts',
		array(
			'label'               => 'Find Posts',
			'description'         => 'Search posts by keyword in the title or content. Returns post ID, title, status, link and current featured image. Use this to get the post_id before setting a featured image.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'search'    => array(
						'type'        => 'string',
						'description' => 'Keyword to search for. Leave empty to list the most recent posts.',
						'default'     => '',
					),
					'post_type' => array(
						'type'        => 'string',
						'description' => 'Post type to search, e.g. post or page.',
						'default'     => 'post',
					),
					'limit'     => array(
						'type'        => 'integer',
						'description' => 'Max results (1-50).',
						'default'     => 10,
					),
				),
			),
			'execute_callback'    => function ( $input ) {
				$query = new WP_Query(
					array(
						'post_type'      => sanitize_key( $input['post_type'] ?? 'post' ),
						'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
						's'              => sanitize_text_field( $input['search'] ?? '' ),
						'posts_per_page' => max( 1, min( 50, (int) ( $input['limit'] ?? 10 ) ) ),
						'no_found_rows'  => true,
					)
				);

				$posts = array();
				foreach ( $query->posts as $post ) {
					$thumb_id = get_post_thumbnail_id( $post );
					$posts[]  = array(
						'post_id'        => $post->ID,
						'title'          => get_the_title( $post ),
						'status'         => $post->post_status,
						'link'           => get_permalink( $post ),
						'featured_image' => $thumb_id ? array(
							'attachment_id' => $thumb_id,
							'url'           => wp_get_attachment_url( $thumb_id ),
						) : null,
					);
				}
				return array( 'posts' => $posts );
			},
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'public'       => true,
				'show_in_rest' => true,
				'annotations'  => array(
					'readOnlyHint'    => true,
					'destructiveHint' => false,
				),
			),
		)
	);

	// 2. Find images already in the media library.
	wp_register_ability(
		'pbs/find-media',
		array(
			'label'               => 'Find Media',
			'description'         => 'Search images in the media library by file name or title. Returns attachment ID, title, URL and alt text.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'search' => array(
						'type'        => 'string',
						'description' => 'Keyword to search for. Leave empty to list the most recent images.',
						'default'     => '',
					),
					'limit'  => array(
						'type'        => 'integer',
						'description' => 'Max results (1-50).',
						'default'     => 10,
					),
				),
			),
			'execute_callback'    => function ( $input ) {
				$query = new WP_Query(
					array(
						'post_type'      => 'attachment',
						'post_status'    => 'inherit',
						'post_mime_type' => 'image',
						's'              => sanitize_text_field( $input['search'] ?? '' ),
						'posts_per_page' => max( 1, min( 50, (int) ( $input['limit'] ?? 10 ) ) ),
						'no_found_rows'  => true,
					)
				);

				$images = array();
				foreach ( $query->posts as $attachment ) {
					$images[] = array(
						'attachment_id' => $attachment->ID,
						'title'         => get_the_title( $attachment ),
						'url'           => wp_get_attachment_url( $attachment->ID ),
						'alt'           => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
					);
				}
				return array( 'images' => $images );
			},
			'permission_callback' => function () {
				return current_user_can( 'upload_files' );
			},
			'meta'                => array(
				'public'       => true,
				'show_in_rest' => true,
				'annotations'  => array(
					'readOnlyHint'    => true,
					'destructiveHint' => false,
				),
			),
		)
	);

	// 3. Set a post's featured image, from the media library or a public image URL.
	wp_register_ability(
		'pbs/set-featured-image',
		array(
			'label'               => 'Set Featured Image',
			'description'         => 'Set the featured image of a post. Provide either attachment_id (an image already in the media library) or image_url (a public image URL, which is downloaded into the media library first). Optionally sets alt text.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'post_id'       => array(
						'type'        => 'integer',
						'description' => 'ID of the post to update.',
					),
					'attachment_id' => array(
						'type'        => 'integer',
						'description' => 'ID of an existing image in the media library.',
						'default'     => 0,
					),
					'image_url'     => array(
						'type'        => 'string',
						'description' => 'Public URL of an image to import (jpg, jpeg, png, gif, webp).',
						'default'     => '',
					),
					'alt_text'      => array(
						'type'        => 'string',
						'description' => 'Optional alt text to save on the image.',
						'default'     => '',
					),
				),
				'required'   => array( 'post_id' ),
			),
			'execute_callback'    => function ( $input ) {
				$post_id = (int) ( $input['post_id'] ?? 0 );
				$post    = get_post( $post_id );
				if ( ! $post ) {
					return new WP_Error( 'pbs_post_not_found', 'No post found with that ID.' );
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return new WP_Error( 'pbs_forbidden', 'You are not allowed to edit this post.' );
				}

				$attachment_id = (int) ( $input['attachment_id'] ?? 0 );
				$image_url     = esc_url_raw( $input['image_url'] ?? '' );

				if ( ! $attachment_id && ! $image_url ) {
					return new WP_Error( 'pbs_no_image', 'Provide either attachment_id or image_url.' );
				}

				if ( ! $attachment_id ) {
					if ( ! current_user_can( 'upload_files' ) ) {
						return new WP_Error( 'pbs_forbidden', 'You are not allowed to upload files.' );
					}
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';

					$attachment_id = media_sideload_image( $image_url, $post_id, get_the_title( $post ), 'id' );
					if ( is_wp_error( $attachment_id ) ) {
						return new WP_Error( 'pbs_import_failed', 'Could not import the image: ' . $attachment_id->get_error_message() );
					}
				}

				if ( ! wp_attachment_is_image( $attachment_id ) ) {
					return new WP_Error( 'pbs_not_image', 'That attachment is not an image.' );
				}

				if ( ! empty( $input['alt_text'] ) ) {
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $input['alt_text'] ) );
				}

				set_post_thumbnail( $post_id, $attachment_id );

				return array(
					'post_id'       => $post_id,
					'post_title'    => get_the_title( $post ),
					'attachment_id' => $attachment_id,
					'image_url'     => wp_get_attachment_url( $attachment_id ),
					'edit_link'     => get_edit_post_link( $post_id, 'raw' ),
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'public'       => true,
				'show_in_rest' => true,
				'annotations'  => array(
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
				),
			),
		)
	);
} );

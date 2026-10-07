<?php
/**
 * Plugin Name:       PBS Featured Images for MCP
 * Description:       Adds MCP abilities so Claude can find posts and media, and set featured images one by one or in bulk by matching image file names. Requires the MCP Adapter plugin and WordPress 6.9+.
 * Version:           1.1.0
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
					'category'  => array(
						'type'        => 'string',
						'description' => 'Optional category name, slug or ID to filter by, e.g. 9.0.',
						'default'     => '',
					),
					'limit'     => array(
						'type'        => 'integer',
						'description' => 'Max results (1-50).',
						'default'     => 10,
					),
				),
			),
			'execute_callback'    => function ( $input ) {
				$category = pbs_resolve_category( $input['category'] ?? '' );
				if ( is_wp_error( $category ) ) {
					return $category;
				}
				$query = new WP_Query(
					array(
						'cat'            => $category ? $category->term_id : 0,
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
	// 4. Bulk: set featured images for every post in a category by matching image file names.
	wp_register_ability(
		'pbs/bulk-featured-images-by-name',
		array(
			'label'               => 'Bulk Set Featured Images by Name',
			'description'         => 'For every post in a category, find the media library image whose file name (or title) matches the post\'s name, and set it as the featured image. The name comes from a custom field (e.g. Speaker Name) and falls back to the post title with a trailing year like "-2026" removed. Runs as a preview by default (dry_run = true): review the matches, then run again with dry_run = false to apply.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'category'  => array(
						'type'        => 'string',
						'description' => 'Category name, slug or ID, e.g. 9.0.',
					),
					'meta_key'  => array(
						'type'        => 'string',
						'description' => 'Custom field key holding the name, e.g. speaker_name. If empty or blank on a post, the post title (minus a trailing year) is used.',
						'default'     => 'speaker_name',
					),
					'post_type' => array(
						'type'        => 'string',
						'description' => 'Post type, usually post.',
						'default'     => 'post',
					),
					'overwrite' => array(
						'type'        => 'boolean',
						'description' => 'Replace featured images that are already set. Default false (skip them).',
						'default'     => false,
					),
					'dry_run'   => array(
						'type'        => 'boolean',
						'description' => 'Preview only, change nothing. Default true.',
						'default'     => true,
					),
				),
				'required'   => array( 'category' ),
			),
			'execute_callback'    => function ( $input ) {
				$category = pbs_resolve_category( $input['category'] ?? '' );
				if ( is_wp_error( $category ) ) {
					return $category;
				}
				if ( ! $category ) {
					return new WP_Error( 'pbs_no_category', 'Please provide a category.' );
				}

				$meta_key  = sanitize_text_field( $input['meta_key'] ?? 'speaker_name' );
				$overwrite = ! empty( $input['overwrite'] );
				$dry_run   = ! isset( $input['dry_run'] ) || (bool) $input['dry_run'];

				$post_ids = get_posts(
					array(
						'cat'            => $category->term_id,
						'post_type'      => sanitize_key( $input['post_type'] ?? 'post' ),
						'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);

				$media   = pbs_media_name_index();
				$results = array(
					'category'   => $category->name . ' (ID ' . $category->term_id . ')',
					'dry_run'    => $dry_run,
					'total'      => count( $post_ids ),
					'updated'    => array(),
					'skipped'    => array(),
					'no_match'   => array(),
				);

				foreach ( $post_ids as $post_id ) {
					$title      = get_the_title( $post_id );
					$meta_value = $meta_key ? trim( (string) get_post_meta( $post_id, $meta_key, true ) ) : '';
					$names      = array_filter( array_unique( array( $meta_value, pbs_strip_year( $title ) ) ) );

					$row = array(
						'post_id' => $post_id,
						'title'   => $title,
						'name'    => $meta_value ? $meta_value : pbs_strip_year( $title ),
					);

					if ( has_post_thumbnail( $post_id ) && ! $overwrite ) {
						$row['reason']          = 'Already has a featured image';
						$results['skipped'][] = $row;
						continue;
					}

					$attachment_id = 0;
					foreach ( $names as $name ) {
						$key = pbs_normalize_name( $name );
						if ( isset( $media[ $key ] ) ) {
							$attachment_id = $media[ $key ];
							break;
						}
					}

					if ( ! $attachment_id ) {
						$row['looked_for']       = array_values( array_map( 'pbs_normalize_name', $names ) );
						$results['no_match'][] = $row;
						continue;
					}

					$row['attachment_id'] = $attachment_id;
					$row['image_file']    = wp_basename( (string) get_attached_file( $attachment_id ) );

					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						$row['reason']          = 'No permission to edit this post';
						$results['skipped'][] = $row;
						continue;
					}

					if ( ! $dry_run ) {
						set_post_thumbnail( $post_id, $attachment_id );
					}
					$results['updated'][] = $row;
				}

				$results['summary'] = sprintf(
					'%s%d of %d posts %s, %d skipped, %d with no matching image.',
					$dry_run ? 'PREVIEW: ' : '',
					count( $results['updated'] ),
					$results['total'],
					$dry_run ? 'would be updated' : 'updated',
					count( $results['skipped'] ),
					count( $results['no_match'] )
				);

				return $results;
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

/**
 * Resolve a category from a name, slug or numeric ID. Returns null when empty.
 *
 * @param string|int $value Category name, slug or ID.
 * @return WP_Term|WP_Error|null
 */
function pbs_resolve_category( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return null;
	}

	$term = ctype_digit( $value ) ? get_term( (int) $value, 'category' ) : null;
	if ( ! $term || is_wp_error( $term ) ) {
		$term = get_term_by( 'name', $value, 'category' );
	}
	if ( ! $term ) {
		$term = get_term_by( 'slug', sanitize_title( $value ), 'category' );
	}

	return $term ? $term : new WP_Error( 'pbs_category_not_found', sprintf( 'No category found matching "%s".', $value ) );
}

/**
 * Remove a trailing year such as "-2026" or " – 2026" from a title.
 *
 * @param string $title Post title.
 * @return string
 */
function pbs_strip_year( $title ) {
	$title = html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' );
	return trim( preg_replace( '/\s*[-–—]\s*(19|20)\d{2}\s*$/u', '', $title ) );
}

/**
 * Normalise a name or file name for matching: "Jane O'Brien" and "jane-obrien.jpg" both become "jane-obrien".
 *
 * @param string $name Name or file name.
 * @return string
 */
function pbs_normalize_name( $name ) {
	$name = preg_replace( '/\.(jpe?g|png|gif|webp|avif)$/i', '', (string) $name );
	return sanitize_title( remove_accents( $name ) );
}

/**
 * Map normalised image file names and titles to attachment IDs.
 *
 * @return array<string,int>
 */
function pbs_media_name_index() {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	$index    = array();
	$fallback = array();
	foreach ( $ids as $id ) {
		$file = pbs_normalize_name( wp_basename( (string) get_post_meta( $id, '_wp_attached_file', true ) ) );
		$file = preg_replace( '/-scaled$/', '', $file );

		// Exact file name wins; newest upload wins on ties.
		if ( $file ) {
			$index[ $file ] = $id;
			// "jane-smith-1" (a duplicate upload) also answers for "jane-smith" if nothing else does.
			$fallback[ preg_replace( '/-\d+$/', '', $file ) ] = $id;
		}
		$title = pbs_normalize_name( get_the_title( $id ) );
		if ( $title ) {
			$fallback[ $title ] = $id;
		}
	}

	return $index + $fallback;
}

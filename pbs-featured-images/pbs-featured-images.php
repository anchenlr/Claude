<?php
/**
 * Plugin Name:       PBS Featured Images for MCP
 * Description:       Adds MCP abilities so Claude can find posts and media, set featured images (one by one or in bulk by file name), bulk-assign terms and apply a speaker schedule (day + time). Also shows Speaker Categories in the admin list, Quick Edit and Bulk Edit. Requires the MCP Adapter plugin and WordPress 6.9+.
 * Version:           1.4.0
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

// Show speaker_categories like normal categories: admin list column, Quick/Bulk Edit checkboxes and the block editor panel.
add_filter( 'register_taxonomy_args', function ( $args, $taxonomy ) {
	if ( 'speaker_categories' === $taxonomy ) {
		$args['show_ui']            = true;
		$args['show_admin_column']  = true;
		$args['show_in_quick_edit'] = true;
		$args['show_in_rest']       = true;
	}
	return $args;
}, 99, 2 );

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
						'description' => 'Optional term name, slug or ID to filter by, e.g. PBS 9.0 or pbs-9-0.',
						'default'     => '',
					),
					'taxonomy'  => array(
						'type'        => 'string',
						'description' => 'Taxonomy the term belongs to, e.g. category or speaker_categories. Leave empty to search every taxonomy of the post type.',
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
				$post_type = sanitize_key( $input['post_type'] ?? 'post' );
				$category  = pbs_resolve_category( $input['category'] ?? '', $input['taxonomy'] ?? '', $post_type );
				if ( is_wp_error( $category ) ) {
					return $category;
				}
				$query = new WP_Query(
					array(
						'tax_query'      => pbs_term_tax_query( $category ),
						'post_type'      => $post_type,
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
			'description'         => 'For every post in a category or custom taxonomy term (e.g. speaker posts in speaker_categories), find the media library image whose file name (or title) matches the post\'s name, and set it as the featured image. The name comes from a custom field (e.g. Speaker Name) and falls back to the post title with a trailing year like "-2026" removed. Runs as a preview by default (dry_run = true): review the matches, then run again with dry_run = false to apply.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'category'  => array(
						'type'        => 'string',
						'description' => 'Term name, slug or ID, e.g. PBS 9.0 or pbs-9-0.',
					),
					'taxonomy'  => array(
						'type'        => 'string',
						'description' => 'Taxonomy the term belongs to, e.g. speaker_categories. Leave empty to search every taxonomy of the post type.',
						'default'     => '',
					),
					'meta_key'  => array(
						'type'        => 'string',
						'description' => 'Custom field key holding the name, e.g. speaker_name. If empty or blank on a post, the post title (minus a trailing year) is used.',
						'default'     => 'speaker_name',
					),
					'post_type' => array(
						'type'        => 'string',
						'description' => 'Post type, e.g. post or speaker.',
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
				$post_type = sanitize_key( $input['post_type'] ?? 'post' );
				$category  = pbs_resolve_category( $input['category'] ?? '', $input['taxonomy'] ?? '', $post_type );
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
						'tax_query'      => pbs_term_tax_query( $category ),
						'post_type'      => $post_type,
						'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);

				$media   = pbs_media_name_index();
				$results = array(
					'category'   => $category->name . ' (' . $category->taxonomy . ', ID ' . $category->term_id . ')',
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

	// 5. Bulk-assign taxonomy terms (e.g. speaker categories) to posts.
	wp_register_ability(
		'pbs/assign-terms',
		array(
			'label'               => 'Assign Terms to Posts',
			'description'         => 'Add, replace or remove taxonomy terms (e.g. speaker_categories like PBS 9.0) on a list of posts. Pick posts by post_ids, or by search keyword and/or an existing term via from_term. Runs as a preview by default (dry_run = true): review, then run again with dry_run = false.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'taxonomy'  => array(
						'type'        => 'string',
						'description' => 'Taxonomy to change, e.g. speaker_categories or category.',
					),
					'terms'     => array(
						'type'        => 'array',
						'items'       => array( 'type' => 'string' ),
						'description' => 'Term names, slugs or IDs to apply, e.g. ["pbs-9-0"]. Terms must already exist.',
					),
					'mode'      => array(
						'type'        => 'string',
						'enum'        => array( 'add', 'replace', 'remove' ),
						'description' => 'add = keep existing terms and add these; replace = set exactly these; remove = take these off.',
						'default'     => 'add',
					),
					'post_type' => array(
						'type'        => 'string',
						'description' => 'Post type, e.g. speaker or post.',
						'default'     => 'post',
					),
					'post_ids'  => array(
						'type'        => 'array',
						'items'       => array( 'type' => 'integer' ),
						'description' => 'Exact post IDs to change. If given, search and from_term are ignored.',
						'default'     => array(),
					),
					'search'    => array(
						'type'        => 'string',
						'description' => 'Keyword to select posts by title/content, e.g. -2026.',
						'default'     => '',
					),
					'from_term' => array(
						'type'        => 'string',
						'description' => 'Only posts that already have this term (in the same taxonomy), e.g. pbs-8-0.',
						'default'     => '',
					),
					'dry_run'   => array(
						'type'        => 'boolean',
						'description' => 'Preview only, change nothing. Default true.',
						'default'     => true,
					),
				),
				'required'   => array( 'taxonomy', 'terms' ),
			),
			'execute_callback'    => function ( $input ) {
				$taxonomy  = sanitize_key( $input['taxonomy'] ?? '' );
				$post_type = sanitize_key( $input['post_type'] ?? 'post' );
				$mode      = in_array( $input['mode'] ?? 'add', array( 'add', 'replace', 'remove' ), true ) ? $input['mode'] : 'add';
				$dry_run   = ! isset( $input['dry_run'] ) || (bool) $input['dry_run'];

				if ( ! taxonomy_exists( $taxonomy ) ) {
					return new WP_Error( 'pbs_taxonomy_not_found', sprintf( 'No taxonomy called "%s".', $taxonomy ) );
				}

				$term_ids = array();
				foreach ( (array) ( $input['terms'] ?? array() ) as $value ) {
					$term = pbs_resolve_category( $value, $taxonomy );
					if ( is_wp_error( $term ) || ! $term ) {
						return is_wp_error( $term ) ? $term : new WP_Error( 'pbs_no_terms', 'Provide at least one term.' );
					}
					$term_ids[] = (int) $term->term_id;
				}
				if ( ! $term_ids ) {
					return new WP_Error( 'pbs_no_terms', 'Provide at least one term.' );
				}

				$post_ids = array_filter( array_map( 'intval', (array) ( $input['post_ids'] ?? array() ) ) );
				if ( ! $post_ids ) {
					$from_term = pbs_resolve_category( $input['from_term'] ?? '', $taxonomy );
					if ( is_wp_error( $from_term ) ) {
						return $from_term;
					}
					$search = sanitize_text_field( $input['search'] ?? '' );
					if ( ! $from_term && '' === $search ) {
						return new WP_Error( 'pbs_no_selection', 'Choose posts with post_ids, search or from_term.' );
					}
					$post_ids = get_posts(
						array(
							'post_type'      => $post_type,
							'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
							's'              => $search,
							'tax_query'      => pbs_term_tax_query( $from_term ),
							'posts_per_page' => -1,
							'fields'         => 'ids',
						)
					);
				}

				$changed = array();
				$skipped = array();
				foreach ( $post_ids as $post_id ) {
					$post = get_post( $post_id );
					if ( ! $post || ! is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
						$skipped[] = array( 'post_id' => $post_id, 'reason' => 'Post not found or does not use this taxonomy' );
						continue;
					}
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						$skipped[] = array( 'post_id' => $post_id, 'reason' => 'No permission to edit this post' );
						continue;
					}

					$before = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
					$before = is_wp_error( $before ) ? array() : array_map( 'intval', $before );
					if ( 'replace' === $mode ) {
						$after = $term_ids;
					} elseif ( 'remove' === $mode ) {
						$after = array_values( array_diff( $before, $term_ids ) );
					} else {
						$after = array_values( array_unique( array_merge( $before, $term_ids ) ) );
					}

					$row = array(
						'post_id' => $post_id,
						'title'   => get_the_title( $post_id ),
						'before'  => pbs_term_names( $before, $taxonomy ),
						'after'   => pbs_term_names( $after, $taxonomy ),
					);

					sort( $before );
					$sorted_after = $after;
					sort( $sorted_after );
					if ( $before === $sorted_after ) {
						$row['reason'] = 'No change needed';
						$skipped[]     = $row;
						continue;
					}

					if ( ! $dry_run ) {
						$result = wp_set_object_terms( $post_id, $after, $taxonomy );
						if ( is_wp_error( $result ) ) {
							$row['reason'] = $result->get_error_message();
							$skipped[]     = $row;
							continue;
						}
					}
					$changed[] = $row;
				}

				return array(
					'dry_run' => $dry_run,
					'summary' => sprintf(
						'%s%d posts %s, %d skipped.',
						$dry_run ? 'PREVIEW: ' : '',
						count( $changed ),
						$dry_run ? 'would change' : 'changed',
						count( $skipped )
					),
					'changed' => $changed,
					'skipped' => $skipped,
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

	// 6. Apply a speaker schedule: set each speaker's Day term and scheduled_time field.
	wp_register_ability(
		'pbs/apply-schedule',
		array(
			'label'               => 'Apply Speaker Schedule',
			'description'         => 'Apply a schedule to speaker posts. For each row (speaker, day, time) it finds the speaker post by name (Speaker Name field or title without the trailing year) or post ID, sets the Day term (removing any other Day term) and saves the time as text in the scheduled_time field. Use only_term (e.g. pbs-9-0) so speakers from previous years are not matched. Runs as a preview by default (dry_run = true): review, then run again with dry_run = false.',
			'category'            => 'pbs-content',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'schedule'   => array(
						'type'        => 'array',
						'description' => 'One row per speaker.',
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'speaker' => array(
									'type'        => 'string',
									'description' => 'Speaker name (e.g. Tim Strifler) or post ID.',
								),
								'day'     => array(
									'type'        => 'string',
									'description' => 'Day term name, slug or ID (e.g. Day 1). Leave empty to leave the day unchanged.',
									'default'     => '',
								),
								'time'    => array(
									'type'        => 'string',
									'description' => 'Scheduled time, saved exactly as given (e.g. 10:00am EST). Leave empty to leave it unchanged.',
									'default'     => '',
								),
							),
							'required'   => array( 'speaker' ),
						),
					),
					'only_term'  => array(
						'type'        => 'string',
						'description' => 'Only match speakers that have this term, e.g. pbs-9-0. Strongly recommended.',
						'default'     => '',
					),
					'taxonomy'   => array(
						'type'        => 'string',
						'description' => 'Taxonomy holding the Day terms (and only_term).',
						'default'     => 'speaker_categories',
					),
					'day_prefix' => array(
						'type'        => 'string',
						'description' => 'Other terms on the speaker whose name starts with this are treated as days and removed when a new day is set. Empty = never remove.',
						'default'     => 'Day',
					),
					'time_field' => array(
						'type'        => 'string',
						'description' => 'Custom field name for the time.',
						'default'     => 'scheduled_time',
					),
					'name_field' => array(
						'type'        => 'string',
						'description' => 'Custom field holding the speaker name.',
						'default'     => 'speaker_name',
					),
					'post_type'  => array(
						'type'        => 'string',
						'description' => 'Post type of speakers.',
						'default'     => 'speaker',
					),
					'dry_run'    => array(
						'type'        => 'boolean',
						'description' => 'Preview only, change nothing. Default true.',
						'default'     => true,
					),
				),
				'required'   => array( 'schedule' ),
			),
			'execute_callback'    => function ( $input ) {
				$taxonomy   = sanitize_key( $input['taxonomy'] ?? 'speaker_categories' );
				$post_type  = sanitize_key( $input['post_type'] ?? 'speaker' );
				$day_prefix = trim( (string) ( $input['day_prefix'] ?? 'Day' ) );
				$time_field = sanitize_key( $input['time_field'] ?? 'scheduled_time' );
				$name_field = sanitize_key( $input['name_field'] ?? 'speaker_name' );
				$dry_run    = ! isset( $input['dry_run'] ) || (bool) $input['dry_run'];

				if ( ! taxonomy_exists( $taxonomy ) ) {
					return new WP_Error( 'pbs_taxonomy_not_found', sprintf( 'No taxonomy called "%s".', $taxonomy ) );
				}
				$only_term = pbs_resolve_category( $input['only_term'] ?? '', $taxonomy );
				if ( is_wp_error( $only_term ) ) {
					return $only_term;
				}

				// Index candidate speakers by normalised name.
				$candidates = get_posts(
					array(
						'post_type'      => $post_type,
						'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
						'tax_query'      => pbs_term_tax_query( $only_term ),
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);
				$by_name = array();
				foreach ( $candidates as $id ) {
					$keys = array(
						pbs_normalize_name( (string) get_post_meta( $id, $name_field, true ) ),
						pbs_normalize_name( pbs_strip_year( get_the_title( $id ) ) ),
					);
					foreach ( array_unique( array_filter( $keys ) ) as $key ) {
						$by_name[ $key ][] = $id;
					}
				}

				$changed   = array();
				$unchanged = array();
				$problems  = array();
				$day_cache = array();

				foreach ( (array) ( $input['schedule'] ?? array() ) as $row ) {
					$speaker = trim( (string) ( $row['speaker'] ?? '' ) );
					$day     = trim( (string) ( $row['day'] ?? '' ) );
					$time    = sanitize_text_field( (string) ( $row['time'] ?? '' ) );

					// Find the speaker post.
					if ( ctype_digit( $speaker ) ) {
						$matches = in_array( (int) $speaker, $candidates, true ) ? array( (int) $speaker ) : array();
					} else {
						$matches = array_values( array_unique( $by_name[ pbs_normalize_name( $speaker ) ] ?? array() ) );
					}
					if ( 1 !== count( $matches ) ) {
						$problems[] = array(
							'speaker' => $speaker,
							'reason'  => $matches
								? 'More than one speaker matches (post IDs ' . implode( ', ', $matches ) . '). Use only_term or the post ID.'
								: 'No speaker found with this name' . ( $only_term ? ' in ' . $only_term->name : '' ) . '.',
						);
						continue;
					}
					$post_id = $matches[0];

					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						$problems[] = array( 'speaker' => $speaker, 'reason' => 'No permission to edit this post.' );
						continue;
					}

					// Resolve the day term.
					$day_term = null;
					if ( '' !== $day ) {
						if ( ! isset( $day_cache[ $day ] ) ) {
							$day_cache[ $day ] = pbs_resolve_category( $day, $taxonomy );
						}
						$day_term = $day_cache[ $day ];
						if ( is_wp_error( $day_term ) ) {
							$problems[] = array( 'speaker' => $speaker, 'reason' => $day_term->get_error_message() );
							continue;
						}
					}

					$before_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
					$before_ids = is_wp_error( $before_ids ) ? array() : array_map( 'intval', $before_ids );
					$after_ids  = $before_ids;
					if ( $day_term ) {
						if ( '' !== $day_prefix ) {
							$after_ids = array_values(
								array_filter(
									$after_ids,
									function ( $id ) use ( $taxonomy, $day_prefix, $day_term ) {
										$term = get_term( $id, $taxonomy );
										return $id === (int) $day_term->term_id
											|| ! $term || is_wp_error( $term )
											|| 0 !== stripos( $term->name, $day_prefix );
									}
								)
							);
						}
						if ( ! in_array( (int) $day_term->term_id, $after_ids, true ) ) {
							$after_ids[] = (int) $day_term->term_id;
						}
					}

					$time_before = (string) get_post_meta( $post_id, $time_field, true );
					$time_after  = '' !== $time ? $time : $time_before;

					$sorted_before = $before_ids;
					$sorted_after  = $after_ids;
					sort( $sorted_before );
					sort( $sorted_after );

					$result = array(
						'post_id'     => $post_id,
						'title'       => get_the_title( $post_id ),
						'day_before'  => pbs_term_names( $before_ids, $taxonomy ),
						'day_after'   => pbs_term_names( $after_ids, $taxonomy ),
						'time_before' => $time_before,
						'time_after'  => $time_after,
					);

					if ( $sorted_before === $sorted_after && $time_before === $time_after ) {
						$unchanged[] = $result;
						continue;
					}

					if ( ! $dry_run ) {
						if ( $sorted_before !== $sorted_after ) {
							$set = wp_set_object_terms( $post_id, $after_ids, $taxonomy );
							if ( is_wp_error( $set ) ) {
								$problems[] = array( 'speaker' => $speaker, 'reason' => $set->get_error_message() );
								continue;
							}
						}
						if ( $time_before !== $time_after ) {
							if ( function_exists( 'update_field' ) ) {
								update_field( $time_field, $time_after, $post_id );
							} else {
								update_post_meta( $post_id, $time_field, $time_after );
							}
						}
					}
					$changed[] = $result;
				}

				return array(
					'dry_run'  => $dry_run,
					'summary'  => sprintf(
						'%s%d speakers %s, %d already up to date, %d problems.',
						$dry_run ? 'PREVIEW: ' : '',
						count( $changed ),
						$dry_run ? 'would be updated' : 'updated',
						count( $unchanged ),
						count( $problems )
					),
					'speakers'  => $changed,
					'unchanged' => $unchanged,
					'problems'  => $problems,
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

/**
 * Resolve a term from a name, slug or numeric ID. Returns null when empty.
 *
 * Looks in $taxonomy when given, otherwise in every taxonomy registered for
 * $post_type (so a speaker term in speaker_categories is found automatically).
 *
 * @param string|int $value     Term name, slug or ID.
 * @param string     $taxonomy  Optional taxonomy.
 * @param string     $post_type Post type whose taxonomies to search.
 * @return WP_Term|WP_Error|null
 */
function pbs_resolve_category( $value, $taxonomy = '', $post_type = 'post' ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return null;
	}

	$taxonomy   = sanitize_key( (string) $taxonomy );
	$taxonomies = $taxonomy ? array( $taxonomy ) : get_object_taxonomies( $post_type ? $post_type : 'post' );
	if ( $taxonomy && ! taxonomy_exists( $taxonomy ) ) {
		return new WP_Error( 'pbs_taxonomy_not_found', sprintf( 'No taxonomy called "%s".', $taxonomy ) );
	}

	foreach ( $taxonomies as $tax ) {
		$term = ctype_digit( $value ) ? get_term( (int) $value, $tax ) : null;
		if ( ! $term || is_wp_error( $term ) ) {
			$term = get_term_by( 'name', $value, $tax );
		}
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $value ), $tax );
		}
		if ( $term ) {
			return $term;
		}
	}

	return new WP_Error(
		'pbs_category_not_found',
		sprintf( 'No term matching "%s" in: %s.', $value, implode( ', ', $taxonomies ) )
	);
}

/**
 * Build a tax_query for a resolved term (or none).
 *
 * @param WP_Term|null $term Term to filter by.
 * @return array
 */
function pbs_term_tax_query( $term ) {
	if ( ! $term ) {
		return array();
	}
	return array(
		array(
			'taxonomy' => $term->taxonomy,
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	);
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

/**
 * Turn term IDs into readable names.
 *
 * @param int[]  $ids      Term IDs.
 * @param string $taxonomy Taxonomy.
 * @return string[]
 */
function pbs_term_names( $ids, $taxonomy ) {
	$names = array();
	foreach ( $ids as $id ) {
		$term    = get_term( $id, $taxonomy );
		$names[] = ( $term && ! is_wp_error( $term ) ) ? $term->name : (string) $id;
	}
	return $names;
}

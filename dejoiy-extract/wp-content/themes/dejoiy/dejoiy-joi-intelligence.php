<?php
/**
 * DEJOIY JOI Intelligence — marketplace search (SKU, DPIN, products) + shared AJAX.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function dejoiy_joi_intelligence_api_base() {
	return apply_filters( 'dejoiy_joi_api_base', 'https://joi.dejoiy.tech/api' );
}

/**
 * Format one product for JOI / header search JSON.
 *
 * @param int $product_id Product ID.
 * @return array<string, mixed>|null
 */
function dejoiy_joi_intelligence_format_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return null;
	}

	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
	if ( ! $product ) {
		return null;
	}

	$registry = function_exists( 'dejoiy_ecosystem_registry' ) ? dejoiy_ecosystem_registry() : array();
	$eco      = function_exists( 'dejoiy_get_product_ecosystem' ) ? dejoiy_get_product_ecosystem( $product_id ) : 'marketplace';
	$label    = isset( $registry[ $eco ]['label'] ) ? $registry[ $eco ]['label'] : 'DEJOIY';
	$url      = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $product_id ) : get_permalink( $product_id );
	$thumb    = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
	$dpin     = function_exists( 'dejoiy_display_product_dpin' ) ? dejoiy_display_product_dpin( $product_id ) : '';
	$sku      = (string) $product->get_sku();
	$meta     = array();

	if ( $dpin ) {
		$meta[] = $dpin;
	}
	if ( $sku ) {
		$meta[] = 'SKU ' . $sku;
	}

	return array(
		'type'  => 'product',
		'eco'   => $eco,
		'badge' => $label,
		'title' => html_entity_decode( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ),
		'url'   => $url,
		'thumb' => $thumb ? $thumb : '',
		'price' => wp_strip_all_tags( $product->get_price_html() ),
		'dpin'  => $dpin,
		'sku'   => $sku,
		'meta'  => implode( ' · ', $meta ),
	);
}

/**
 * Collect product IDs — DPIN, SKU, then marketplace text search.
 *
 * @param string $term  Search term.
 * @param string $scope Scope key.
 * @param int    $limit Max results.
 * @return array<int, int>
 */
function dejoiy_joi_intelligence_collect_product_ids( $term, $scope = 'all', $limit = 12 ) {
	$term  = trim( (string) $term );
	$limit = max( 1, min( 20, (int) $limit ) );

	if ( strlen( $term ) < 2 ) {
		return array();
	}

	$ids = array();

	if ( function_exists( 'dejoiy_dpin_resolve_product_id' ) ) {
		$dpin_pid = (int) dejoiy_dpin_resolve_product_id( $term );
		if ( $dpin_pid > 0 ) {
			$ids[] = $dpin_pid;
		}
	}

	if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
		$sku_pid = (int) wc_get_product_id_by_sku( $term );
		if ( $sku_pid > 0 ) {
			$ids[] = $sku_pid;
		}
	}

	$clean = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $term ) );
	if ( strlen( $clean ) >= 4 && function_exists( 'dejoiy_dpin_meta_key' ) ) {
		$dpin_ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => dejoiy_dpin_meta_key(),
						'value'   => $clean,
						'compare' => 'LIKE',
					),
				),
			)
		);
		$ids = array_merge( $ids, array_map( 'intval', $dpin_ids ) );
	}

	if ( strlen( $term ) >= 3 ) {
		$sku_ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
			)
		);
		$ids = array_merge( $ids, array_map( 'intval', $sku_ids ) );
	}

	$cat_map = array(
		'nexus'     => array( 'dejoiy-library', 'e-books', 'courses' ),
		'services'  => array( 'services-marketplace' ),
		'quickmart' => array( 'quick-products' ),
	);

	$tax_query = array();
	if ( isset( $cat_map[ $scope ] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $cat_map[ $scope ],
		);
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		's'              => $term,
		'posts_per_page' => max( $limit, 12 ),
		'no_found_rows'  => true,
		'fields'         => 'ids',
	);

	if ( $tax_query ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$text_ids = get_posts( $args );
	$ids      = array_merge( $ids, array_map( 'intval', $text_ids ) );

	$ids = array_values( array_unique( array_filter( $ids ) ) );
	return array_slice( $ids, 0, $limit );
}

/**
 * @param string $term  Search term.
 * @param string $scope Scope key.
 * @param int    $limit Max results.
 * @return array<int, array<string, mixed>>
 */
function dejoiy_joi_intelligence_search( $term, $scope = 'all', $limit = 12 ) {
	$out = array();
	foreach ( dejoiy_joi_intelligence_collect_product_ids( $term, $scope, $limit ) as $pid ) {
		$row = dejoiy_joi_intelligence_format_product( $pid );
		if ( $row ) {
			$out[] = $row;
		}
	}
	return $out;
}

/**
 * AJAX: enhanced marketplace search (replaces header-os-v4 handler).
 */
function dejoiy_joi_intelligence_ajax_search() {
	check_ajax_referer( 'dejoiy_header_os_v4', 'nonce' );

	$term  = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
	$scope = isset( $_REQUEST['scope'] ) ? sanitize_key( wp_unslash( $_REQUEST['scope'] ) ) : 'all';

	if ( strlen( $term ) < 2 ) {
		wp_send_json( array( 'results' => array() ) );
	}

	$out = dejoiy_joi_intelligence_search( $term, $scope, 12 );

	if ( ( 'all' === $scope || 'nexus' === $scope ) && count( $out ) < 12 ) {
		$pages = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				's'              => $term,
				'posts_per_page' => 3,
				'no_found_rows'  => true,
			)
		);
		foreach ( $pages->posts as $page ) {
			$out[] = array(
				'type'  => 'page',
				'eco'   => 'page',
				'badge' => __( 'Page', 'dejoiy' ),
				'title' => html_entity_decode( get_the_title( $page->ID ), ENT_QUOTES, 'UTF-8' ),
				'url'   => get_permalink( $page->ID ),
				'thumb' => '',
				'price' => '',
				'meta'  => '',
			);
		}
		wp_reset_postdata();
	}

	wp_send_json( array( 'results' => $out ) );
}

/**
 * OpenAI API key — wp-config constant DEJOIY_OPENAI_API_KEY or encrypted option (never in theme files).
 *
 * @return string
 */
function dejoiy_joi_get_openai_api_key() {
	if ( defined( 'DEJOIY_OPENAI_API_KEY' ) && DEJOIY_OPENAI_API_KEY ) {
		return (string) DEJOIY_OPENAI_API_KEY;
	}
	$key = get_option( 'dejoiy_openai_api_key', '' );
	return is_string( $key ) ? trim( $key ) : '';
}

/**
 * @return string
 */
function dejoiy_joi_openai_model() {
	return (string) apply_filters( 'dejoiy_openai_model', 'gpt-4o-mini' );
}

/**
 * @param array<int, array<string, string>> $messages Chat messages.
 * @param int                               $max_tokens Max tokens.
 * @return string|WP_Error
 */
function dejoiy_joi_openai_request( $messages, $max_tokens = 450 ) {
	$key = dejoiy_joi_get_openai_api_key();
	if ( '' === $key ) {
		return new WP_Error( 'dejoiy_openai_missing', __( 'OpenAI is not configured.', 'dejoiy' ) );
	}

	$response = wp_remote_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'timeout' => 50,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'model'       => dejoiy_joi_openai_model(),
					'messages'    => $messages,
					'max_tokens'  => max( 64, min( 900, (int) $max_tokens ) ),
					'temperature' => 0.65,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( $code < 200 || $code >= 300 ) {
		$msg = isset( $body['error']['message'] ) ? (string) $body['error']['message'] : __( 'OpenAI request failed.', 'dejoiy' );
		return new WP_Error( 'dejoiy_openai_http', $msg, array( 'status' => $code ) );
	}

	$text = isset( $body['choices'][0]['message']['content'] ) ? (string) $body['choices'][0]['message']['content'] : '';
	return trim( $text );
}

/**
 * @param array<int, array<string, mixed>> $products Product rows.
 * @return string
 */
function dejoiy_joi_marketplace_system_prompt( $products ) {
	$lines = array(
		'You are JOI — the intelligent guide for DEJOIY, a premium joy-driven marketplace (Shop, Nexus books, Custom Studio, Refurbished, Services, QuickMart).',
		'Be warm, concise, and trustworthy. Answer in 2–4 short sentences unless the user asks for detail.',
		'Recommend real DEJOIY products when relevant. Never invent products not listed below.',
	);

	if ( ! empty( $products ) ) {
		$lines[] = 'Matching marketplace products:';
		foreach ( array_slice( $products, 0, 8 ) as $product ) {
			$line = '- ' . ( $product['title'] ?? '' );
			if ( ! empty( $product['meta'] ) ) {
				$line .= ' (' . $product['meta'] . ')';
			}
			$lines[] = $line;
		}
	}

	return implode( "\n", $lines );
}

/**
 * @param string                            $message  User message.
 * @param array<int, array<string, mixed>> $products Products.
 * @return string
 */
function dejoiy_joi_fallback_marketplace_reply( $message, $products ) {
	if ( ! empty( $products ) ) {
		return sprintf(
			/* translators: %d: product count */
			_n(
				'I found %d product on DEJOIY that may match your search. Browse the results below.',
				'I found %d products on DEJOIY that may match your search. Browse the results below.',
				count( $products ),
				'dejoiy'
			),
			count( $products )
		);
	}
	return __( 'I could not find an exact match yet — try a product name, SKU, DPIN, or describe what you need.', 'dejoiy' );
}

/**
 * AJAX: JOI marketplace chat (Ask JOI + OpenAI).
 */
function dejoiy_joi_ajax_chat() {
	check_ajax_referer( 'dejoiy_header_os_v4', 'nonce' );

	$message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
	if ( strlen( $message ) < 2 ) {
		wp_send_json_error( array( 'message' => __( 'Message too short.', 'dejoiy' ) ), 400 );
	}

	$products = dejoiy_joi_intelligence_search( $message, 'all', 8 );
	$reply    = dejoiy_joi_openai_request(
		array(
			array(
				'role'    => 'system',
				'content' => dejoiy_joi_marketplace_system_prompt( $products ),
			),
			array(
				'role'    => 'user',
				'content' => $message,
			),
		)
	);

	if ( is_wp_error( $reply ) || '' === $reply ) {
		$reply = dejoiy_joi_fallback_marketplace_reply( $message, $products );
	}

	wp_send_json_success(
		array(
			'reply'   => $reply,
			'results' => $products,
		)
	);
}

/**
 * Parse prior user messages sent from the Nexus JOI chat UI.
 *
 * @param string $context Newline-separated prior user messages.
 * @return array<int, string>
 */
function dejoiy_joi_librarian_parse_context( $context ) {
	$context = trim( (string) $context );
	if ( '' === $context ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/u', $context );
	if ( ! is_array( $lines ) ) {
		return array();
	}

	$out = array();
	foreach ( $lines as $line ) {
		$line = trim( (string) $line );
		if ( strlen( $line ) >= 2 ) {
			$out[] = $line;
		}
	}

	return array_slice( array_values( array_unique( $out ) ), -6 );
}

/**
 * Normalize conversational librarian queries (hyphens, filler words).
 *
 * @param string $text Raw query.
 * @return string
 */
function dejoiy_joi_librarian_normalize_query( $text ) {
	$text  = trim( (string) $text );
	$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
	$lower = str_replace( array( '-', '_', '/', '–', '—' ), ' ', $lower );

	$fillers = array(
		'themed', 'theme', 'style', 'type', 'kind', 'genre', 'based', 'related',
		'book', 'books', 'story', 'stories', 'novel', 'novels', 'reading', 'read',
	);
	foreach ( $fillers as $filler ) {
		$lower = preg_replace( '/\b' . preg_quote( $filler, '/' ) . '\b/u', ' ', $lower );
	}

	return trim( (string) preg_replace( '/\s+/u', ' ', $lower ) );
}

/**
 * Build candidate search strings from the current message and chat context.
 *
 * @param string $message User message.
 * @param string $context Prior user messages (newline-separated).
 * @return array<int, string>
 */
function dejoiy_joi_librarian_collect_search_terms( $message, $context = '' ) {
	$message = trim( (string) $message );
	$terms   = array( $message );

	foreach ( dejoiy_joi_librarian_parse_context( $context ) as $line ) {
		$terms[] = $line;
	}

	$combined = trim( $message . ' ' . $context );
	if ( strlen( $combined ) >= 2 ) {
		$terms[] = $combined;
	}

	$normalized_message  = dejoiy_joi_librarian_normalize_query( $message );
	$normalized_combined = dejoiy_joi_librarian_normalize_query( $combined );
	if ( strlen( $normalized_message ) >= 2 ) {
		$terms[] = $normalized_message;
	}
	if ( strlen( $normalized_combined ) >= 2 ) {
		$terms[] = $normalized_combined;
	}

	$expanded = array();
	foreach ( $terms as $term ) {
		$term = trim( (string) $term );
		if ( strlen( $term ) < 2 ) {
			continue;
		}
		$expanded[] = $term;
		$lower      = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term, 'UTF-8' ) : strtolower( $term );
		$parts      = preg_split( '/[\s\-_\/]+/u', $lower );
		if ( is_array( $parts ) ) {
			foreach ( $parts as $part ) {
				$part = trim( (string) $part );
				if ( strlen( $part ) >= 3 ) {
					$expanded[] = $part;
				}
			}
		}
	}

	$unique = array();
	foreach ( $expanded as $term ) {
		$term = trim( (string) $term );
		if ( strlen( $term ) < 2 ) {
			continue;
		}
		if ( ! in_array( $term, $unique, true ) ) {
			$unique[] = $term;
		}
	}

	return $unique;
}

/**
 * Resolve Nexus books from conversational librarian queries.
 *
 * @param string $message User message.
 * @param int    $limit   Max results.
 * @param string $context Prior user messages (newline-separated).
 * @return array<int, array<string, mixed>>
 */
function dejoiy_joi_librarian_search_books( $message, $limit = 24, $context = '' ) {
	if ( ! function_exists( 'dejoiy_library_search_nexus_books' ) ) {
		return array();
	}

	$message = trim( (string) $message );
	if ( strlen( $message ) < 2 ) {
		return array();
	}

	$terms = dejoiy_joi_librarian_collect_search_terms( $message, $context );
	foreach ( $terms as $term ) {
		$books = dejoiy_library_search_nexus_books( $term, $limit );
		if ( ! empty( $books ) ) {
			return $books;
		}
	}

	$combined_lower = function_exists( 'mb_strtolower' )
		? mb_strtolower( trim( $message . ' ' . $context ), 'UTF-8' )
		: strtolower( trim( $message . ' ' . $context ) );

	$hints = array(
		'love'         => 'love',
		'romance'      => 'love',
		'romantic'     => 'love',
		'heart'        => 'love',
		'relationship' => 'love',
		'fiction'      => 'fiction',
		'novel'        => 'novel',
		'story'        => 'story',
		'adventure'    => 'adventure',
		'adventur'     => 'adventure',
		'adventours'   => 'adventure',
		'adventour'    => 'adventure',
		'thriller'     => 'thriller',
		'mystery'      => 'mystery',
		'fantasy'      => 'fantasy',
		'biography'    => 'biography',
		'kahani'       => 'story',
		'hindi'        => 'hindi',
		'english'      => 'english',
		'business'     => 'business',
		'science'      => 'science',
		'history'      => 'history',
		'poetry'       => 'poetry',
		'kids'         => 'children',
		'children'     => 'children',
	);
	foreach ( $hints as $needle => $search ) {
		if ( false !== strpos( $combined_lower, $needle ) ) {
			$books = dejoiy_library_search_nexus_books( $search, $limit );
			if ( ! empty( $books ) ) {
				return $books;
			}
		}
	}

	$stop_words = array(
		'a', 'an', 'the', 'for', 'me', 'my', 'i', 'you', 'we', 'please', 'find', 'show', 'book', 'books',
		'read', 'want', 'need', 'give', 'recommend', 'suggest', 'joi', 'hi', 'hello', 'hey', 'any', 'some',
		'about', 'what', 'which', 'best', 'good', 'nice', 'can', 'could', 'would', 'should', 'tell',
		'themed', 'theme', 'style', 'type', 'kind', 'genre', 'based', 'related',
		'mujhe', 'koi', 'dedo', 'padne', 'padhna', 'rha', 'hai', 'ho', 'ka', 'ki', 'ke', 'ko', 'se', 'mn', 'man',
		'yrr', 'yar', 'yahi', 'wala', 'wali', 'hain', 'kr', 'kar', 'raha', 'rahi', 'krna', 'karna',
	);
	foreach ( $terms as $term ) {
		$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term, 'UTF-8' ) : strtolower( $term );
		$words = preg_split( '/\s+/u', preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $lower ) );
		if ( ! is_array( $words ) ) {
			continue;
		}
		foreach ( $words as $word ) {
			$word = trim( (string) $word );
			if ( strlen( $word ) < 3 || in_array( $word, $stop_words, true ) ) {
				continue;
			}
			$books = dejoiy_library_search_nexus_books( $word, $limit );
			if ( ! empty( $books ) ) {
				return $books;
			}
		}
	}

	return array();
}

/**
 * Fallback librarian reply when catalog books were found.
 *
 * @param array<int, array<string, mixed>> $books Nexus books.
 * @return string
 */
function dejoiy_joi_librarian_books_reply( $books ) {
	$count = count( $books );
	if ( $count < 1 ) {
		return '';
	}

	$titles = array();
	foreach ( array_slice( $books, 0, 3 ) as $book ) {
		if ( ! empty( $book['title'] ) ) {
			$titles[] = (string) $book['title'];
		}
	}

	$lead = sprintf(
		/* translators: %d: book count */
		_n(
			'I found %d book in Nexus that matches your search.',
			'I found %d books in Nexus that match your search.',
			$count,
			'dejoiy'
		),
		$count
	);

	if ( $titles ) {
		$lead .= ' ' . sprintf(
			/* translators: %s: comma-separated book titles */
			__( 'Try %s — tap a title below to open it in Nexus.', 'dejoiy' ),
			implode( ', ', $titles )
		);
	} else {
		$lead .= ' ' . __( 'Tap a title below to open it in Nexus.', 'dejoiy' );
	}

	return $lead;
}

/**
 * Ensure AI does not claim "no match" when catalog results exist.
 *
 * @param string                           $reply AI reply.
 * @param array<int, array<string, mixed>> $books Nexus books.
 * @return string
 */
function dejoiy_joi_librarian_finalize_reply( $reply, $books ) {
	if ( empty( $books ) ) {
		return $reply;
	}

	$reply = trim( (string) $reply );
	if ( '' === $reply ) {
		return dejoiy_joi_librarian_books_reply( $books );
	}

	$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $reply, 'UTF-8' ) : strtolower( $reply );
	$bad   = array(
		"can't find",
		'cannot find',
		'could not find',
		'couldn\'t find',
		'no exact',
		'no specific',
		'not find',
		'did not find',
		'didn\'t find',
		'no books matched',
		'no nexus books',
	);
	foreach ( $bad as $needle ) {
		if ( false !== strpos( $lower, $needle ) ) {
			return dejoiy_joi_librarian_books_reply( $books );
		}
	}

	return $reply;
}

/**
 * @param string                            $term    Search term.
 * @param array<int, array<string,mixed>>  $books   Nexus books.
 * @param string                            $context Prior user messages.
 * @return string|WP_Error
 */
function dejoiy_joi_librarian_openai_reply( $term, $books, $context = '' ) {
	$book_lines = array();
	foreach ( array_slice( $books, 0, 10 ) as $book ) {
		$line = '- ' . ( $book['title'] ?? '' );
		if ( ! empty( $book['author'] ) ) {
			$line .= ' — ' . $book['author'];
		}
		$book_lines[] = $line;
	}

	$system = "You are JOI Librarian for DEJOIY Nexus — a premium digital library for books and learning.\n";
	$system .= "Reply in 2–3 warm, expert sentences in the user's language when possible.\n";
	$system .= "Only recommend books from the Nexus list below — never invent titles not in the list.\n";
	$system .= "Mention book titles exactly as listed so readers can open them.\n";
	if ( $book_lines ) {
		$system .= "Real-time Nexus catalog matches — you MUST recommend these books by exact title:\n" . implode( "\n", $book_lines );
		$system .= "\nNever say you cannot find books when this list is non-empty.";
	} else {
		$system .= 'No exact books matched — suggest how to refine the search (title, author, language, or theme like adventure-themed).';
	}

	$messages = array(
		array(
			'role'    => 'system',
			'content' => $system,
		),
	);

	foreach ( dejoiy_joi_librarian_parse_context( $context ) as $line ) {
		$messages[] = array(
			'role'    => 'user',
			'content' => $line,
		);
	}

	$messages[] = array(
		'role'    => 'user',
		'content' => $term,
	);

	return dejoiy_joi_openai_request( $messages, 300 );
}

/**
 * AJAX: Nexus JOI Librarian search with OpenAI guidance.
 */
function dejoiy_joi_nexus_search_handler() {
	$lang_file = get_stylesheet_directory() . '/library-languages.php';
	if ( is_readable( $lang_file ) && ! function_exists( 'dejoiy_library_resolve_search_language' ) ) {
		require_once $lang_file;
	}

	$term    = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$context = isset( $_REQUEST['context'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['context'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( strlen( $term ) < 2 ) {
		wp_send_json_success(
			array(
				'message' => '',
				'books'   => array(),
				'term'    => $term,
			)
		);
	}

	$books      = dejoiy_joi_librarian_search_books( $term, 24, $context );
	$lang       = function_exists( 'dejoiy_library_resolve_search_language' ) ? dejoiy_library_resolve_search_language( $term ) : '';
	$langs      = function_exists( 'dejoiy_library_get_languages' ) ? dejoiy_library_get_languages() : array();
	$books_only = ! empty( $_REQUEST['books_only'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$message = '';
	if ( $books_only ) {
		if ( $books && $lang && isset( $langs[ $lang ]['label'] ) ) {
			$message = sprintf(
				/* translators: %s: language label */
				__( 'Books in %s', 'dejoiy' ),
				$langs[ $lang ]['label']
			);
		} elseif ( $books ) {
			$message = sprintf(
				/* translators: %s: search keywords */
				__( 'Nexus results for “%s”', 'dejoiy' ),
				$term
			);
		} else {
			$message = __( 'No Nexus books matched — try a title, author, or language (e.g. Hindi, English).', 'dejoiy' );
		}
	} else {
		$ai = dejoiy_joi_librarian_openai_reply( $term, $books, $context );
		if ( ! is_wp_error( $ai ) && '' !== $ai ) {
			$message = dejoiy_joi_librarian_finalize_reply( $ai, $books );
		} elseif ( $books && $lang && isset( $langs[ $lang ]['label'] ) ) {
			$message = sprintf(
				/* translators: %s: language label */
				__( 'Books in %s', 'dejoiy' ),
				$langs[ $lang ]['label']
			);
		} elseif ( $books ) {
			$message = sprintf(
				/* translators: %s: search keywords */
				__( 'Nexus results for “%s”', 'dejoiy' ),
				$term
			);
		} else {
			$message = __( 'No Nexus books matched — try a title, author, or language (e.g. Hindi, English).', 'dejoiy' );
		}
	}

	$home = function_exists( 'dejoiy_library_get_landing_url' ) ? dejoiy_library_get_landing_url() : home_url( '/dejoiy-library/' );

	wp_send_json_success(
		array(
			'message' => $message,
			'books'   => $books,
			'term'    => $term,
			'home'    => add_query_arg(
				array(
					'dejoiy_library' => '1',
					'dlu_q'          => $term,
				),
				$home
			),
		)
	);
}

/**
 * AJAX: JOI Librarian chat (OpenAI + Nexus books).
 */
function dejoiy_joi_ajax_librarian_chat() {
	check_ajax_referer( 'dejoiy_header_os_v4', 'nonce' );

	$message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
	$context = isset( $_POST['context'] ) ? sanitize_textarea_field( wp_unslash( $_POST['context'] ) ) : '';
	if ( strlen( $message ) < 2 ) {
		wp_send_json_error( array( 'message' => __( 'Message too short.', 'dejoiy' ) ), 400 );
	}

	$books = dejoiy_joi_librarian_search_books( $message, 12, $context );
	$reply = dejoiy_joi_librarian_openai_reply( $message, $books, $context );

	if ( is_wp_error( $reply ) || '' === $reply ) {
		if ( $books ) {
			$reply = dejoiy_joi_librarian_books_reply( $books );
		} else {
			$reply = __( 'I could not find an exact match — try a title, author, or language (e.g. Hindi, English).', 'dejoiy' );
		}
	} else {
		$reply = dejoiy_joi_librarian_finalize_reply( $reply, $books );
	}

	$home = function_exists( 'dejoiy_library_get_landing_url' ) ? dejoiy_library_get_landing_url() : home_url( '/dejoiy-library/' );

	wp_send_json_success(
		array(
			'reply'   => $reply,
			'message' => $reply,
			'books'   => $books,
			'home'    => add_query_arg(
				array(
					'dejoiy_library' => '1',
					'dlu_q'          => $message,
				),
				$home
			),
		)
	);
}

/**
 * Prefer enhanced search handler over legacy header-os-v4.
 */
function dejoiy_joi_intelligence_register_ajax() {
	remove_action( 'wp_ajax_dejoiy_joi_search', 'dejoiy_header_os_v4_ajax_search' );
	remove_action( 'wp_ajax_nopriv_dejoiy_joi_search', 'dejoiy_header_os_v4_ajax_search' );

	add_action( 'wp_ajax_dejoiy_joi_search', 'dejoiy_joi_intelligence_ajax_search' );
	add_action( 'wp_ajax_nopriv_dejoiy_joi_search', 'dejoiy_joi_intelligence_ajax_search' );
	add_action( 'wp_ajax_dejoiy_joi_chat', 'dejoiy_joi_ajax_chat' );
	add_action( 'wp_ajax_nopriv_dejoiy_joi_chat', 'dejoiy_joi_ajax_chat' );
	add_action( 'wp_ajax_dejoiy_joi_librarian_chat', 'dejoiy_joi_ajax_librarian_chat' );
	add_action( 'wp_ajax_nopriv_dejoiy_joi_librarian_chat', 'dejoiy_joi_ajax_librarian_chat' );
}
add_action( 'init', 'dejoiy_joi_intelligence_register_ajax', 99 );

/**
 * Wire OpenAI into Nexus JOI Librarian (replaces keyword-only handler when Nexus is loaded).
 */
function dejoiy_joi_register_nexus_handlers() {
	if ( ! function_exists( 'dejoiy_library_search_nexus_books' ) ) {
		return;
	}

	remove_action( 'wp_ajax_dejoiy_library_nexus_search', 'dejoiy_library_nexus_search_handler' );
	remove_action( 'wp_ajax_nopriv_dejoiy_library_nexus_search', 'dejoiy_library_nexus_search_handler' );
	remove_action( 'wp_ajax_dejoiy_library_joi', 'dejoiy_library_joi_recommend_handler' );
	remove_action( 'wp_ajax_nopriv_dejoiy_library_joi', 'dejoiy_library_joi_recommend_handler' );

	add_action( 'wp_ajax_dejoiy_library_nexus_search', 'dejoiy_joi_nexus_search_handler' );
	add_action( 'wp_ajax_nopriv_dejoiy_library_nexus_search', 'dejoiy_joi_nexus_search_handler' );
	add_action( 'wp_ajax_dejoiy_library_joi', 'dejoiy_joi_nexus_search_handler' );
	add_action( 'wp_ajax_nopriv_dejoiy_library_joi', 'dejoiy_joi_nexus_search_handler' );
}
add_action( 'init', 'dejoiy_joi_register_nexus_handlers', 100 );

/**
 * Single-h1 SEO: the JOI page is an Elementor-canvas iframe app with no
 * heading of its own; give the page one real (screen-reader) h1.
 * Echoed directly on body open because the canvas template bypasses
 * the_content().
 */
function dejoiy_joi_page_h1_body() {
	if ( is_page( 4987 ) && ! is_admin() ) {
		echo '<h1 class="dejoiy-joi-h1 screen-reader-text" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;">JOI &mdash; DEJOIY AI Assistant for Shopping, Selling and Support</h1>';
	}
}
add_action( 'wp_body_open', 'dejoiy_joi_page_h1_body', 1 );

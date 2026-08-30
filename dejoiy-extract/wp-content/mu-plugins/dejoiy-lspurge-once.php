<?php
/**
 * One-time LiteSpeed purge helper — delete after use.
 */
add_action( 'init', function () {
	if ( ! isset( $_GET['dejoiy_lspurge'] ) || 'b839' !== $_GET['dejoiy_lspurge'] ) {
		return;
	}
	if ( class_exists( 'LiteSpeed\Purge' ) ) {
		\LiteSpeed\Purge::purge_all();
	}
	if ( function_exists( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	if ( function_exists( 'run_litespeed_cache' ) ) {
		do_action( 'litespeed_purge_url', home_url( '/' ) );
	}
	header( 'Content-Type: text/plain' );
	echo 'purged';
	exit;
}, 1 );

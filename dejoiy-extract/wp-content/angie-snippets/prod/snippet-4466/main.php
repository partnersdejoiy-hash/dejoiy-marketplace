<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

const DEJOIY_TERMS_ASSETS_VERSION_8a155159 = '1.0.0';

function register_dejoiy_terms_widget_8a155159( $widgets_manager ) {
    require_once __DIR__ . '/widget-dejoiy-terms.php';
    $widgets_manager->register( new \AngieSnippets\Dejoiy_Terms_8a155159() );
}
add_action( 'elementor/widgets/register', 'register_dejoiy_terms_widget_8a155159' );

function register_dejoiy_terms_assets_8a155159() {
	wp_register_script( 'dejoiy-terms-script-8a155159', angie_cs_get_snippet_asset_url( __FILE__, 'script.js' ), [ 'elementor-frontend' ], DEJOIY_TERMS_ASSETS_VERSION_8a155159, true );
	wp_register_style( 'dejoiy-terms-style-8a155159', angie_cs_get_snippet_asset_url( __FILE__, 'style.css' ), [], DEJOIY_TERMS_ASSETS_VERSION_8a155159 );
}
add_action( 'wp_enqueue_scripts', 'register_dejoiy_terms_assets_8a155159' );

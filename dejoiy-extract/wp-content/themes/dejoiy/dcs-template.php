<?php
  /**
   * Force page-4900.php template, running AFTER Elementor's template_include (priority 12).
   */
  if ( ! defined( 'ABSPATH' ) ) exit;
  add_filter( 'template_include', 'dcs_force_template', 20 );
  function dcs_force_template( $template ) {
      if ( get_queried_object_id() !== 4900 ) return $template;
      $custom = get_stylesheet_directory() . '/page-4900.php';
      return file_exists( $custom ) ? $custom : $template;
  }

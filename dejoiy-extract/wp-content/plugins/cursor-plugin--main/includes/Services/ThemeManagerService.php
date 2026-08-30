<?php
/**
 * Theme management service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;
use WP_Theme;

/**
 * Theme templates and child theme operations.
 */
class ThemeManagerService {

	/**
	 * List installed themes.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_themes(): array {
		$themes = wp_get_themes();
		$active = get_stylesheet();
		$result = array();

		foreach ( $themes as $slug => $theme ) {
			$result[] = $this->format_theme( $theme, $slug === $active );
		}

		return $result;
	}

	/**
	 * Get theme templates.
	 *
	 * @param string|null $theme_slug Theme slug.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_templates( ?string $theme_slug = null ): array {
		$theme = $theme_slug ? wp_get_theme( $theme_slug ) : wp_get_theme();
		if ( ! $theme->exists() ) {
			return array();
		}

		$files = $theme->get_files( 'php', 2, true );
		$templates = array();

		foreach ( $files as $file => $path ) {
			$templates[] = array(
				'file' => $file,
				'path' => str_replace( $theme->get_stylesheet_directory(), '', $path ),
			);
		}

		return $templates;
	}

	/**
	 * Read template file.
	 *
	 * @param string $theme_slug Theme.
	 * @param string $template   Template path relative to theme.
	 * @return array<string, mixed>|WP_Error
	 */
	public function read_template( string $theme_slug, string $template ) {
		$theme = wp_get_theme( $theme_slug );
		if ( ! $theme->exists() ) {
			return new WP_Error( 'theme_not_found', __( 'Theme not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$path = $theme->get_stylesheet_directory() . '/' . ltrim( $template, '/' );
		$base = realpath( $theme->get_stylesheet_directory() );

		if ( false === $base || 0 !== strpos( realpath( dirname( $path ) ) ?: '', $base ) ) {
			return new WP_Error( 'invalid_path', __( 'Invalid template path.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
		}

		if ( ! is_file( $path ) ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		return array(
			'theme'   => $theme_slug,
			'template' => $template,
			'content' => file_get_contents( $path ),
		);
	}

	/**
	 * Write template file.
	 *
	 * @param string $theme_slug Theme.
	 * @param string $template   Path.
	 * @param string $content    Content.
	 * @return array<string, mixed>|WP_Error
	 */
	public function write_template( string $theme_slug, string $template, string $content ) {
		$theme = wp_get_theme( $theme_slug );
		if ( ! $theme->exists() ) {
			return new WP_Error( 'theme_not_found', __( 'Theme not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$path = $theme->get_stylesheet_directory() . '/' . ltrim( $template, '/' );
		$dir  = dirname( $path );
		wp_mkdir_p( $dir );

		file_put_contents( $path, $content );

		return array( 'theme' => $theme_slug, 'template' => $template, 'success' => true );
	}

	/**
	 * Create child theme.
	 *
	 * @param string $slug         Child slug.
	 * @param string $parent_slug  Parent theme slug.
	 * @param string $name         Theme name.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_child_theme( string $slug, string $parent_slug, string $name ) {
		$parent = wp_get_theme( $parent_slug );
		if ( ! $parent->exists() ) {
			return new WP_Error( 'parent_not_found', __( 'Parent theme not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$slug = sanitize_key( $slug );
		$dir  = get_theme_root() . '/' . $slug;

		if ( file_exists( $dir ) ) {
			return new WP_Error( 'exists', __( 'Theme directory exists.', 'dejoiy-ai-control-bridge' ), array( 'status' => 409 ) );
		}

		wp_mkdir_p( $dir );

		$style = "/*\nTheme Name: {$name}\nTemplate: {$parent_slug}\nVersion: 1.0.0\n*/\n\n@import url('../{$parent_slug}/style.css');\n";
		file_put_contents( $dir . '/style.css', $style );

		$functions = "<?php\n/**\n * {$name} child theme functions.\n */\n";
		file_put_contents( $dir . '/functions.php', $functions );

		return array( 'created' => true, 'slug' => $slug, 'parent' => $parent_slug );
	}

	/**
	 * @param WP_Theme $theme  Theme.
	 * @param bool     $active Active flag.
	 * @return array<string, mixed>
	 */
	private function format_theme( WP_Theme $theme, bool $active ): array {
		return array(
			'slug'    => $theme->get_stylesheet(),
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
			'parent'  => $theme->parent() ? $theme->parent()->get_stylesheet() : null,
			'active'  => $active,
		);
	}
}

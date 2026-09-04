<?php
/**
 * Dejoiy child footer template.
 *
 * Keeps XStore's structural wrappers (page-wrapper / template-content /
 * template-container) byte-identical to the parent but replaces the footer
 * content itself with the unified DEJOIY Global Footer. Falls back to the
 * parent behaviour (Elementor theme-builder footer / etheme_footer) if the
 * global footer module is unavailable.
 *
 * @package Dejoiy
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( function_exists( 'dejoiy_global_footer_active' ) && dejoiy_global_footer_active() ) :
	do_action( 'dejoiy_global_footer_before' );
	?>
	</div> <!-- page wrapper -->

	<div class="et-footers-wrapper">
		<?php
		if ( function_exists( 'dejoiy_global_footer_render' ) ) {
			dejoiy_global_footer_render();
		} else {
			do_action( 'etheme_footer' );
		}
		?>
	</div>

	</div> <!-- template-content -->

	<?php do_action( 'after_page_wrapper' ); ?>
	</div> <!-- template-container -->

	<?php wp_footer(); ?>

</body>
</html>
	<?php
else :
	/* Match parent footer.php when the global footer is inactive. */
	if ( apply_filters( 'etheme_footer_template_basic', false ) || ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
		do_action( 'etheme_prefooter' );
		?>
		</div> <!-- page wrapper -->

		<div class="et-footers-wrapper">
			<?php do_action( 'etheme_footer' ); ?>
		</div>

		</div> <!-- template-content -->

		<?php do_action( 'after_page_wrapper' ); ?>
		</div> <!-- template-container -->
		<?php
	}

	wp_footer();
	?>
</body>
</html>
<?php endif; ?>
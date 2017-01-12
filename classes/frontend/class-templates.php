<?php
/**
 * FES Templates
 *
 * This file deals with loading FES templates.
 *
 * @package FES
 * @subpackage Frontend
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Templates.
 *
 * This class registers template locations and 
 * retrieves FES templates.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Templates {

	/**
	 * Get template directory.
	 *
	 * Retrieves the default FES template
	 * directory.
	 * 
	 * @since 2.0.0
	 * @access public
	 *
	 * @return string Template directory.
	 */		
	public function fes_get_templates_dir() {
		return fes_plugin_dir . 'templates';
	}
	
	/**
	 * Get template directory url.
	 *
	 * Retrieves the default FES template
	 * directory url.
	 * 
	 * @since 2.0.0
	 * @access public
	 *
	 * @return string Template directory url.
	 */	
	public function fes_get_templates_url() {
		return fes_plugin_url . 'templates';
	}
	
	/**
	 * Retrieves a template part
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @uses FES_Templates::fes_locate_template()
	 * @uses load_template()
	 * @uses get_template_part()
	 *
	 * @param string $slug Slug of template.
	 * @param string $name Optional. Default null.
	 * @param bool   $load Load the template.
	 *
	 * @return string Template piece.
	 */
	public function fes_get_template_part( $slug, $name = null, $load = true ) {
		// Execute code for this part
		/**
		 * Get template part.
		 *
		 * Run an action on getting template part.
		 *
		 * @since 2.3.0
		 * 
		 * @param  string $slug Slug of template.
		 * @param  string $name Name of template.
		 */
		do_action( 'get_template_part_' . $slug, $slug, $name );
		// Setup possible parts
		$templates = array();

		if ( isset( $name ) ){
			$templates[] = $slug . '-' . $name . '.php';
		}

		$templates[] = $slug . '.php';

		// Allow template parts to be filtered
		/**
		 * Get template part.
		 *
		 * Change which template is being retrieved.
		 *
		 * @since 2.3.0
		 *
		 * @param  array $templates Templates to look for.
		 * @param  string $slug Slug of template.
		 * @param  string $name Name of template.
		 */
		$templates = apply_filters( 'fes_get_template_part', $templates, $slug, $name );

		// Return the part that is found
		return EDD_FES()->templates->fes_locate_template( $templates, $load, false );
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the theme-compat folder last.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @uses FES_Templates::fes_get_theme_template_paths() To get template paths to try.
	 * 
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool $load If true the template file will be loaded if it is found.
	 * @param bool $require_once Whether to require_once or require. Default true.
	 *   Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function fes_locate_template( $template_names, $load = false, $require_once = true ) {
		// No file found yet
		$located = false;
		// Try to find a template file
		foreach ( (array) $template_names as $template_name ) {
			// Continue if template is empty
			if ( empty( $template_name ) )
				continue;
			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );
			// try locating this template file by looping through the template paths
			foreach( EDD_FES()->templates->fes_get_theme_template_paths() as $template_path ) {
				if ( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break;
				}
			}
			if ( $located ) {
				break;
			}
		}
		if ( ( true == $load ) && ! empty( $located ) ){
			load_template( $located, $require_once );
		}
		return $located;
	}

	/**
	 * Template Paths.
	 *
	 * Returns a list of paths to check for template locations.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return array Locations of templates.
	 */
	public function fes_get_theme_template_paths() {
		$fes_template_dir = EDD_FES()->templates->fes_get_theme_template_dir_name();
		$edd_template_dir = edd_get_theme_template_dir_name();
		$file_paths = array(
			1 => trailingslashit( get_stylesheet_directory() )  . $fes_template_dir,
			10 => trailingslashit( get_stylesheet_directory() ) . $edd_template_dir,
			100 => trailingslashit( get_template_directory() )  . $fes_template_dir,
			1000 => trailingslashit( get_template_directory() ) . $edd_template_dir,
			10000 => EDD_FES()->templates->fes_get_templates_dir()
		);
		/**
		 * Template paths.
		 *
		 * Add/edit/remove FES template paths to search in.
		 *
		 * @since 2.3.0
		 *
		 * @param  array $file_paths Templates paths to try.
		 */
		$file_paths = apply_filters( 'fes_template_paths', $file_paths );
		// sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );
		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Returns the template directory name.
	 *
	 * Themes can filter this by using the FES_templates_dir filter.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return string
	 */
	public function fes_get_theme_template_dir_name() {
		$dir = 'fes_templates';
		/**
		 * Template dir.
		 *
		 * Default directory to look for templates in.
		 *
		 * @since 2.3.0
		 *
		 * @param  string $dir Default folder name.
		 */		
		$template_dir = apply_filters( 'fes_templates_dir', $dir );
		return trailingslashit( $template_dir );
	}
}
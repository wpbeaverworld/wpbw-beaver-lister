<?php 

/**
 * BeaverLister class.
 *
 * @subpackage  classes
 * @package     wpbw-beaver-lister
 *
 * @author      WP Beaver World
 * @link        http://www.wpbeaverworld.com
 * @copyright   Copyright (c) 2016 WP Beaver World.
 *
 * @since       1.0
 * @version 	1.0.3
 */
class BeaverLister {
	/**
	 * All post types where beaver builder is enabled.
	 *
	 * @author    WP Beaver World
	 * @var       array
	 * @access    private
	 */
	private $builder_work;

	function __construct() {
		$this->builder_work = FLBuilderModel::get_post_types();

		add_action( 'current_screen', array( $this, 'wpbw_do_beaver_lister' ) );
	}

	/**
	 * Adding custom columns
	 * Performing search
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 * @version 1.0.3
	 *
	 * @access  public
	 * @return  void
	 */
	public function wpbw_do_beaver_lister( $current_screen ) {
		if( in_array( $current_screen->post_type, $this->builder_work ) ) {
			add_filter( 'manage_'. $current_screen->post_type .'_posts_columns' , array( $this, 'wpbw_bl_custom_posts_columns' ), 99 );
			add_action( 'manage_'. $current_screen->post_type .'_posts_custom_column' , array( $this, 'wpbw_bl_custom_post_column_data' ), 100, 2 );
			add_action( 'restrict_manage_posts' , array( $this, 'wpbw_bl_restrict_manage_posts' ), 99, 10 );
			add_action( 'manage_posts_extra_tablenav', array( $this, 'wpbw_bl_extra_tablenav' ) );
			add_filter( 'parse_query' , array( $this, 'wpbw_bl_request_query' ), 999 );
		}

		if( is_admin() && $current_screen->post_type === "fl-builder-template" ) {
			add_action( 'admin_footer', array( $this, 'wpbw_bl_add_css' ) );
			add_filter( 'post_class', array( $this, 'wpbw_bl_add_custom_class' ), 99 );
		}
	}

	/** 
	 * Add or remove (unset) custom columns to a list of custom post types
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 *
	 * @access  public
	 * @param 	array 	$columns
	 * @return  array
	 */
	public function wpbw_bl_custom_posts_columns( $columns ) {

		unset(
			$columns['date']
		);

		$new_columns = array(
			'active_page_builder' 	=> __( 'Builder Enabled?', 'beaver-lister' ),
		);

		if( 'fl-builder-template' === get_post_type() ) {
			$new_columns['is_used'] = __( 'Is Used?', 'wordpress' );	
		}

		$new_columns['modules_used'] 	= __( 'Modules Used', 'beaver-lister' );
		$new_columns['date'] 			= __( 'Date', 'wordpress' );

	    return array_merge( $columns, $new_columns );
	}

	/** 
	 * Displaying the data of custom columns
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 * @version 1.0.2
	 *
	 * @access  public
	 * @param 	string 	$column 	Column slug
	 * @param 	integer $post_id 	Current Post Id
	 * @return  array
	 */
	public function wpbw_bl_custom_post_column_data( $column, $post_id ) {

		switch ( $column ) {

	        case 'active_page_builder' :
	            if( FLBuilderModel::is_builder_enabled() ) {
	            	echo '&#x2611;';
	            }
	            break;

	        case "modules_used" :
	        	$data = get_post_meta( $post_id, '_fl_builder_data', true );
	        	if( $data ) {
	        		$modules_used = array();
	        		foreach ($data as $object ) {

	        			if( ! empty( $object->template_id ) && get_post_type() !== 'fl-builder-template' ) {
							$postId = FLBuilderModel::get_node_template_post_id( $object->template_id );
							$global_tpl_data = get_post_meta( $postId, '_fl_builder_data', true );
							if( $global_tpl_data ) :
								foreach ($global_tpl_data as $obj ) {
									if( $obj->type != 'module' ) {
										continue;
									}

									if( ! in_array( $obj->settings->type, $modules_used ) )
										$modules_used[ $obj->settings->type ] = ucwords( str_replace( '-', ' ', $obj->settings->type ) );
								}
							endif;
						}

	        			if( $object->type != 'module' ) {
							continue;
						}

						if( ! in_array( $object->settings->type, $modules_used ) )
							$modules_used[ $object->settings->type ] = ucwords( str_replace( '-', ' ', $object->settings->type ) );
	        		}

	        		echo implode(', ', $modules_used );
	        	}
	        	break;

	        case "is_used" :
	        		$posts = FLBuilderModel::get_posts_with_global_node_template( $post_id );
	        		if ( count( $posts ) > 0 ) {
	        			echo '&#x2611;';
	        		}
	        	break;
	    }
	}

	/** 
	 * Adding CSS
	 * It will identify the global template row
	 *
	 * @author 	WP Beaver World
	 * @since   1.0.3
	 *
	 * @access  public
	 */
	public function wpbw_bl_add_css() {
		echo '<style type="text/css">.global-tpl{ background: #fbd6a1!important;}</style>';
	}

	/** 
	 * Adding custom class in wp post list table
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 *
	 * @access  public
	 * @param 	array 	$classes 	The post class
	 * @return  array 	$classes 	The updated post class
	 */
	public function wpbw_bl_add_custom_class( $classes ) {
		global $post;
		$posts = FLBuilderModel::get_posts_with_global_node_template( $post->ID );
		if ( count( $posts ) > 0 ) {
			$classes[] = 'global-tpl';
		}
		return $classes;
	}

	/** 
	 * Adding "filter by selected module" drop down list
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 *
	 * @access  public
	 * @param 	sting 	$columns 	The post type slug
	 * @param 	string 	$which 		The location of the extra table nav markup
	 * @return  array
	 */
	public function wpbw_bl_restrict_manage_posts( $post_type, $which ) {
		$cat_modules = FLBuilderModel::get_categorized_modules();
		if( $cat_modules ) {
		?>
			<label for="filter-by-bb-module" class="screen-reader-text"><?php _e( 'Beaver Modules' ); ?></label>
			<select name="meta_bb_module" id="filter-by-bb-module">
				<option value=""><?php _e( 'Beaver Pages by Module', 'fl-automator' ); ?></option>
				<?php
					foreach( $cat_modules as $key => $modules ) {
						printf( '<optgroup label="%s">', $key );
						foreach( $modules as $module ) {	
							$sel = ( ! empty( $_GET['meta_bb_module'] ) && $_GET['meta_bb_module'] == $module->slug ) ? ' selected' : '' ;

							printf( '<option value="%s"%s>%s</option>', $module->slug, $sel, $module->name );
						}
						echo '</optgroup>' . "\n";
					}
				?>
			</select>
		<?php	
		}
	}

	/** 
	 * Adding 'Show Beaver Pages' button at top of wp list tablenav
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 *
	 * @access  public
	 * @param 	string 	$which 	The location of the extra table nav markup
	 * @return  array
	 */
	public function wpbw_bl_extra_tablenav( $which ) {
		global $wpdb, $post_type;
    
	    if ( $which == "top" ) {
	    	echo '<div class="alignleft actions"><input type="submit" class="button" name="show_bb_pages" value="' . __( 'Show Beaver Pages', 'beaver-lister' ) . '" /></div>';
	    }
	}

	/** 
	 * Set up query variables
	 *
	 * @author 	WP Beaver World
	 * @since   1.0
	 *
	 * @access  public
	 * @param 	object 	$query 		The query object that parsed the query
	 * @return  void
	 */
	public function wpbw_bl_request_query( $query ) {
		
		global $pagenow, $post_type, $wpdb;
		
	 	$qv = &$query->query_vars;
	 	
		if( ! empty( $pagenow ) && $pagenow == 'edit.php' && in_array( $post_type , $this->builder_work ) ) {
			
			if ( ! empty( $_GET['meta_bb_module'] ) ) {
				
				$bb_module = esc_attr( $_GET['meta_bb_module'] );
				$results = $wpdb->get_results( "SELECT $wpdb->postmeta.post_id FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type != 'revision' AND $wpdb->postmeta.meta_key = '_fl_builder_data' and ( $wpdb->postmeta.meta_value LIKE '%{$bb_module}%' )", OBJECT );
				
				if( $results ) {
					foreach ($results as $value) {
						$qv['post__in'][] = $value->post_id;
					}
				} else {
					$qv['post__in'][] = -999;
				} 
			}

			if ( ! empty( $_GET['show_bb_pages'] ) ) {
				$qv['meta_key'] = '_fl_builder_enabled';
				$qv['meta_value'] = 1;
			}
		}
	}
}
<?php
/**
 * Plugin Name: Academic Course post
 * Description: Adds Course post type
 * Version: 1.0
 * Author: Shlomi Gottlieb
 * License: GPL2
 */

class CoursePostType {

	private static $instance;
	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;
	/**
	 * Holds all course types
	 */
	protected $course_types;
	/**
	 * Holds all course semesters
	 */
	protected $course_semesters;
	/**
	 * save current language site
	 */
	protected static $lang;
	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new CoursePostType();
		} 
		return self::$instance;
	} 
	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	public function __construct() {
		$this->templates = array();

		$this->course_types = array(
			'Enrichment' => __('Enrichment', 'academic'),
			'Arts' => __('Arts', 'academic'),
			'Languages' => __('Languages', 'academic'),
			'English' => __('English', 'academic'),
			'Hebrew' => __('Hebrew', 'academic'),
			'Physical-Education' => __('Physical-Education', 'academic'),
			'CALL-LAB' => __('CALL-LAB', 'academic')
		);

		$this->course_semesters = array(
			'winter' => __('Winter', 'academic'),
			'spring' => __('Spring', 'academic'),
			'summer' => __('Summer', 'academic'),
		);

		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_course_templates' )
			);
		} else {
			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);
		}
		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_course_templates' ) 
		);
		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_course_template') 
		);
		// Add your templates to this array.
		$this->templates = array(
			'academic-custom-courses.php' => 'Custom courses (from plugin)',
		);

		add_action( 'wp_enqueue_scripts', array($this ,'add_scripts_and_styles') );
		add_action( 'init', array($this ,'faculty_course_custom_posttypes') );
		add_action( 'init', array($this, 'faculty_course_custom_taxonomies') );
		add_action( 'cmb2_admin_init', array($this, 'fatfish_register_course_metabox') );
		add_action( 'cmb2_admin_init', array($this, 'register_course_template_metabox') );
		add_filter( 'template_include', array($this, 'include_template_courses'), 1 );

		register_activation_hook( __FILE__, array('CoursePostType', 'course_rewrite_flush') );
	}
	
	public function add_scripts_and_styles() {
		wp_enqueue_script( 'course_vue', plugin_dir_url( __FILE__ ).'/inc/js/vue.js', array(), true );
		wp_enqueue_style( 'course_style', plugin_dir_url( __FILE__ ).'inc/css/course_style.css' );
	}

	public function faculty_course_custom_posttypes() {
		//Create Course post type
		$labels = array(
			'name'               => _x( 'Courses', 'Courses post type', 'academic' ),
			'singular_name'      => _x( 'Course', 'Course', 'academic' ),
			'menu_name'          => _x( 'Courses', 'admin menu', 'academic' ),
			'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'academic' ),
			'add_new'            => _x( 'Add New', 'Course', 'academic' ),
			'add_new_item'       => __( 'Add New Course', 'academic' ),
			'new_item'           => __( 'New Course', 'academic' ),
			'edit_item'          => __( 'Edit Course', 'academic' ),
			'view_item'          => __( 'View Course', 'academic' ),
			'all_items'          => __( 'All Courses', 'academic' ),
			'search_items'       => __( 'Search Course', 'academic' ),
			'parent_item_colon'  => __( 'Parent Course:', 'academic' ),
			'not_found'          => __( 'No Courses found.', 'academic' ),
			'not_found_in_trash' => __( 'No Courses found in Trash.', 'academic' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Adds Course post type.', 'academic' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => _x( 'faculty_courses', 'courses', 'academic' ) ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 6,
			'menu_icon'      	 => 'dashicons-welcome-learn-more',
			'supports'           => array( 'title', 'thumbnail', 'revisions')
		);

		register_post_type( 'course', $args );
	}

	public function course_rewrite_flush() {
		$this->faculty_course_custom_posttypes();
		flush_rewrite_rules();
	}

	public function faculty_course_custom_taxonomies() {
		$labels = array(
			'name'              => _x( 'Course category','academic' ),
			'singular_name'     => _x( 'Course category','academic' ),
			'search_items'      => __( 'Search Course category','academic' ),
			'all_items'         => __( 'All Course categories','academic' ),
			'parent_item'       => __( 'Parent Course category','academic' ),
			'parent_item_colon' => __( 'Parent Course category:','academic' ),
			'edit_item'         => __( 'Edit Course category','academic' ),
			'update_item'       => __( 'Update Course category','academic' ),
			'add_new_item'      => __( 'Add New Course category','academic' ),
			'new_item_name'     => __( 'New Course category Name','academic' ),
			'menu_name'         => __( 'Course category','wpml' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'course-category' ),
			
		);
		register_taxonomy( 'course_category', array( 'course' ), $args );   
		
	}

	public function fatfish_register_course_metabox( ) {
		// Start with an underscore to hide fields from custom fields list
		$prefix = 'cmb_';//custom meta box prefix

		/**
		* Sample metabox to demonstrate each field type included
		*/
		$meta_boxes = new_cmb2_box( array(
			'id'         => 'faculty_course_metabox',
			'title'      => __( 'Faculty Course Information', 'academic' ),
			'object_types'     => array( 'course', ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',		
			'show_names' => true, 
			'fields'     => array(
				array(
					'name' => __( 'Course number', 'academic' ),
					'id'   => $prefix .'course_number',
					'type' => 'text',				
				),
				array(
					'name' => __('Course credit', 'academic'),
					'id' => $prefix . 'course_credit',
					'type' => 'text'
				),
				array(
					'name' => __('Degree', 'academic'),
					'id' => $prefix . 'course_degree',
					'type' => 'select',
					'show_option_none' => true,
					'default' => '1',
					'options' => array(
						'1' => __('First degree', 'academic'),
						'2' => __('Master\'s degree', 'academic'),
						'3' => __('Ph.D', 'academic'),
					)
				),
				array(
					'name' => __('Course Year', 'academic'),
					'id' => $prefix . 'course_year',
					'type' => 'text'
				),
				array(
					'name' => __( 'Semester', 'academic' ),
					'id'   => $prefix .'course_semester',
					'type' => 'multicheck',
					'options' => $this->course_semesters
				),              
				array(
					'name' => __( 'Active', 'academic' ),
					'id'   => $prefix .'course_active',
					'type' => 'checkbox',
					'default' => 0,
				),
				array(
					'name' => __( 'Course type', 'academic' ),
					'id'   => $prefix .'type_course',
					'type' => 'multicheck',
					'options' => $this->course_types
				),
				array(
					'name' => __( 'Lecturer name', 'academic' ),
					'id'   => $prefix .'course_lecturer_name',
					'type' => 'text',
				),             
				array(
					'name' => __( 'Lecturer link', 'academic' ),
					'id'   => $prefix .'course_lecturer_link',
					'type' => 'text',
				),              
				array(
					'name' => __('Syllabus','academic' ),
					'id' => $prefix .'course_syllabus',
					'type' => 'wysiwyg',
					'options' => array(
						'wpautop' => true, 
						'media_buttons' => true,
						'textarea_rows' => get_option('default_post_edit_rows', 7), 
						'tabindex' => '0',
						'editor_css' => '', 
						'editor_class' => '', 
						'teeny' => false, 
						'dfw' => false, 
						'tinymce' => true, 
						'quicktags' => true 
					),
				),
				array(
					'name' => __('Course File', 'academic'),
					'id' => $prefix . 'course_file',
					'type' => 'file',
					'allow' => array( 'url', 'attachment' )
				),
			)
		) );	
	}

	public function include_template_courses( $template_path ) {

		if ( get_post_type() == 'course' ) {
			if ( is_single() ) {
				// checks if the file exists in the theme first,
				// otherwise serve the file from the plugin
				if ( $theme_file = locate_template( array ( 'single-course.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = plugin_dir_path( __FILE__ ) . '/template-parts/single-course.php';
				}
			}
			if ( is_archive() ) {
				// checks if the file exists in the theme first,
				// otherwise serve the file from the plugin
				if ( $theme_file = locate_template( array ( 'archive-course.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = plugin_dir_path( __FILE__ ) . '/template-parts/archive-course.php';
				}
			}	
		}

		return $template_path;
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}
	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_course_templates( $atts ) {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 
		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');
		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );
		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	} 
	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_course_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result)
		if ( is_search() ) {
			return $template;
		}
		
		// Get global post
		global $post;
		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}
		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 
		$file = plugin_dir_path( __FILE__ ). 'page-templates/' . get_post_meta( 
			$post->ID, '_wp_page_template', true
		);
		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}
		// Return template
		return $template;
	}

	public function register_course_template_metabox() {
		$prefix = 'course_template_';

		$custom_course = new_cmb2_box( array(
			'id'           => 'course_custom_view',
			'title'        => __('Custom parameters for the page', 'academic'),
			'object_types' => array( 'page' ),
			'show_on'      => array( 'key' => 'page-template', 'value' => 'academic-custom-courses.php' ),
			'context'      => 'normal',
			'priority'     => 'high', 
			'show_names'   => true,
			'fields'     => array(
				array(
					'name' => __( 'Year', 'academic' ),
					'desc' => __('Choose courses year to display.', 'academic'),
					'id'   => $prefix .'course_years',
					'type' => 'text',				
				),
				array(
					'name' => __('Semesters', 'academic'),
					'desc' => __('Choose courses semesters to display', 'academic'),
					'id'   => $prefix . 'course_semesters',
					'type' => 'select',
					'show_option_none' => true,
					'options' => $this->course_semesters
				),
				array(
					'name' => __('Types', 'academic'),
					'desc' => __('Choose courses types to display', 'academic'),
					'id'   => $prefix . 'course_types',
					'type' => 'select',
					'show_option_none' => true,
					'options' => $this->course_types
				)
			)
		) );
	}

	public static function get_years_of_courses() {
		global $wpdb;
		$wpdb_fields = $wpdb->get_col("
			SELECT DISTINCT meta_value
			FROM " . $wpdb->postmeta . "
			WHERE meta_key = 'cmb_course_year'
			ORDER BY meta_value DESC
		");
		return $wpdb_fields;
	}

	public static function add_course_from_query($post_id) {
		$prefix = "cmb_";
		$course = array();
		$course['id'] = $post_id;
		$course['url'] = get_post_permalink($course['id']);
		$course['name'] = get_the_title($course['id']);
		$course['number'] = get_post_meta( $course['id'], $prefix.'course_number', true );
		$course['degree'] = get_post_meta( $course['id'], $prefix.'course_degree', true );
		if ($course['degree'] == 1) {
			$course['degree'] = __('BA', 'academic');
		} else if ($course['degree'] == 2) {
			$course['degree'] = __('MA', 'academic');
		} else if ($course['degree'] == 3) {
			$course['degree'] = __('Ph.D', 'academic');
		}
		$course['credit'] = get_post_meta( $course['id'], $prefix.'course_credit', true );
		$course['year'] = get_post_meta( $course['id'], $prefix.'course_year', true );
		$course['semester'] = get_post_meta( $course['id'], $prefix.'course_semester', true );
		if ($course['semester'] == "") $course['semester'] = array("-");
		$course['active'] = get_post_meta( $course['id'], $prefix.'course_active', true ) ? true : false;
		$course['type'] = get_post_meta( $course['id'], $prefix.'type_course', true );
		$course['lecturer_name'] = get_post_meta( $course['id'], $prefix.'course_lecturer_name', true );
		$course['lecturer_link'] = get_post_meta( $course['id'], $prefix.'course_lecturer_link', true );
		$course['file'] = get_post_meta( $course['id'], $prefix.'course_file', true );

		$course = str_replace(array("'", "\"", "&quot;"), "", $course);

		return $course;
	}
}
add_action( 'plugins_loaded', array( 'CoursePostType', 'get_instance' ) );
		

<?php
/**
 * WP Robo Theme
 */

/** Defines  */
define( 'ROBO_THEME_DIR', get_template_directory() );
define( 'ROBO_THEME_URL', get_stylesheet_directory_uri() );

/**
 * If you are installing Timber as a Composer dependency in your theme, you'll need this block
 * to load your dependencies and initialize Timber. If you are using Timber via the WordPress.org
 * plug-in, you can safely delete this block.
 */
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}

/**
 * This ensures that Timber is loaded and available as a PHP class.
 * If not, it gives an error message to help direct developers on where to activate
 */
if ( ! class_exists( 'Timber' ) ) {

	add_action(
		'admin_notices',
		function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

/**
 * Sets the directories (inside your theme) to find .twig files
 */
Timber::$dirname = [ 'templates', 'views' ];

/**
 * By default, Timber does NOT autoescape values. Want to enable Twig's autoescape?
 * No prob! Just set this value to true
 */
Timber::$autoescape = false;


/**
 * We're going to configure our theme inside of a subclass of Timber\Site
 * You can move this to its own file and include here via php's include("MySite.php")
 */
class RoboSite extends Timber\Site {
	/** Add timber support. */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'robo_theme_setup' ] );
		add_action( 'after_setup_theme', [ $this, 'robo_menus_register' ] );
		add_action( 'widgets_init', [ $this, 'robo_widgets_init' ] );
		add_filter( 'timber/context', [ $this, 'add_to_context' ] );
		add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
		add_filter( 'timber/acf-gutenberg-blocks-templates', [$this, 'robo_block_templates'] );

		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		// add_action( 'init', [ $this, 'robo_gutemberg_style']);

		add_action( 'acf/init', [$this, 'robo_acf_init']);
		parent::__construct();
	}
	/** This is where you can register custom post types. */
	public function register_post_types() {

	}
	/** This is where you can register custom taxonomies. */
	public function register_taxonomies() {

	}

	/** This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context( $context ) {
		$context['roboweb'] = 'RoboWeb';
		$context['reprezentuj'] = 'Reprezentuj';
		$context['menu'] = [
			'primary'	=> new Timber\Menu('primary'),
			'footer' 	=> new Timber\Menu('footer'),
			'langs' 	=> new Timber\Menu('langs'),
			'social' 	=> new Timber\Menu('social')
		];
		$context['site']  = $this;
		return $context;
	}

	/**
	 * robo_theme_setup function
	 */
	public function robo_theme_setup() {
		/*
         * Make theme available for translation.
         * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/robo
         * If you're building a theme based on Twenty Sixteen, use a find and replace
         * to change 'robo' to the name of your theme in all the template files
         */
		load_theme_textdomain('robo', ROBO_THEME_DIR . '/languages');
		
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size('rcom-featured-image', 2000, 1200, true);
		set_post_thumbnail_size( 1568, 9999 );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			[
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			]
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			[
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			]
		);

		add_theme_support( 'menus' );

		// Indicate widget sidebars can use selective refresh in the Customizer.
		add_theme_support('customize-selective-refresh-widgets');
		
		// DISBALE WP AUTO P
        // remove_filter( 'the_content', 'wpautop' );
		remove_filter('the_excerpt', 'wpautop');
		// REMOVE WP EMOJI
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		
		/** EDITOR - GUTENBERG*/
		add_theme_support( 'wp-block-styles' );
		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );
		add_editor_style( 'style-editor.css' );

		// Editor color palette.
		$black     = '#000000';
		$dark_gray = '#28303D';
		$gray      = '#39414D';
		$green     = '#D1E4DD';
		$blue      = '#D1DFE4';
		$purple    = '#D1D1E4';
		$red       = '#E4D1D1';
		$orange    = '#E4DAD1';
		$yellow    = '#EEEADD';
		$white     = '#FFFFFF';

		add_theme_support(
			'editor-color-palette',
			[
				[
					'name'  => esc_html__( 'Black', 'robo' ),
					'slug'  => 'black',
					'color' => $black,
				],
				[
					'name'  => esc_html__( 'Dark gray', 'robo' ),
					'slug'  => 'dark-gray',
					'color' => $dark_gray,
				],
				[
					'name'  => esc_html__( 'Gray', 'robo' ),
					'slug'  => 'gray',
					'color' => $gray,
				],
				[
					'name'  => esc_html__( 'Green', 'robo' ),
					'slug'  => 'green',
					'color' => $green,
				],
				[
					'name'  => esc_html__( 'Blue', 'robo' ),
					'slug'  => 'blue',
					'color' => $blue,
				],
				[
					'name'  => esc_html__( 'Purple', 'robo' ),
					'slug'  => 'purple',
					'color' => $purple,
				],
				[
					'name'  => esc_html__( 'Red', 'robo' ),
					'slug'  => 'red',
					'color' => $red,
				],
				[
					'name'  => esc_html__( 'Orange', 'robo' ),
					'slug'  => 'orange',
					'color' => $orange,
				],
				[
					'name'  => esc_html__( 'Yellow', 'robo' ),
					'slug'  => 'yellow',
					'color' => $yellow,
				],
				[
					'name'  => esc_html__( 'White', 'robo' ),
					'slug'  => 'white',
					'color' => $white,
				],
			]
		);

		// Add custom editor font sizes.
		// add_theme_support('disable-custom-font-sizes');
		add_theme_support(
			'editor-font-sizes',
			[
				[
					'name'      => esc_html__( 'Extra small', 'robo' ),
					'shortName' => esc_html_x( 'XS', 'Font size', 'robo' ),
					'size'      => 16,
					'slug'      => 'extra-small',
				],
				[
					'name'      => esc_html__( 'Small', 'robo' ),
					'shortName' => esc_html_x( 'S', 'Font size', 'robo' ),
					'size'      => 18,
					'slug'      => 'small',
				],
				[
					'name'      => esc_html__( 'Normal', 'robo' ),
					'shortName' => esc_html_x( 'M', 'Font size', 'robo' ),
					'size'      => 20,
					'slug'      => 'normal',
				],
				[
					'name'      => esc_html__( 'Large', 'robo' ),
					'shortName' => esc_html_x( 'L', 'Font size', 'robo' ),
					'size'      => 24,
					'slug'      => 'large',
				],
				[
					'name'      => esc_html__( 'Extra large', 'robo' ),
					'shortName' => esc_html_x( 'XL', 'Font size', 'robo' ),
					'size'      => 40,
					'slug'      => 'extra-large',
				],
				[
					'name'      => esc_html__( 'Huge', 'robo' ),
					'shortName' => esc_html_x( 'XXL', 'Font size', 'robo' ),
					'size'      => 96,
					'slug'      => 'huge',
				],
				[
					'name'      => esc_html__( 'Gigantic', 'robo' ),
					'shortName' => esc_html_x( 'XXXL', 'Font size', 'robo' ),
					'size'      => 144,
					'slug'      => 'gigantic',
				],
			]
		);

		// Custom background color.
		add_theme_support(
			'custom-background',
			[
				'default-color' => 'd1e4dd',
			]
		);

		// Add support for custom line height controls.
		add_theme_support( 'custom-line-height' );
	}

	/**
	 * robo Menus Register
	 */
	public function robo_menus_register() {
		register_nav_menus(
			[
				'primary' => esc_html__( 'Primary menu', 'robo' ),
				'footer'  => __( 'Footer menu', 'robo' ),
				'langs'  => __( 'Languages menu', 'robo' ),
				'social'  => __( 'Social Links menu', 'robo' ),
			]
		);
	}

	/**
	 * robo_widgets_init
	 * For registers all widget places
	 */
	public function robo_widgets_init() {
		register_sidebar([
            'name' => __('Bottom Sidebar', 'rcom'),
            'id' => 'sidebar-bottom',
            'description' => __('Add widgets here to appear in the bottom of the pages.', 'rcom'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
		]);
        register_sidebar([
            'name' => __('Sidebar', 'rcom'),
            'id' => 'sidebar',
            'description' => __('Add widgets here to appear in the sidebar.', 'rcom'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
		]);
	}

	/** Print HTML with date information for current post */
	public function robo_date() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s"><span class="day">%2$s</span><span class="month">%3$s</span><span class="year">%4$s</span></time>';

        $date = get_the_modified_date('c') !== get_the_date('c') ? get_the_modified_date('c') : get_the_date('c');
        $date_y = get_the_date('Y');
        $date_m = get_the_date('F');
        $date_d = get_the_date('d');

        $time_string = sprintf($time_string,
            esc_attr($date),
            $date_d, $date_m, $date_y
        );

        printf('<span class="posted-on">%1$s</span>',
            $time_string
        );
	}

	/** This Would return 'foo bar!'.
	 *
	 * @param string $text being 'foo', then returned 'foo bar!'.
	 */
	public function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
	}

	/** This is where you can add your own functions to twig.
	 *
	 * @param string $twig get extension.
	 */
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		$twig->addFilter( new Twig\TwigFilter( 'myfoo', [ $this, 'myfoo' ] ) );
		return $twig;
	}

	public function robo_block_templates() {
		return ['templates/blocks'];
	}

	/** Robo ACF init */
	public function robo_acf_init() {
		// sprawdzamy czy funkcja istnieje
        if( !function_exists('acf_register_block_type') ) return;
                
		// `Hero Content`
		acf_register_block_type([
			'name'				=> 'robo-hero-content',
			'title'             => __('Robo: Hero Content Block'),
			'description'       => __('A part of hero section with header, introduction and link.'),
			'render_callback'   => [$this, 'robo_acf_block_render_callback'],
			'category'          => 'layout',
			'icon'              => 'format-aside',
			'keywords'          => [ 'hero', 'text', 'quote' ]
		]);

		// Robo Img
		// acf_register_block_type([
		// 	'name'				=> 'robo-image',
		// 	'title'             => __('Robo: Image'),
		// 	'description'       => __('Image with additionals settings.'),
		// 	'render_callback'   => [$this, 'robo_acf_block_render_callback'],
		// 	'category'          => 'layout',
		// 	'icon'              => 'format-image',
		// 	'keywords'          => [ 'image', 'align', 'offset' ]
		// ]);

	}

	public function robo_acf_block_render_callback( $block, $content = '', $is_preview = false ) {
		// $context = Timber::context();
	
		// Store block values.
		$vars['block'] = $block;
	
		// Store field values.
		$vars['fields'] = get_fields();
	
		// Store $is_preview value.
		$vars['is_preview'] = $is_preview;
	
		// convert name ("acf/testimonial") into path friendly slug ("testimonial")
		$slug = str_replace('acf/', '', $block['name']);
	
		// Render the block.
		Timber::render( 'templates/blocks/' . $block['name'] . '.twig', $vars );
	}
	
}

new RoboSite();
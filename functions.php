<?php
/**
 * WP Robo Theme
 */

/** Defines  */
define( 'ROBO_THEME_DIR', get_template_directory() );
define( 'ROBO_THEME_URL', get_stylesheet_directory_uri() );
define( 'GOOGLE_MAP_API_KEY', 'AIzaSyBle18YiP8NvmXQQTHDz2oxbRvbiwZVaWM');

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
	/** @private $color_palette {Array} */
	private $color_palette = [];

	/** Add timber support. */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'robo_theme_setup' ] );
		add_action( 'after_setup_theme', [ $this, 'robo_menus_register' ] );

		add_action('phpmailer_init', [$this, 'robo_phpmailer_init']);

		add_action( 'widgets_init', [ $this, 'robo_widgets_init' ] );
		add_filter( 'timber/context', [ $this, 'add_to_context' ] );
		add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );

		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		add_action( 'acf/init', [$this, 'robo_acf_init']);

		add_action( 'wp_enqueue_scripts', [$this, 'robo_jscipts'] );

		// remove styles of advanced forms
		// wp_dequeue_style( 'af-form-style' );
		// wp_dequeue_style( 'select2' );
		// wp_dequeue_style( 'acf-input' );

		// // Date picker
		// wp_dequeue_script( 'jquery-ui-datepicker' );
		// wp_dequeue_style( 'acf-datepicker' );

		// // Date and time picker
		// wp_dequeue_script( 'acf-timepicker' );
		// wp_dequeue_style( 'acf-timepicker' );

		// // Color picker
		// wp_dequeue_script( 'wp-color-picker' );
		// wp_dequeue_style( 'wp-color-picker' );

		parent::__construct();
	}
	/** REgister js scripts */
	public function robo_jscipts() {
		wp_enqueue_script(
			'robo_js',
			get_theme_file_uri( '/static/js/min.main.js' ),
			[],
			'20210325.1919.33',
			true
		);
		wp_enqueue_script(
			'robo_scrollme_js',
			get_theme_file_uri( '/static/js/jquery.scrollme.min.js' ),
			['jquery'],
			'1.1.0',
			true
		);
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
		$context['roboweb'] 	= 'RoboWeb';
		$context['reprezentuj'] = 'Reprezentuj';

		$context['custom_logo'] = [
			'max' => [
				'width' => 250,
				'height' => 96
			],
			'min' => [
				'width' => 120,
				"height" => 76			]
		];
		$context['menu'] = [
			'primary'	=> new Timber\Menu('primary'),
			'footer' 	=> new Timber\Menu('footer'),
			'social' 	=> new Timber\Menu('social')
		];
		$context['langs'] = pll_the_languages(['raw' => 1]);

		$context['bottom_widgets'] = Timber::get_widgets( 'sidebar-bottom' );
		$context['site'] = $this;
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
		add_filter('use_block_editor_for_post', '__return_false', 10);
		// add_theme_support( 'wp-block-styles' );
		// Add support for full and wide align images.
		// add_theme_support( 'align-wide' );

		// Add support for editor styles.
		// add_theme_support( 'editor-styles' );
		// add_editor_style( 'style-editor.css' );




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

	/**
	 * @param number of ID of formular
	 */
	public function render_form($id) {
		$args = [
			'display_title' => false,
			'display_description' => false,
			'submit_text' => __('Submit', 'rcom'),
			'echo' => false,
			'target' => '#contact_form_' . $id
		];

		return advanced_form($id, $args);
	}

	/** This is where you can add your own functions to twig.
	 *
	 * @param string $twig get extension.
	 */
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		$twig->addFilter( new Twig\TwigFilter( 'myfoo', [ $this, 'myfoo' ] ) );
		$twig->addFunction(new Timber\Twig_Function('renderForm', [$this, 'render_form']));
		return $twig;
	}

	/** Robo ACF init */
	public function robo_acf_init() {
		
		// Register Google API Key
		acf_update_setting('google_api_key', GOOGLE_MAP_API_KEY);

		// sprawdzamy czy funkcja istnieje
        if( !function_exists('acf_register_block_type') ) return;
		// Check function exists.
		if( function_exists('acf_add_options_page') ) {

			// Add parent.
			$parent = acf_add_options_page(array(
				'page_title'  => __('Page General Settings'),
				'menu_title'  => __('Site Settings'),
				'redirect'    => false,
			));
	
			// Add sub page.
			$child = acf_add_options_page(array(
				'page_title'  => __('Form Subbmission Settings'),
				'menu_title'  => __('SMTP E-mail Account'),
				'parent_slug' => $parent['menu_slug'],
			));
		}

	}

	private function __addColors($color, $variants) {
		/** 
		 * Pattern:
		 * [
		 * 	'name'  => '',
		 * 	'slug'  => '',
		 * 	'color' => ''
		 * ];
		 * */

		foreach($variants as $key => $value) {
			$this->color_palette[] = [
				'name' => $color . ' (' . $key . ')',
				'slug' => strtolower($color) . '_' . $key,
				'color' => $value
			];
		}
	}	

	/** Robo PHPMailer config */
	public function robo_phpmailer_init($pm) {
		// $pm -> phpmailer
		$pm->Host = get_field('host', 'options') ?: 'smtp.dpoczta.pl';
		$pm->Port = get_field('port', 'options') ?: 587;
		$pm->Username = get_field('email_account_username', 'options') ?: 'wpapp.admin@roboweb.eu'; // your SMTP username
		$pm->Password = get_field('email_account_password', 'options') ?: '8foC9jUy5D'; // your SMTP password
		$pm->SMTPAuth = true; 
		$pm->SMTPSecure = get_field('smtp_auth', 'options') ?: 'tls'; // preferable but optional
		$pm->IsSMTP();
	}
}

new RoboSite();
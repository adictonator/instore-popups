<?php
/**
 * Plugin Name: Instore Popups
 * Description: The one and only custom popup solution for Instore.
 * Version: 1.0.0
 * Author: Sagmetic Infotech Pvt. Ltd.
 * Author URI: https://sagmetic.com
 * Licence: GNU
 */

/**
 * @author Adictonator <adityabhaskarsharma@email.com>
 * @package Instore Popup
 * @since 1.0.0
 */
final class IPopUp
{
	/**
	 * Holds the instance of current class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Various animation type for the popup.
	 *
	 */
	const ANIMATIONS = [
		'fade' => 'Fade In',
		'left' => 'Slide from Left',
		'right' => 'Slide from Right',
		'top' => 'Slide from Top',
		'bottom' => 'Slide from Bottom',
	];

	/**
	 * Bootstrap all the functions and hooks.
	 *
	 */
	public function __construct()
	{
		add_action( 'init', [__CLASS__, 'popupPostType'] );
		add_action( 'wp_footer', [$this, 'displayPopup'] );
		add_action( 'add_meta_boxes', [$this, 'popupMetabox'] );
		add_action( 'save_post', [$this, 'onSavePost'] );
		add_shortcode( 'ipop_timer', [$this, 'timerSCFunction'] );
		add_action( 'wp_enqueue_scripts', [$this, 'popupAssets'] );
		add_action( 'wp_ajax_popupSaveMeta', [$this, 'popupSaveMeta'] );
		add_action( 'wp_ajax_getPopupURLs', [$this, 'getPopupURLs'] );
		add_action( 'wp_ajax_nopriv_getPopupURLs', [$this, 'getPopupURLs'] );
		add_action( 'admin_enqueue_scripts', [$this, 'popupAdminAssets'] );
	}

	/**
	 * Update `post ids` collection data to show popup.
	 *
	 * @param integer $postID
	 * @return void
	 */
	public function onSavePost( int $postID )
	{
		if ( wp_is_post_revision( $postID ) ) return;

		$catSlugs = get_option( '__instore_popup_cat_slugs', [] );
		$catPostIDs = get_option( '__instore_popup_cat_post_ids', [] );
		$postCatSlugs = [];

		foreach ( get_the_category( $postID ) as $cats ) :
			$postCatSlugs[] = $cats->slug;
		endforeach;

		foreach ( $catSlugs as $popupID => $slug ) :
			if ( ! empty( array_intersect( $slug, $postCatSlugs ) ) &&
				 ! in_array( $postID, $catPostIDs[ $popupID ] ) ) :
				$catPostIDs[ $popupID ][] = $postID;
				update_option( '__instore_popup_cat_post_ids', $catPostIDs );
				break;
			endif;
		endforeach;
	}

	/**
	 * Save popup meta values to database.
	 *
	 * @return void
	 */
	public function popupSaveMeta()
	{
		if ( isset( $_POST['postID'] ) && $_POST['postID'] > 0 ) :
			$urls = $wildUrls = $catSlugs = null;

			/** Save page URLs if provided. */
			if ( isset( $_POST['ipopup-urls'] ) ) :
				if ( empty( $_POST['ipopup-urls'] ) ) $urls = [ 'http://' ];
				else $urls = array_filter( preg_split( '/\r\n|\r|\n/', $_POST['ipopup-urls'] ) );

				$this->checkURLType( $urls, $_POST['postID'] );
			endif;

			/** Save `wildcard` URLs if provided. */
			if ( isset( $_POST['ipopup-wild-urls'] ) ) :
				$wildUrls = get_option( '__instore_popup_wild_urls', [] );
				$wildUrls[  $_POST['postID'] ] = array_filter( preg_split( '/\r\n|\r|\n/', $_POST['ipopup-wild-urls'] ) );

				update_option( '__instore_popup_wild_urls', $wildUrls );
			endif;

			/** Save `category slugs` if provided. */
			if ( isset( $_POST['ipopup-cat-slugs'] ) ) :
				$catSlugsArr = array_filter( preg_split( '/\r\n|\r|\n/', $_POST['ipopup-cat-slugs'] ) );
				$catSlugs[ $_POST['postID'] ] = $catSlugsArr;

				/** Need this collection during `individual post save` check. */
				update_option( '__instore_popup_cat_slugs', $catSlugs );
				$allPostIDs = [];

				foreach ( $catSlugsArr as $catSlug ) :
					$query = $this->getPostsByTerm( $catSlug );

					if ( $query->have_posts() ) :
						while ( $query->have_posts() ) : $query->the_post();
							$allPostIDs[ $_POST['postID'] ][] = get_the_ID();
						endwhile;

						wp_reset_postdata();
					endif;
				endforeach;

				update_option( '__instore_popup_cat_post_ids', $allPostIDs );
			endif;

			/** Style variables. @todo might shift to a separate function. */
			$desktopWidth = $_POST['ipopup-width']['desktop']['val'].$_POST['ipopup-width']['desktop']['unit'];
			$tabletWidth = $_POST['ipopup-width']['tablet']['val'].$_POST['ipopup-width']['tablet']['unit'];
			$mobileWidth = $_POST['ipopup-width']['mobile']['val'].$_POST['ipopup-width']['mobile']['unit'];
			$bgColor = isset( $_POST['ipopup-bg-color'] ) ?
					    ( empty ( $_POST['ipopup-bg-color'] ) ? 'transparent' : $_POST['ipopup-bg-color'] )
						: '#FFF';

			/** Prepare meta data. */
			$popupMeta = [
				'enabled' => $_POST['ipopup-true'],
				'popID' => $_POST['ipopup'],
				'urls' => $urls,
				'wildUrls' => $wildUrls,
				'catSlugs' => $catSlugs,
				'animation' => $_POST['ipopup-anim'],
				'style' => [
					'bg' => $bgColor,
					'width' => [
						'desktop' => $desktopWidth,
						'tablet' => $tabletWidth,
						'mobile' => $mobileWidth,
					],
				],
			];

			/** Only update if the `post type` is either `ipopups` or `page`. */
			if ( get_post_type( $_POST['postID'] ) === 'ipopups' || get_post_type( $_POST['postID'] ) === 'page' )
				update_post_meta( $_POST['postID'], '__instore_meta', $popupMeta );
		endif;

		wp_die();
	}

	/**
	 * Retrieves posts based on the category slug provided.
	 *
	 * @param string $categorySlug
	 * @return object WP_Query object
	 */
	private function getPostsByTerm( string $categorySlug )
	{
		$catID = get_term_by( 'slug', $categorySlug, 'category' )->term_id;

		$args = [
			'post_type' => 'post',
			'tax_query' => [
				[
					'taxonomy' => 'category',
					'field' => 'term_id',
					'terms' => $catID,
				],
			],
		];

		return new WP_Query( $args );
	}

	/**
	 * Displays popup to the page statically.
	 *
	 * @return void
	 */
	public function displayPopup()
	{
		$popupMeta = get_post_meta( get_the_ID(), '__instore_meta', true );

		if ( ! empty( $popupMeta ) && isset( $popupMeta['enabled'] ) && $popupMeta['enabled'] === 'on'  ) :
			echo $this->getPopupContent( $popupMeta['popID'] );
		endif;
	}

	/**
	 * Retrieves the different conditions for URL matching.
	 * Check for `wild urls`, `full urls` and `category slugs`.
	 * Accessed via AJAX.
	 *
	 * @return void
	 */
	public function getPopupURLs()
	{
		$urls = get_option( '__instore_popup_urls', [] );
		$cats = get_option( '__instore_popup_cat_post_ids', [] );
		$wildUrls = get_option( '__instore_popup_wild_urls', [] );

		$pageURL = $_SERVER['HTTP_REFERER'];
		$currentURL = explode( '/', $pageURL );
		$return['success'] = false;

		foreach ( $urls as $popupID => $url ) :
			$hasUrl = in_array( $pageURL, $url );

			if ( ! empty( $hasUrl ) ) :
				$return['success'] = true;
				$return['type'] = 'url';
				$return['html'] = $this->getPopupContent( $popupID );
				break;
			endif;
		endforeach;

		foreach ( $wildUrls as $popupID => $url ) :
			$hasUrl = array_intersect( $url, $currentURL );

			if ( ! empty( $hasUrl ) ) :
				$return['success'] = true;
				$return['type'] = 'wild';
				$return['html'] = $this->getPopupContent( $popupID );
				break;
			endif;
		endforeach;

		foreach ( $cats as $popupID => $cat ) :
			if ( in_array( $_GET['postID'], $cat ) ) :
				$return['success'] = true;
				$return['type'] = 'cat';
				$return['html'] = $this->getPopupContent( $popupID );
				break;
			endif;
		endforeach;

		echo json_encode( $return );
		wp_die();
	}

	/**
	 * Displays metabox to select popups in page post types.
	 *
	 * @return void
	 */
	public function popupMetaboxContent()
	{
		ob_start();
		include_once __DIR__ . '/includes/metabox/metabox.php';
		$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	}

	/**
	 * Displays `instore` post type metabox and its properties.
	 *
	 * @return void
	 */
	public function popupMetaboxPostContent()
	{
		$settings = get_post_meta( get_the_ID(), '__instore_meta', true );
		$urls = isset( $settings['urls'] ) ? implode( "\n", $settings['urls'] ) : '';
		$wildUrls = isset( $settings['wildUrls'][ get_the_ID() ] ) ? implode( "\n", $settings['wildUrls'][ get_the_ID() ] ) : '';
		$catSlugs = isset( $settings['catSlugs'][ get_the_ID() ] ) ? implode( "\n", $settings['catSlugs'][ get_the_ID() ] ) : '';
		$animation = isset( $settings['animation'] ) ? $settings['animation'] : false;
		$width = isset( $settings['style'] ) && isset( $settings['style']['width'] ) ? $settings['style']['width'] : false;
		$desktopWidth = ( int ) $width['desktop'];
		$desktopUnit = preg_replace( '/[0-9]+/', '', $width['desktop'] );

		$tabletWidth = ( int ) $width['tablet'];
		$tabletUnit = preg_replace( '/[0-9]+/', '', $width['tablet'] );

		$mobileWidth = ( int ) $width['mobile'];
		$mobileUnit = preg_replace( '/[0-9]+/', '', $width['mobile'] );

		ob_start();
		include_once __DIR__ . '/includes/metabox/post-metabox.php';
		$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	}

	/**
	 * Checks if the URL is a valid one and updates the `post ids` collection.
	 *
	 * @param array $urls
	 * @param integer $popupID
	 * @return void
	 */
	private function checkURLType( array $urls, int $popupID )
	{
		foreach ( $urls as $url ) :
			/** Check if the URL has `https/http`. */
			if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) :
				$saveUrls[ $popupID ][] = $url;

				update_option( '__instore_popup_urls', $saveUrls );
			endif;
		endforeach;
	}

	/**
	 * not sure.
	 *
	 * @deprecated maybe
	 * @param [type] $attrs
	 * @return void
	 */
	public function timerSCFunction( $attrs )
	{
		$atts = shortcode_atts( array(
			'time' => 600,
		), $attrs, 'ipop_timer' );

		return "<span data-popup-counter='{$atts['time']}'>{$atts['time']}</span>";
	}

	/**
	 * Displays popup content to the page.
	 *
	 * @param integer $id
	 * @return mixed Popup content
	 */
	private function getPopupContent( int $id )
	{
		$popupContent = get_post( $id )->post_content;
		$popupContentMeta = get_post_meta( $id, '__instore_meta', true );
		$animClass = isset( $popupContentMeta['animation'] ) ? ' instore-anim-' . $popupContentMeta['animation'] : '';
		$width = isset( $popupContentMeta['style'] ) && isset( $popupContentMeta['style']['width'] ) ? $popupContentMeta['style']['width'] : '800px';
		$bgColor = isset( $popupContentMeta['style'] ) && isset( $popupContentMeta['style']['bg'] ) ? $popupContentMeta['style']['bg'] : '#FFF';

		ob_start();
		include_once __DIR__ . '/includes/popup/popup.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Add meta-boxes to post types.
	 *
	 * @return void
	 */
	public function popupMetabox()
	{
		add_meta_box( 'ipopup-metabox', __( 'Select a Popup Type' ), [$this, 'popupMetaboxContent'], 'page', 'side' );
		add_meta_box( 'ipopup-metabox-post', __( 'Popup Configurations' ), [$this, 'popupMetaboxPostContent'], 'ipopups' );
	}

	/**
	 * Enqueue required assets for backend.
	 *
	 * @return void
	 */
	public function popupAdminAssets()
	{
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'ipopup-admin', plugin_dir_url( __FILE__ ) . 'assets/admin/js/app.js', ['jquery'], null, true );
		wp_enqueue_style( 'ipopup-admin', plugin_dir_url( __FILE__ ) . 'assets/admin/css/app.css' );
	}

	/**
	 * Enqueue required assets for frontend.
	 *
	 * @return void
	 */
	public function popupAssets()
	{
		wp_enqueue_script( 'ipopup', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', ['jquery'], null, true );
		wp_localize_script( 'ipopup', 'ipopup', [
			'url' => admin_url( 'admin-ajax.php' ),
			'postID' => get_the_ID(),
		] );
		wp_enqueue_style( 'ipopup', plugin_dir_url( __FILE__ ) . 'assets/css/app.css' );
	}

	/**
	 * Register the custom post type `ipopups`.
	 *
	 * @return void
	 */
	public static function popupPostType()
	{
		$labels = array(
			'name'               => _x( 'Instore Popups', 'post type general name', 'your-plugin-textdomain' ),
			'singular_name'      => _x( 'Instore Popup', 'post type singular name', 'your-plugin-textdomain' ),
			'menu_name'          => _x( 'Instore Popups', 'admin menu', 'your-plugin-textdomain' ),
			'name_admin_bar'     => _x( 'Instore Popup', 'add new on admin bar', 'your-plugin-textdomain' ),
			'add_new'            => _x( 'Add New', 'Instore Popup', 'your-plugin-textdomain' ),
			'add_new_item'       => __( 'Add New Instore Popup', 'your-plugin-textdomain' ),
			'new_item'           => __( 'New Instore Popup', 'your-plugin-textdomain' ),
			'edit_item'          => __( 'Edit Instore Popup', 'your-plugin-textdomain' ),
			'view_item'          => __( 'View Instore Popup', 'your-plugin-textdomain' ),
			'all_items'          => __( 'All Instore Popups', 'your-plugin-textdomain' ),
			'search_items'       => __( 'Search Instore Popups', 'your-plugin-textdomain' ),
			'parent_item_colon'  => __( 'Parent Instore Popups:', 'your-plugin-textdomain' ),
			'not_found'          => __( 'No Instore Popups found.', 'your-plugin-textdomain' ),
			'not_found_in_trash' => __( 'No Instore Popups found in Trash.', 'your-plugin-textdomain' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'your-plugin-textdomain' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'instore-popup' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);

		register_post_type( 'ipopups', $args );
	}

	/**
	 * Set the instance of current class.
	 *
	 * @return object IPopUp object
	 */
	private static function __instance()
	{
		if ( null === self::$_instance ) self::$_instance = new self;

		return self::$_instance;
	}

	/**
	 * Instantiates the class.
	 *
	 * @return void
	 */
	public static function start()
	{
		self::__instance();
	}
}

IPopUp::start();
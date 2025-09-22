<?php
/**
 * @package    WordPress
 * @subpackage Traveler
 * @since      1.0
 *
 * Class STCustomize
 *
 * Created by ShineTheme
 */

if ( ! class_exists( 'STCustomize' ) ) {
	class STCustomize {
		protected static $_inst;
		public function __construct() {
			add_action( 'after_setup_theme', [ $this, 'st_custom_logo_setup' ] );
			add_action( 'customize_register', [ $this, 'st_customizer_setting' ] );
			add_filter( 'get_custom_logo', [ $this, 'st_change_logo_class' ] );
		}

		public function st_change_logo_class( $html ) {

			$html = str_replace( 'custom-logo-link', 'logo d-none d-sm-none d-lg-block', $html );

			return $html;
		}

		public function st_custom_logo_setup() {
			$defaults = [
				'height'      => 250,
				'width'       => 250,
				'flex-height' => true,
				'flex-width'  => true,
			];
			add_theme_support( 'custom-logo', $defaults );
		}

		public function st_customizer_setting( $wp_customize ) {
			$wp_customize->add_setting( 'mobile_logo' );
			$wp_customize->add_control( new WP_Customize_Cropped_Image_Control( $wp_customize, 'mobile_logo', [
				'label'       => __( 'Logo Mobile', 'Text Domain' ),
				'section'     => 'title_tagline',
				'height'      => 100, // cropper Height
				'width'       => 100, // Cropper Width
				'flex_width'  => true, // Flexible Width
				'flex_height' => true, // Flexible Heiht
			] ) );
		}
		static function inst() {
			if ( ! self::$_inst ) {
				self::$_inst = new self();
			}

			return self::$_inst;
		}
	}

	STCustomize::inst();
}

<?php
/**
 * The Image Seo Pro Rest functionality for the plugin.
 *
 * Defines the functionality for the Image Seo Pro Rest.
 *
 * @since      3.0.83
 * @package    RankMathPro
 * @subpackage RankMathPro\Image_Seo_Pro
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Image_Seo_Pro;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use RankMathPro\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class for Image SEO module.
 */
class Rest extends WP_REST_Controller {
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Plugin path
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace   = \RankMath\Rest\Rest_Helper::BASE;
		$this->plugin      = 'imagify';
		$this->plugin_path = 'imagify/imagify.php';
	}

	/**
	 * Registers rest routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/activateImagify',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'activate_imagify_plugin' ],
				'permission_callback' => [ $this, 'can_manage_plugins' ],
			]
		);
	}

	/**
	 * Handles plugin installation and activation.
	 *
	 * @param WP_REST_Request $request Incoming request object.
	 *
	 * @return \WP_REST_Response Rest Response object.
	 */
	public function activate_imagify_plugin( WP_REST_Request $request ) {
		if ( ! $this->is_plugin_installed() ) {
			if ( ! $this->install_plugin() ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => sprintf(
							/* translators: Bold text */
							esc_html__( '%s Unable to install the Imagify plugin. Please try again later.', 'rank-math-pro' ),
							'<strong>' . esc_html__( 'Installation Failed:', 'rank-math-pro' ) . '</strong>'
						),
					]
				);
			}
		}

		if ( ! $this->is_plugin_activated() ) {
			$activate_plugin = activate_plugin( $this->plugin_path );
			if ( is_wp_error( $activate_plugin ) ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => $activate_plugin->get_error_message(),
					]
				);
			}
		}

		update_option( 'imagifyp_id', 'rankmathpro', false );
		return new WP_REST_Response(
			[
				'success' => true,
				'message' => sprintf(
					/* translators: Bold text */
					esc_html__( 'Imagify has been successfully activated! %s.', 'rank-math-pro' ),
					'<strong><a href="' . esc_url( admin_url( 'options-general.php?page=imagify' ) ) . '">' . esc_html__( 'Configure settings now', 'rank-math-pro' ) . '</a></strong>'
				),
			]
		);
	}

	/**
	 * Check if user can manage imagify plugin installation and activation.
	 *
	 * @return bool
	 */
	public function can_manage_plugins() {
		return Admin_Helper::can_activate_imagify();
	}

	/**
	 * Check if plugin is installed on the site.
	 *
	 * @return boolean Whether it's installed or not.
	 */
	private function is_plugin_installed() {
		// First check if active, because that is less costly.
		if ( $this->is_plugin_activated() ) {
			return true;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @phpstan-ignore-line
		}
		$installed_plugins = get_plugins();

		return array_key_exists( $this->plugin_path, $installed_plugins );
	}

	/**
	 * Install the plugin from the wordpress.org repository.
	 *
	 * @return bool Whether install was successful.
	 */
	private function install_plugin() {
		include_once ABSPATH . 'wp-includes/pluggable.php'; // @phpstan-ignore-line
		include_once ABSPATH . 'wp-admin/includes/misc.php'; // @phpstan-ignore-line
		include_once ABSPATH . 'wp-admin/includes/file.php'; // @phpstan-ignore-line
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // @phpstan-ignore-line
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // @phpstan-ignore-line
		$skin        = new \Automatic_Upgrader_Skin();
		$upgrader    = new \Plugin_Upgrader( $skin );
		$plugin_file = "https://downloads.wordpress.org/plugin/{$this->plugin}.latest-stable.zip";
		$result      = $upgrader->install( $plugin_file );

		return $result;
	}

	/**
	 * Check if given plugin is activated on the site.
	 *
	 * @return boolean Whether it's active or not.
	 */
	private function is_plugin_activated() {
		$active_plugins = get_option( 'active_plugins', [] );
		return in_array( $this->plugin_path, $active_plugins, true );
	}
}

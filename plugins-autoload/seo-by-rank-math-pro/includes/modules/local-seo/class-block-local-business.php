<?php
/**
 * The Local_Business Block
 *
 * @since      3.0.76
 * @package    RankMath
 * @subpackage RankMathPro
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Local_Seo;

use WP_Block_Type_Registry;
use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Location Block class.
 */
class Block_Local_Business extends Location_Shortcode {

	use Hooker;

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/local-business';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_Local_Business
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_Local_Business instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_Local_Business
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_Local_Business ) ) {
			self::$instance = new Block_Local_Business();
		}

		return self::$instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( WP_Block_Type_Registry::get_instance()->is_registered( $this->block_type ) ) {
			return;
		}

		parent::__construct();

		$this->filter( 'rank_math/metabox/post/values', 'block_settings_metadata' );
		register_block_type(
			RANK_MATH_PRO_PATH . 'includes/modules/local-seo/blocks/local-business/block.json',
			[
				'render_callback' => [ $this, 'local_shortcode' ],
			]
		);
	}

	/**
	 * Add meta data to use in the Local Business block.
	 *
	 * @param array $values Aray of tabs.
	 *
	 * @return array
	 */
	public function block_settings_metadata( $values ) {
		$values['localBusiness'] = [
			'limit'      => Helper::get_settings( 'titles.limit_results', 10 ),
			'mapStyle'   => Helper::get_settings( 'titles.map_style', 'roadmap' ),
			'routeLabel' => Helper::get_settings( 'titles.route_label' ),
		];

		return $values;
	}

	/**
	 * Schema Block render callback.
	 *
	 * @param array $attributes Block Attributes.
	 */
	public function local_shortcode( $attributes ) {
		$attributes['is_block'] = true;

		return parent::local_shortcode( $attributes );
	}
}

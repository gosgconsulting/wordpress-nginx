<?php
/**
 * The robots.txt editor module.
 *
 * @since      3.0.92
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Robots_Txt class.
 */
class Robots_Txt {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/admin/settings/robots', 'add_options' );
		$this->action( 'admin_enqueue_scripts', 'enqueue', 9 );
	}

	/**
	 * Enqueue robots.txt scripts.
	 *
	 * @return void
	 */
	public function enqueue() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );
		// Enqueue robots.txt scripts with dependencies.
		wp_enqueue_style( 'rank-math-robots-txt', $uri . '/rtt-library/main.css', [], rank_math_pro()->version );
		wp_enqueue_script( 'rank-math-rtt', $uri . '/rtt-library/bundle.js', [ 'wp-element' ], rank_math_pro()->version, true );
		Helper::add_json( 'siteUrl', home_url( '/' ) );
	}

	/**
	 * Add options to Image SEO module.
	 *
	 * @param object $cmb CMB object.
	 */
	public function add_options( $cmb ) {
		$cmb->remove_field( 'robots_tester' );
		include_once __DIR__ . '/options.php';
	}
}

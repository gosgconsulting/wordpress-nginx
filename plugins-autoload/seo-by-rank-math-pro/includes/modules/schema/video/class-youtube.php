<?php
/**
 * The Youtube
 *
 * @since      2.0.0
 * @package    RankMath
 * @subpackage RankMath\Schema\Video
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Schema\Video;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Youtube class.
 */
class Youtube {

	/**
	 * Match url.
	 *
	 * @param  string $url Url to match.
	 * @return bool
	 */
	public static function match( $url ) {
		if ( ! preg_match( '#^https?://(?:www\.)?(?:youtube\.com/|youtu\.be/)#', $url ) ) {
			return false;
		}

		preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match );
		if ( empty( $match[1] ) ) {
			return false;
		}

		if ( ! Helper::get_settings( 'sitemap.youtube_api_key' ) ) {
			return self::fetch_embed_data( $match[1], $url );
		}

		return self::fetch_from_api( $match[1], $url );
	}

	/**
	 * Fetch data.
	 *
	 * @param  string $video_id Video ID.
	 * @param  string $url      Video Source.
	 * @return array
	 */
	private static function fetch_embed_data( $video_id, $url ) {
		$data = [
			'src'   => $url,
			'embed' => true,
		];

		$response = wp_remote_get( "https://www.youtube.com/watch?v={$video_id}" );
		if (
			is_wp_error( $response ) ||
			! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 204 ], true )
		) {
			return $data;
		}

		$content = wp_remote_retrieve_body( $response );
		preg_match_all( '/<meta itemprop="(width|height|isFamilyFriendly|duration|uploadDate)" content="(.*?)">/i', $content, $item_props, PREG_SET_ORDER );
		foreach ( $item_props as $item_prop ) {
			$data[ $item_prop[1] ] = $item_prop[2];
		}

		preg_match_all( '/<meta name="(title|description)" content="(.*?)">/i', $content, $item_props, PREG_SET_ORDER );
		foreach ( $item_props as $item_prop ) {
			$key          = 'title' === $item_prop[1] ? 'name' : $item_prop[1];
			$data[ $key ] = $item_prop[2];
		}

		preg_match( '/<meta property="og:image" content="(.*?)">/i', $content, $image );
		$data['thumbnail'] = ! empty( $image ) && isset( $image[1] ) ? $image[1] : '';

		return $data;
	}

	/**
	 * Fallback to retrieve the video details from the YouTube API.
	 * ( Could be improved to query multiple video_ids in one request ).
	 *
	 * @param string $video_id The YT video id.
	 * @param string $url      The YT video embed URL.
	 *
	 * @return array
	 */
	private static function fetch_from_api( $video_id, $url ) {
		$data = [
			'src'   => $url,
			'embed' => true,
		];

		$api_key = Helper::get_settings( 'sitemap.youtube_api_key' );
		if ( empty( $api_key ) ) {
			return $data;
		}

		$fields     = '&fields=items(snippet(title,description,publishedAt,thumbnails(standard)),contentDetails(duration),player(embedHtml))';
		$query_args = "part=snippet,player,contentDetails&id={$video_id}&key={$api_key}" . $fields;

		$args = [
			'headers' => [
				'Content-Type' => 'application/json',
			],
		];

		$response = wp_remote_get( 'https://youtube.googleapis.com/youtube/v3/videos?' . $query_args, $args );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $data;
		}

		$video = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $video['items'][0] ) ) {
			return $data;
		}
		$video = $video['items'][0];

		$data['embed']       = ! empty( $video['player']['embedHtml'] );
		$data['duration']    = $video['contentDetails']['duration'];
		$data['name']        = $video['snippet']['title'];
		$data['description'] = wp_html_excerpt( $video['snippet']['description'], 157, '...' );
		$data['thumbnail']   = $video['snippet']['thumbnails']['standard']['url'];
		$data['uploadDate']  = $video['snippet']['publishedAt'];
		return $data;
	}
}

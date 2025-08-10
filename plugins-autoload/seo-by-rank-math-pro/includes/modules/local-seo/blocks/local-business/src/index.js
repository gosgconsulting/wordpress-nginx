/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import edit from './edit'

registerBlockType( 'rank-math/local-business', {
	edit,
} )

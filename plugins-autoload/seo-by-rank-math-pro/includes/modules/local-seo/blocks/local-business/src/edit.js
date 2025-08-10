/**
 * External dependencies
 */
import { forEach } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { ToggleControl, PanelBody, SelectControl, TextControl, RangeControl } from '@wordpress/components'
import ServerSideRender from '@wordpress/server-side-render'

const getFieldsData = ( hash, props ) => {
	const settings = []
	forEach( hash, ( value, key ) => {
		if ( 'boolean' === value.type ) {
			settings.push(
				<ToggleControl
					label={ value.label }
					checked={ props.attributes[ key ] }
					onChange={ ( newValue ) => props.setAttributes( { [ key ]: newValue } ) }
				/>
			)
		}
		if ( 'string' === value.type ) {
			settings.push(
				<TextControl
					label={ value.label }
					value={ props.attributes[ key ] }
					onChange={ ( newValue ) => props.setAttributes( { [ key ]: newValue } ) }
				/>
			)
		}
		if ( 'select' === value.type ) {
			settings.push(
				<SelectControl
					label={ value.label }
					value={ props.attributes[ key ] }
					options={ value.options }
					onChange={ ( newValue ) => props.setAttributes( { [ key ]: newValue } ) }
				/>
			)
		}
		if ( 'range' === value.type ) {
			settings.push(
				<RangeControl
					label={ value.label }
					value={ props.attributes[ key ] }
					onChange={ ( newValue ) => props.setAttributes( { [ key ]: newValue } ) }
					min={ value.min }
					max={ value.max }
				/>
			)
		}
	} )

	return settings
}

const getAddressSettings = ( props ) => {
	const hash = {
		show_company_name: {
			label: __( 'Show Company Name', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_company_address: {
			label: __( 'Show Company Address', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_on_one_line: {
			label: __( 'Show address on one line', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_state: {
			label: __( 'Show State', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_country: {
			label: __( 'Show Country', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_telephone: {
			label: __( 'Show Primary number', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_secondary_number: {
			label: __( 'Show Secondary number', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_fax: {
			label: __( 'Show FAX number', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_email: {
			label: __( 'Show Email', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_url: {
			label: __( 'Show Business URL', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_logo: {
			label: __( 'Show Logo', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_vat_id: {
			label: __( 'Show VAT number', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_tax_id: {
			label: __( 'Show TAX ID', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_coc_id: {
			label: __( 'Show COC number', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_pricerange: {
			label: __( 'Show Price Indication', 'rank-math-pro' ),
			type: 'boolean',
		},
	}

	if ( 'address' === props.attributes.type ) {
		delete hash.show_company_address
	}

	return getFieldsData( hash, props )
}

const getHoursSettings = ( props ) => {
	const settings = []
	const type = props.attributes.type

	if ( 'address' === type ) {
		settings.push(
			<ToggleControl
				label={ __( 'Show Opening Hours', 'rank-math-pro' ) }
				checked={ props.attributes.show_opening_hours }
				onChange={ ( newValue ) => props.setAttributes( { show_opening_hours: newValue } ) }
			/>
		)
	}

	if ( 'opening-hours' === type || props.attributes.show_opening_hours ) {
		const enabledDays = props.attributes.show_days.split( ',' )
		forEach( [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ], ( value ) => {
			settings.push(
				<ToggleControl
					// translators: Weekdays name
					label={ sprintf( __( 'Show %s', 'rank-math-pro' ), value ) }
					checked={ enabledDays.includes( value ) }
					onChange={ () => {
						const index = enabledDays.indexOf( value )
						if ( index > -1 ) {
							enabledDays.splice( index, 1 )
						} else {
							enabledDays.push( value )
						}

						props.setAttributes( { show_days: enabledDays.toString() } )
					} }
				/>
			)
		} )

		settings.push(
			<ToggleControl
				label={ __( 'Hide Closed Days', 'rank-math-pro' ) }
				checked={ props.attributes.hide_closed_days }
				onChange={ ( newValue ) => props.setAttributes( { hide_closed_days: newValue } ) }
			/>
		)
		settings.push(
			<ToggleControl
				label={ __( 'Show open now label after opening hour for current day', 'rank-math-pro' ) }
				checked={ props.attributes.show_opening_now_label }
				onChange={ ( newValue ) => props.setAttributes( { show_opening_now_label: newValue } ) }
			/>
		)

		if ( props.attributes.show_opening_now_label ) {
			settings.push(
				<TextControl
					label={ __( 'Show open now label after opening hour for current day', 'rank-math-pro' ) }
					value={ props.attributes.opening_hours_note }
					onChange={ ( newValue ) => props.setAttributes( { opening_hours_note: newValue } ) }
				/>
			)
		}
	}

	return settings
}

const getMapSettings = ( props ) => {
	const settings = []
	const isStoreLocator = 'store-locator' === props.attributes.type

	if ( isStoreLocator ) {
		settings.push( <ToggleControl
			label={ __( 'Show Map', 'rank-math-pro' ) }
			checked={ props.attributes.show_map }
			onChange={ ( showMap ) => props.setAttributes( { show_map: showMap } ) }
		/> )
	}

	if ( isStoreLocator && ! props.attributes.show_map ) {
		return settings
	}

	const hash = {
		map_style: {
			label: __( 'Map Type', 'rank-math-pro' ),
			type: 'select',
			options: [
				{
					value: 'roadmap',
					label: __( 'Roadmap', 'rank-math-pro' ),
				},
				{
					value: 'hybrid',
					label: __( 'Hybrid', 'rank-math-pro' ),
				},
				{
					value: 'satellite',
					label: __( 'Satellite', 'rank-math-pro' ),
				},
				{
					value: 'terrain',
					label: __( 'Terrain', 'rank-math-pro' ),
				},
			],
		},
		map_width: {
			label: __( 'Map Width', 'rank-math-pro' ),
			type: 'string',
		},
		map_height: {
			label: __( 'Map Height', 'rank-math-pro' ),
			type: 'string',
		},
		show_category_filter: {
			label: __( 'Show Category filter', 'rank-math-pro' ),
			type: 'boolean',
		},
		zoom_level: {
			label: __( 'Zoom Level', 'rank-math-pro' ),
			type: 'range',
			min: -1,
			max: 19,
		},
		allow_zoom: {
			label: __( 'Allow Zoom', 'rank-math-pro' ),
			type: 'boolean',
		},
		allow_scrolling: {
			label: __( 'Allow Zoom by scroll', 'rank-math-pro' ),
			type: 'boolean',
		},
		allow_dragging: {
			label: __( 'Allow Dragging', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_marker_clustering: {
			label: __( 'Show Marker Clustering', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_infowindow: {
			label: __( 'Show InfoWindow', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_route_planner: {
			label: __( 'Show Route Planner', 'rank-math-pro' ),
			type: 'boolean',
		},
		route_label: {
			label: __( 'Route Label', 'rank-math-pro' ),
			type: 'string',
		},
	}

	if ( ! isStoreLocator ) {
		delete hash.show_route_planner
		delete hash.route_label
	}

	return settings.concat( getFieldsData( hash, props ) )
}

const getStoreLocatorSettings = ( props ) => {
	const hash = {
		show_radius: {
			label: __( 'Show radius', 'rank-math-pro' ),
			type: 'boolean',
		},
		search_radius: {
			label: __( 'Search Locations within the radius', 'rank-math-pro' ),
			type: 'range',
			min: 5,
			max: 1000,
		},
		show_category_filter: {
			label: __( 'Add dropdown to filter results by category', 'rank-math-pro' ),
			type: 'boolean',
		},
		show_nearest_location: {
			label: __( 'Show nearest location if none is found within radius', 'rank-math-pro' ),
			type: 'boolean',
		},
	}

	return getFieldsData( hash, props )
}

/**
 * Get Toggle Data.
 *
 * @param {string} type Block Type.
 */
const getAdditionalSettings = ( type, props ) => {
	let title = __( 'Address Settings', 'rank-math-pro' )
	if ( 'getHoursSettings' === type ) {
		title = __( 'Opening Hours Settings', 'rank-math-pro' )
	} else if ( 'getMapSettings' === type ) {
		title = __( 'Map Settings', 'rank-math-pro' )
	}

	return (
		<PanelBody title={ title } >
			{ type === 'getHoursSettings' && getHoursSettings( props ) }
			{ type === 'getAddressSettings' && getAddressSettings( props ) }
			{ type === 'getMapSettings' && getMapSettings( props ) }
		</PanelBody>
	)
}

/**
 * Get Block Content
 *
 * @param {Array} attributes Block Attributes.
 */
const getBlockContent = ( attributes ) => {
	if ( 'map' === attributes.type ) {
		return (
			<img src={ rankMath.previewImage } alt={ __( 'Preview Image', 'rank-math-pro' ) } />
		)
	}

	return (
		<ServerSideRender
			block="rank-math/local-business"
			attributes={ attributes }
		/>
	)
}

const Edit = ( props ) => {
	const blockProps = useBlockProps()
	const { className, setAttributes, attributes, locationsData, termsData } = props
	const defaultValues = rankMath.localBusiness
	if ( ! attributes.limit ) {
		setAttributes( { limit: defaultValues.limit } )
	}

	if ( ! attributes.map_style ) {
		setAttributes( { map_style: defaultValues.map_style } )
	}

	if ( ! attributes.route_label ) {
		setAttributes( { route_label: defaultValues.route_label } )
	}

	return (
		<div { ...blockProps }>
			<div
				id="rank-math-local"
				className={ 'rank-math-block ' + className }
			>
				<InspectorControls key={ 'inspector' }>
					<PanelBody
						title={ __( 'Settings', 'rank-math-pro' ) }
						initialOpen="true"
					>
						<SelectControl
							label={ __( 'Type', 'rank-math-pro' ) }
							value={ attributes.type }
							options={ [
								{
									value: 'address',
									label: __( 'Address', 'rank-math-pro' ),
								},
								{
									value: 'opening-hours',
									label: __( 'Opening Hours', 'rank-math-pro' ),
								},
								{
									value: 'map',
									label: __( 'Map', 'rank-math-pro' ),
								},
								{
									value: 'store-locator',
									label: __( 'Store Locator', 'rank-math-pro' ),
								},
							] }
							onChange={ ( type ) => setAttributes( { type } ) }
						/>

						{ 'store-locator' !== attributes.type && <SelectControl
							label={ __( 'Locations', 'rank-math-pro' ) }
							value={ attributes.locations }
							options={ locationsData }
							onChange={ ( locations ) => setAttributes( { locations } ) }
						/> }

						{ 'store-locator' !== attributes.type && <SelectControl
							label={ __( 'Location Categories', 'rank-math-pro' ) }
							multiple
							value={ attributes.terms }
							options={ termsData }
							onChange={ ( terms ) => setAttributes( { terms } ) }
						/> }

						<TextControl
							type="number"
							label={ __( 'Maximum number of locations to show', 'rank-math-pro' ) }
							value={ attributes.limit }
							onChange={ ( limit ) => props.setAttributes( { limit } ) }
						/>

						{ 'address' === attributes.type && getAddressSettings( props ) }
						{ 'opening-hours' === attributes.type && getHoursSettings( props ) }
						{ 'map' === attributes.type && getMapSettings( props ) }
						{ 'store-locator' === attributes.type && getStoreLocatorSettings( props ) }
					</PanelBody>

					{ 'address' === attributes.type && getAdditionalSettings( 'getHoursSettings', props ) }
					{ ( 'map' === attributes.type || 'store-locator' === attributes.type ) && getAdditionalSettings( 'getAddressSettings', props ) }
					{ 'store-locator' === attributes.type && getAdditionalSettings( 'getMapSettings', props ) }

				</InspectorControls>
				{ getBlockContent( attributes ) }
			</div>
		</div>
	)
}

export default withSelect( ( select ) => {
	const locations = select( 'core' ).getEntityRecords( 'postType', 'rank_math_locations', { per_page: -1 } )
	const locationsData = []
	if ( locations ) {
		locationsData.push( { value: 0, label: __( 'All Locations', 'rank-math-pro' ) } )
		locations.forEach( ( post ) => {
			locationsData.push( {
				value: post.id,
				label: post.title.rendered,
			} )
		} )
	}

	const terms = select( 'core' ).getEntityRecords( 'taxonomy', 'rank_math_location_category', { per_page: -1 } )
	const termsData = []
	if ( terms ) {
		terms.forEach( ( term ) => {
			termsData.push( {
				value: term.id,
				label: term.name,
			} )
		} )
	}
	return {
		locationsData,
		termsData,
	}
} )( Edit )

import { TextControl, RangeControl, ToggleControl, SelectControl, Button, BaseControl, DateTimePicker, PanelBody } from '@wordpress/components';
import { useBlockProps, InspectorControls, PanelColorSettings, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import Map from './map';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { gpx } from '@tmcw/togeojson';
import './styles/editor.scss';
 
export default function Edit( { attributes, setAttributes } ) {

    const blockProps = useBlockProps();
	const mapboxBlockToken = wp.data.select('core/editor').getEditedPostAttribute('meta')[
		'mapboxApiTokenField'
	];

	const mapboxAPIRequest = `https://api.mapbox.com/geocoding/v5/mapbox.places/rndstrasdf.json?access_token=${mapboxBlockToken}`;

	useEffect( () => {
		fetch( mapboxAPIRequest )
		.then( response => {
			if ( !response.ok ) {
			  setAttributes( { isTokenValid: false } );
			  wp.data.dispatch( 'core/editor' ).savePost();
			} else {
			  setAttributes( { isTokenValid: true, mapboxApiTokenField: mapboxBlockToken } );
			  wp.data.dispatch( 'core/editor' ).savePost();
			}
		  } );
	  }, [] ); 

	 useEffect( () => {
		runMapboxMap( attributes );
	}, [attributes] ); 

	useEffect( () => {
		if ( attributes.trackUrl &&  ! attributes.showStartLocations ) {
			fetch( attributes.trackUrl )
			.then( res => res.text())
			.then( str => new DOMParser().parseFromString( str, 'text/xml' ) )
			.then( data => gpx( data ) )
			.then (gpxAsJSON => {
				let coordinates = gpxAsJSON.features[0].geometry.coordinates;
				setAttributes({ gpxAsJSON: gpxAsJSON });
				setAttributes({ trackCoords: coordinates });
			} );
		}
	  }, [attributes.trackUrl] ); 


	const inspectorControls = (
		<InspectorControls key='setting'>
			<div id='block-plugin-controls'>
				<PanelBody
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Map description text field', 'map-garmin-activities' ) }
						value={ attributes.mapGarminActivitiesTextField || '' }
						onChange={ ( val ) => setAttributes( { mapGarminActivitiesTextField: val } ) }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Initial map display settings', 'map-garmin-activities' ) }
					initialOpen={ true }
				>
					<p>{ __( 'Initial map display location. If no GPX or live locations set, this will be the visible map on display.', 'map-garmin-activities' ) }</p>
					<RangeControl
						label={ __('Zoom Level', 'map-garmin-activities' ) }
						value={ attributes.zoom }
						onChange={ ( val)  => setAttributes( { zoom: Number( val) } ) }
						min={ 1 }
						max={ 22 }
					/>
					<RangeControl
						label={ __( 'Pitch', 'map-garmin-activities' ) }
						value={ attributes.pitch }
						onChange={ ( val)  => setAttributes( { pitch: Number( val ) } ) }
						min={ -90 }
						max={ 90 }
					/>
					<RangeControl
						label={ __( 'Bearing', 'map-garmin-activities' ) }
						value={ attributes.bearing }
						onChange={ ( val )  => setAttributes( { bearing: Number( val ) } ) }
						min={ 0 }
						max={ 360 }
					/>
					<TextControl
						label={ __( 'Longitude', 'map-garmin-activities' ) }
						value={ attributes.longitude }
						onChange={ ( val ) => {
							if ( ! isNaN( val ) ) {
								setAttributes( { longitude: Number ( val ) } )
							}
						} }
					/>
					<TextControl
						label={ __( 'Latitude', 'map-garmin-activities' ) }
						value={ attributes.latitude }
						onChange={ ( val ) => {
							if ( ! isNaN( val ) ) {
								setAttributes( { latitude: Number ( val ) } )
							}
						} }
					/>
				</PanelBody>
				<PanelBody
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show start locations', 'map-garmin-activities' ) }
						help={
							attributes.showStartLocations
								? 
								__( 'Show activity start locations within a given date range', 'map-garmin-activities' )
								: 
								__( 'Default: Upload a GPX route', 'map-garmin-activities' )
						}
						checked={ attributes.showStartLocations }
						onChange={ ( val)  => setAttributes({ showStartLocations: val })}
					/>
				</PanelBody>
				{ attributes.showStartLocations ?
					<>
						{ 
						( typeof ( garminActivityData) === 'object' && ( garminActivityData !== null ) && ( typeof ( garminActivityData[0] ) !== 'undefined' ) ) 
							?
							<p className='map-garmin-activities-warning'>{ garminActivityData[0]['error'] }.</p>
							:
							''
						}
						{
						( typeof ( garminActivityData) !== 'object' )
							? 
							<>
								<p className='map-garmin-activities-warning'>
								{ __( 'Add your Garmin login credentials to the relevant fields in the Map Garmin Activities Sidebar.', 'map-garmin-activities' ) }
								</p>
							</>
							:
							''
						}

						<PanelBody
							title={ __( 'Date from', 'map-garmin-activities' ) }
							initialOpen={ false }
						>
							{ 
							attributes.dateFrom < attributes.dateTo
								?
								''
								: 
								<>
									<p className='map-garmin-activities-warning'>
										{ __( 'Start date must be earlier than end date.', 'map-garmin-activities' ) }
									</p>
								</>
							}
							<DateTimePicker
								currentDate={ attributes.dateFrom }
								onChange={ ( date ) => {
									setAttributes( {
										dateFrom: date,
									} );
								} }
								isInvalidDate={ 
									( date ) => {
										return (attributes.dateFrom > attributes.dateTo && date.toISOString() > attributes.dateFrom )
										?
										true 
										:
										false
									}
								}
								is12Hour={ true }
							/>
						</PanelBody>
						<PanelBody
							title={ __( 'Date to', 'map-garmin-activities' ) }
							initialOpen={ false }
						>
							{ 
							attributes.dateTo > attributes.dateFrom
								?
								'' 
								: 
								<>
									<p className='map-garmin-activities-warning'>
										{ __( 'End date must be later than start date.', 'map-garmin-activities' ) }
									</p>
								</>
							}
							<DateTimePicker
								currentDate={ attributes.dateTo }
								onChange={ ( date ) => {
									setAttributes( {
										dateTo: date,
									} );
								} }
								isInvalidDate={
									( date ) => {
										return (attributes.dateTo < attributes.dateFrom && date.toISOString() < attributes.dateFrom )
										?
										true 
										:
										false
									}
								}
								is12Hour={ true }
							/>
						</PanelBody>
						<PanelBody
							initialOpen={ true }
						>
							<SelectControl
								label={ __( 'Activity Type', 'map-garmin-activities' ) }
								value={ attributes.activityType }
								options={ [
									{ label: __( 'Walking', 'map-garmin-activities' ), value: 'walking' },
									{ label: __( 'Hiking', 'map-garmin-activities' ), value: 'hiking' },
									{ label: __( 'Running', 'map-garmin-activities' ), value: 'running' },
									{ label: __( 'Cycling', 'map-garmin-activities' ), value: 'cycling' },
									{ label: __( 'Swimming', 'map-garmin-activities' ), value: 'swimming' },
								] }
								onChange={ ( val)  => setAttributes( { activityType: val } ) }
								__nextHasNoMarginBottom
							/>
							<SelectControl
								label={ __( 'Distance measurement', 'map-garmin-activities' ) }
								value={ attributes.distanceMeasurement }
								options={ [
									{ label: __( 'Kilometres (metric)', 'map-garmin-activities' ), value: 'km' },
									{ label: __( 'Miles (imperial)', 'map-garmin-activities' ), value: 'miles' },
								] }
								onChange={ ( val)  => setAttributes( { distanceMeasurement: val } ) }
								__nextHasNoMarginBottom
							/>
						</PanelBody>
						<PanelBody
							title={ __( 'Map, track and marker styles', 'map-garmin-activities' ) }
							initialOpen={ true }
						>
							<SelectControl
								label={ __( 'Map Style', 'map-garmin-activities' ) }
								value={ attributes.mapStyle }
								options={ [
									{ label: __( 'Satellite', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/satellite-v9' },
									{ label: __( 'Satellite Streets', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/satellite-streets-v12' },
									{ label: __( 'Street View', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/streets-v12' },
									{ label: __( 'Outdoors', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/outdoors-v12' },
									{ label: __( 'Navigation Day', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/navigation-day-v1' },
								] }
								onChange={ ( val)  => setAttributes( { mapStyle: val } ) }
								__nextHasNoMarginBottom
							/>
							<RangeControl
								label={ __( 'Boundary padding', 'map-garmin-activities' ) }
								value={ attributes.padding }
								onChange={ ( val )  => setAttributes( { padding: Number( val ) } ) }
								min={ 0 }
								max={ 190 }
							/>
							<RangeControl
								label={ __( 'Marker stroke width', 'map-garmin-activities' ) }
								value={ attributes.markerStrokeWidth }
								onChange={ ( val )  => setAttributes( { markerStrokeWidth: Number( val ) } ) }
								min={ 1 }
								max={ 10 }
							/>
							<RangeControl
								label={ __( 'Marker radius', 'map-garmin-activities' ) }
								value={ attributes.markerRadius }
								onChange={ ( val )  => setAttributes( { markerRadius: Number( val ) } ) }
								min={ 1 }
								max={ 10 }
							/>
							<PanelColorSettings 
								title={__( 'Colours', 'map-garmin-activities' )}
								colorSettings={[
									{
										value: attributes.markerColour,
										onChange: ( val)  => setAttributes({ markerColour: val }),
										label: __( 'Marker Colour', 'map-garmin-activities' )
									},
									{
										value: attributes.markerBorderColour,
										onChange: ( val)  => setAttributes({ markerBorderColour: val }),
										label: __( 'Marker Border Colour', 'map-garmin-activities' )
									},
									{
										value: attributes.trackColour,
										onChange: ( val)  => setAttributes({ trackColour: val }),
										label: __( 'Track Colour', 'map-garmin-activities' )
									},
								]}
							/>
						</PanelBody>
					</>
					:
					<>
						<PanelBody
							initialOpen={ true }
						>
							<BaseControl label={__( 'Add GPX track', 'map-garmin-activities' )} className={ 'map-garmin-activities-control-label' }>
								<MediaUploadCheck>
									<MediaUpload
										value={attributes.trackUrl ? attributes.trackUrl : ''}
										onSelect={media => setAttributes({ trackUrl: media.url })}
										render={({ open }) => (
											attributes.trackUrl
											?
											<div>
												<p>
													{ __( 'File location:', 'map-garmin-activities' ) } { attributes.trackUrl }
												</p>
												<p>
													<Button onClick={() => setAttributes({ trackUrl: '' })} className='button is-small'>{ __( 'Remove', 'map-garmin-activities' ) }</Button>
												</p>
											</div>
											:
											<Button variant='secondary' onClick={open}>
											{ __( 'Upload/Select Track File', 'map-garmin-activities' ) }
											</Button>
										)}
									/>
								</MediaUploadCheck>
							</BaseControl>
						</PanelBody>
						<PanelBody
							title={ __( 'Map, track and marker styles', 'map-garmin-activities' ) }
							initialOpen={ true }
						>
						<SelectControl
							label={ __( 'Map Style', 'map-garmin-activities' ) }
							value={ attributes.mapStyle }
							options={ [
								{ label: __( 'Satellite', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/satellite-v9' },
								{ label: __( 'Satellite Streets', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/satellite-streets-v12' },
								{ label: __( 'Street View', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/streets-v12' },
								{ label: __( 'Outdoors', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/outdoors-v12' },
								{ label: __( 'Navigation Day', 'map-garmin-activities' ), value: 'mapbox://styles/mapbox/navigation-day-v1' },
							] }
							onChange={ ( val)  => setAttributes( { mapStyle: val } ) }
							__nextHasNoMarginBottom
						/>
						{ attributes.trackUrl
							?
							<>
								<RangeControl
									label={__( 'Track Thickness', 'map-garmin-activities' )}
									value={attributes.trackThickness}
									onChange={ ( val)  => setAttributes({ trackThickness: Number( val) })}
									min={ 0 }
									max={ 20 }
								/>
								<RangeControl
									label={ __( 'Boundary padding', 'map-garmin-activities' ) }
									value={ attributes.padding }
									onChange={ ( val )  => setAttributes( { padding: Number( val ) } ) }
									min={ 0 }
									max={ 190 }
								/>
								<PanelColorSettings 
									title={ __( 'Colours', 'map-garmin-activities' ) }
									colorSettings={[
										{
											value: attributes.trackColour,
											onChange: ( val)  => setAttributes({ trackColour: val }),
											label: __( 'Track Colour', 'map-garmin-activities' )
										}
									]}
								/>
							</>
							:
							''
						}
						</PanelBody>
					</>
				}		
			</div>
		</InspectorControls>
	);

	const mapControls = (
		<Map blockProps={ attributes } />
	);


	if ( attributes.isTokenValid && attributes.mapboxApiTokenField !== '' ) {
		return (
			<div { ...blockProps } >
				{ inspectorControls }
				<TextControl
					{ ...blockProps }
					label={ __( 'Map title', 'map-garmin-activities' ) }
					value={ attributes.mapTitle }
					onChange={ ( val ) => setAttributes( { mapTitle: val } ) }
				/>
				<div>
					{ mapControls }
				</div>
				<p className="map-garmin-activities-further-information">{ attributes.mapGarminActivitiesTextField }</p>
			</div>
    	);
	}
	else {
		return (
			<div className='mapbox-block-token'>
				<h2>{ __( 'Map Garmin Activities Block Setup', 'map-garmin-activities' ) }</h2>
				<p>{ __( 'To use this block, you need to sign up for a Mapbox account and generate your token.', 'map-garmin-activities' ) }</p>
				<a href='https://www.mapbox.com/' className='mapbox-block-token-cta'>{ __( 'Create a Mapbox Token here', 'map-garmin-activities' ) }</a>. 
				<p>{ __( 'Once you have a valid token, add it to the Mapbox token field in the plugin sidebar.' ) }</p>
			</div>
		);
	}
}

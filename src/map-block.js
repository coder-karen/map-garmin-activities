/**
 * Builds the mapbox map using user defined attributes.
 */
function runMapboxMap( attributes ) {

	let mapAttributes = [];

	// If the map block hasn't even been added to the page / post yet, leave early.
	if ( typeof blockAttributes === 'undefined' ) {
		return;
	} else if ( attributes ) {
		mapAttributes = attributes;
	} else {
		mapAttributes = blockAttributes;
	}

	if ( document.getElementById( 'mapbox-map' ) ) {

		if ( mapAttributes.isTokenValid ) {

			mapboxgl.accessToken = mapboxgl.accessToken ?  mapboxgl.accessToken : mapAttributes.mapboxApiTokenField;

			let map = new mapboxgl.Map( {
				container: 'mapbox-map', // container ID
				style: mapAttributes.mapStyle,
				zoom:  mapAttributes.zoom,
				center: [ `${mapAttributes.longitude}`, `${mapAttributes.latitude}` ],
				pitch: mapAttributes.pitch,
				bearing: mapAttributes.bearing,
			} );
			map.on('style.load', () => {
				map.addSource('mapbox-dem', {
				'type': 'raster-dem',
				'url': 'mapbox://mapbox.mapbox-terrain-dem-v1',
				'tileSize': 512,
				'maxzoom': 14
				} );
				map.setTerrain( { 'source': 'mapbox-dem', 'exaggeration': 1.5 } );
			} );
			map.on( 'style.load', () => {
				map.setFog({});
			} );

			if ( mapAttributes.trackUrl && ! mapAttributes.showStartLocations ) {
				map.on( 'style.load', () => {
					getGeoJSONfromGPX( mapAttributes, map );
				} );
	
			}
			if ( mapAttributes.showStartLocations ) {
				if ( typeof ( garminActivityData) !== 'object' || ( garminActivityData === null ) ) {
					console.error( 'Garmin data not currently available' );
				} else if ( typeof (garminActivityData[0]) !== 'undefined' )  {
					console.error(garminActivityData[0]['error']);
				} else {
					map.on( 'style.load', () => {
						map.addSource('places', {
						'type': 'geojson',
						'data': garminActivityData
						} );
						map.addLayer( {
							'id': 'places',
							'type': 'circle',
							'source': 'places',
							'paint': {
							'circle-color': mapAttributes.markerColour,
							'circle-radius': mapAttributes.markerRadius,
							'circle-stroke-width': mapAttributes.markerStrokeWidth,
							'circle-stroke-color': mapAttributes.markerBorderColour
							}
							} );

							let newCoords = [];

							for ( const singleActivityData of garminActivityData['features'] ) {
								newCoords.push( [singleActivityData['geometry']['coordinates'][0], singleActivityData['geometry']['coordinates'][1]] );
							}

							let bounds = newCoords.reduce( ( bounds, coord ) => {
								return bounds.extend(coord);
							}, new mapboxgl.LngLatBounds( newCoords[1], newCoords[0] ) );
							//myCoords = newCoordinates;
	
							map.fitBounds( bounds, {
								padding: mapAttributes.padding
							} );

						// When a click event occurs on a feature in the places layer, open a popup at the
						// location of the feature, with description HTML from its properties.
						map.on( 'click', 'places', (e) => {

							const coordinates = e.features[0].geometry.coordinates.slice();
							const description = e.features[0].properties.description;
							
							new mapboxgl.Popup()
							.setLngLat(coordinates)
							.setHTML(description)
							.addTo(map);
						} );

						map.on( 'mouseenter', 'places', () => {
							map.getCanvas().style.cursor = 'pointer';
						} );
						
						map.on( 'mouseleave', 'places', () => {
							map.getCanvas().style.cursor = '';
						} );
					});
				}
			}
		} else if ( document.getElementsByClassName( 'map-block' )[0] ) {
					document.getElementsByClassName( 'map-block' )[0].style.display = 'none';
		}
	}
}

function getGeoJSONfromGPX( mapAttributes, map ) {

	//let trackCoords = mapAttributes.trackCoords;
	//let myCoords = [];
	let newCoordinates = [];
	for ( const trackCoord of mapAttributes.trackCoords ) {
		newCoordinates.push( [trackCoord[0], trackCoord[1]] );
	}

	let bounds = newCoordinates.reduce( ( bounds, coord ) => {
		return bounds.extend( coord );
	}, new mapboxgl.LngLatBounds( newCoordinates[0], newCoordinates[0] ) );

	map.fitBounds( bounds, {
		padding: mapAttributes.padding
	} );

	map.addSource( 'route', {
		'type': 'geojson',
		'data': {
			'type': 'Feature',
			'properties': {},
			'geometry': {
				'type': 'LineString',
				'coordinates': newCoordinates
			}
		}
	} );
	map.addLayer( {
		id: 'route',
		type: 'line',
		source: 'route',
		layout: {
			'line-join': 'round',
			'line-cap': 'round'
		},
		paint: {
			'line-color': mapAttributes.trackColour,
			'line-width': mapAttributes.trackThickness
		}
	} );
}

runMapboxMap();
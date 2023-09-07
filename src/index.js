'use client';
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './styles/style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from '../block.json';
import MetaFields from './plugin-sidebar.js';

registerPlugin( 'my-plugin-sidebar', {
	 render: function(){
		  return (
		  <>
			  <MetaFields/>
		  </>
		);
	 }
  } );



/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	attributes: {
		mapGarminActivitiesTextField: {
			type: 'string',
		},
		mapTitle: {
			type: 'string',
		},
		isTokenValid: {
			type: 'boolean',
			default: false,
		},
		mapboxApiTokenField: {
			type: 'string',
		},
		mapStyle: {
			type: 'string',
			default: 'mapbox://styles/mapbox/satellite-streets-v12'
		},
		trackColour: {
			type: 'string',
			default: '#888888'
		},
		trackThickness: {
			type: 'number',
			default: 5
		  },
		markerColour: {
			type: 'string',
			default: '#FF0000',
		},
		markerBorderColour: {
			type: 'string',
			default: 'white',
		},
		zoom: {
			type: 'integer',
			default: 13,
		},
		longitude: {
			type: 'number',
			default: -3.647891
		},
		latitude: {
			type: 'number',
			default: 57.120085
		},
		pitch: {
			type: 'number',
			default: 80,
		},
		bearing: {
			type: 'number',
			default: 10,
		},
		showStartLocations: {
			type: 'boolean',
			default: false,
		},
		trackUrl: {
			type: 'string',
			default: '',
		  },
		trackCoords: {
			type: 'string',
			default: '',
		},
		gpxAsJSON: {
			type: 'string',
			default: '',
		},
		dateFrom: {
			type: 'string',
			default: '',
		},
		dateTo: {
			type: 'string',
			default: '',
		},
		padding: {
			type: 'number',
			default: 30,
		},
		activityType: {
			type: 'string',
			default: 'walking'
		},
		distanceMeasurement: {
			type: 'string',
			default: 'km'
		},
		markerStrokeWidth: {
			type: 'number',
			default: 2,
		},
		markerRadius: {
			type: 'number',
			default: 6,
		},
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,

} );

import { PluginSidebar, PluginSidebarMoreMenu} from '@wordpress/edit-post';
import { TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';


const MetaFields = ( props ) => {
		return (
			<PluginSidebar className='map-garmin-activities-sidebar' name='map-garmin-activities-sidebar' title='Map Garmin Activities Sidebar'>
				<MapboxAPIField/>
				<GarminAccountEmailField/>
				<GarminAccountPasswordField/>
			</PluginSidebar>
		);
	};


const MapboxAPIField = ( props ) => {
	const metaFieldValue = useSelect(function (select) {
		return select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)['mapboxApiTokenField'];
	}, []);

	const editPost = useDispatch( 'core/editor' ).editPost;

	return (
		<div>
		<TextControl
			label={ __( 'Mapbox API Token', 'map-garmin-activities' ) }
			value={ metaFieldValue || '' }
			onChange={ function( content ) { 
				editPost({
					meta: { mapboxApiTokenField: content },
				});
			}
			}
		/>
		<p>{ __( 'Refresh after update to view changes.', 'map-garmin-activities' ) }</p>
		</div>
	);
};

const GarminAccountEmailField = ( props ) => {
	const GarminAccountEmailValue = useSelect( function ( select ) {
		return select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)[ 'garmin_account_email_field' ];
	}, [] );

	const editPost = useDispatch( 'core/editor' ).editPost;

	return (
		<TextControl
			label={ __( 'Garmin Account Email Field', 'map-garmin-activities' ) }
			value={ GarminAccountEmailValue || '' }
			onChange={ ( content ) => 
				editPost( {
				meta: { garmin_account_email_field: content },
				} )
			}
		/>
	);
};

const GarminAccountPasswordField = ( props ) => {
	const GarminAccountPasswordValue  = useSelect( function ( select ) {
		return select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)[ 'garmin_account_password_field' ];
	}, [] );

	const editPost = useDispatch( 'core/editor' ).editPost;

	return (
		<TextControl
			label={ __( 'Garmin Account Password Field' ) }
			value={ GarminAccountPasswordValue || ''  }
			onChange={ ( content ) => 
				editPost( {
				meta: { garmin_account_password_field: content },
				} )
			}
		/>
	);
};


export default MetaFields;

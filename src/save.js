import { useBlockProps } from '@wordpress/block-editor';
import Map from './map';

 
export default function save( { attributes } ) {

	const blockProps = useBlockProps.save();

	if ( attributes.isTokenValid === true ) {
		return (
			<>
				<h3>{ attributes.mapTitle }</h3>
				<div { ...blockProps } className="map-block">
					<Map blockProps={ attributes }/>
				</div>
				<p>{ attributes.mapGarminActivitiesTextField }</p>
			</>
		);
	} else {
		return (
			<div></div>
		);
	}

}

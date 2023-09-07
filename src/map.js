
export default class Map extends React.Component {

	render() {
		return (
			<div>
				<div id='mapbox-map' />
			</div>
		);
	}

	componentDidMount() {
		const mapAttributes = this.props.blockProps;
		runMapboxMap( mapAttributes ); 
	}
}

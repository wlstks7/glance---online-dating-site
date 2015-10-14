<?php  

	function bar_get_nearby( $lat, $lng, $limit = 50, $distance = 50, $unit = 'mi' ) {
		
		// radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'
		if( $unit == 'km' ) { $radius = 6371.009; }
		elseif ( $unit == 'mi' ) { $radius = 3958.761; }

		// latitude boundaries
		$maxLat = ( float ) $lat + rad2deg( $distance / $radius );
		$minLat = ( float ) $lat - rad2deg( $distance / $radius );

		// longitude boundaries (longitude gets smaller when latitude increases)
		$maxLng = ( ( float ) $lng + rad2deg( $distance / $radius) ) /  cos( deg2rad( ( float ) $lat ) );
		$minLng = ( ( float ) $lng - rad2deg( $distance / $radius) ) /  cos( deg2rad( ( float ) $lat ) );

		$max_min_values = array(
			'max_latitude' => $maxLat,
			'min_latitude' => $minLat,
			'max_longitude' => $maxLng,
			'min_longitude' => $minLng
		);

		return $max_min_values;
	}

?>
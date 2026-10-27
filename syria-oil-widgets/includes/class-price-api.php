<?php
/**
 * Thin commodity price API client (EIA Open Data v2).
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetches benchmark prices and normalizes them for the feed layer.
 */
class Price_API {

	/**
	 * Commodity definitions: id => EIA request config.
	 *
	 * @return array<string, array{label: string, unit: string, route: string, series: string}>
	 */
	public static function commodities() {
		return array(
			'brent'  => array(
				'label'  => __( 'Brent Crude', 'syria-oil-widgets' ),
				'unit'   => __( '/bbl', 'syria-oil-widgets' ),
				'route'  => 'petroleum/pri/spt',
				'series' => 'RBRTE',
			),
			'wti'    => array(
				'label'  => __( 'WTI', 'syria-oil-widgets' ),
				'unit'   => __( '/bbl', 'syria-oil-widgets' ),
				'route'  => 'petroleum/pri/spt',
				'series' => 'RWTC',
			),
			'natgas' => array(
				'label'  => __( 'Natural Gas', 'syria-oil-widgets' ),
				'unit'   => __( '/MMBtu', 'syria-oil-widgets' ),
				'route'  => 'natural-gas/pri/spot',
				'series' => 'RNGWHHD',
			),
		);
	}

	/**
	 * Fetch all supported commodities.
	 *
	 * @param string $api_key EIA API key.
	 * @return array{items: array<string, array>, error?: string}|\WP_Error
	 */
	public function fetch_all( $api_key ) {
		$api_key = is_string( $api_key ) ? trim( $api_key ) : '';
		if ( '' === $api_key ) {
			return new \WP_Error( 'syria_oil_missing_key', __( 'EIA API key is not configured.', 'syria-oil-widgets' ) );
		}

		$items  = array();
		$errors = array();

		foreach ( self::commodities() as $id => $meta ) {
			$row = $this->fetch_series( $api_key, $id, $meta );
			if ( is_wp_error( $row ) ) {
				$errors[] = $id . ': ' . $row->get_error_message();
				continue;
			}
			$items[ $id ] = $row;
		}

		if ( empty( $items ) ) {
			return new \WP_Error(
				'syria_oil_fetch_failed',
				! empty( $errors ) ? implode( '; ', $errors ) : __( 'No commodity prices returned.', 'syria-oil-widgets' )
			);
		}

		$result = array( 'items' => $items );
		if ( ! empty( $errors ) ) {
			$result['error'] = implode( '; ', $errors );
		}

		return $result;
	}

	/**
	 * Fetch latest + previous daily values for one series and normalize.
	 *
	 * @param string               $api_key API key.
	 * @param string               $id      Commodity id.
	 * @param array<string,string> $meta    Commodity meta.
	 * @return array{id: string, label: string, price: float, change_pct: float|null, unit: string, currency: string, period: string}|\WP_Error
	 */
	protected function fetch_series( $api_key, $id, $meta ) {
		$url = add_query_arg(
			array(
				'api_key'            => $api_key,
				'frequency'          => 'daily',
				'data[0]'            => 'value',
				'facets[series][]'   => $meta['series'],
				'sort[0][column]'    => 'period',
				'sort[0][direction]' => 'desc',
				'offset'             => 0,
				'length'             => 2,
			),
			'https://api.eia.gov/v2/' . $meta['route'] . '/data/'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( 200 !== $code || ! is_array( $data ) ) {
			$message = isset( $data['error'] ) ? (string) $data['error'] : sprintf(
				/* translators: %d: HTTP status code */
				__( 'EIA HTTP %d', 'syria-oil-widgets' ),
				$code
			);
			return new \WP_Error( 'syria_oil_eia_http', $message );
		}

		$rows = array();
		if ( isset( $data['response']['data'] ) && is_array( $data['response']['data'] ) ) {
			$rows = $data['response']['data'];
		}

		if ( empty( $rows ) || ! isset( $rows[0]['value'] ) ) {
			return new \WP_Error( 'syria_oil_eia_empty', __( 'EIA returned no data points.', 'syria-oil-widgets' ) );
		}

		$latest = (float) $rows[0]['value'];
		$period = isset( $rows[0]['period'] ) ? (string) $rows[0]['period'] : '';
		$change = null;

		if ( isset( $rows[1]['value'] ) && is_numeric( $rows[1]['value'] ) ) {
			$prev = (float) $rows[1]['value'];
			if ( 0.0 !== $prev ) {
				$change = round( ( ( $latest - $prev ) / $prev ) * 100, 2 );
			}
		}

		return array(
			'id'         => $id,
			'label'      => $meta['label'],
			'price'      => round( $latest, 2 ),
			'change_pct' => $change,
			'unit'       => $meta['unit'],
			'currency'   => 'USD',
			'period'     => $period,
		);
	}
}

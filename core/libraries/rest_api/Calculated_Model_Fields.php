<?php
namespace EventEspresso\core\libraries\rest_api;
use EventEspresso\core\libraries\rest_api\controllers\Base;
/**
 *
 * Class Calculations
 *
 * Class for defining which model fields can be calculated, and performing those calculations 
 * as requested
 *
 * @package         Event Espresso
 * @subpackage    
 * @author				Mike Nelson
 * @since		 	   4.8.35.rc.001
 *
 */
if( !defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

class Calculated_Model_Fields {
	/**
	 *
	 * @var array
	 */
	protected $_mapping;
	/**
	 * @return array top-level-keys are model names (eg "Event")
	 * next-level are the calculed field names, and their values are
	 * callbacks for calculating them. 
	 * These callbacks should accept as arguments:
	 * the wpdb row results,
	 * the WP_Request object,
	 * the controller object
	 */
	public function mapping( $refresh = false ) { 
		if( ! $this->_mapping || $refresh ) {
			$this->_mapping = $this->_generate_new_mapping();
		}
		return $this->_mapping;
		
	}
	
	/**
	 * Generates  anew mapping between model calculated fields and their callbacks
	 * @return array
	 */
	protected function _generate_new_mapping() {
		$rest_api_calculations_namespace = 'EventEspresso\core\libraries\rest_api\calculations\\';
		$event_calculations_class = $rest_api_calculations_namespace . 'Event';
		$datetime_calculations_class = $rest_api_calculations_namespace . 'Datetime';
		return apply_filters(
			'FHEE__EventEspresso\core\libraries\rest_api\Calculated_Model_Fields__mapping', 
			array(
				'Event' => array(
					'optimum_sales_at_start' => array(
						$event_calculations_class,
						'optimum_sales_at_start'
					),
					'optimum_sales_now' => array(
						$event_calculations_class,
						'optimum_sales_now'
					),
					'spots_taken' => array(
						$event_calculations_class,
						'spots_taken'
					),
					'spots_taken_pending_payment' => array(
						$event_calculations_class,
						'spots_taken_pending_payment',
					),
					'spaces_remaining' => array(
						$event_calculations_class,
						'spaces_remaining'
					),
					'registrations_checked_in_count' => array(
						$event_calculations_class,
						'registrations_checked_in_count'
					),
					'registrations_checked_out_count' => array(
						$event_calculations_class,
						'registrations_checked_out_count'
					)
				),
				'Datetime' => array(
					'spaces_remaining_considering_tickets' => array(
						$datetime_calculations_class,
						'spaces_remaining_considering_tickets'
					),
					'registrations_checked_in_count' => array(
						$datetime_calculations_class,
						'registrations_checked_in_count',
					),
					'registrations_checked_out_count' => array(
						$datetime_calculations_class,
						'registrations_checked_out_count'
					),
					'spots_taken_pending_payment' => array(
						$datetime_calculations_class,
						'spots_taken_pending_payment',
					),
				)
			)
		);
	}
	
	/**
	 * Gets the known calculated fields for model
	 * @param \EEM_Base $model
	 * @return array allowable values for this field
	 */
	public function retrieve_calculated_fields_for_model( \EEM_Base $model ) {
		$mapping = $this->mapping();
		if( isset( $mapping[ $model->get_this_model_name() ] ) ) {
			return array_keys( $mapping[ $model->get_this_model_name() ] );
		} else {
			return array();
		}
	}
	
	/**
	 * Retrieves the value for this calculation
	 * @param \EEM_Base type $model
	 * @param string $field_name
	 * @param array $wpdb_row
	 * @param \WP_REST_Request
	 */
	public function retrieve_calculated_field_value( \EEM_Base $model, $field_name, $wpdb_row, $rest_request, Base $controller ) {
		$mapping = $this->mapping();
		if( isset( $mapping[ $model->get_this_model_name() ] ) 
			&& $mapping[ $model->get_this_model_name() ][ $field_name ] ) {
			return call_user_func( $mapping[ $model->get_this_model_name() ][ $field_name ], $wpdb_row, $rest_request, $controller );
		}
		if( defined( 'EE_REST_API_DEBUG_MODE' )
			&& EE_REST_API_DEBUG_MODE ) {
			throw new \EE_Error( sprintf( __( 'There is no calculated field %1$s', 'event_espresso' ), $field_name ) );
		} else {
			return null;
		}
	}
}

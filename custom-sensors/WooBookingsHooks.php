<?php
/**
 * The Class is used for custom alerts in the WP Security Audit Log plugin.
 *
 * @package Woocommerce_Bookings_Extensions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Bookings sensor.
 *
 * 9100 Booking created
 * 9101 Booking updated
 * 9102 Booking deleted
 * 9103 Booking date changed
 * 9104 Booking product changed
 * 9105 Booking persons changed
 * 9106 Booking metadata changed
 */

/**
 * Class WSAL_Sensors_WooBookingsHooks
 */
class WSAL_Sensors_WooBookingsHooks extends WSAL_AbstractSensor {

	public $hidden_meta_keys = array(
		'_edit_lock',
		'_booking_modified_user_id',
	);

	/**
	 * Old values for bookings meta to compare with for tracking changes.
	 *
	 * @var array
	 */
	private $booking_meta_cache;

	/**
	 * Method: Hook events related to sensor.
	 */
	public function HookEvents() {
		/**
		 * Use add_action() for every hook and pass the following:
		 *
		 * @param string sample_hook_name
		 * @param string SampleFunction - the name of the function above
		 * @param int 10 - priority (Optional)
		 * @param int 2 - number of parameters passed to the function (Optional)(Check the hook documentation)
		 *
		 * @see http://adambrown.info/p/wp_hooks for more information on WordPress hooks
		 */
		add_action( 'woocommerce_new_booking', array( $this, 'bookingCreated' ) );
		add_action( 'save_post_wc_booking', array( $this, 'bookingUpdated' ), 99, 3 );
		add_action( 'updated_postmeta', array( $this, 'bookingMetaUpdated' ), 10, 4 );
		add_action( 'before_delete_post', array( $this, 'bookingDeleted' ) );

		add_action( 'update_postmeta', array( $this, 'storeBookingMeta' ), 10, 4 );

		//do_action( 'post_updated', $post_ID, $post_after, $post_before );
		//woocommerce_before_booking_object_save', $this, $this->data_store )
		//do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
		//do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
		//get_post_meta( $post_id ); // Gets the old data
		//get_object_subtype( $meta_type, $object_id );
	}

	public function storeBookingMeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		$meta_type        = 'post';
		$hidden_meta_keys = apply_filters( 'woocommerce_bookings_audit_hidden_meta_keys', $this->hidden_meta_keys );
		$sub_type         = get_object_subtype( $meta_type, $object_id );
		if ( 'wc_booking' === $sub_type && ! in_array( $meta_key, $hidden_meta_keys, true ) ) {
			$current_values = get_post_meta( $object_id );
			if ( ! empty( $current_values ) && isset( $current_values[ $meta_key ] ) ) {
				$current_value = $current_values[ $meta_key ][0];

				if ( $current_value != $meta_value ) {
					$this->booking_meta_cache[ $object_id ][ $meta_key ] = array(
						'old_value' => $current_value,
						'new_value' => $meta_value,
					);
				}
			}
		}
	}

	public function bookingMetaUpdated( $meta_id, $object_id, $meta_key, $meta_value ) {
		$meta_type        = 'post';
		$hidden_meta_keys = apply_filters( 'woocommerce_bookings_audit_hidden_meta_keys', $this->hidden_meta_keys );
		$sub_type = get_object_subtype( $meta_type, $object_id );

		if ( 'wc_booking' === $sub_type && ! in_array( $meta_key, $hidden_meta_keys, true ) ) {
			$alert_code = 9107;

			$post = get_post( $object_id );

			$variables = array(
				'BookingTitle'   => $post->post_title,
				'EditorLinkPost' => get_edit_post_link( $object_id ),
				'Changed'        => $this->booking_meta_cache[ $object_id ],
			);

			foreach ( $this->booking_meta_cache[ $object_id ] as $key => $value ) {
				switch ( $key ) {
					case '_booking_all_day':
					case '_booking_start':
					case '_booking_end':
						unset( $this->booking_meta_cache['_booking_all_day'] );
						unset( $this->booking_meta_cache['_booking_start'] );
						unset( $this->booking_meta_cache['_booking_end'] );
						$this->bookingDateChanged( $object_id );
						break;
					case '_booking_product_id':
						$this->bookingProductChanged( $object_id );
						break;
					case '_booking_customer_id':
						unset( $this->booking_meta_cache[ $key ] );
						$this->bookingCustomerChanged( $object_id );
				}
			}

			if ( ! empty( $this->booking_meta_cache[ $object_id ] ) ) {
				$this->plugin->alerts->Trigger( $alert_code, $variables );
				$this->booking_meta_cache[ $object_id ] = array();
			}
		}
	}

	public function bookingCustomerChanged( $booking_id ) {
		$alert_code = 9106;

		$post = get_post( $booking_id );

		$variables = array(
			'BookingTitle'   => $post->post_title,
			'EditorLinkPost' => get_edit_post_link( $booking_id ),
		);

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	public function bookingProductChanged( $booking_id ) {
		$alert_code = 9104;

		$post = get_post( $booking_id );

		$variables = array(
			'BookingTitle'   => $post->post_title,
			'EditorLinkPost' => get_edit_post_link( $booking_id ),
		);

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	public function bookingDateChanged( $booking_id ) {
		$alert_code = 9103;

		$post = get_post( $booking_id );

		$variables = array(
			'BookingTitle'   => $post->post_title,
			'EditorLinkPost' => get_edit_post_link( $booking_id ),
		);

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	/**
	 * Sensor for created bookings.
	 *
	 * @param int $booking_id   WC_Booking post id.
	 */
	public function bookingCreated( $booking_id ) {
		$alert_code = 9100;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$booking = get_wc_booking( $booking_id );

		$variables = array(
			'BookingId'      => $booking_id,
			'EditorLinkPost' => get_edit_post_link( $booking_id ),
		);

		try {
			$timezone                            = new DateTimeZone( wc_timezone_string() );
			$variables['BookingStartDateObject'] = self::get_datetime( $booking->get_start() );
			$variables['BookingStartDateObject']->setTimezone( $timezone );
			$variables['BookingStartDate'] = $variables['BookingStartDateObject']->format( wc_date_format() . ' ' . wc_time_format() );
		} catch ( Exception $e ) {
			$variables['BookingStartDate'] = 'N/A';
		}

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	/**
	 * Sensor for updated bookings.
	 *
	 * @param integer $post_id - Post ID.
	 * @param WP_Post $post    - WC Product CPT object.
	 * @param integer $update  - True if product update, false if product is new.
	 */
	public function bookingUpdated( $post_id, $post, $update ) {
		$alert_code = 9101;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$newpost = get_post( $post_id );
		$booking = get_wc_booking( $post_id );
		$changes = $booking->get_changes();
		WC_Cache_Helper::get_transient_version( 'bookings', true );

		$variables = array(
			'BookingTitle'   => $newpost->post_title,
			'EditorLinkPost' => get_edit_post_link( $newpost->ID ),
			'Changes'        => $changes,
		);

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	/**
	 * Sensor for deleted bookings.
	 *
	 * @param int $booking_id WC_Booking post id.
	 */
	public function bookingDeleted( $booking_id ) {
		$alert_code = 9102;

		$post = get_post( $booking_id );

		$variables = array(
			'BookingId'    => $post->ID,
			'BookingTitle' => $post->post_title,
		);

		$this->plugin->alerts->Trigger( $alert_code, $variables );
	}

	/**
	 * Convert from string to DateTime.
	 *
	 * @param $date
	 *
	 * @return bool|DateTime
	 * @throws Exception
	 */
	public static function get_datetime( $date ) {
		$timezone = new DateTimeZone( wc_timezone_string() );
		$offset   = $timezone->getOffset( new DateTime() );

		$res = DateTime::createFromFormat( 'U', $date - $offset, $timezone );

		return $res;
	}

}
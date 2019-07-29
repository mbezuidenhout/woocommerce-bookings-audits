<?php

$custom_alerts = array(
	__( 'Third Party Plugins', 'wp-security-audit-log' ) => array(
		__( 'WooCommerce Bookings', 'wp-security-audit-log' ) => array(
			array(
				9100,
				E_NOTICE,
				__( 'Booking created', 'wp-security-audit-log' ),
				__( 'Created new booking for %BookingStartDate%. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9101,
				E_WARNING,
				__( 'Booking changed', 'wp-security-audit-log' ),
				__( 'Booking changed %BookingTitle%. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9102,
				E_CRITICAL,
				__( 'Booking deleted', 'wp-security-audit-log' ),
				__( 'Booking %BookingTitle% with ID %BookingId% deleted', 'wp-security-audit-log' )
			),
			array(
				9103,
				E_WARNING,
				__( 'Booking date changed', 'wp-security-audit-log' ),
				__( 'Modified booking %BookingTitle% dates. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9104,
				E_WARNING,
				__( 'Booking product changed', 'wp-security-audit-log' ),
				__( 'Modified booking %BookingTitle% product. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9105,
				E_WARNING,
				__( 'Booking persons changed', 'wp-security-audit-log' ),
				__( 'Modified booking %BookingTitle% persons. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9106,
				E_WARNING,
				__( 'Booking customer changed', 'wp-security-audit-log' ),
				__( 'Modified booking %BookingTitle% customer. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9107,
				E_WARNING,
				__( 'Booking metadata changed', 'wp-security-audit-log' ),
				__( 'Modified booking %BookingTitle% meta data. View the booking: %EditorLinkPost%', 'wp-security-audit-log' )
			),
			array(
				9108,
				E_CRITICAL,
				__( 'Booking moved to bin', 'wp-security-audit-log' ),
				__( 'Booking %BookingTitle% with ID %BookingId% moved to bin.', 'wp-security-audit-log' )
			),
		)
	)
);

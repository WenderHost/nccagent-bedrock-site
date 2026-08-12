<?php

/**
 * Exports NCC Agent (Subscriber) users to CSV: Name, NPN, Email.
 *
 * Usage (from web/):
 *   wp eval-file ../bin/export-users.php
 */

if ( ! class_exists( 'WP_CLI' ) ) {
	exit( "This script must be run via WP-CLI (wp eval-file).\n" );
}

$export_dir = __DIR__ . '/exports';
if ( ! is_dir( $export_dir ) && ! mkdir( $export_dir, 0755, true ) ) {
	WP_CLI::error( "Could not create export directory: {$export_dir}" );
}

$filename = sprintf( 'nccagent_%s_%s.csv', date( 'Y-m-d' ), date( 'Hi' ) );
$filepath = $export_dir . '/' . $filename;

$handle = fopen( $filepath, 'w' );
if ( ! $handle ) {
	WP_CLI::error( "Could not open {$filepath} for writing." );
}

fputcsv( $handle, [ 'Name', 'NPN', 'Email' ] );

$per_page = 200;
$paged    = 1;
$exported = 0;

do {
	$query = new WP_User_Query( [
		'role'    => 'subscriber',
		'number'  => $per_page,
		'paged'   => $paged,
		'orderby' => 'ID',
		'order'   => 'ASC',
		'fields'  => 'all',
	] );

	$users = $query->get_results();

	foreach ( $users as $user ) {
		$name = trim( $user->first_name . ' ' . $user->last_name );
		if ( '' === $name ) {
			$name = $user->display_name;
		}

		$npn = get_user_meta( $user->ID, 'npn', true );

		fputcsv( $handle, [ $name, $npn, $user->user_email ] );
		$exported++;
	}

	$paged++;
} while ( count( $users ) === $per_page );

fclose( $handle );

WP_CLI::success( "Exported {$exported} subscriber user(s) to {$filepath}" );

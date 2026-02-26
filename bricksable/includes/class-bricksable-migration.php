<?php
/**
 * Bricksable Data Migration.
 *
 * Handles migrating legacy settings keys in Bricks element data stored in wp_postmeta.
 * Uses WP-Cron for batched background processing.
 *
 * @package Bricksable/Classes
 * @since   1.6.77
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bricksable_Migration
 *
 * Uses WP-Cron to batch-process postmeta rows, renaming
 * `content_toggle_item` → `content-toggle-item` inside serialized Bricks element data.
 */
class Bricksable_Migration {

	const MIGRATION_VERSION_KEY = 'bricksable_migration_version';
	const TARGET_VERSION        = '1.6.82';
	const CRON_HOOK             = 'bricksable_migrate_content_toggle_batch';
	const OFFSET_OPTION         = 'bricksable_migration_offset';
	const BATCH_SIZE            = 50;

	const MANUAL_ACTION = 'bricksable_run_migration';

	/**
	 * Initialize migration hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_schedule_migration' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_manual_trigger' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'process_batch' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
	}

	/**
	 * Show admin notice with manual trigger button if migration is pending.
	 */
	public static function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_version = get_option( self::MIGRATION_VERSION_KEY, '0' );

		if ( version_compare( $current_version, self::TARGET_VERSION, '>=' ) ) {
			return;
		}

		$url = wp_nonce_url(
			add_query_arg( 'action', self::MANUAL_ACTION ),
			self::MANUAL_ACTION
		);

		$is_running = (bool) get_option( self::OFFSET_OPTION, false );
		$message    = $is_running
			? esc_html__( 'Bricksable: Content Toggle data migration is in progress.', 'bricksable' )
			: esc_html__( 'Bricksable: A data migration is required for the Content Toggle element.', 'bricksable' );

		printf(
			'<div class="notice notice-warning"><p>%s &nbsp;<a href="%s" class="button button-primary">%s</a></p></div>',
			esc_html( $message ),
			esc_url( $url ),
			$is_running
				? esc_html__( 'Run Remaining Batches Now', 'bricksable' )
				: esc_html__( 'Run Migration Now', 'bricksable' )
		);
	}

	/**
	 * Handle manual migration trigger from admin notice button.
	 */
	public static function handle_manual_trigger() {
		if ( ! isset( $_GET['action'] ) || self::MANUAL_ACTION !== $_GET['action'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( self::MANUAL_ACTION );

		// Run all batches synchronously until done.
		$current_version = get_option( self::MIGRATION_VERSION_KEY, '0' );
		if ( version_compare( $current_version, self::TARGET_VERSION, '>=' ) ) {
			return;
		}

		// Allow up to 80% of the PHP time limit, minimum 20s.
		$time_limit = (int) ini_get( 'max_execution_time' );
		$deadline   = time() + ( $time_limit > 0 ? (int) ( $time_limit * 0.8 ) : 20 );

		// Reset offset so we start fresh, then loop through all batches.
		delete_option( self::OFFSET_OPTION );

		do {
			self::process_batch_sync();

			if ( time() >= $deadline ) {
				// Ran out of time — schedule cron to finish the rest and let the notice persist.
				wp_schedule_single_event( time(), self::CRON_HOOK );
				wp_safe_redirect( remove_query_arg( 'action' ) );
				exit;
			}
		} while ( false !== get_option( self::OFFSET_OPTION, false ) );

		wp_safe_redirect( remove_query_arg( 'action' ) );
		exit;
	}

	/**
	 * Synchronous version of process_batch — does not schedule next cron event.
	 */
	private static function process_batch_sync() {
		global $wpdb;

		$meta_keys = self::get_bricks_meta_keys();

		if ( empty( $meta_keys ) ) {
			self::complete_migration();
			return;
		}

		$offset       = (int) get_option( self::OFFSET_OPTION, 0 );
		$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
				WHERE meta_key IN ($placeholders)
				AND meta_id > %d
				AND (meta_value LIKE %s OR meta_value LIKE %s)
				ORDER BY meta_id ASC
				LIMIT %d",
				array_merge(
					$meta_keys,
					array( $offset, '%content_toggle_item%', '%content-toggle-item%', self::BATCH_SIZE )
				)
			)
		);

		if ( empty( $rows ) ) {
			self::complete_migration();
			return;
		}

		$last_meta_id = 0;
		foreach ( $rows as $row ) {
			$last_meta_id = (int) $row->meta_id;
			self::migrate_meta_row( $row );
		}

		update_option( self::OFFSET_OPTION, $last_meta_id );
	}

	/**
	 * Check if migration is needed and schedule the first batch.
	 */
	public static function maybe_schedule_migration() {
		$current_version = get_option( self::MIGRATION_VERSION_KEY, '0' );

		if ( version_compare( $current_version, self::TARGET_VERSION, '>=' ) ) {
			return;
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_single_event( time(), self::CRON_HOOK );
		}
	}

	/**
	 * Process a batch of postmeta rows.
	 */
	public static function process_batch() {
		global $wpdb;

		$meta_keys = self::get_bricks_meta_keys();

		if ( empty( $meta_keys ) ) {
			self::complete_migration();
			return;
		}

		$offset       = (int) get_option( self::OFFSET_OPTION, 0 );
		$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
				WHERE meta_key IN ($placeholders)
				AND meta_id > %d
				AND (meta_value LIKE %s OR meta_value LIKE %s)
				ORDER BY meta_id ASC
				LIMIT %d",
				array_merge(
					$meta_keys,
					array( $offset, '%content_toggle_item%', '%content-toggle-item%', self::BATCH_SIZE )
				)
			)
		);

		if ( empty( $rows ) ) {
			self::complete_migration();
			return;
		}

		$last_meta_id = 0;

		foreach ( $rows as $row ) {
			$last_meta_id = (int) $row->meta_id;
			self::migrate_meta_row( $row );
		}

		// Save cursor and schedule next batch.
		update_option( self::OFFSET_OPTION, $last_meta_id );
		wp_schedule_single_event( time() + 5, self::CRON_HOOK );
	}

	/**
	 * Mark migration as complete and clean up.
	 */
	private static function complete_migration() {
		update_option( self::MIGRATION_VERSION_KEY, self::TARGET_VERSION );
		delete_option( self::OFFSET_OPTION );
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Migrate a single postmeta row by renaming the settings key.
	 *
	 * @param object $row The postmeta row with meta_id and meta_value.
	 */
	private static function migrate_meta_row( $row ) {
		global $wpdb;

		$data = maybe_unserialize( $row->meta_value );

		if ( ! is_array( $data ) ) {
			return;
		}

		$changed = false;
		self::migrate_elements_recursive( $data, $changed );

		if ( ! $changed ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->postmeta,
			array( 'meta_value' => maybe_serialize( $data ) ),
			array( 'meta_id' => (int) $row->meta_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Recursively walk all elements and migrate the settings key.
	 *
	 * @param array $elements Reference to elements array.
	 * @param bool  $changed  Reference flag, set to true if any change was made.
	 */
	private static function migrate_elements_recursive( array &$elements, &$changed ) {
		foreach ( $elements as &$element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['name'] ) && 'ba-content-toggle' === $element['name'] ) {
				// Handle both the old underscore key and the previously-migrated hyphen key.
				$old_key = null;
				if ( isset( $element['settings']['content_toggle_item'] ) ) {
					$old_key = 'content_toggle_item';
				} elseif ( isset( $element['settings']['content-toggle-item'] ) ) {
					$old_key = 'content-toggle-item';
				}

				if ( $old_key ) {
					$element['settings']['contentToggleItem'] = $element['settings'][ $old_key ];
					unset( $element['settings'][ $old_key ] );
					$changed = true;
				}
			}

			// Recurse into children.
			if ( ! empty( $element['children'] ) && is_array( $element['children'] ) ) {
				self::migrate_elements_recursive( $element['children'], $changed );
			}
		}
		unset( $element );
	}

	/**
	 * Get the list of Bricks meta keys that store element data.
	 *
	 * @return array
	 */
	private static function get_bricks_meta_keys() {
		$keys = array(
			'_bricks_page_content_2',
		);

		$areas = array( 'header', 'content', 'footer' );
		foreach ( $areas as $area ) {
			$keys[] = '_bricks_page_' . $area . '_2';
		}

		return $keys;
	}
}

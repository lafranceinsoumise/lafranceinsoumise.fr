<?php
/**
 * This file handles background processes.
 *
 * @package GP Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access, please.
}

if ( class_exists( 'WP_Background_Process' ) ) {

	/**
	 * Image Background Process
	 *
	 * @since 1.0.11
	 */
	class GeneratePress_Site_Background_Process extends WP_Background_Process {

		/**
		 * What we're doing.
		 *
		 * @var $action
		 */
		protected $action = 'image_process';

		/**
		 * Do the task.
		 *
		 * @param class $process The process.
		 */
		protected function task( $process ) {

			if ( method_exists( $process, 'import' ) ) {
				$process->import();
			}

			return false;
		}

		/**
		 * Complete the task.
		 */
		protected function complete() {

			parent::complete();

		}

	}

}

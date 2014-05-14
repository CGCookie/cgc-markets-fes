<?php

class CGC_Markets_FES_Dev_Fund {
	
	public function __construct() {
		add_action( 'fes_submit_submission_form_bottom', array( $this, 'save_dev_fund_status' ) );
		add_filter( 'edd_settings_misc', array( $this, 'settings' ) );
	}

	/**
	 * Detects if a submission has opted into the dev fund
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function save_dev_fund_status( $post_id ) {

		$dev_fund = ! empty( $_POST ['dev_fund'] ) ? sanitize_text_field( $_POST ['dev_fund'] ) : false;

		if ( ! empty( $dev_fund ) && 'yes' == strtolower( $dev_fund ) ) {
			
			// User has opted into the dev fund

			if( ! term_exists( 'dev-fund', 'download_tag' ) ) {
				wp_insert_term( 'dev-fund', 'download_tag' );
			}

			wp_set_object_terms( $post_id, 'dev-fund', 'download_tag' );

			$dev_fund_id = edd_get_option( 'dev_fund_user', false );

			if( ! empty( $dev_fund_id ) ) {
				
				// Get the commission recipients
				$recipients = eddc_get_recipients( $post_id );

				if( in_array( $dev_fund_id, $recipients ) ) {

					return; // Dev fund ID already set
				
				}

				$recipients[] = $dev_fund_id;
				$commission_settings = get_post_meta( $post_id, '_edd_commission_settings', true );
				$commission_settings['user_id'] = implode( ',', $recipients );
				update_post_meta( $post_id, '_edd_commission_settings', $commission_settings );

			}

		} else {

			// User has opted out of the dev fund

			wp_remove_object_terms( $post_id, 'dev-fund', 'download_tag' );

			$dev_fund_id = edd_get_option( 'dev_fund_user', false );

			if( ! empty( $dev_fund_id ) ) {
				
				// Get the commission recipients
				$recipients = eddc_get_recipients( $post_id );

				if( ! in_array( $dev_fund_id, $recipients ) ) {

					return; // Dev fund ID not set
				
				}

				unset( $recipients[ array_search( $dev_fund_id, $recipients ) ] );
				$commission_settings = get_post_meta( $post_id, '_edd_commission_settings', true );
				$commission_settings['user_id'] = implode( ',', $recipients );
				update_post_meta( $post_id, '_edd_commission_settings', $commission_settings );

			}


		}
	}
	
	/**
	 * Registers a setting in EDD > Settings > Misc for the development fund account ID
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function settings( $settings ) {
		$settings['dev_fund_user'] = array(
			'id'   => 'dev_fund_user',
			'type' => 'text',
			'size' => 'small',
			'name' => 'Development Fund User ID',
			'desc' => 'Enter the User ID of the development fund account'
		);
		return $settings;
	}

}
<?php

class CGC_Markets_FES_Dev_Fund {

	public function __construct() {
		add_action( 'fes_custom_post_button', array( $this, 'dev_fund_field_button' ) );
		add_action( 'fes_admin_field_dev_fund', array( $this, 'dev_fund_admin_field' ), 10, 3 );
		add_filter( 'fes_formbuilder_custom_field', array( $this, 'dev_fund_formbuilder_is_custom_field' ), 10, 2 );
		add_action( 'fes_render_field_dev_fund', array( $this, 'dev_fund_field' ), 10, 3 );
		add_action( 'fes_submit_submission_form_bottom', array( $this, 'save_dev_fund_status' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		add_filter( 'edd_settings_misc', array( $this, 'settings' ) );

	}
	/**
	 * Register a custom FES submission form button
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function dev_fund_field_button( $title ) {
		echo  '<button class="fes-button button" data-name="dev_fund" data-type="action" title="' . esc_attr( $title ) . '">Dev Fund</button>';
	}

	/**
	 * Setup the custom FES form field
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function dev_fund_admin_field( $field_id, $label = "", $values = array() ) {
		if( ! isset( $values['label'] ) ) {
			$values['label'] = 'Dev Fund';
		}

		$values['no_css']  = true;
		$values['is_meta'] = true;
		$values['name']    = 'dev_fund';
		?>
		<li class="dev_fund">
			<?php FES_Formbuilder_Templates::legend( $values['label'] ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][input_type]", 'dev_fund' ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][template]", 'dev_fund' ); ?>
			<div class="fes-form-holder">
				<?php FES_Formbuilder_Templates::common( $field_id, 'dev_fund', false, $values ); ?>
			</div> <!-- .fes-form-holder -->
		</li>
		<?php
	}

	/**
	 * Indicate that this is a custom field
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	function dev_fund_formbuilder_is_custom_field( $bool, $template_field ) {
		if ( $bool ) {
			return $bool;
		} else if ( isset( $template_field['template'] ) && $template_field['template'] == 'dev_fund' ) {
			return true;
		} else {
			return $bool;
		}
	}

	/**
	 * Render our dev fund rate field
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function dev_fund_field( $attr, $post_id, $type ) {

		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			$required = apply_filters( 'fes_required_class', ' edd-required-indicator', $attr );
		}

		$yes     = has_term( 'dev-fund', 'download_tag', $post_id );
		$amount  = get_post_meta( $post_id, 'dev_fund_amount', true );
		$display = $yes ? '' : 'display:none;';

		$item_price = edd_get_download_price( $post_id );
		$saved_amount = $item_price * ($amount / 100);

		?>
		<script>
			jQuery(function($){
				$('#dev_fund_yes,#dev_fund_no').change(function() {
					$('#dev_fund_amount_wrap').toggle();
				});
				$('#dev_fund_slider').slider({
					max: 70,
					value: '<?php echo $amount; ?>',
					slide: function( event, ui ) {
						//console.log(ui.value);

						// get amount
						var setPrice = $('.fes-price-value').val();
						var totalPrice = setPrice * (ui.value / 100);
						var totalPriceRounded = totalPrice.toFixed(2);

						//console.log(priceValue);
						$('#dev_fund_amount').val( ui.value );
						$('#dev_fund_percentage .percentage').text(ui.value);
						$('#dev_fund_total .amount').text( totalPriceRounded);


					}
				});
			});
		</script>
		<div class="fes-fields <?php echo sanitize_key( $attr['name']); ?>">
			<label for="dev_fund_yes">
				<input type="radio" id="dev_fund_yes" name="dev_fund" value="yes"<?php checked( true, $yes ); ?>/> Yes
			</label>
			<label for="dev_fund_no">
				<input type="radio" id="dev_fund_no" name="dev_fund" value="no"<?php checked( false, $yes ); ?>/> No
			</label>
			<div id="dev_fund_amount_wrap" style="<?php echo $display; ?>">
				<label>How much of each sale would you like to contribute?</label>
				<div id="dev_fund_percentage">
					<span class="percentage"><?php echo absint( $amount ); ?></span>
					<span class="percentage-sign">%</span>
				</div>
				<span class="equals">=</span>
				<div id="dev_fund_total">
					<span class="dollar-sign">$</span>
					<span class="amount"><?php echo number_format( $saved_amount, 2 ); ?></span>
				</div>
				<div id="dev_fund_slider"></div>
				<input type="hidden" name="dev_fund_amount" id="dev_fund_amount" value="<?php echo esc_attr( absint( $amount ) ); ?>"/>
			</div>
		</div> <!-- .fes-fields -->
		<?php
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
		$amount   = ! empty( $_POST['dev_fund_amount'] ) ? absint( $_POST['dev_fund_amount'] ) : 0;

		// if the dev fund isnt' empty and they opted in and the amount isn't empty
		if ( ! empty( $dev_fund ) && 'yes' == strtolower( $dev_fund )  && ! empty( $amount ) ) {

			// User has opted into the dev fund

			if( ! term_exists( 'dev-fund', 'download_tag' ) ) {
				wp_insert_term( 'dev-fund', 'download_tag' );
			}

			// give this download a dev-fund download tag
			wp_set_object_terms( $post_id, 'dev-fund', 'download_tag', true );

			// get the userid of the devfund user assigned to recieve commissions
			$dev_fund_id = edd_get_option( 'dev_fund_user', false );

			// if the dev fund isnt empty, let's continue
			if( ! empty( $dev_fund_id ) ) {

				// Get the commission recipients
				$recipients = eddc_get_recipients( $post_id );

				$settings = get_post_meta( $post_id, '_edd_commission_settings', true );

				// Add the dev fund user to the recipients
				$recipients[] = $dev_fund_id;
				$settings['user_id'] = implode( ',', $recipients );

				$rates           = array_map( 'trim', explode( ',', $settings['amount'] ) );
				$vendor_rate_key = array_search( get_current_user_id(), $recipients );
				$dev_rate_key    = array_search( $dev_fund_id, $recipients );

				// if this vendor is already part of the dev fund users, then update their rate as they set it
				if( in_array( $dev_fund_id, $recipients ) ) {

					// Set the new vendor rate
					if( false !== $vendor_rate_key ) {
						$rates[ $vendor_rate_key ] = 70 - $amount;
					} else {
						$rates[] = 70 - $amount;
					}

					// update the total dev fund amopunt
					update_post_meta( $post_id, 'dev_fund_amount', $amount );


				// this vendor is a first timer, so set the new dev fund rate
				} else {

					// Set the new dev fund rate
					if( false !== $dev_rate_key ) {
						$rates[ $dev_rate_key ] = $amount;
					} else {
						$rates[] = $amount;
					}

					$settings['amount'] = implode( ',', $rates );

					// update the commission settings for this download
					update_post_meta( $post_id, '_edd_commission_settings', $settings );
				}


				// set a flag for this vendor that they are a contributor
				update_user_meta( get_current_user_id(), 'dev_fund_contributor', true );

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

				$settings = get_post_meta( $post_id, '_edd_commission_settings', true );

				// Remove dev fund rate
				$rates           = array_map( 'trim', explode( ',', $settings['amount'] ) );
				$vendor_rate_key = array_search( get_current_user_id(), $recipients );
				$dev_rate_key    = array_search( $dev_fund_id, $recipients );

				if( false !== $dev_rate_key ) {
					unset( $rates[ $dev_rate_key ] );
				}

				// Set the new vendor rate
				if( false !== $vendor_rate_key ) {
					$rates[ $vendor_rate_key ] = 70;
				} else {
					$rates[] = 70;
				}

				$settings['amount'] = implode( ',', $rates );

				// Remove dev fund from recipients
				unset( $recipients[ array_search( $dev_fund_id, $recipients ) ] );

				$settings['user_id'] = implode( ',', $recipients );

				update_post_meta( $post_id, '_edd_commission_settings', $settings );

			}


		}
	}

	/**
	 * Scripts
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function scripts() {
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-slider' );
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
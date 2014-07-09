<?php
/*
 * Plugin Name: CGC Markets - Frontend Submissions
 * Description: Tweaks some of the behavior for EDD Frontend Submissions
 * Author: Pippin Williamson
 * Version: 1.0
 */

class CGC_Markets_FES {

	private $dev_fund;

	public function __construct() {

		$this->includes();
		$this->filters();

	}


	public function includes() {

		include dirname( __FILE__ ) . '/includes/class-dev-fund.php';

		$this->dev_fund = new CGC_Markets_FES_Dev_Fund;

	}

	public function filters() {
		add_filter( 'fes_vendor_dashboard_menu', array( $this, 'remove_logout_menu' ) );
		add_filter( 'fes_register_form_pending_vendor', array( $this, 'vendor_registration_redirect' ) );
	}

	public function remove_logout_menu( $menu ) {
		unset( $menu['logout'] );
		return $menu;
	}

	public function vendor_registration_redirect( $response ) {
	
		$response['redirect_to'] = home_url( 'vendor-app-confirmation' );

		return $response;
	}


}
new CGC_Markets_FES;
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
	}

	public function remove_logout_menu( $menu ) {
		unset( $menu['logout'] );
		return $menu;
	}


}
new CGC_Markets_FES;
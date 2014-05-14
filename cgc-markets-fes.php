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

	}


	public function includes() {

		include dirname( __FILE__ ) . '/includes/class-dev-fund.php';

		$this->dev_fund = new CGC_Markets_FES_Dev_Fund;

	}


}
new CGC_Markets_FES;
<?php
/**
 * This script is used for updating the transaction details
 *
 * @author    Novalnet AG
 * @copyright Copyright (c) Novalnet
 * @license   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 * @link      https://www.novalnet.de
 *
 * This free contribution made by request.
 *
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant
 *
 * Script : novalnet_redirect.php
 */

ob_start();
define('ROOT', './');
define('ASSETS', ROOT.'assets/');
define('BASEASSETS', null);
define('DBPATH', './assets/shopdata/');
define('DOCS', './assets/docdata/');
require(ASSETS.'s2diconf.php');
require(CC_INCLUDE_INIT);

$log = handle_transaction();

// Save log
if (LOG_PAYMENT)
    save_to_file(FILE_PAYMENTLOG, "\nNovalnet ".time()."\n".($log ? $log : 'OK'));

if ($log) {
    $log = urlencode($log);
    if ($myorder && $payment_id)
        redirect(CC_FILENAME_COMPLETE.'?'.PARAMETER_MODE.'=success&'.PARAMETER_ORDER.'='.$myorder->order_id.'&'.PARAMETER_KEY.'='.$myorder->generate_key().'&'.PARAMETER_ID.'='.$payment_id.'&result=success&nnmess='.$log);
    else
        script_die(CC_RESSOURCE_FORBIDDEN, __FILE__, __LINE__);
} else
    redirect(CC_FILENAME_COMPLETE.'?'.PARAMETER_MODE.'=success&'.PARAMETER_ORDER.'='.$myorder->order_id.'&'.PARAMETER_KEY.'='.$myorder->generate_key().'&'.PARAMETER_ID.'='.$payment_id.'&result=success');

/**
 * Handle the transaction success and failure
 * @param none
 *
 * @return  mixed
 */
function handle_transaction() {

    global $payment_id, $myorder;

    $order_id = is_post('order_no') ? post('order_no') : null;

    // Check order
    $myorder = new order($order_id);
    if ($myorder->order_id != $order_id)
        return "Order ID mismatch: '$order_id' and '".$myorder->order_id."' \n";

    // Checking customer id
    $client_id = is_post('customer_no') ? post('customer_no') : null;
    if ($myorder->client->client_id != $client_id)
        return "Client ID mismatch: '$client_id' and '".$myorder->client->client_id."' \n";

    // Check payment method
    session_start();
    $payment_id = (!empty($_SESSION['nn_uid'])) ? $_SESSION['nn_uid'] : $myorder->client->payment_uid;
    unset($_SESSION['nn_uid']);
    $payment = new payment();
    if (!$pm = $payment->get($payment_id))
        return "Cannot open payment $payment_id\n";
    $response    = $_POST;
    $payment_name = $pm->parameter[0];
	if($response['status'] == 100) {
		$global_config = get_global_config($payment);
		$access_key    = $global_config[5];
		if(!isset($_SESSION))
        session_start();
        decode($response, $access_key, $payment_name);
        $_SESSION['novalnet']['redirect_tid']      = $response['tid'];
        $_SESSION['novalnet']['redirect_testmode'] = ((isset($response['test_mode']) && $response['test_mode'] == 1) || (isset($pm->parameter[1]) && $pm->parameter[1] == 1)) ? CC_SITE_NNTESTORDER : '' ;

        // Set the confirmed order status
        $myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS, true);
        $myorder->nn_tid_status = $response['tid_status'];

        // Setting order status for PayPal pending status (tid_status = 90)
        if($payment_name == 'novalnetpaypal' && ($response['tid_status'] == 90 || $response['tid_status'] == '86')) {
			$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGPAYMENT, true);

		}

        $transaction_details = "\n" . CC_SITE_NNTRANSID . $response['tid'] . "\n" . $_SESSION['novalnet']['redirect_testmode'];
        update_message($transaction_details, $myorder);
    }
    if ($pm->autocharge) {
        $myorder->send_status_email();
        if (CC_SITE_AUTOSENDCOUPONS) {
            $myorder->send_coupon_emails();
        }
    }
}

/**
 * Updating the transaction details in the shop
 * @param $transaction_details
 *
 * @return  void
 */
function update_message($transaction_details, $myorder) {
	$existing_message = $myorder->client->message;
	$myorder->client->message  = $existing_message . $transaction_details;
	$myorder->payment_date = date('Y-m-d');
	$myorder->db_updateobject();
	$myorder->client->store(true, $myorder->client->client_id);
}
?>

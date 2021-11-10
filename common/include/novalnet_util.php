<?php
/**
 * This script is used for utility functions for
 * payment processing
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
 * Script : novalnet_util.php
 */

/**
 * Validate merchant global configuration
 * @param $payment_name
 * @param $global_config
 *
 * @return boolean
 */
function validate_global_config($payment_name, $global_config) {

	// Validate merchant global configuration
    $global_config = array_map("trim", $global_config);
    if( !preg_match('/^\d+$/',$global_config[1]) || empty($global_config[2]) || !preg_match('/^\d+$/',$global_config[3]) || !preg_match('/^\d+$/',$global_config[4]))
		return true;

	// Validate merchant payment access key configuration for redirect payments
    if(in_array($payment_name[0], array( 'novalnetinstant', 'novalnetideal', 'novalneteps', 'novalnetpaypal', 'novalnetgiropay', 'novalnetcc','novalnetprzelewy24','novalnetpostfinance','novalnetfinancecard', 'novalnetbancontact'))
    && empty($global_config[5]))
        return true;
}

/**
 * Validate payment parameter values and assign payment parameter input values
 * @param $order
 *
 * @return mixed
 */
function validate_payment_parameter($order) {

	// Credit Card
	if($order->pm->parameter[0] == 'novalnetcc') {
		$nn_pan_hash = trim(post('nn_pan_hash'));
		$nn_cc_uniqueid = trim(post('nn_cc_uniqueid'));
		if( empty($nn_pan_hash) || empty($nn_cc_uniqueid)) { // Card details validation
			return CC_SITE_NNCARDERRORMSG;
		}
		$_SESSION['novalnet']['nncc_pan_hash']  = $nn_pan_hash;
		$_SESSION['novalnet']['nncc_uniqueid']  = $nn_cc_uniqueid;
	}

	// Direct Debit SEPA and payment guarantee SEPA
	if($order->pm->parameter[0] == 'novalnetsepa') {
		$novalnet_sepa_account_holder = trim(post('novalnet_sepa_account_holder'));
		$novalnet_sepa_iban = trim(post('novalnet_sepa_iban'));
		if(empty($novalnet_sepa_account_holder) || empty($novalnet_sepa_iban)) { // Account details validation
			return CC_SITE_NNACCOUNTERRORMSG;
		}
		$sepa_due_date = trim($order->pm->parameter[5]);
		if($sepa_due_date != '' && (!preg_match('/^\d+$/',$sepa_due_date) || $sepa_due_date < 2 || $sepa_due_date > 14)) { // Deu date validation
			return CC_SITE_SEPA_DUE_DATE_ERROR;
		}
		$_SESSION['novalnet']['nnsepa_account_holder']  = $novalnet_sepa_account_holder;
		$_SESSION['novalnet']['nnsepa_iban']  = $novalnet_sepa_iban;
		if($order->pm->parameter[6] == 1) { // Payment guarante
			$sepa_date = post('novalnet_sepa_date');
			$sepa_month = post('novalnet_sepa_month');
			$sepa_year = post('novalnet_sepa_year');
			if($order->pm->parameter[8] != 1 && empty($order->client->company) && !checkdate($sepa_month, $sepa_date, $sepa_year)) // Birth date validation
				return CC_SITE_NNINVALIDDOB;
			$_SESSION['novalnet']['nnsepa_dob'] = $date = $sepa_year.'-'.$sepa_month.'-'.$sepa_date;
			return check_guarantee_conditions($order, $date);
		}
	}

	// Invoice and payment guarante Invoice
	if($order->pm->parameter[0] == 'novalnetinvoice') {
		$invoice_date = post('novalnet_invoice_date');
		$invoice_month = post('novalnet_invoice_month');
		$invoice_year = post('novalnet_invoice_year');
		if(trim($pm->parameter[5]) != '' &&  !preg_match('/^[0-9]+$/', trim($pm->parameter[5]))) {
			return CC_SITE_DUE_DATE_ERROR;
		}
		if($order->pm->parameter[6] == 1) { // Payment guarante
			if($order->pm->parameter[8] != 1 && empty($order->client->company) && !checkdate($invoice_month, $invoice_date, $invoice_year)) // Birth date validation
				return CC_SITE_NNINVALIDDOB;
			$_SESSION['novalnet']['nninvoice_dob'] = $date = $invoice_year.'-'.$invoice_month.'-'.$invoice_date;
			return check_guarantee_conditions($order, $date);
		}
	}
    return '';
}

/**
 * Check payment guarantee conditions
 * @param $order
 * @param $date
 *
 * @return mixed
 */
function check_guarantee_conditions(&$order, &$date){
	$order_amount = $order->get_totalprice() * 100;
	$minimum_amount_gurantee = ((trim($order->pm->parameter[7]) != '') && ($order->pm->parameter[7] > 999)) ? $order->pm->parameter[7] : 999;
	$error_message = '';

	if (!in_array(strtoupper(substr($order->client->country, 0, 2)), array('DE', 'AT', 'CH'))) { // Non DACH countries will not be allowed for payment guarantee
		$error_message = htmlspecialchars_decode(CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_COUNTRY, ENT_QUOTES);
	} else if ($order->currency != 'EUR') { // Non EURO currency will not be allowed for payment guarantee
		$error_message = htmlspecialchars_decode(CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_CURRENCY, ENT_QUOTES);
	} else if ($order_amount < $minimum_amount_gurantee ) { // Minimum amount validation
		$error_message = htmlspecialchars_decode(sprintf(CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_AMOUNT, $minimum_amount_gurantee), ENT_QUOTES);
	}else if($order->client->deviating_shipping_address == 1) { // Billing and shipping address should be same for payment guarantee
		if(strtolower($order->client->firstname) != strtolower($order->client->shipping_firstname)  || strtolower($order->client->lastname) != strtolower($order->client->shipping_lastname)
		|| strtolower($order->client->street) != strtolower($order->client->shipping_street) || strtolower($order->client->streetnumber) != strtolower($order->client->shipping_streetnumber)
		|| ($order->client->zip) != ($order->client->shipping_zip) || strtolower($order->client->city) != strtolower($order->client->shipping_city)
		|| substr($order->client->country, 0, 2) != substr($order->client->shipping_country, 0, 2) ) {
			$error_message = htmlspecialchars_decode(CC_SITE_NNGUARANTEE_INVALID_ADDRESS, ENT_QUOTES);
		}
	} else if (empty($order->client->company) && (date('Y-m-d') - $date) < 18) { // Age should be greater than or equal to 18 for payment guarantee
		$error_message = CC_SITE_NNVALIDDOB;
	}

	if($order->pm->parameter[6] == 1 && $order->pm->parameter[8] != 1 && $error_message != '')
		return $error_message;
	else if($error_message == '')
		$error_message = $_SESSION['novalnet']['proceed_gurantee'] = 1;
	else
		$error_message = 1;
	return $error_message;
}

/**
 * Return payment key of the concern payment type
 * @param $payment_type
 *
 * @return string
 */
function get_payment_key($payment_type) {

    $payment_types = array(
        'novalnetcc'                => '6',
        'novalnetsepa'              => '37',
        'novalnetsepaguarantee'     => '40',
        'novalnetinvoice'           => '27',
        'novalnetinvoiceguarantee'  => '41',
        'novalnetprepayment'        => '27',
        'novalnetinstant'           => '33',
        'novalnetpaypal'            => '34',
        'novalnetideal'             => '49',
        'novalneteps'               => '50',
        'novalnetcashpayment'       => '59',
        'novalnetgiropay'           => '69',
        'novalnetprzelewy24'        => '78',
        'novalnetfinancecard'       => '87',
        'novalnetpostfinance'       => '88',
        'novalnetbancontact'        => '44',
        'novalnetmultibanco'        => '73',
	);
	return $payment_types[$payment_type];
}

/**
 * Get merchant details
 * @param $config
 *
 * @return array
 */
function get_merchant_details($config) {
	$config_details = array(
		'vendor'    => $config[1],
		'auth_code' => $config[2],
		'product'   => $config[3],
		'tariff'    => $config[4],
	);
	return $config_details;
}

/**
 * Get the payment key, amount, currency and invoice type
 * @param $order
 * @param $payment_type
 * @param $authorize_amount
 *
 * @return array
 */
function get_payment_details($order, $payment_type, $authorize_amount = '') {
	$order_amount = $order->get_totalprice() * 100;
    $payment_details = array(
		'key' 		=> get_payment_key($payment_type),
        'amount' 	=> $order_amount,
        'currency' 	=> $order->currency,
    );

    if( in_array($payment_type, array('novalnetinvoice', 'novalnetprepayment'))) { // Assigning invoice type
        $payment_details['invoice_type'] = (($payment_type == 'novalnetinvoice') ? 'INVOICE' : 'PREPAYMENT');
    }

	if($authorize_amount !== '' && ((int)$order_amount >= $authorize_amount)) { // Assigning onhold
		$payment_details['on_hold'] = '1';
	}

    return $payment_details;
}

/**
 * Get end customer details
 * @param $myorder
 *
 * @return array
 */
function get_customer_details($myorder) {
    $site_url = (CC_SITE_SSLURL != '') ? CC_SITE_SSLURL : CC_SITE_HTTPURL;
	$site_url = ($site_url !='')? $site_url : CC_SITE_LOCALURL;
    $customer_details = array(
		'gender'           	=> 'u',
		'first_name'		=> $myorder->client->firstname,
		'last_name'			=> $myorder->client->lastname,
        'email'            	=> $myorder->client->email,
        'city'             	=> $myorder->client->city,
        'country_code'     	=> substr($myorder->client->country, 0, 2),
        'zip'              	=> $myorder->client->zip,
        'remote_ip'        	=> get_remote_ip(),
        'system_ip'        	=> $_SERVER['SERVER_ADDR'],
        'system_name'      	=> 'Siquando',
        'system_version'	=> 'v_10-NN-1.3.0',
        'system_url'       	=> $site_url,
        'customer_no'      	=> $myorder->client->client_id,
        'lang'             	=> defined('CC_SHOP_NNLANG') ? CC_SHOP_NNLANG : strtoupper(substr($order->client->country, 0, 2)),
        'order_no'         	=> $myorder->order_id,
    );
    if(!empty($myorder->client->streetnumber)) {
		$customer_details['house_no'] = $myorder->client->streetnumber;
		$customer_details['street'] = $myorder->client->street;
	} else {
		$customer_details['street'] = $myorder->client->street;
		$customer_details['search_in_street'] = 1;
	}
    if($myorder->client->company !='') { // Company
		$customer_details['company'] = $myorder->client->company;
	}
	if($myorder->client->phone != '') { // Telephone
        $customer_details['tel'] = $myorder->client->phone;
    }
    if($myorder->client->cellphone != '') { // Mobile
        $customer_details['mobile'] = $myorder->client->cellphone;
    }
    if($myorder->client->fax != '') { // Fax
        $customer_details['fax'] = $myorder->client->fax;
    }
    return $customer_details;
}

/**
 * Get payment gateway URL
 * @param $payment_type
 *
 * @return string
 */
function get_payment_url($payment_type) {
    $api_handler_payments = array('novalnetcc', 'novalnetsepa', 'novalnetinvoice', 'novalnetprepayment', 'novalnetcashpayment', 'novalnetmultibanco');
    $online_transfer_handler_payments = array('novalnetinstant', 'novalnetideal');
    $giropay_handler_payments = array('novalnetgiropay', 'novalneteps');
    $postfinance_handler_payments = array('novalnetpostfinance', 'novalnetfinancecard');
    $payment_url = array(
	'api_handler'        		=>  'https://payport.novalnet.de/paygate.jsp',
        'online_transfer_handler' 	=>  'https://payport.novalnet.de/online_transfer_payport',
        'paypal_handler'     		=>  'https://payport.novalnet.de/paypal_payport',
        'giropay_handler'        	=>  'https://payport.novalnet.de/giropay',
        'przelewy24_handler' 		=>  'https://payport.novalnet.de/globalbank_transfer',
        'postfinance_handler' 		=>  'https://payport.novalnet.de/postfinance',
        'bancontact_handler' 		=>  'https://payport.novalnet.de/bancontact'
    );
    if( in_array($payment_type, $api_handler_payments)) {
        return $payment_url['api_handler'];
    } elseif(in_array($payment_type, $online_transfer_handler_payments)) {
        return $payment_url['online_transfer_handler'];
    } elseif(in_array($payment_type, $giropay_handler_payments) ) {
        return $payment_url['giropay_handler'];
    } elseif($payment_type == 'novalnetpaypal') {
        return $payment_url['paypal_handler'];
    } elseif($payment_type == 'novalnetprzelewy24') {
        return $payment_url['przelewy24_handler'];
    } elseif(in_array($payment_type, $postfinance_handler_payments) ) {
        return $payment_url['postfinance_handler'];
    } elseif($payment_type == 'novalnetbancontact') {
        return $payment_url['bancontact_handler'];
    }
}

/**
 * Send request to sever and return response
 * @param $urlparam
 * @param $nn_url
 *
 * @return array
 */
function send_request($urlparam, $nn_url) {
    $ch = curl_init($nn_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $urlparam);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 240);
    $result   = curl_exec($ch);
    $is_error  = curl_errno($ch);
    if($is_error < 0)
		$is_error = 0;
    curl_close($ch);
    return  $result;
}



/**
 * Get global configuration
 * @param $payment
 *
 * @return  string
 */
function get_global_config($payment) {
	foreach ($payment as $obj) {
        foreach($obj as $k) {
            if($k->parameter['0'] == 'novalnetconfig') {
                $novalnet_globalparam = $k->parameter;
            }
        }
    }
    return $novalnet_globalparam;
}

/**
 * Get status description
 * @param $response
 *
 * @return  string
 */
function get_status_message($response) {
    return (($response['status_desc'] != '') ? set_decode($response['status_desc']) : (($response['status_text'] != '') ? set_decode($response['status_text']) : set_decode($response['status_message'])));
}

/**
 * Decode UTF data
 * @param $str
 *
 * @return string
 */
function set_decode($str) {
    return iconv("UTF-8", "ISO-8859-15", $str);
}

/**
 * Get remote address
 *
 * @return string
 */
function get_remote_ip() {
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $this->is_public_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
		if ($this->is_public_ip($iplist[0])) {
			return $iplist[0];
		}
	} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && $this->is_public_ip($_SERVER['HTTP_CLIENT_IP']))  {
		return $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->is_public_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
		return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && $this->is_public_ip($_SERVER['HTTP_FORWARDED_FOR']) ) {
		return $_SERVER['HTTP_FORWARDED_FOR'];
	}
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * Check public IP address
 * @param $value
 *
 * @return string
 */
function is_public_ip($value) {
	if (!$value || count(explode('.',$value))!=4) {
		return false;
	}
	return !preg_match('~^((0|10|172\.16|192\.168|169\.254|255|127\.0)\.)~', $value);
}

/**
 * Get unique id
 *
 * @return string
 */
function get_uniqueid()
{
    $random = explode(',', '8,7,6,5,4,3,2,1,9,0,9,7,6,1,2,3,4,5,6,7,8,9,0');
    shuffle($random);
    return substr(implode($random, ''), 0, 16);
}

/**
 * Encode the data
 * @param $parameters
 * @param $access_key
 *
 * @return string
 */
function encode(&$parameters, $access_key) {
    foreach (array('auth_code', 'product', 'tariff', 'amount', 'test_mode') as $key) {
        // Encoding the values
        $parameters[$key] = generate_encode($parameters[$key], $parameters['uniqid'], $access_key);
    }
}

/**
 * Generate the encoded value using AES algorithm
 * @param $data
 * @param $uniqid
 * @param $payment_access_key
 *
 * @return string
 */
function generate_encode($data, $uniqid, $payment_access_key) {
	// Encryption process
	return htmlentities(base64_encode(openssl_encrypt($data, "aes-256-cbc", $payment_access_key, true, $uniqid)));
}

/**
 * Generating hash value using SHA256 algorithm
 * @param $data
 * @param $payment_access_key
 *
 * @return string
 */
function generate_hash($data, $payment_access_key) {
    // Hash generation using sha256 and encoded merchant details
    return hash('sha256', ($data['auth_code'].$data['product'].$data['tariff'].$data['amount'].$data['test_mode'].$data['uniqid'].strrev($payment_access_key)));
}

/**
 * Decode the data
 * @param $parameters
 * @param $payment_access_key
 *
 * @return string
 */
function decode(&$parameters, $payment_access_key) {
    $parameter_to_be_encoded = array('auth_code', 'product', 'tariff', 'amount', 'test_mode');
	foreach($parameter_to_be_encoded as $value ) {
        $sData = $parameters[$value];
		$parameters[$value] = openssl_decrypt(base64_decode($sData), "aes-256-cbc", $payment_access_key, true, $parameters['uniqid']);
	}
    return true;
}

/**
 * Get Novalnet bank details Invoice and Prepayment
 * @param $request
 * @param $response
 * @param $myorder
 *
 * @return string
 */
function get_bank_details($request, $response, $myorder=NULL) {
    $new_line = "\n";
    $note = '';
    $note .= CC_SITE_NNTRANSINFO . $new_line;
	if(!empty($request['due_date'])) {
		$due_date = date('d.m.Y', strtotime($request['due_date']));
		$note .= CC_SITE_NNTRANSVALIDUNTIL .' '. $due_date . $new_line;
	}
	$note .= CC_SITE_NNTRANSACCHOLDER  . ' ' . 'NOVALNET AG' . $new_line;
	$note .= CC_SITE_NNBANKIBAN . ' ' . $response['invoice_iban'] . $new_line;
	$note .= CC_SITE_NNBANKBIC . ' ' .$response['invoice_bic'] . $new_line;
	$note .= CC_SITE_NNBANKNAME . ' ' . utf8_decode($response['invoice_bankname']) . ' ' . $response['invoice_bankplace'] . $new_line;
	$note .= CC_SITE_NNAMOUNT . ' ' . format::price($myorder->get_totalprice(), $myorder->currency) . $new_line;
    return $note;
}

/**
 * Get nearest cashpayment store details
 * @param array $request
 * @param array $response
 * @param string $store_name
 *
 * return string
 */
function get_cashpayment_store_details($response){
	$nearest_store =  get_nearest_store($response, 'nearest_store');
    $i =0;
	foreach ($nearest_store as $key => $values) {
		$i++;
		if(!empty($nearest_store['nearest_store_title_'.$i])) {
			$order_invoice_comments .= PHP_EOL . $nearest_store['nearest_store_title_'.$i].PHP_EOL;
		}
		if (!empty($nearest_store['nearest_store_street_'.$i])) {
			$order_invoice_comments .= $nearest_store['nearest_store_street_'.$i].PHP_EOL;
		}
		if(!empty($nearest_store['nearest_store_city_'.$i])) {
			$order_invoice_comments .= $nearest_store['nearest_store_city_'.$i].PHP_EOL;
		}
		if(!empty($nearest_store['nearest_store_zipcode_'.$i])) {
			$order_invoice_comments .= $nearest_store['nearest_store_zipcode_'.$i].PHP_EOL;
		}
		if(!empty($nearest_store['nearest_store_country_'.$i])) {
			$order_invoice_comments .= $nearest_store['nearest_store_country_'.$i].PHP_EOL;
		}
	}
	return $order_invoice_comments;

}

/**
 * Get nearest stores
 * @param array $response
 * @param string $store_name
 *
 * return array
 */
function get_nearest_store($response, $store_name){
	$stores_details = array();
	foreach ($response as $key => $stores_details){
		if(stripos($key, $store_name)!==FALSE){
			$stores[$key] = $stores_details;
		}
	}
	return $stores;
}

/**
 * Get payment refrences based for Invoice and Prepayment
 * @param $tid
 * @param $order_no
 * @param $product_id
 *
 * @return string
 */
function get_payment_reference($tid, $order_no, $product_id) {
     $payment_references  = CC_SITE_NNINVOICEREFDESCMORE . PHP_EOL;
     $payment_references .= CC_SITE_NNINVOICEREFTEXTSINGLE . '1 : BNR-' . $product_id . '-' . $order_no . PHP_EOL;
     $payment_references .= CC_SITE_NNINVOICEREFTEXTSINGLE . '2 : TID '. $tid;
     return $payment_references;
}

/**
 * Get payment refrences based for Multibanco
 * @param $partner_payment_reference
 * @param $service_supplier_id
 *
 * @return string
 */
function get_multibanco_payment_reference($partner_payment_reference, $service_supplier_id) {
     $payment_references  = CC_SITE_NNMULTIBANCOREFDESC . PHP_EOL;
     $payment_references .= CC_SITE_NNMULTIBANCOREF . ' ' . $partner_payment_reference . PHP_EOL;
     $payment_references .= CC_SITE_NNMULTIBANCOENTITYNO . ' '. $service_supplier_id;
     return $payment_references;
}


/**
 * Get due date
 * @param $days
 *
 * @return  string
 */
function get_due_date($days = 0) {
    return  date('Y-m-d', strtotime('+' . $days . ' days')) ;
}

?>

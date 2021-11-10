<?php 
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script: callback_novalnet2siquandoshop.php
 *
 */
error_reporting(E_ALL);
ini_set('display_errors',1);

define('ROOT', './');
define('ASSETS', ROOT.'assets/');
define('BASEASSETS', null);
define('DBPATH', './assets/shopdata/');
require(ASSETS.'s2diconf.php');
require(ASSETS.'s2dinovalnet_util.php');
require(CC_INCLUDE_INIT);

$request_params = $_REQUEST;
$payment = new payment();
$novalnet_globalparam = get_global_config($payment);
$vendor_script = new NovalnetVendorScript($request_params); 
$params = $request_params;
$myorder = $vendor_script->get_order_reference();
$params['gateway_status'] = $myorder->nn_tid_status;    
$currency = (isset($params['currency'])) ? $params['currency'] : '';
$payment_type_level = $vendor_script->get_payment_type_level();
if($payment_type_level === 0) { // Level 0 payments	
	if(in_array($params['payment_type'],array('PAYPAL','CREDITCARD')) && $params['status'] == 100 && $params['tid_status'] == 100 && in_array($params['gateway_status'], array(85,90,98))) {
		$comments = PHP_EOL .sprintf(CC_SITE_NNTRANSACTION_CONFIRM_TEXT, date('Y-m-d H:i:s'));
		$order_status = CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING;
	} else if(in_array($params['payment_type'],array('GUARANTEED_INVOICE','INVOICE_START','GUARANTEED_DIRECT_DEBIT_SEPA','DIRECT_DEBIT_SEPA')) 
	&& in_array($params['tid_status'], array(91,99,100)) && $params['status'] == 100 && in_array($params['gateway_status'] ,array(75,91,99))){
		$comments = PHP_EOL .sprintf(CC_SITE_NNTRANSACTION_CONFIRM_TEXT, date('Y-m-d H:i:s'));
		if($params['gateway_status'] == 75 && in_array($params['tid_status'], array(91,99))) {
			$order_status = CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS;
			$comments = PHP_EOL .sprintf(CC_SITE_NNPENDING_TO_ONHOLD_TEXT, $params['tid'], date('Y-m-d H:i:s'));
		} else {
			$order_status = CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING;
		}
		if($myorder->payment_param == 'novalnetinvoice' && $params['gateway_status'] == 75) {
			$comments .= PHP_EOL .$vendor_script->get_invoice_comments();
		}
	} else if(in_array($params['payment_type'],array('GUARANTEED_INVOICE','GUARANTEED_DIRECT_DEBIT_SEPA')) && $params['tid_status'] != 100 && $params['status'] != 100 && in_array($params['gateway_status'], array(75,91,99))) {
		$order_status = CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS;
		$comments = PHP_EOL .sprintf(CC_SITE_NNTRANSACTION_CANCELLATION_TEXT, date('Y-m-d H:i:s'));
	} else if(in_array($params['payment_type'],array('PAYPAL','PRZELEWY24')) && $params['status'] == 100 && $params['tid_status'] == 100 && $params['tid_status']!=100) {
			 $comments = PHP_EOL .sprintf(CC_SITE_NNLEVEL_ZERO_CALLBACK_PAID_TEXT, $params['tid'], $myorder->get_totalprice(), date('Y-m-d H:i:s'));
			 $order_status = CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING;
	} else if($params['payment_type']=='PAYPAL' && $params['tid_status'] == 90) {
			$comments = PHP_EOL .sprintf(CC_SITE_NNPENDING_TO_ONHOLD_TEXT, $params['tid'], date('Y-m-d H:i:s'));
			$order_status = CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS;
	} else {
		$vendor_script->display_message('Novalnet Callbackscript received. Payment type ( '.$params['payment_type'].' ) is not applicable for this process!');
	}
	$myorder->set_bill_data();
	$myorder->set_status($order_status, true);
	$myorder->db_updateobject();
	$vendor_script->send_notify_mail($comments);
	$vendor_script->update_comments($myorder, $comments);
	$vendor_script->display_message($comments);
} elseif($payment_type_level == 1) { // Level 1 payments
	
	$comments = in_array($params['payment_type'], array('PAYPAL_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU','CREDITCARD_BOOKBACK', 'PRZELEWY24_REFUND','CASHPAYMENT_REFUND','GUARANTEED_INVOICE_BOOKBACK','GUARANTEED_SEPA_BOOKBACK')) ? PHP_EOL .sprintf(CC_SITE_NNCALLBACK_BOOKBACK_TEXT, $params['tid_payment'], sprintf('%.2f',($params['amount']/100)).' '.$currency, date('Y-m-d H:i:s'), $params['tid']) . PHP_EOL : PHP_EOL . sprintf(CC_SITE_NNCALLBACK_CHARGEBACK_TEXT, $params['tid_payment'], sprintf('%.2f',($params['amount']/100)).' '.$currency, date('Y-m-d H:i:s'), $params['tid']) . PHP_EOL;
	
	$vendor_script->send_notify_mail($comments);
	$vendor_script->update_comments($myorder, $comments);
	$vendor_script->display_message($comments);            
} 
elseif($payment_type_level == 2) { // Credit entry payment and Collections available            
	// Credit entry of INVOICE or PREPAYMENT
	$comments = sprintf(CC_SITE_NNCALLBACK_PAID_TEXT , $params['tid_payment'], sprintf('%.2f',($params['amount']/100)).' '.$currency, date('Y-m-d H:i:s'), $params['tid']);
	if($params['payment_type'] == 'ONLINE_TRANSFER_CREDIT') {
		$comments = PHP_EOL .sprintf(CC_SITE_NNCALLBACK_ONLINE_TRANSFER_CREDIT_TEXT, $myorder->get_totalprice(), $params['order_no']);
	}
	if(in_array($params['payment_type'], array('INVOICE_CREDIT', 'CASHPAYMENT_CREDIT'))) {
		if($params['amount'] > $myorder->nn_paid_amount) {
			$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING, true);
		}
	} else {
		$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING, true);
	}
	$myorder->nn_paid_amount += $params['amount'];
	$myorder->set_bill_data();
	$myorder->db_updateobject();
	$vendor_script->send_notify_mail($comments);
	$vendor_script->update_comments($myorder, $comments);           
	$vendor_script->display_message('Novalnet callback received. Callback Script executed already. Refer Order :'.$myorder->order_id);
}
else if ($params['payment_type'] == 'TRANSACTION_CANCELLATION') { // Handle transaction cancellation
	$comments = sprintf(CC_SITE_NNTRANSACTION_CANCELLATION_TEXT, date('Y-m-d H:i:s'));
	$myorder->nn_tid_status = $params['tid_status'];
	$myorder->db_updateobject();
	$vendor_script->send_notify_mail($comments);
	$vendor_script->update_comments($myorder, $comments);
	$vendor_script->display_message($comments);
}

class NovalnetVendorScript {
    
    /** Initial payments - Level : 0 */
    protected   $initial_payments = array('CREDITCARD', 'INVOICE_START', 'DIRECT_DEBIT_SEPA', 'GUARANTEED_INVOICE', 'PAYPAL', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'ONLINE_TRANSFER', 'IDEAL', 'EPS', 'GIROPAY', 'PRZELEWY24', 'CASHPAYMENT');
    
    /** Chargeback, bookback, return debit and refund payments - Level : 1 */
    protected   $chargeback_payments =  array('RETURN_DEBIT_SEPA', 'REVERSAL', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'REFUND_BY_BANK_TRANSFER_EU', 'PAYPAL_BOOKBACK', 'PRZELEWY24_REFUND', 'CASHPAYMENT_REFUND', 'GUARANTEED_INVOICE_BOOKBACK', 'GUARANTEED_SEPA_BOOKBACK');
    
    /** Credit entry payment and collections payments - Level : 2 */
    protected   $collection_credit_payments = array('INVOICE_CREDIT', 'CREDIT_ENTRY_CREDITCARD', 'CREDIT_ENTRY_SEPA', 'DEBT_COLLECTION_SEPA', 'DEBT_COLLECTION_CREDITCARD', 'ONLINE_TRANSFER_CREDIT', 'CASHPAYMENT_CREDIT', 'CREDIT_ENTRY_DE', 'DEBT_COLLECTION_DE');
    
    protected $subscription_payments = array('SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE');
	
	protected $cancel_payment = array('TRANSACTION_CANCELLATION');
	
    protected $payment_group = array(
        'novalnetcc'         	=> array('CREDITCARD', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'CREDIT_ENTRY_CREDITCARD', 'DEBT_COLLECTION_CREDITCARD', 'SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE', 'TRANSACTION_CANCELLATION'),
        'novalnetsepa'       	=> array('DIRECT_DEBIT_SEPA', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'RETURN_DEBIT_SEPA', 'GUARANTEED_SEPA_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU', 'DEBT_COLLECTION_SEPA', 'CREDIT_ENTRY_SEPA', 'SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE', 'TRANSACTION_CANCELLATION'),
        'novalnetinvoice'    	=> array('INVOICE_START', 'GUARANTEED_INVOICE', 'INVOICE_CREDIT', 'GUARANTEED_INVOICE_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU', 'SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE', 'TRANSACTION_CANCELLATION'),
        'novalnetprepayment' 	=> array('INVOICE_START','INVOICE_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU', 'SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE', 'TRANSACTION_CANCELLATION'),
        'novalnetcashpayment'	=> array('CASHPAYMENT', 'CASHPAYMENT_REFUND', 'CASHPAYMENT_CREDIT', 'TRANSACTION_CANCELLATION'),
        'novalnetideal'      	=> array('IDEAL', 'ONLINE_TRANSFER_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU', 'TRANSACTION_CANCELLATION'),
        'novalnetinstant'    	=> array('ONLINE_TRANSFER', 'ONLINE_TRANSFER_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU', 'TRANSACTION_CANCELLATION'),
        'novalneteps'        	=> array('EPS', 'ONLINE_TRANSFER_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU', 'TRANSACTION_CANCELLATION'),
        'novalnetgiropay'    	=> array('GIROPAY', 'ONLINE_TRANSFER_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU', 'TRANSACTION_CANCELLATION'),
        'novalnetprzelewy24'    => array('PRZELEWY24', 'PRZELEWY24_REFUND', 'TRANSACTION_CANCELLATION'),
        'novalnetpaypal'     	=> array('PAYPAL', 'PAYPAL_BOOKBACK', 'SUBSCRIPTION_STOP', 'SUBSCRIPTION_PAUSE', 'SUBSCRIPTION_UPDATE', 'TRANSACTION_CANCELLATION'),
   );   
    
    /** @Array Callback Capture parameters */
    protected $capture_params = array();
    protected $required_params   = array('vendor_id', 'status', 'payment_type', 'tid', 'tid_status');
    
    function __construct($capture = array()) {
        self::validate_ip_address();
        if(empty($capture)) {
            self::display_message('Novalnet callback received. No params passed over!');
        }
        $this->capture_params = self::validate_capture_params($capture);
    }
    
    /**
     * Validate IP address
     *
     * @return void
     */ 
    function validate_ip_address() {
        global $novalnet_globalparam;
        // Get host IP address
        $host_address = gethostbyname('pay-nn.de');
        if (empty($host_address))
			self::display_message("Novalnet HOST IP missing");
        $client_ip = get_remote_ip();
        if($novalnet_globalparam[7] == '0' && $client_ip != $host_address) {
            self::display_message("Novalnet callback received. Unauthorised access from the IP $client_ip");
        }
    }
    
    /**
     * Perform parameter validation process and set empty value if not exist in capture
     * @param $capture
     * 
     * @return Array
     */
    function validate_capture_params($capture = array()) {
		foreach ($this->required_params as $v) {
			if (empty($capture[$v])) {
				  self::display_message('Required param ( ' . $v . '  ) missing!');
			} elseif (in_array($v, array('tid', 'tid_payment', 'signup_tid'), true) && !preg_match('/^\d{17}$/', $capture [$v])) {
				$this->display_message('Novalnet callback received. Invalid TID [ ' . $capture [$v] . ' ] for Order.');
			}			
		}       
		
		// Payment type validation     
		if (!in_array($capture['payment_type'], array_merge($this->initial_payments, $this->chargeback_payments, $this->collection_credit_payments, $this->subscription_payments, $this->cancel_payment))) { 
			self::display_message('Novalnet callback received. Payment type ( '.$capture['payment_type'].' ) is mismatched!');
		}
		
		if (!empty($capture['payment_type']) && in_array($capture ['payment_type'], array_merge($this->chargeback_payments, $this->collection_credit_payments), true)) { // Collection, credit payments or chargeback, return debit, bookback and refund payments
			$capture['shop_tid'] = $capture['tid_payment'];
		} elseif ((isset($capture['subs_billing']) && $capture['subs_billing'] === '1') || $capture['payment_type'] == 'SUBSCRIPTION_REACTIVATE') { // Subscription
			$capture['shop_tid'] = $capture['signup_tid'];
		} else { 
			$capture['shop_tid'] = $capture['tid'];
		}
        return $capture;
    }

    /**
     * Return payment type level
     *   
     * @return  integer
     */
    function get_payment_type_level() {
        if(in_array($this->capture_params['payment_type'], $this->initial_payments)) {
            return 0;
        } 
        else if(in_array($this->capture_params['payment_type'], $this->chargeback_payments)) {
            return 1;
        }
        else if(in_array($this->capture_params['payment_type'], $this->collection_credit_payments)) {
            return 2;
        }
    }

    /**
     * Get order reference from the novalnet_transaction_detail table on shop database
     *
     * @return object
     */
    function get_order_reference() { 
        $db = new db(TABLE_CLIENTS);
        $fields = array('t2.order_id as clt_order_id', 't1.client_id as client_id', 't2.payment_param as payment_name', 't1.message as message');   
        $where = "t1.message like '%" . $this->capture_params['shop_tid'] . "%'";
        $db->db_selectjoin($fields, TABLE_ORDERS, 'order_id', $where);
        if($row = $db->db_fetch()) {
            $myorder = new order($row->clt_order_id);
            if (!empty($myorder) && $this->capture_params['order_no'] != $myorder->order_id) // Order number validation
                self::display_message('Novalnet callback received. Order Number is not valid.');
			if (!in_array($this->capture_params['payment_type'], $this->payment_group[$row->payment_name])) // Payment type validation
                self::display_message('Novalnet callback received. Payment Type [' . $this->capture_params['payment_type'] . '] is not valid.');		
        } else {
			$order_id = isset($this->capture_params['order_no'])? $this->capture_params['order_no'] :'';
            if($order_id != '') { 
				$db = new db(TABLE_ORDERS);
				$fields = array('payment_param as payment_name');
				$where  = "order_id = ".$order_id;
				$db->db_select($fields,$where);
				$row = $db->db_fetch();					
				if (!in_array($this->capture_params['payment_type'], $this->payment_group[$row->payment_name])) { 
					self::display_message('Novalnet callback received. Payment Type [' . $this->capture_params['payment_type'] . '] is not valid.');
				$myorder = new order($order_id);
				$existing_message = $myorder->client->message;
				if ($this->capture_params['status'] == '100') {
					$trans_details = $existing_message. "  " . $test_order . ' Novalnet Transaktions-ID '.$this->capture_params['shop_tid'];
					if(in_array($this->capture_params['payment_type'], array('INVOICE_START', 'GUARANTEED_INVOICE')) && $this->capture_params['tid_status'] != '75') {
						$bank_details = $this->get_invoice_comments();
					}
					$trans_details = !empty($bank_details) ? $trans_details . $bank_details : $trans_details;
					// Updating the order status based on payments and transaction status
					if(in_array($this->capture_params['tid_status'], array('85', '91', '98', '99')) || $row->payment_name == 'novalnetinvoice')
						$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS, true);
					else if(in_array($this->capture_params['tid_status'], array('75', '86', '90')) || in_array($row->payment_name, array('novalnetprepayment','novalnetcashpayment')))
						$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGPAYMENT, true);
					else
						$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING, true);
				} else {
					$status = !empty($this->capture_params['status_text']) ? $this->capture_params['status_text'] : !empty($this->capture_params['status_desc']) ?  $this->capture_params['status_desc'] : $this->capture_params['status_message'];
					$trans_details = $existing_message. PHP_EOL . $test_order. ' ' . ' Novalnet Transaktions-ID '.$this->capture_params['shop_tid'] . ' - ' .$status;
				}				
				$myorder->client->message  = $trans_details;	
				$myorder->nn_tid_status = $this->capture_params['tid_status'];					
				$myorder->db_updateobject();				
				$myorder->client->store(true, $myorder->client->client_id);					
				self::display_message("Novalnet callback received. $trans_details");
			} else { 
				self::display_message('Transaction mapping failed');
			}			            
        }
        }
        return $myorder;
	}
    
    /**
     * Get Novalnet bank details
     * 
     * @return string
     */
    function get_invoice_comments() {
		$comments.= CC_SITE_NNTRANSINFO . PHP_EOL;
		$comments.= CC_SITE_NNTRANSVALIDUNTIL . date('d.m.Y', strtotime($this->capture_params['due_date'])) . PHP_EOL;
		$comments.= CC_SITE_NNTRANSACCHOLDER . $this->capture_params['invoice_account_holder'] . PHP_EOL;
		$comments.= CC_SITE_NNBANKIBAN . $this->capture_params['invoice_iban'] . PHP_EOL;
		$comments.= CC_SITE_NNBANKBIC . $this->capture_params['invoice_bic'] . PHP_EOL;
		$comments.= CC_SITE_NNBANKNAME . $this->capture_params['invoice_bankname'] . " " . $this->capture_params['invoice_bankplace'] . PHP_EOL;
		$comments.= CC_SITE_NNAMOUNT . ': ' . format::price($this->capture_params['amount'], $this->capture_params['currency']). PHP_EOL;			
		$comments.= CC_SITE_NNINVOICEREFDESCMORE . PHP_EOL;
		$comments.= CC_SITE_NNINVOICEREFTEXTSINGLE . '1: ' . 'BNR-' . $this->capture_params['product'] . '-' . $myorder->order_id . PHP_EOL;
		$comments.= CC_SITE_NNINVOICEREFTEXTSINGLE . '2: ' . 'TID ' . $this->capture_params['tid'] . PHP_EOL;
		return $comments;		
	}

    /**
     * Update callback comments 
     * @param $myorder
     * @param $callback_comments
     * 
     * @return void
     */
    function update_comments($myorder, $callback_comments) {
        $existing_message = $myorder->client->message;
        $myorder->client->message  = $existing_message . "  " . $callback_comments;
        $myorder->client->store(true, $myorder->client->client_id);
        $this->display_message($callback_comments);
    }
    
	/**
     * Display excuation message
     * @param $message
     * 
     * @return void
     */
    function display_message($message) {
		echo $message; exit;
    }
    
    /**
     * Send notification to the configured email
     * @param $message
     * 
     * @return void
     */ 
    function send_notify_mail($message) {
        global $novalnet_globalparam, $myorder;
        $client = new client(null, $myorder->order_id);
        if($novalnet_globalparam[8] == '1' && $client->check_email($novalnet_globalparam[9])) {
            $email = new email();
            $subject ='Novalnet Callback Script Access Report';
            $email->to_email      = $novalnet_globalparam[9];
            $email->to_plain      = '';
            $email->from_email    = CC_SITE_MAILFROM;
            $email->from_plain    = CC_SITE_MAILNAME;
            $email->subject       = $subject;
            $email->content_plain = $message ;
            $email->content_html  = $message;
            $email->store();
        }
    }
}
?>

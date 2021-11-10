// Novalnet code STARTS

case 'novalnetprepayment':
case 'novalnetinvoice':
case 'novalnetcashpayment':
case 'novalnetsepa':
case 'novalnetcc':
	if(in_array($myorder->payment_param, array('novalnetprepayment','novalnetinvoice','novalnetsepa','novalnetcashpayment')) || ($myorder->payment_param == 'novalnetcc' && $pm->parameter[5] != '1' && $pm->parameter[6] != '1')) {
		if(preg_match('/\d{17}/', $myorder->client->message)) {
			if(!empty($_SESSION['nn_completed']) && $_SESSION['nn_completed'] == $myorder->order_id)
				redirect(CC_URL_ACCOUNT . '?m=order&o=' . $myorder->order_id);
			$_SESSION['nn_completed'] = $myorder->order_id;
		}

		if(!sizeof($myorder->items))
			redirect(CC_URL_BASKET);

		$payment = new payment();
		$global_config = get_global_config($payment);
		if (!preg_match('/^[0-9]+$/', trim($global_config[1])) || !$global_config[2] || !preg_match('/^[0-9]+$/', trim($global_config[3])) || !preg_match('/^[0-9]+$/', trim($global_config[4])) || !$global_config[5])
			redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));

		if(get('result') != 'success') {
			$config_details = get_merchant_details($global_config);
			$config_details['test_mode'] = ($pm->parameter[1] == 1) ? 1 : 0;
			$payment_name = $myorder->payment_param;
			$authorize_amount = '';
			if (trim($pm->parameter[3]) == 1) {
				$authorize_amount = trim($pm->parameter[4]) != '' ? trim($pm->parameter[4]) : 0 ;
			}
			$payment_details = get_payment_details($myorder, $payment_name, $authorize_amount);
			$customer_details = get_customer_details($myorder);
			$novalnet_parameters = array_merge($config_details, $payment_details, $customer_details);
			$novalnet_parameters['pid'] = $pm->uid;

			if($payment_name == 'novalnetsepa') {
				$novalnet_parameters['account_holder']  = $_SESSION['novalnet']['nnsepa_account_holder'];
				$novalnet_parameters['iban']            = $_SESSION['novalnet']['nnsepa_iban'];
				if($pm->parameter[6] == 1 && isset($_SESSION['novalnet']['proceed_gurantee']) && $_SESSION['novalnet']['proceed_gurantee'] == 1) {
					$novalnet_parameters['birth_date'] = $_SESSION['novalnet']['nnsepa_dob'];
					$novalnet_parameters['key'] = get_payment_key($payment_name.'guarantee');
				}
				if (empty($novalnet_parameters['iban']))
					redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));
			}
			if($payment_name == 'novalnetcc') {
				$novalnet_parameters['unique_id']     = $_SESSION['novalnet']['nncc_uniqueid'];
				$novalnet_parameters['pan_hash']      = $_SESSION['novalnet']['nncc_pan_hash'];
				$novalnet_parameters['nn_it']         = 'iframe';
				if (empty($novalnet_parameters['pan_hash']))
					redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));
			}
			$myorder->client->store(true, $myorder->client->client_id);

			if( ($myorder->payment_param == 'novalnetinvoice')) {
				if(trim($pm->parameter[5]) != '' &&  preg_match('/^[0-9]+$/', $pm->parameter[5]))
					$novalnet_parameters['due_date'] = get_due_date($pm->parameter[5]);
				if($pm->parameter[6] == 1 && isset($_SESSION['novalnet']['proceed_gurantee']) && $_SESSION['novalnet']['proceed_gurantee'] == 1) {
					$novalnet_parameters['key'] = get_payment_key($payment_name.'guarantee');
					$novalnet_parameters['birth_date']      = $_SESSION['novalnet']['nninvoice_dob'];
					unset($novalnet_parameters['invoice_type']);
				}
				if ($pm->parameter[6] == 1 && $pm->parameter[8] != 1 && !isset($_SESSION['novalnet']['proceed_gurantee']))
					redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));
			}
			if( ($myorder->payment_param == 'novalnetcashpayment') && trim($pm->parameter[3]) != '' &&  preg_match('/^[0-9]+$/', $pm->parameter[3])) {
				$novalnet_parameters['cp_due_date'] = get_due_date($pm->parameter[3]);
			}
			if( ($myorder->payment_param == 'novalnetsepa') && trim($pm->parameter[5]) != '' && preg_match('/^[0-9]+$/', $pm->parameter[5])) {
				$sepa_due_date = ($pm->parameter[5] > 7) ?  date('Y-m-d', strtotime('+' . max(0, $pm->parameter[5]) . ' days')) : date('Y-m-d', strtotime('+' . max(0, 7) . ' days')) ;
				$novalnet_parameters['sepa_due_date'] = $sepa_due_date ;
			}

			if(in_array($payment_name, array('novalnetinvoice', 'novalnetprepayment'))) {
				$novalnet_parameters['invoice_ref'] = 'BNR-' . $novalnet_parameters['product'] . '-' . $myorder->order_id;
			}
			
			$novalnet_parameters = array_map("trim", $novalnet_parameters);
			$data = send_request($novalnet_parameters, get_payment_url($payment_name));
			parse_str($data, $response);
			
			if($response['status'] == 100) {
				$myorder->result = 'success';

				// To display transaction detail in success page
				$template->show('__novalnet_transactiondetails');
				$template->show('__novalnet_testorder');
				$payment_title = $myorder->pm->caption;
				$template->assign(array('_nnpayment_name' => $payment_title));
				$template->assign(array('__novalnet_tid' =>  $response['tid']));
				// For guarantee payments
				if(in_array($response['key'], array(40, 41))) {
					$template->show('__novalnet_guarantee_payment');
					$guarantee_texts = CC_SITE_NNGUARANTEE_TEXT. PHP_EOL;
					if($response['tid_status'] == 75) // For pending status
						$guarantee_texts .= CC_SITE_NNGUARANTEE_INVOICE_PENDING_TEXT. PHP_EOL;
					$template->assign(array('__novalnet_guarantee_desc' =>  $guarantee_texts));
				}

				$bank_store_details = '';
				if($payment_name == 'novalnetprepayment' || ($payment_name == 'novalnetinvoice' && $response['tid_status'] != 75)) { // For Inovice & Prepayment
					$bank_store_details .= get_bank_details($novalnet_parameters, $response, $myorder);
					$bank_store_details .= get_payment_reference($response['tid'], $myorder->order_id, $global_config[4]);
					$template->show('__novalnet_invoicetrans');
					$template->assign(array('__novalnet_transdesc' =>  CC_SITE_NNTRANSINFO));
					$template->assign(array('__novalnet_transduedate' =>   date('d.m.Y', strtotime($response['due_date']))));
					$template->assign(array('__novalnet_transaccountholder' =>  $response['invoice_account_holder']));
					$template->assign(array('__novalnet_transiban' =>   $response['invoice_iban']));
					$template->assign(array('__novalnet_transbic' =>   $response['invoice_bic']));
					$template->assign(array('__novalnet_transbank' =>   utf8_decode($response['invoice_bankname']).' '.utf8_decode($response['invoice_bankplace'])));
					$template->assign(array('__novalnet_transamount' =>   format::price($myorder->get_totalprice(), $myorder->currency)));
					$nn_ref_text    = CC_SITE_NNINVOICEREFDESCMORE .PHP_EOL;
					$template->assign(array('__novalnet_invoicerefrence' =>   $nn_ref_text));
					$invoice_refrenceone = CC_SITE_NNINVOICEREFTEXTSINGLE .'1: BNR-'.trim($global_config[3]).'-'.$myorder->order_id.PHP_EOL;
					$template->show('__novalnet_invoicerefone');
					$template->assign(array('__novalnet_invoicerefone' =>   $invoice_refrenceone));
					$invoice_refrencetwo = CC_SITE_NNINVOICEREFTEXTSINGLE .'2: TID '.$response['tid'].PHP_EOL;
					$template->show('__novalnet_invoicereftwo');
					$template->assign(array('__novalnet_invoicereftwo' =>   $invoice_refrencetwo));
				} else if($payment_name == 'novalnetcashpayment') { // For Cashpayment
						$bank_store_details .= CC_SITE_NNCASHPAYMENTSLIPEXPIRYDATE.date('d.m.Y', strtotime($response['cashpayment_due_date'])).PHP_EOL;
						$bank_store_details .= PHP_EOL. CC_SITE_NNCASHPAYMENTNEARSTORE.PHP_EOL;
						$bank_store_details .= utf8_decode(get_cashpayment_store_details($response));
						$template->show('__novalnet_cashpaymentname');
						$template->assign(array('__novalnet_cashpayment_name' =>  $payment_name));
						$template->show('__novalnet_cashpayment_token');
						$template->assign(array('__novalnet_cp_checkout_token' =>  $response['cp_checkout_token']));
						$barzahlen_checkout_url = "https://cdn.barzahlen.de/js/v2/checkout-sandbox.js";
						if($response['test_mode'] == '0')
							$barzahlen_checkout_url = "https://cdn.barzahlen.de/js/v2/checkout.js";
						$template->show('__novalnet_cashpayment_checkout_url');
						$template->assign(array('__novalnet_cashpayment_checkout_url' =>  $barzahlen_checkout_url));
						$template->assign(array('__novalnet_cashpayment_checkout_button_name' =>  CC_SITE_NNCASHPAYMENTNEARCHECKOUTBUTTONNAME));
						$template->show('__novalnet_cashpaymenttrans');
						$template->show('__novalnet_cashpayment_transduedate');
						$template->assign(array('__novalnet_cashpayment_transduedate' =>   date('d.m.Y', strtotime($response['cashpayment_due_date']))));
						$template->show('__novalnet_cashpayment_nearstore');
						$template->assign(array('__novalnet_cashpayment_nearstore' =>  CC_SITE_NNCASHPAYMENTNEARSTORE));
						$nearest_store =  get_nearest_store($response, 'nearest_store');
						$i =0;
						foreach ($nearest_store as $key => $values) {
							$i++;
							if(!empty($nearest_store['nearest_store_title_'.$i])) {
								$template->show('__novalnet_cashpayment_store_desc');
								$cashpayment_title .= PHP_EOL . $nearest_store['nearest_store_title_'.$i].PHP_EOL;
								$cashpayment_title .= $nearest_store['nearest_store_street_'.$i].PHP_EOL;
								$cashpayment_title .= $nearest_store['nearest_store_city_'.$i].PHP_EOL;
								$cashpayment_title .= $nearest_store['nearest_store_zipcode_'.$i].PHP_EOL;								
								$cashpayment_title .= $nearest_store['nearest_store_country_'.$i].PHP_EOL.' | ';
								$template->assign(array('__novalnet_cashpayment_store_desc' =>   utf8_decode($cashpayment_title)));
							}
						}
				}
				$existing_message = $myorder->client->message;
				$test_mode = ((isset($response['test_mode']) && $response['test_mode'] == '1') ? CC_SITE_NNTESTORDER :  '' );
				$transDetails  = PHP_EOL . CC_SITE_NNTRANSID . $response['tid']. PHP_EOL . $test_mode . PHP_EOL;
				$myorder->client->message  = $existing_message . $transDetails . $guarantee_texts . $bank_store_details;
				// Storing in the client table
				$myorder->client->store(true, $myorder->client->client_id);
				$myorder->nn_tid_status = $response['tid_status'];
				// Update in the order table
				$myorder->db_updateobject();

				// Updating the order status based on payments and transaction status
				if(in_array($response['tid_status'], array(85,91,98,99)) || $payment_name == 'novalnetinvoice')
					$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGITEMS, true);
				else if(in_array($response['tid_status'], array(75,86,90)) || in_array($payment_name, array('novalnetprepayment','novalnetcashpayment')))
					$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_WAITINGPAYMENT, true);
				else
					$myorder->set_status(CC_RESSOURCE_ORDERSTATUSSHORT_PROCESSING, true);

				if ($pm->autocharge) {
					$myorder->send_status_email();
					if (CC_SITE_AUTOSENDCOUPONS) {
						$myorder->send_coupon_emails();
					}
				}
				$_SESSION['nn_completed'] = $myorder->order_id;
			} else {
				$myorder->result = 'error';
				$template->show('nnpayment_errormsg');
				$template->assign(array('__nnpayment_errormsg' => utf8_decode($response['status_desc'])));
				$existing_message = $myorder->client->message;
				$error_message = "\n" . get_status_message($response)."\n";
				$error_message .= CC_SITE_NNTRANSID . $response['tid'];
				$myorder->client->message  = $existing_message."\n".$error_message;
				$myorder->client->store(true, $myorder->client->client_id);
			}
			if(!isset($_SESSION))
				session_start();
			if(isset($_SESSION['novalnet']))
				unset($_SESSION['novalnet']);
		}
	} else { // For Credit Card 3D secure
		 if(preg_match('/\d{17}/', $myorder->client->message)) {
			if(!empty($_SESSION['nn_completed']) && $_SESSION['nn_completed'] == $myorder->order_id)
				redirect(CC_URL_ACCOUNT . '?m=order&o=' . $myorder->order_id);
			$_SESSION['nn_completed'] = $myorder->order_id;
		}

		if(!sizeof($myorder->items))
			redirect(CC_URL_BASKET);

		$payment = new payment();
		$global_config = get_global_config($payment);
		if (!preg_match('/^[0-9]+$/', trim($global_config[1])) || !$global_config[2] || !preg_match('/^[0-9]+$/', trim($global_config[3])) || !preg_match('/^[0-9]+$/', trim($global_config[4]))
		|| !$global_config[5])
				redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));

		if(is_get('nnmess'))
			$template->show('nnmess');

		if(get('result') != 'success') {
			$payment_name = $myorder->payment_param;
			$config_details = get_merchant_details($global_config);
			$config_details['test_mode'] = ($pm->parameter[1] == 1) ? 1 : 0;
			$authorize_amount = '';
			if (trim($pm->parameter[3]) == 1) {
				$authorize_amount = trim($pm->parameter[4]) != '' ? trim($pm->parameter[4]) : 0 ; 
			}
			$payment_details     = get_payment_details($myorder, $payment_name, $authorize_amount);
			$customer_details    = get_customer_details($myorder);
			$novalnet_parameters = array_merge($config_details, $payment_details, $customer_details);
			$novalnet_parameters['implementation'] = 'ENC';
			$novalnet_parameters['uniqid'] = get_uniqueid();
			$novalnet_parameters['nn_it']  = 'iframe';
			$novalnet_parameters['return_method'] = 'POST';
			$novalnet_parameters['error_return_method'] = 'POST';
			if($pm->parameter[5] == '1') {
				$novalnet_parameters['cc_3d']    = '1';
			}
			$novalnet_parameters = array_map("trim", $novalnet_parameters);
			encode($novalnet_parameters, $global_config[5], $payment_name);
			$novalnet_parameters['hash']      = generate_hash($novalnet_parameters, $global_config[5]);
			$novalnet_parameters['unique_id'] = $_SESSION['novalnet']['nncc_uniqueid'];
			$novalnet_parameters['pan_hash']  = $_SESSION['novalnet']['nncc_pan_hash'];
			$site_url = (CC_SITE_SSLURL != '') ? CC_SITE_SSLURL : CC_SITE_HTTPURL;
			$site_url = ($site_url !='')? $site_url : CC_SITE_LOCALURL;
			$url_nnerror = $site_url.CC_PAYMENT_NOVALNETGATEWAY;
			$url_nnerrorurl = $site_url.CC_FILENAME_COMPLETE.'?'.PARAMETER_ORDER.'='.$myorder->order_id.'&'.PARAMETER_KEY.'='.$myorder->generate_key().'&'.PARAMETER_ID.'='.$pm->uid;
			$novalnet_parameters['return_url']          = $url_nnerror;
			$novalnet_parameters['error_return_url']    = $url_nnerrorurl;

			if(empty($novalnet_parameters['pan_hash']))
				redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));
			
			$template->show('novalnetredirectpayment');
			$template->show('nnmess');
			$template->assign(array('nnmess' =>  CC_SITE_NNREDIRECTTEXT));
			if(!isset($_SESSION))
				session_start();
			$_SESSION['nn_uid'] = $pm->uid;
			if(isset($_SESSION['novalnet'])) {
				unset($_SESSION['novalnet']);
			}
			foreach ($novalnet_parameters as $name => $value) {
				$template->loop('novalnet_parameters', array(
					'name' => $name,
					'value' => $value,
				));
			}
			$template->assign(array(
				'_action' => 'https://payport.novalnet.de/pci_payport',
			));
		}
	}
break;

case 'novalnetinstant':
case 'novalnetideal':
case 'novalnetpaypal':
case 'novalneteps':
case 'novalnetgiropay':
case 'novalnetprzelewy24':
	if(preg_match('/\d{17}/', $myorder->client->message)) {
		if(!empty($_SESSION['nn_completed']) && $_SESSION['nn_completed'] == $myorder->order_id)
			redirect(CC_URL_ACCOUNT . '?m=order&o=' . $myorder->order_id);
		$_SESSION['nn_completed'] = $myorder->order_id;
	}

	if(!sizeof($myorder->items))
		redirect(CC_URL_BASKET);

	$payment = new payment();
	$global_config = get_global_config($payment);
	if (!preg_match('/^[0-9]+$/', trim($global_config[1])) || !$global_config[2] || !preg_match('/^[0-9]+$/', trim($global_config[3])) || !preg_match('/^[0-9]+$/', trim($global_config[4]))
	|| !$global_config[5])
		redirect(CC_URL_PAYMENT.'?'.PARAMETER_ERROR.('&nnmessage=bsi'));

	if(is_get('nnmess'))
		$template->show('nnmess');

	if(get('result') != 'success') {
		$payment_name = $myorder->payment_param;
		$config_details = get_merchant_details($global_config);
		$config_details['test_mode'] = ($pm->parameter[1] == 1) ? 1 : 0;
		$authorize_amount = '';
		if ($payment_name == 'novalnetpaypal' && trim($pm->parameter[3]) == 1) {
			$authorize_amount = trim($pm->parameter[4]) != '' ? trim($pm->parameter[4]) : 0 ; 
		}
		$payment_details = get_payment_details($myorder, $payment_name);
		$customer_details = get_customer_details($myorder);
		$novalnet_parameters = array_merge($config_details, $payment_details, $customer_details);
		$novalnet_parameters['implementation'] = 'ENC';
		$novalnet_parameters['uniqid'] = get_uniqueid();
		$novalnet_parameters['return_method'] = 'POST';
		$novalnet_parameters['error_return_method'] = 'POST';
		$novalnet_parameters = array_map("trim", $novalnet_parameters);
		encode($novalnet_parameters, $global_config[5], $payment_name);
		$novalnet_parameters['hash'] = generate_hash($novalnet_parameters, $global_config[5]);
		$site_url = (CC_SITE_SSLURL != '') ? CC_SITE_SSLURL : CC_SITE_HTTPURL;
		$site_url = ($site_url !='')? $site_url : CC_SITE_LOCALURL;
		$url_nnerror = $site_url.CC_PAYMENT_NOVALNETGATEWAY;
		$url_nnerrorurl = $site_url.CC_FILENAME_COMPLETE.'?'.PARAMETER_ORDER.'='.$myorder->order_id.'&'.PARAMETER_KEY.'='.$myorder->generate_key().'&'.PARAMETER_ID.'='.$pm->uid;
		$novalnet_parameters['return_url'] = $url_nnerror;
		$novalnet_parameters['error_return_url'] = $url_nnerrorurl;
		
		$template->show('novalnetredirectpayment');
		if(!isset($_SESSION))
			session_start();
		$_SESSION['nn_uid'] = $pm->uid;
		if(isset($_SESSION['novalnet'])) {
			unset($_SESSION['novalnet']);
		}
		foreach ($novalnet_parameters as $name => $value) {
			$template->loop('novalnet_parameters', array(
				'name' => $name,
				'value' => $value,
			));
		}
		$template->assign(array(
			'_action' => get_payment_url($payment_name),
		));
	}
break;

// Novalnet code ENDS

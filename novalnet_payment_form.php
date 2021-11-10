// Novalnet code STARTS

	$payment = new payment();
	$global_config = get_global_config($payment);

	// If basic parameter was not configured, for hiding payments
	if (!$global_config[1] || !$global_config[2] || !$global_config[3] || !$global_config[4] || !$global_config[5])
		$global_config = array('_missing_config_params' => 1);

	// For Credit Card
	if($pm->parameter[0] == 'novalnetcc') {
		$vendor    = $global_config[1];
		$product   = $global_config[3];
		$server_ip = (filter_var($_SERVER['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['SERVER_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['SERVER_ADDR'];
		$signature = base64_encode("vendor=$vendor&product=$product&server_ip=$server_ip");
		$lang = defined('CC_SHOP_NNLANG') ? CC_SHOP_NNLANG : strtoupper(substr($order->client->country, 0, 2));
		// Iframe path
		$iframe_path = 'https://secure.novalnet.de/cc?api='. $signature . '&ln=' .$lang ;
		$custom_css = '#iframeForm , #iframeForm input { font-family: "Open Sans", Verdana, Arial, sans-serif;font-size: 16px;color: #333333;height:unset;} #iframeForm .label-group{ width: 28%;padding: 9px 0; } #iframeForm .input-group{ width: 68%; }';
		$input_css = 'border: 1px solid #dddddd;background: #DFE3DE;padding: 8px 12px;display: block;border-radius: 0;box-sizing: border-box;float: left;';
		$credit_card = array(
			'_nncc_paymentlogo'      => ($global_config[6] == '1' ? 'images/novalnet/cc.png' : ''),
			'_nncc_customerinfo'    => $pm->parameter[2],
			'_nncc_testmode'         => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
			'_nncc_iframe_url'       => $iframe_path,
			'_nn_css_standard'       => $pm->parameter[7],
			'_nn_css_standard_input' => empty($pm->parameter[8]) ? $input_css : $pm->parameter[8],
			'_nn_css_text'           => empty($pm->parameter[9]) ? $custom_css : $pm->parameter[9],
			'_nn_cc_uid'               => $pm->uid,
		);
	} elseif($pm->parameter[0] == 'novalnetsepa') { // For Direct Debit SEPA with and without payment guarantee
		$nnCustomerDetails = (array)$order->client;
		$sepa = array(
			'_nnsepa_paymentlogo'  => ($global_config[6] == '1') ? 'images/novalnet/sepa.png' : '',
			'_nnsepa_customerinfo' => $pm->parameter[2],
			'_nnsepa_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
			'_nnCustomerName'      => $nnCustomerDetails['firstname'] . ' ' . $nnCustomerDetails['lastname'] ,
		);
		if($pm->parameter[6] == 1 && empty($order->client->company)) { // For Date of birth field
			$template->show('nnsepa_dob');
			for ($d = 1; $d <= 31; $d++)
				$template->loop('nnsepa_date', array(
					'value' => $d < 10 ? "0$d" : $d ,
				));
			for ($m = 1; $m <= 12; $m++)
				$template->loop('nnsepa_month', array(
					'value' => $m < 10 ? "0$m" : $m ,
				));
			for ($y = date('Y'); $y >= (date('Y')-120); $y--)
				$template->loop('nnsepa_year', array(
					'value' => $y,
				));
		}
	}
	elseif($pm->parameter[0] == 'novalnetconfig') {
		$config = array('_nnconfig_uid' => $pm->uid);
	}
	elseif($pm->parameter[0] == 'novalnetideal') { // For iDEAL
		$ideal = array(
			'_nnideal_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/ideal.png' : '',
			'_nnidealcustomeinfo'  => $pm->parameter[2],
			'_nnideal_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetinstant') { // For Sofort
		$instant_bank_transfer = array(
			'_nninstant_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/banktransfer.png' : '',
			'_nninstantcustomeinfo'  => $pm->parameter[2],
			'_nninstant_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetpaypal') { // For PayPal
		$paypal = array(
			'_nnpaypal_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/paypal.png' : '',
			'_nnpaypalcustomeinfo'  => $pm->parameter[2],
			'_nnpaypal_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalneteps') { // For EPS
		$eps = array(
			'_nneps_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/eps.png' : '',
			'_nnepscustomeinfo'  => $pm->parameter[2],
			'_nneps_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetgiropay') { // For Giropay
		$giropay = array(
			'_nngiropay_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/giropay.png' : '',
			'_nngiropaycustomeinfo'  => $pm->parameter[2],
			'_nngiropay_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetprzelewy24') { // For Przelewy24
		$przelewy24 = array(
			'_nnprzelewy24_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/przelewy24.png' : '',
			'_nnprzelewy24customeinfo'  => $pm->parameter[2],
			'_nnprzelewy24_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetinvoice') { // For Invoice with and without payment guarantee
		$invoice = array(
			'_nninvoice_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/invoice.png' : '',
			'_nninvoicecustomeinfo'  => $pm->parameter[2],
			'_nninvoice_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
		if($pm->parameter[6] == 1 && empty($order->client->company)) { // For Date of birth field
			$template->show('nninvoice_dob');
			for ($d = 1; $d <= 31; $d++)
				$template->loop('nninvoice_date', array(
					'value' => $d < 10 ? "0$d" : $d ,
				));
			for ($m = 1; $m <= 12; $m++)
				$template->loop('nninvoice_month', array(
					'value' => $m < 10 ? "0$m" : $m ,
				));
			for ($y = date('Y'); $y >= (date('Y')-120); $y--)
				$template->loop('nninvoice_year', array(
					'value' => $y,
				));
		}
	}
	elseif($pm->parameter[0] == 'novalnetcashpayment') { // For Cashpayment
		$cashpayment = array(
			'_nncashpayment_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/cashpayment.png' : '',
			'_nncashpaymentcustomeinfo'  => $pm->parameter[2],
			'_nncashpayment_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}
	elseif($pm->parameter[0] == 'novalnetprepayment') { // For Prepayment
		$prepayment = array(
			'_nnprepayment_paymentlogo' => ($global_config[6] == '1') ? 'images/novalnet/prepayment.png' : '',
			'_nnprepaymentcustomeinfo'  => $pm->parameter[2],
			'_nnprepayment_testmode'     => ($pm->parameter[1] == '1') ? CC_SITE_NNTESTORDER_MESSAGE : '',
		);
	}

// Novalnet code ENDS

// Novalnet code STARTS

if(isset($credit_card))
	$template->assign($credit_card);
if(isset($sepa))
	$template->assign($sepa);
if(isset($config))
	$template->assign($config);
if(isset($ideal))
	$template->assign($ideal);
if(isset($instant_bank_transfer))
	$template->assign($instant_bank_transfer);
if(isset($prepayment))
	$template->assign($prepayment);
if(isset($invoice))
	$template->assign($invoice);
if(isset($cashpayment))
	$template->assign($cashpayment);
if(isset($eps))
	$template->assign($eps);
if(isset($giropay))
	$template->assign($giropay);
if(isset($przelewy24))
	$template->assign($przelewy24);
if(isset($paypal))
	$template->assign($paypal);

if( is_get('nnmessage') ) {
	$template->show_cond($error, 'nnmessage');
	$template->assign_cond($error, array('novalnetmessage' => get('nnmessage')));
}

// Novalnet code ENDS

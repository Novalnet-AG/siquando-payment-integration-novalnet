<!-- Novalnet code STARTS -->
<input type="hidden" id="nnconfiguid" value="{_nnconfig_uid}">
<input type="hidden" id="missing_config_params" value="{_missing_config_params}">
<script type="text/javascript">
	$(document).ready(function(){
		var nn_configuid = $('#nnconfiguid').val();
		nn_configuid = 'method'+nn_configuid;
		$('#'+nn_configuid).closest("tr").css({"display": "none"});
		
		if($('#missing_config_params').val() == 1) {
			$('.novalpayments').css({"display": "none"});
			$('.sqschoise').each(function(){
				if($(this).attr('data-choise').match(/^novalnet/) == 'novalnet')  {
					$(this).closest("tr").css({"display": "none"});
				}
			});
		}
	});
</script>

<div class="sqrpara sqschoiseblock novalpayments novalnetcc">
	<div class="sqrform">
		<p id="novalnetcc_test" class="sqrformerr"> {_nncc_testmode} </p>
	</div>
	<div> {_nncc_customerinfo} </div>	
	<div style="width: 140px;"> <img src="{_nncc_paymentlogo}" alt="Novalnet AG"> </div>
	<div>
        <input type="hidden" id="nn_pan_hash" name="nn_pan_hash" value="">
        <input type="hidden" id="nn_cc_uniqueid" name="nn_cc_uniqueid" value="">
        <input type="hidden" id="nn_css_standard" name="nn_css_standard" value="{_nn_css_standard}">
        <input type="hidden" id="nn_css_standard_input" name="nn_css_standard_input" value="{_nn_css_standard_input}">
        <input type="hidden" id="nn_css_text" name="nn_css_text" value="{_nn_css_text}">
        <input name="" value="{_nn_cc_uid}" id="nn_cc_payment_name" type="hidden">
        
		<script type="text/javascript" language="javascript">
			
			var submit_flag = false;
			if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
				window.addEventListener("load", novalnet_cc_load);
			} else if (window.attachEvent) { // For IE 8 and earlier versions
				window.attachEvent("onload", novalnet_cc_load);
			}

			function novalnet_cc_load() {
				jQuery('#payment_form').submit(function (evt) {
					var selected_payment_radio_uid = jQuery("input[name='payment_uid']:checked").val();
					var selected_payment_uid = jQuery("#nn_cc_payment_name").val();
					if(selected_payment_radio_uid != selected_payment_uid) { return true; }
					if(!submit_flag) {
						evt.preventDefault();
						cchashcall();
					 } else if(submit_flag) {
						$('#payment_form').find('#submit').click();
					}
				});

				function cchashcall() {
					var iframe= jQuery('#nn_iframe')[0].contentWindow ? jQuery('#nn_iframe')[0].contentWindow : jQuery('#nn_iframe')[0].contentDocument.defaultView;
					iframe.postMessage(JSON.stringify({
						callBack: 'getHash'
					}), 'https://secure.novalnet.de'); // Call the postMessage event for getting sudo hash value
				}
			}
			
			if (window.addEventListener) {
				window.addEventListener('message', function(e) {
					assignHash(e);
				}, false);
			} else {
				window.attachEvent('onmessage', function(e) {
					assignHash(e);
				});
			}

			function assignHash(e) {
				if (e.origin === 'https://secure.novalnet.de') { // To check the message listener origin with the iframe host
					var data = (typeof e.data === 'string') ? eval('(' + e.data.replace(/(<([^>]+)>)/gi, "") + ')') : e.data; // Convert message string to object
					if (data['callBack'] == 'getHash') { // To check the eventListener message from iframe for hash
						if (data['error_message'] != undefined) {
							alert(jQuery('<textarea />').html(data['error_message']).text());
							return false;
						} else {
							jQuery('#nn_pan_hash').val(data['hash']);
							jQuery('#nn_cc_uniqueid').val(data['unique_id']);
							submit_flag = true;
							jQuery('#payment_form').submit();
						}
					} else if (data['callBack'] == 'getHeight') { // To check the eventListener message from iframe to get the iframe content height
						jQuery('#nn_iframe').attr('height', data['contentHeight']);// Set the content height to the iframe
					}
				}
			}
			
			function getIframeForm() {
				var styleObj = {
					labelStyle: (jQuery('#nn_css_standard').val()) ? jQuery('#nn_css_standard').val() : '',
					inputStyle: (jQuery('#nn_css_standard_input').val()) ? jQuery('#nn_css_standard_input').val() : '',
					styleText: (jQuery('#nn_css_text').val()) ? jQuery('#nn_css_text').val() : '',
				};				
				var textObj   = {
					card_holder: {
						labelText: '',
						inputText: '',
					},
					card_number: {
						labelText: '',
						inputText: '',
					},
					expiry_date: {
						labelText: '',
						inputText: '',
					},
					cvc: {
						labelText: '',
						inputText: '',
					},
					cvcHintText: '',
					errorText: '',
				};	
				var requestObj = {
					callBack: 'createElements',
					customText: textObj,
					customStyle: styleObj
				};
				var iframe= jQuery('#nn_iframe')[0].contentWindow ? jQuery('#nn_iframe')[0].contentWindow : jQuery('#nn_iframe')[0].contentDocument.defaultView;
				iframe.postMessage(JSON.stringify(requestObj), 'https://secure.novalnet.de');
				iframe.postMessage(JSON.stringify({callBack: 'getHeight'}), 'https://secure.novalnet.de');
			}
		</script>
		<iframe scrolling="off" id="nn_iframe" width="100%" src="{_nncc_iframe_url}" onload="getIframeForm();" frameBorder="0"></iframe>
	</div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetsepa">
	<div class="sqrform">
		<p id="novalnetsepa_test" class="sqrformerr">{_nnsepa_testmode}</p>
	</div>
	<div>{_nnsepa_customerinfo}</div>
	<div style="width: 140px;"><img src="{_nnsepa_paymentlogo}" alt="Novalnet AG"></div>
	<div class="sqrform">
		<div>
			<label for="novalnet_sepa_account_holder" class="sqrforml">{CC_SITE_NNSEPAACCOUNTHOLDER}&nbsp;*</label>
			<input class="sqrformr" type="text" id="novalnet_sepa_account_holder" name="novalnet_sepa_account_holder" value="{_nnCustomerName}" autocomplete="off">
		</div>		
		<div>
			<label for="novalnet_sepa_iban" class="sqrforml">{CC_SITE_NNSEPAIBAN}&nbsp;*</label>
			<input class="sqrformr" type="text" id="novalnet_sepa_iban" name="novalnet_sepa_iban" autocomplete="off">
		</div>
		<!--IF nnsepa_dob-->
		<div>
			<label for="novalnet_sepa_date" class="sqrforml">{CC_SITE_NNDOB}&nbsp;*</label>
			<div class="sqrformr">
				<select id="novalnet_sepa_date" name="novalnet_sepa_date" class="sqrform1" style="display: inline-block;width: 32%;margin-right: 10px;">
					<option value="">Date</option>
					<!--LOOP nnsepa_date-->
					<option value="{nnsepa_date.value}">{nnsepa_date.value}</option>
					<!--ENDLOOP-->
				</select>
				<select name="novalnet_sepa_month" id="novalnet_sepa_month" class="sqrform1" style="display: inline-block;width: 32%;margin-right: 10px;">
					<option value="">Month</option>
					<!--LOOP nnsepa_month-->
					<option value="{nnsepa_month.value}">{nnsepa_month.value}</option>
					<!--ENDLOOP-->
				</select>
				<select name="novalnet_sepa_year" id="novalnet_sepa_year" class="sqrform1" style="display: inline-block;width: 32%;">
					<option value="">Year</option>
					<!--LOOP nnsepa_year-->
					<option value="{nnsepa_year.value}">{nnsepa_year.value}</option>
					<!--ENDLOOP-->
				</select>
			</div>
		</div>		
		<!--ENDIF-->
	</div>
	<div id="sepa_info_header">{CC_SITE_NNSEPAMANDATECONFIRM}</div></br>
		<div class="sepa_info" style="display:none;">{CC_SITE_NNSEPA_INFO_TEXT1}</div></br>
		<div class="sepa_info" style="display:none;">{CC_SITE_NNSEPA_INFO_TEXT2}</div></br>
		<div class="sepa_info" style="display:none;">{CC_SITE_NNSEPA_INFO_TEXT3}</div>
	<div>			
		<script type="text/javascript" language="javascript">
			
			jQuery('#sepa_info_header').click(function() {
				jQuery('.sepa_info').toggle(500);
			});
			
			jQuery('#novalnet_sepa_account_holder').on('input', function() {
				var input_val = jQuery('#novalnet_sepa_account_holder').val().replace(/[0-9\/\\|\]\[|#,+()$@'~%"`~:;*?<>!^{}=_]/g,'');
				jQuery('#novalnet_sepa_account_holder').val(input_val);
			});
			
			jQuery('#novalnet_sepa_iban').keyup(function (event) {
			   this.value = this.value.toUpperCase();
			   var field = this.value;
			   var value = "";
			   for(var i = 0; i < field.length;i++){
				   if(i <= 1){
					   if(field.charAt(i).match(/^[A-Za-z]/)){
							value += field.charAt(i);
					   }
				   }
				   if(i > 1){
					   if(field.charAt(i).match(/^[0-9]/)){
							value += field.charAt(i);
					   }
				   }
			   }
			   field = this.value = value;
			});
			
		</script>
		<style>
			#sepa_info_header:hover {
				cursor: pointer;
				text-decoration: underline;
			}
		</style>
	</div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetinvoice">
	<div class="sqrform">
		<p id="novalnetinvoice_test" class="sqrformerr">{_nninvoice_testmode}</p>
	</div>
	<div>{_nninvoicecustomeinfo}</div>
	<div><img src="{_nninvoice_paymentlogo}" alt="Novalnet AG"></div>
	<div class="sqrform">
		<!--IF nninvoice_dob-->
		<div>
			<label for="novalnet_invoice_date" class="sqrforml">{CC_SITE_NNDOB}&nbsp;*</label>
			<div class="sqrformr">
				<select class="sqrform1" id="novalnet_invoice_date" name="novalnet_invoice_date" style="display: inline-block;width: 32%;margin-right: 10px;">
					<option value="" >Date</option>
					<!--LOOP nninvoice_date-->
					<option value="{nninvoice_date.value}">{nninvoice_date.value}</option>
					<!--ENDLOOP-->
				</select>
				<select class="sqrform1" name="novalnet_invoice_month" id="novalnet_invoice_month" style="display: inline-block;width: 32%;margin-right: 10px;">
					<option value="" >Month</option>
					<!--LOOP nninvoice_month-->
					<option value="{nninvoice_month.value}">{nninvoice_month.value}</option>
					<!--ENDLOOP-->
				</select>
				<select class="sqrform1" name="novalnet_invoice_year" id="novalnet_invoice_year" style="display: inline-block;width: 32%;">
					<option value="" >Year</option>
					<!--LOOP nninvoice_year-->
					<option value="{nninvoice_year.value}">{nninvoice_year.value}</option>
					<!--ENDLOOP-->
				</select>
			</div>
		</div>		
		<!--ENDIF-->
	</div>
</div>
<div class="sqrpara sqschoiseblock novalpayments novalnetcashpayment">
	<div class="sqrform">
		<p id="novalnetcashpayment_test" class="sqrformerr">{_nncashpayment_testmode}</p>
	</div>
	<div>{_nncashpaymentcustomeinfo}</div>	
	<div><img src="{_nncashpayment_paymentlogo}" alt="Novalnet AG"></div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetprepayment">
	<div class="sqrform">
		<p id="novalnetprepayment_test" class="sqrformerr">{_nnprepayment_testmode}</p>
	</div>
	<div>{_nnprepaymentcustomeinfo}</div>
	<div><img src="{_nnprepayment_paymentlogo}" alt="Novalnet AG"></div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetpaypal">
	<div class="sqrform">
		<p id="novalnetpaypal_test" class="sqrformerr">{_nnpaypal_testmode}</p>
	</div>
	<div>{_nnpaypalcustomeinfo}</div>
	<div><img src="{_nnpaypal_paymentlogo}" alt="Novalnet AG"></div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetideal">
	<div class="sqrform">
		<p id="novalnetideal_test" class="sqrformerr">{_nnideal_testmode}</p>
	</div>
	<div>{_nnidealcustomeinfo}</div>
	<div><img src="{_nnideal_paymentlogo}" alt="Novalnet AG"></div>	
</div>

<div class="sqrpara sqschoiseblock novalpayments novalneteps">	
	<div class="sqrform">
		<p id="novalneteps_test" class="sqrformerr">{_nneps_testmode}</p>
	</div>
	<div>{_nnepscustomeinfo}</div>
	<div><img src="{_nneps_paymentlogo}" alt="Novalnet AG"></div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetgiropay">
	<div class="sqrform">
		<p id="novalnetgiropay_test" class="sqrformerr">{_nngiropay_testmode}</p>
	</div>
	<div>{_nngiropaycustomeinfo}</div>
	<div><img src="{_nngiropay_paymentlogo}" alt="Novalnet AG"></div>
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetprzelewy24">
	<div class="sqrform">
		<p id="novalnetprzelewy24_test" class="sqrformerr">{_nnprzelewy24_testmode}</p>
	</div>
	<div>{_nnprzelewy24customeinfo}</div>
	<div><img src="{_nnprzelewy24_paymentlogo}" alt="Novalnet AG"></div>	
</div>

<div class="sqrpara sqschoiseblock novalpayments novalnetinstant">
	<div class="sqrform">
		<p id="novalnetinstant_test" class="sqrformerr">{_nninstant_testmode}</p>
	</div>
	<div>{_nninstantcustomeinfo}</div>
	<div><img src="{_nninstant_paymentlogo}" alt="Novalnet AG"></div>
</div>
<!-- Novalnet code ENDS -->

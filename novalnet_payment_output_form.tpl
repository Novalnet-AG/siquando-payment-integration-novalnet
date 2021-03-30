<!-- Novalnet code STARTS -->
<input type="hidden" id="nnconfiguid" value="{_nnconfig_uid}">
<input type="hidden" id="missing_config_params" value="{_missing_config_params}">
<script type="text/javascript">
	$(document).ready(function(){
		var nn_configuid = $('#nnconfiguid').val();
		nn_configuid = 'uid_'+nn_configuid;
		$('label[for='+nn_configuid+']').hide();
		$('#'+nn_configuid).closest("tr").css({"display": "none"});
		
		if($('#missing_config_params').val() == 1) {
			$('.novalpayments').css({"display": "none"});
			$('.sqschoise').each(function(){
				if($(this).attr('data-choise').match(/^novalnet/) == 'novalnet')  {
					$(this).closest("tr").css({"display": "none"});
					$('label[for='+$(this).attr('id')+']').hide();
				}
			});
		}
	});
</script>
<table style="display: none" id="novalnetcc" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetcc special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetcc_test">
			{_nncc_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nncc_customerinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nncc_paymentlogo}" alt=""></td>
	</tr>
	<tr>
        <td>
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
        </td>
    </tr>	
</table>

<table style="display: none" id="novalnetsepa" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetsepa special">

	<tr>
		<td style="color:red" colspan="2" id="novalnetsepa_test">
			{_nnsepa_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnsepa_customerinfo}</td>
	</tr>
	<tr>
		<td style="width: 140px;"><img src="{_nnsepa_paymentlogo}" alt=""></td>
	</tr>
	<tr>
		<input type="hidden" id="nn_sepa_uniqueid" value="{_nncc_randomno}">
		<input type="hidden" id="nn_lang_valid_account_details" value="{CC_SITE_NNSEPAACCOUNTERRORMSG}">
		<input type="hidden" id="nn_vendor" value="{_nnsepa_vendor}" />
		<input type="hidden" id="nn_auth_code" value="{_nnsepa_authcode}" />
	</tr>
	<tr>
		<td>{CC_SITE_NNSEPAACCOUNTHOLDER}<span style='color:red;'>*</span></td>
		<td><input class="tx" type="text" id="novalnet_sepa_account_holder" name="novalnet_sepa_account_holder" value="{_nnCustomerName}" autocomplete="off"></td>
	</tr>
	<tr>
		<td>{CC_SITE_NNSEPAIBAN}<span style='color:red;'>*</span></td>
		<td><input class="tx" type="text" id="novalnet_sepa_iban" name="novalnet_sepa_iban" autocomplete="off"></td>
	</tr>
	<!--IF nnsepa_dob-->
	<tr>
		<td>{CC_SITE_NNDOB}<span style='color:red;'>*</span></td>
		<td>
			<select id="novalnet_sepa_date" name="novalnet_sepa_date" class="tx" style="display: inline-block;width: 21%;margin-right: 10px;">
				<option value="">Date</option>
				<!--LOOP nnsepa_date-->
				<option value="{nnsepa_date.value}">{nnsepa_date.value}</option>
				<!--ENDLOOP-->
			</select>
			<select name="novalnet_sepa_month" id="novalnet_sepa_month" class="tx" style="display: inline-block;width: 21%;margin-right: 10px;">
				<option value="">Month</option>
				<!--LOOP nnsepa_month-->
				<option value="{nnsepa_month.value}">{nnsepa_month.value}</option>
				<!--ENDLOOP-->
			</select>
			<select name="novalnet_sepa_year" id="novalnet_sepa_year" class="tx" style="display: inline-block;width: 21%;">
				<option value="">Year</option>
				<!--LOOP nnsepa_year-->
				<option value="{nnsepa_year.value}">{nnsepa_year.value}</option>
				<!--ENDLOOP-->
			</select>
		</td>
	</tr>
	<!--ENDIF--> 
	<tr id="sepa_info_header"><td colspan="2">{CC_SITE_NNSEPAMANDATECONFIRM}</td></tr>
	<tr class="sepa_info" style="display: none;"><td colspan="2">{CC_SITE_NNSEPA_INFO_TEXT1}</td></tr>
	<tr class="sepa_info" style="display: none;"><td colspan="2">{CC_SITE_NNSEPA_INFO_TEXT2}</td></tr>
	<tr class="sepa_info" style="display: none;"><td colspan="2">{CC_SITE_NNSEPA_INFO_TEXT3}</td></tr>
	<tr>
		<td>
			<script type="text/javascript" language="javascript">
			
				jQuery('#sepa_info_header').click(function() {
					if(jQuery('.sepa_info').is(':visible'))
						jQuery('.sepa_info').css('display','none');
					else
						jQuery('.sepa_info').css('display','table-row');
				});
				
				jQuery('#novalnet_sepa_account_holder').keyup(function() {
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
		</td>
	</tr>
</table>

<table style="display: none" id="novalnetinvoice" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetinvoice special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetinvoice_test">
			{_nninvoice_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nninvoicecustomeinfo}</td>
	</<tr>
	<tr>
		<td><img src="{_nninvoice_paymentlogo}" alt=""></td>
	</tr>
	<!--IF nninvoice_dob-->
	<tr>
		<td>{CC_SITE_NNDOB}<span style='color:red;'>*</span></td>
		<td>
			<select id="novalnet_invoice_date" name="novalnet_invoice_date" class="tx" style="display: inline-block;width: 21%;margin-right: 10px;">
				<option value="">Date</option>
				<!--LOOP nninvoice_date-->
				<option value="{nninvoice_date.value}">{nninvoice_date.value}</option>
				<!--ENDLOOP-->
			</select>
			<select name="novalnet_invoice_month" id="novalnet_invoice_month" class="tx" style="display: inline-block;width: 21%;margin-right: 10px;">
				<option value="">Month</option>
				<!--LOOP nninvoice_month-->
				<option value="{nninvoice_month.value}">{nninvoice_month.value}</option>
				<!--ENDLOOP-->
			</select>
			<select name="novalnet_invoice_year" id="novalnet_invoice_year" class="tx" style="display: inline-block;width: 21%;">
				<option value="">Year</option>
				<!--LOOP nninvoice_year-->
				<option value="{nninvoice_year.value}">{nninvoice_year.value}</option>
				<!--ENDLOOP-->
			</select>
		</td>
	</tr>
	<!--ENDIF-->
</table>
<table style="display: none" id="novalnetcashpayment" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetcashpayment special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetcashpayment_test">
			{_nncashpayment_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nncashpaymentcustomeinfo}</td>
	</<tr>
	<tr>
		<td><img src="{_nncashpayment_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetmultibanco" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetmultibanco special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetmultibanco_test">
			{_nnmultibanco_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnmultibancocustomeinfo}</td>
	</<tr>
	<tr>
		<td><img src="{_nnmultibanco_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetprepayment" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetprepayment special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetprepayment_test">
			{_nnprepayment_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnprepaymentcustomeinfo}</td>
	</<tr>
	<tr>
		<td><img src="{_nnprepayment_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetpaypal" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetpaypal special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetpaypal_test">
			{_nnpaypal_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnpaypalcustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnpaypal_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetideal" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetideal special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetideal_test">
			{_nnideal_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnidealcustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnideal_paymentlogo}" alt=""></td>
	</tr>
</table>


<table style="display: none" id="novalneteps" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalneteps special">
	<tr>
		<td style="color:red" colspan="2" id="novalneteps_test">
			{_nneps_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnepscustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nneps_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetgiropay" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetgiropay special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetgiropay_test">
			{_nngiropay_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nngiropaycustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nngiropay_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetprzelewy24" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetprzelewy24 special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetprzelewy24_test">
			{_nnprzelewy24_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnprzelewy24customeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnprzelewy24_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetpostfinance" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetpostfinance special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetpostfinance_test">
			{_nnpostfinance_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnpostfinancecustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnpostfinance_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetfinancecard" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetfinancecard special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetfinancecard_test">
			{_nnpostfinancecard_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnpostfinancecardcustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnpostfinancecard_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetbancontact" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetbancontact special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetbancontact_test">
			{_nnbancontact_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nnbancontactcustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nnbancontact_paymentlogo}" alt=""></td>
	</tr>
</table>

<table style="display: none" id="novalnetinstant" cellpadding="0" cellspacing="5" class="formtab pluginwidth novalpayments novalnetinstant special">
	<tr>
		<td style="color:red" colspan="2" id="novalnetinstant_test">
			{_nninstant_testmode}
		</td>
	</tr>
	<tr>
		<td>{_nninstantcustomeinfo}</td>
	</tr>
	<tr>
		<td><img src="{_nninstant_paymentlogo}" ></td>
	</tr>
</table>
<!-- Novalnet code ENDS -->
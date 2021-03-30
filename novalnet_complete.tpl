<!-- Novalnet code STARTS -->

<!--IF novalnetredirectpayment-->
<form method="post" action="{_action}" id="novalnetpayment" name="novalnetpayment">
	<!--LOOP novalnet_parameters-->
		<input type="hidden" name="{novalnet_parameters.name}" value="{novalnet_parameters.value}">
	<!--ENDLOOP-->

	<!--IF nnmess-->
		<div class="sqrpara">{nnmess}</div>
	<!--ENDIF-->

	<!--IF _nnerrormsg-->
		<div class="sqrpara">{_nnerrormsg}</div>
		</form>
	<!--ELSE-->
		</form>
		<script> document.forms["novalnetpayment"].submit();</script>
	<!--ENDIF-->
<!--ENDIF-->

<!-- Novalnet code ENDS -->
{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{if $form.credit_card_number or $form.bank_account_number}
    <div id="payment_information">
        <fieldset class="billing_mode-group {if $paymentProcessor.payment_type & 2}direct_debit_info-group{else}credit_card_info-group{/if}">
            <legend>
               {if $paymentProcessor.payment_type & 2}
                    {ts}Direct Debit Information{/ts}
               {else}
<!-- Modified for vanco -->
	       	    {if $isVanco eq 1}
		    	{ts}Payment Information{/ts}
		    {else}
			{ts}Credit Card Information{/ts}
		    {/if}
                   
               {/if}
            </legend> 
            {if $paymentProcessor.billing_mode & 2 and !$hidePayPalExpress }
            <div class="crm-section no-label paypal_button_info-section">	
			    <div class="content description">
			        {ts}If you have a PayPal account, you can click the PayPal button to continue. Otherwise, fill in the credit card and billing information on this form and click <strong>Continue</strong> at the bottom of the page.{/ts}
				</div>
			</div>
			 <div class="crm-section no-label {$form.$expressButtonName.name}-section">	
			    <div class="content description">
			        {$form.$expressButtonName.html}
			        <div class="description">Save time. Checkout securely. Pay without sharing your financial information. </div>
				</div>
			</div>
            {/if} 

            {if $paymentProcessor.billing_mode & 1}
                <div class="crm-section billing_mode-section {if $paymentProcessor.payment_type & 2}direct_debit_info-section{else}credit_card_info-section{/if}">
                   {if $paymentProcessor.payment_type & 2}
                        <div class="crm-section {$form.account_holder.name}-section">	
							<div class="label">{$form.account_holder.label}</div>
                            <div class="content">{$form.account_holder.html}</div>
                            <div class="clear"></div> 
                        </div>
                        <div class="crm-section {$form.bank_account_number.name}-section">	
							<div class="label">{$form.bank_account_number.label}</div>
                            <div class="content">{$form.bank_account_number.html}</div>
                            <div class="clear"></div> 
                        </div>
                        <div class="crm-section {$form.bank_identification_number.name}-section">	
							<div class="label">{$form.bank_identification_number.label}</div>
                            <div class="content">{$form.bank_identification_number.html}</div>
                            <div class="clear"></div> 
                        </div>
                        <div class="crm-section {$form.bank_name.name}-section">	
							<div class="label">{$form.bank_name.label}</div>
                            <div class="content">{$form.bank_name.html}</div>
                            <div class="clear"></div> 
                        </div>
                   {else}
			<!-- Modified by vanco to add extra fields -->
			{if $isVanco eq 1}
				<div class="crm-section {$form.payment_method.name}-section" id="{$form.payment_method.name}-section">
				     <div class="label">{$form.payment_method.label}</div>
				     <div class="content">{$form.payment_method.html}</div>
				     <div class="clear"></div>
				</div>
				<div class="crm-section {$form.routing_number.name}-section" id="{$form.routing_number.name}-section">
				     <div class="label">{$form.routing_number.label}<span class = 'crm-marker'>*</span></div>
				     <div class="content">{$form.routing_number.html}</div>
				     <div class="clear"></div>
				</div>
				<div class="crm-section {$form.account_number.name}-section" id="{$form.account_number.name}-section">
				     <div class="label">{$form.account_number.label}<span class = 'crm-marker'>*</span></div>
				     <div class="content">{$form.account_number.html}</div>
				     <div class="clear"></div>
				</div>
				<div class="crm-section {$form.account_type.name}-section" id="{$form.account_type.name}-section">
				     <div class="label">{$form.account_type.label}</div>
				     <div class="content">{$form.account_type.html}</div>
				     <div class="clear"></div>
				</div>
			{/if}
                		<div class="crm-section {$form.credit_card_type.name}-section" id="{$form.credit_card_type.name}-section">	
			                <div class="label">{$form.credit_card_type.label}<span class = 'crm-marker'>*</span></div>
                			<div class="content">{$form.credit_card_type.html}</div>
                			<div class="clear"></div> 
                		</div>
                		<div class="crm-section {$form.credit_card_number.name}-section" id="{$form.credit_card_number.name}-section">	
				        <div class="label">{$form.credit_card_number.label}<span class = 'crm-marker'>*</span></div>
                			<div class="content">{$form.credit_card_number.html}
                				<div class="description">{ts}Enter numbers only, no spaces or dashes.{/ts}</div>
                			</div>
                			<div class="clear"></div> 
                		</div>
                		<div class="crm-section {$form.cvv2.name}-section" id="{$form.cvv2.name}-section">	
							<div class="label">{$form.cvv2.label}<span class = 'crm-marker'>*</span></div>
                			<div class="content">
                				{$form.cvv2.html}
                				<img src="{$config->resourceBase}i/mini_cvv2.gif" alt="{ts}Security Code Location on Credit Card{/ts}" style="vertical-align: text-bottom;" />
                				<div class="description">{ts}Usually the last 3-4 digits in the signature area on the back of the card.{/ts}</div>
                			</div>
                			<div class="clear"></div> 
                		</div>
                		<div class="crm-section {$form.credit_card_exp_date.name}-section" id="{$form.credit_card_exp_date.name}-section">	
							<div class="label">{$form.credit_card_exp_date.label}<span class = 'crm-marker'>*</span></div>
                			<div class="content">{$form.credit_card_exp_date.html}</div>
                			<div class="clear"></div> 
                		</div>
                    {/if}
                </div>
                </fieldset>

                <fieldset class="billing_name_address-group">
                	<legend>{ts}Billing Name and Address{/ts}</legend>
                    <div class="crm-section billing_name_address-section">
                        <div class="crm-section billingNameInfo-section">	
                        	<div class="content description">
                        	  {if $paymentProcessor.payment_type & 2}
                        	     {ts}Enter the name of the account holder, and the corresponding billing address.{/ts}
                        	  {else}
                        	     {ts}Enter the name as shown on your credit or debit card, and the billing address for this card.{/ts}
                        	  {/if}
                        	</div>
                        </div>
                        <div class="crm-section {$form.billing_first_name.name}-section">	
							<div class="label">{$form.billing_first_name.label}</div>
                            <div class="content">{$form.billing_first_name.html}</div>
                            <div class="clear"></div> 
                        </div>
                        <div class="crm-section {$form.billing_middle_name.name}-section">	
							<div class="label">{$form.billing_middle_name.label}</div>
                            <div class="content">{$form.billing_middle_name.html}</div>
                            <div class="clear"></div> 
                        </div>
                        <div class="crm-section {$form.billing_last_name.name}-section">	
							<div class="label">{$form.billing_last_name.label}</div>
                            <div class="content">{$form.billing_last_name.html}</div>
                            <div class="clear"></div> 
                        </div>
                        {assign var=n value=billing_street_address-$bltID}
                        <div class="crm-section {$form.$n.name}-section">	
							<div class="label">{$form.$n.label}</div>
                            <div class="content">{$form.$n.html}</div>
                            <div class="clear"></div> 
                        </div>
                        {assign var=n value=billing_city-$bltID}
                        <div class="crm-section {$form.$n.name}-section">	
							<div class="label">{$form.$n.label}</div>
                            <div class="content">{$form.$n.html}</div>
                            <div class="clear"></div> 
                        </div>
                        {assign var=n value=billing_country_id-$bltID}
                        <div class="crm-section {$form.$n.name}-section">	
							<div class="label">{$form.$n.label}</div>
                            <div class="content">{$form.$n.html|crmReplace:class:big}</div>
                            <div class="clear"></div> 
                        </div>
                        {assign var=n value=billing_state_province_id-$bltID}
                        <div class="crm-section {$form.$n.name}-section">	
							<div class="label">{$form.$n.label}</div>
                            <div class="content">{$form.$n.html|crmReplace:class:big}</div>
                            <div class="clear"></div> 
                        </div>
                        {assign var=n value=billing_postal_code-$bltID}
                        <div class="crm-section {$form.$n.name}-section">	
							<div class="label">{$form.$n.label}</div>
                            <div class="content">{$form.$n.html}</div>
                            <div class="clear"></div> 
                        </div>
                    </div>
                </fieldset>
            {else}
                </fieldset>
            {/if}
    </div>
{/if}

{literal}
	<script type="text/javascript">
		function showHidePaymentDetails(element)
		{
			value = element.options[element.selectedIndex].text;
			//if(value=='Check' )
			if(value=='ACH')
			{
				document.getElementById('credit_card_type-section').style.display='none';
				document.getElementById('credit_card_number-section').style.display='none';
				document.getElementById('cvv2-section').style.display='none';
				document.getElementById('credit_card_exp_date-section').style.display='none';

				document.getElementById('routing_number-section').style.display='';
				document.getElementById('account_number-section').style.display='';
				document.getElementById('account_type-section').style.display='';

			}else if(value=='Credit Card')
			{
				document.getElementById('credit_card_type-section').style.display='';
				document.getElementById('credit_card_number-section').style.display='';
				document.getElementById('cvv2-section').style.display='';
				document.getElementById('credit_card_exp_date-section').style.display='';

				document.getElementById('routing_number-section').style.display='none';
				document.getElementById('account_number-section').style.display='none';
				document.getElementById('account_type-section').style.display='none';
			}else
			{
				document.getElementById('credit_card_type-section').style.display='none';
				document.getElementById('credit_card_number-section').style.display='none';
				document.getElementById('cvv2-section').style.display='none';
				document.getElementById('credit_card_exp_date-section').style.display='none';
				document.getElementById('routing_number-section').style.display='none';
				document.getElementById('account_number-section').style.display='none';
				document.getElementById('account_type-section').style.display='none';
			}
		}
		showHidePaymentDetails(document.getElementById('payment_method'));		
		
		function copyAddress(){
            var homeArray = new Array( 'first_name', 'last_name', 'street_address-3', 'city-3', 'postal_code-3', 'country-3', 'state_province-3' );
            var billingArray = new Array( 'billing_first_name', 'billing_last_name', 'billing_street_address-5', 'billing_city-5', 'billing_postal_code-5', 'billing_country_id-5', 'billing_state_province_id-5' );

            cj.each( homeArray, function(key, value){

            cj('#'+billingArray[key]).val(cj('#'+value).val());    
           } );
        }
	</script>
{/literal}
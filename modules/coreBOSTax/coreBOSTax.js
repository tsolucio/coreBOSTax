/*************************************************************************************************
 * Copyright 2015 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS Customizations.
 * Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
 * file except in compliance with the License. You can redistribute it and/or modify it
 * under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
 * granted by the License. coreBOS distributed by JPL TSolucio S.L. is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
 * applicable law or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing
 * permissions and limitations under the License. You may obtain a copy of the License
 * at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
 *************************************************************************************************
 *  Module       : coreBOS Tax Javascript
 *  Version      : 1.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/

// we have to capture the whole function and replicate the functionality because it closes the window
var aopcbTaxAccountCapture = corebosaop_meld.around(window,'saa_fillinvalues',function() {
	var account_id = jQuery("#account_id").val();
	var account_name = jQuery("#account_name").val();
	if(typeof(window.opener.document.EditView.account_name) != 'undefined')
		window.opener.document.EditView.account_name.value = account_name;
	if(typeof(window.opener.document.EditView.account_id) != 'undefined')
		window.opener.document.EditView.account_id.value = account_id;
	if (jQuery('#saa_bill').is(':checked')) setReturnAddressBill();
	if (jQuery('#saa_ship').is(':checked')) setReturnAddressShip();
	window.opener.updateAllTaxes();
	window.close();
});

var aopcbTaxContactCapture = corebosaop_meld.around(window,'sca_fillinvalues',function() {
	var contact_id = jQuery("#contact_id").val();
	var contact_name = jQuery("#contact_name").val();
	if(typeof(window.opener.document.EditView.contact_name) != 'undefined')
		window.opener.document.EditView.contact_name.value = contact_name;
	if(typeof(window.opener.document.EditView.contact_id) != 'undefined')
		window.opener.document.EditView.contact_id.value = contact_id;
	if (jQuery('#sca_bill').is(':checked')) setReturnAddressBill();
	if (jQuery('#sca_ship').is(':checked')) setReturnAddressShip();
	window.opener.updateAllTaxes();
	window.close();
});

var aopcbTaxVendorCapture = corebosaop_meld.around(window,'sva_fillinvalues',function() {
	var vendor_id = jQuery("#vendor_id").val();
	var vendor_name = jQuery("#vendor_name").val();
	if(typeof(window.opener.document.EditView.vendor_name) != 'undefined')
		window.opener.document.EditView.vendor_name.value = vendor_name;
	if(typeof(window.opener.document.EditView.vendor_id) != 'undefined')
		window.opener.document.EditView.vendor_id.value = vendor_id;
	if (jQuery('#sva_bill').is(':checked')) setReturnAddressBill();
	if (jQuery('#sva_ship').is(':checked')) setReturnAddressShip();
	window.opener.updateAllTaxes();
	window.close();
});

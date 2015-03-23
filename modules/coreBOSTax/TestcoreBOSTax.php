<?php
/*************************************************************************************************
 * Copyright 2015 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS customizations.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
 *************************************************************************************************
 *  Module       : Advanced Tax Tester
 *  Version      : 1.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/

?>
<script type="text/javascript">
	function taxSearchTaxValue() {
		var acc = jQuery('#acc_to').val();
		var pdo = jQuery('#pdo_to').val();
		var avl = jQuery('#available').val();
		jQuery.ajax({
			url: "index.php?action=coreBOSTaxAjax&file=SearchTax&module=coreBOSTax&acc="+acc+"&pdo="+pdo+"&avl="+avl+"&returnvalidation=1",
			context: document.body
		}).done(function(response) {
			obj = JSON.parse(response);
			var out = '';
			jQuery.each(obj.validation, function(i, val) {
				out = out + val + '<br>';
			});
			out = out + 'Time spent: ' + obj.timespent + ' msec<br>';
			jQuery("#taxtestresults").html(out);
		});
	}
</script>
<style type="text/css">
.gvtestlabeltext {
	font-size: medium;
	font-weight: bold;
	padding-left:10px;
	padding-right:20px;
	padding-top: 12px;
}
#taxtestresults {
	width: 96%;
	margin: auto;
	font-size: medium;
}
</style>
<form name='EditView'>
<table width="98%" align="center" border="0" cellspacing="0" cellpadding="0" class="small">
<tbody><tr><td style="height:2px"></td></tr>
<tr>
	<td nowrap="" class="moduleName" style="padding-left:36px;padding-right:50px;height:32px;background: url(modules/coreBOSTax/coreBOSTax.png) left center no-repeat;"><?php echo getTranslatedString('coreBOSTax','coreBOSTax').'&nbsp;-&nbsp;'.getTranslatedString('Test','coreBOSTax');?></td>
</tr>
<tr><td style="height:2px"></td></tr>
</tbody></table>
<br />
<table width="560px" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class='gvtestlabeltext'><?php echo getTranslatedString('SINGLE_Accounts','Accounts');?></td>
	<td>
		<input id="acc_to" name="acc_to" value="" type="hidden">
		<input id="acc_to_type" name="acc_to_type" value="Accounts" type="hidden">
		<input id="acc_to_display" name="acc_to_display" readonly="" style="border:1px solid #bababa;" value="" type="text">&nbsp;
		<img src="themes/softed/images/select.gif" tabindex="" alt="Select" title="Select" language="javascript" 
			onclick="return vtlib_open_popup_window('','acc_to','coreBOSTax','');" style="cursor:hand;cursor:pointer" align="absmiddle">&nbsp;
		<input src="themes/images/clear_field.gif" alt="Clear" title="Clear" language="javascript" onclick="this.form.acc_to.value=''; this.form.acc_to_display.value=''; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">&nbsp;
</td>
</tr>
<tr>
	<td class='gvtestlabeltext'><?php echo getTranslatedString('SINGLE_Products','Products');?></td>
	<td>
		<input id="pdo_to" name="pdo_to" value="" type="hidden">
		<input id="pdo_to_type" name="pdo_to_type" value="Products" type="hidden">
		<input id="pdo_to_display" name="pdo_to_display" readonly="" style="border:1px solid #bababa;" value="" type="text">&nbsp;
		<img src="themes/softed/images/select.gif" tabindex="" alt="Select" title="Select" language="javascript" 
			onclick="return vtlib_open_popup_window('','pdo_to','coreBOSTax','');" style="cursor:hand;cursor:pointer" align="absmiddle">&nbsp;
		<input src="themes/images/clear_field.gif" alt="Clear" title="Clear" language="javascript" onclick="this.form.pdo_to.value=''; this.form.pdo_to_display.value=''; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">&nbsp;
	</td>
</tr>
<tr>
	<td class='gvtestlabeltext'><?php echo getTranslatedString('Available','coreBOSTax');?></td>
	<td><select name="available" id="available" style='width: 250px;'>
		<option value='empty'>empty</option>
		<option value='available'>available</option>
		<option value='available_associated'>available_associated</option>
		<option value='all'>all</option>
	</select></td>
</tr>
<tr><td style="height:6px"></td></tr>
<tr>
	<td colspan="2" align="center"><button onclick="javascript:taxSearchTaxValue();return false;"><?php echo getTranslatedString('Search Value','GlobalVariable');?></button></td>
</tr>
<tr><td style="height:6px"></td></tr>
</table>
</form>
<div name="taxtestresults" id="taxtestresults"></div>
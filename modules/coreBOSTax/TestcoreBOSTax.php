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
		var shp = jQuery('#shipping').prop('checked') ? '1' : '0';
		jQuery.ajax({
			url: "index.php?action=coreBOSTaxAjax&file=SearchTax&module=coreBOSTax&acc="+acc+"&pdo="+pdo+"&avl="+avl+"&ship="+shp+"&returnvalidation=1",
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
	margin-bottom: 10px;
	font-size: medium;
}
</style>

<div id="page-header-placeholder"></div>
<div id="page-header" class="slds-page-header slds-m-vertical_medium">
	<div class="slds-page-header__row">
		<div class="slds-page-header__col-title">
			<div class="slds-media">
				<div class="slds-media__figure">
					<a class="hdrLink" href="index.php?action=index&module=coreBOSTax">
						<span class="slds-icon_container slds-icon-standard-system-and-global-variable" title="<?php echo getTranslatedString('coreBOSTax', 'coreBOSTax'); ?>">
							<svg class="slds-icon slds-page-header__icon" id="page-header-icon" aria-hidden="true">
								<use xmlns:xlink="http://www.w3.org/1999/xlink"
									xlink:href="include/LD/assets/icons/standard-sprite/svg/symbols.svg#partner_fund_allocation" />
							</svg>
							<span class="slds-assistive-text"><?php echo getTranslatedString('coreBOSTax', 'coreBOSTax'); ?></span>
						</span>
					</a>
				</div>
				<div class="slds-media__body">
					<div class="slds-page-header__name">
						<div class="slds-page-header__name-title">
							<h1>
								<span class="slds-page-header__title slds-truncate" title="<?php echo getTranslatedString('coreBOSTax', 'coreBOSTax'); ?>">
									<a class="hdrLink" href="index.php?action=index&module=coreBOSTax"><?php echo getTranslatedString('coreBOSTax', 'coreBOSTax'); ?></a>
								</span>
							</h1>
							<p class="slds-page-header__row slds-page-header__name-meta">
							<?php echo getTranslatedString('coreBOSTax', 'coreBOSTax').'&nbsp;-&nbsp;'.getTranslatedString('Test', 'coreBOSTax');?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="slds-page-header__col-actions">
		</div>
		<div id="page-header-surplus">
		</div>
	</div>
</div>
<section role="dialog" tabindex="-1" class="slds-fade-in-open slds-modal_large slds-app-launcher">
<div id="view" class="slds-modal__container slds-p-around_none slds-card">
<form name='EditView'>
<div class="slds-form-element">
	<label class="slds-form-element__label gvtestlabeltext" for="vlist"><?php echo getTranslatedString('SINGLE_Accounts', 'Accounts');?></label>
	<div class="slds-form-element__control">
		<input id="acc_to" name="acc_to" value="" type="hidden">
		<input id="acc_to_type" name="acc_to_type" value="Accounts" type="hidden">
		<input id="acc_to_display" name="acc_to_display" readonly="" style="border:1px solid #bababa; width:40%" class="slds-input slds-m-left_large slds-page-header__meta-text" value="" type="text">&nbsp;
		<button class="slds-button slds-button_icon" title="<?php echo getTranslatedString('LBL_SELECT'); ?>" type="button"
			onclick='return vtlib_open_popup_window("", "acc_to", "coreBOSTax", "");'>
		<svg class="slds-button__icon" aria-hidden="true">
			<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#choice"></use>
		</svg>
		<span class="slds-assistive-text"><?php echo getTranslatedString('LBL_SELECT'); ?></span>
		</button>
		<button class="slds-button slds-button_icon" title="<?php echo getTranslatedString('LBL_CLEAR'); ?>" type="button"
			onclick="this.form.acc_to.value=''; this.form.acc_to_display.value='';">
		<svg class="slds-button__icon" aria-hidden="true">
			<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#clear"></use>
		</svg>
		<span class="slds-assistive-text"><?php echo getTranslatedString('LBL_CLEAR'); ?></span>
		</button>
	</div>
</div>
<div class="slds-form-element">
	<label class="slds-form-element__label gvtestlabeltext" for="ulist"><?php echo getTranslatedString('SINGLE_Products', 'Products');?></label>
	<div class="slds-form-element__control">
		<input id="pdo_to" name="pdo_to" value="" type="hidden">
		<input id="pdo_to_type" name="pdo_to_type" value="Products" type="hidden">
		<input id="pdo_to_display" name="pdo_to_display" readonly="" style="border:1px solid #bababa; width:40%" class="slds-input slds-m-left_large slds-page-header__meta-text" value="" type="text">&nbsp;
		<button class="slds-button slds-button_icon" title="<?php echo getTranslatedString('LBL_SELECT'); ?>" type="button"
			onclick='return vtlib_open_popup_window("", "pdo_to", "coreBOSTax", "");'>
		<svg class="slds-button__icon" aria-hidden="true">
			<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#choice"></use>
		</svg>
		<span class="slds-assistive-text"><?php echo getTranslatedString('LBL_SELECT'); ?></span>
		</button>
		<button class="slds-button slds-button_icon" title="<?php echo getTranslatedString('LBL_CLEAR'); ?>" type="button"
			onclick="this.form.pdo_to.value=''; this.form.pdo_to_display.value='';">
		<svg class="slds-button__icon" aria-hidden="true">
			<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#clear"></use>
		</svg>
		<span class="slds-assistive-text"><?php echo getTranslatedString('LBL_CLEAR'); ?></span>
		</button>
	</div>
</div>
<div class="slds-form-element">
	<label class="slds-form-element__label gvtestlabeltext" for="mlist"><?php echo getTranslatedString('Available', 'coreBOSTax');?></label>
	<div class="slds-form-element__control">
		<select name="available" id="available" class='slds-select slds-m-left_large slds-page-header__meta-text' style="width:40%;">
			<option value='empty'>empty</option>
			<option value='available'>available</option>
			<option value='available_associated'>available_associated</option>
			<option value='all'>all</option>
		</select>
	</div>
</div>
<div class="slds-form-element">
	<label class="slds-form-element__label gvtestlabeltext" for="mlist"><?php echo getTranslatedString('Shipping', 'coreBOSTax');?></label>
	<div class="slds-form-element__control">
	<span class="slds-checkbox slds-checkbox_standalone slds-m-left_large slds-page-header__meta-text">
		<input name="shipping" id="shipping" type="checkbox">
		<span class="slds-checkbox_faux"></span>
	</span>
	</div>
</div>
<div class="slds-form-element slds-m-around_large">
	<button class="slds-button slds-button_neutral" type="button" onclick="javascript:taxSearchTaxValue();">
		<svg class="slds-button__icon slds-button__icon_left" aria-hidden="true">
			<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#search"></use>
		</svg>
		<?php echo getTranslatedString('Search Value', 'GlobalVariable');?>
	</button>
</div>
</form>
<div name="taxtestresults" id="taxtestresults"></div>
</div>
</section>

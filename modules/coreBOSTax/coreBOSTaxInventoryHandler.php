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
 *  Module       : Inventory modules Tax Control
 *  Version      : 1.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/

require_once 'modules/coreBOSTax/coreBOSTax.php';

class coreBOSTaxInventoryHandler extends VTEventHandler {

	public function handleEvent($eventName, $entityData) {

		if ($eventName == 'vtiger.entity.afterdelete') {
			// we leave this one deliberatly with no functionality so recovering from recycle bin will work
		}

		if ($eventName == 'vtiger.entity.aftersave.first') {
			$moduleName = $entityData->getModuleName();
			if (in_array($moduleName, getInventoryModules())) {
				$focus = $entityData->focus;
				if ($moduleName == 'Invoice' && isset($focus->_recurring_mode) && $focus->_recurring_mode == 'recurringinvoice_from_so'
					&& isset($focus->_salesorderid) && $focus->_salesorderid != ''
				) {
					// We are getting called from the RecurringInvoice cron service!
					$this->createRecurringInvoiceFromSO($focus);
				} elseif (isset($_REQUEST)) {
					if (inventoryCanSaveProductLines($_REQUEST, $moduleName)) {
						$this->saveInventoryProductDetails($focus, $moduleName);
					}
				}
			}
		}
	}

	public function saveInventoryProductDetails($focus, $module) {
		global $adb;
		$id = $focus->id;
		$all_available_taxes = coreBOSTax::getAllTaxes('available', '', $focus->mode, $id);
		if ($focus->mode == 'edit') {
			$adb->pquery('delete from vtiger_corebostaxinventory where invid=?', array($id));
		}
		if ($module != 'PurchaseOrder') {
			if (GlobalVariable::getVariable('Application_B2B', '1')=='1') {
				$acvid = $focus->column_fields['account_id'];
			} else {
				$acvid = $focus->column_fields['contact_id'];
			}
		} else {
			$acvid = $focus->column_fields['vendor_id'];
		}

		$inssql = 'insert into vtiger_corebostaxinventory (taxname,invid,pdoid,taxp,shipping,cbtaxid,lineitemid,retention) values (?,?,?,?,?,?,?,?)';
		$lines = $adb->pquery('select * from vtiger_inventoryproductrel where id=?', array($id));
		if ($_REQUEST['taxtype'] == 'group') {
			for ($tax_count=0; $tax_count<count($all_available_taxes); $tax_count++) {
				$cbtaxid = $all_available_taxes[$tax_count]['taxid'];
				$tax_name = $all_available_taxes[$tax_count]['taxname'];
				$tax_val = $all_available_taxes[$tax_count]['percentage'];
				$tax_ret = $all_available_taxes[$tax_count]['retention'];
				$request_tax_name = str_replace(' ', '_', $tax_name).'_group_percentage';
				if (isset($_REQUEST[$request_tax_name])) {
					$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
				}
				$adb->pquery($inssql, array($tax_name,$id,0,$tax_val,'0',$cbtaxid,0,$tax_ret));
			}
		} else {
			$i = 1;
			while ($line = $adb->fetch_array($lines)) {
				$pdoid = $line['productid'];
				$lineitemid = $line['lineitem_id'];
				$taxes_for_product = coreBOSTax::getTaxDetailsForProduct($pdoid, $acvid, 'all', false);
				for ($tax_count=0; $tax_count<count($taxes_for_product); $tax_count++) {
					$cbtaxid = $taxes_for_product[$tax_count]['taxid'];
					$tax_name = $taxes_for_product[$tax_count]['taxname'];
					$tax_val = $taxes_for_product[$tax_count]['percentage'];
					$tax_ret = $taxes_for_product[$tax_count]['retention'];
					$request_tax_name = str_replace(' ', '_', $tax_name).'_percentage'.$i;
					if (isset($_REQUEST[$request_tax_name])) {
						$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
					}
					$adb->pquery($inssql, array($tax_name,$id,$pdoid,$tax_val,'0',$cbtaxid,$lineitemid,$tax_ret));
				}
				$i++;
			}
		}

		// to save the S&H tax details
		$sh_tax_details = coreBOSTax::getAllTaxes('all', 'sh');
		for ($i=0; $i<count($sh_tax_details); $i++) {
			$tax_name = $sh_tax_details[$i]['taxname'];
			$cbtaxid = $sh_tax_details[$i]['taxid'];
			$tax_val = $sh_tax_details[$i]['percentage'];
			$request_tax_name = str_replace(' ', '_', $tax_name).'_sh_percent';
			if (isset($_REQUEST[$request_tax_name])) {
				$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
			}
			$adb->pquery($inssql, array($tax_name,$id,0,$tax_val,'1',$cbtaxid,0));
		}
	}

	private function createRecurringInvoiceFromSO($focus) {
		global $adb;
		$inssql = 'insert into vtiger_corebostaxinventory (taxname,invid,pdoid,taxp,shipping,cbtaxid,lineitemid,retention) ';
		$inssql.= '(select taxname,?,pdoid,taxp,shipping,cbtaxid,lineitemid,retention FROM vtiger_corebostaxinventory where invid=?)';
		$adb->pquery($inssql, array($focus->id, $focus->_salesorderid));
	}
}
?>

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

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		if($eventName == 'vtiger.entity.beforesave') {
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'Quotes' || $moduleName == 'SalesOrder' || $moduleName == 'Invoice' || $moduleName == 'PurchaseOrder') {
				$focus = $entityData->focus;
				if($moduleName == 'Invoice' && isset($focus->_recurring_mode) && $focus->_recurring_mode == 'recurringinvoice_from_so' && isset($focus->_salesorderid) && $focus->_salesorderid!='') {
					// We are getting called from the RecurringInvoice cron service!
					$this->createRecurringInvoiceFromSO($focus);
				} else if(isset($_REQUEST)) {
					if(substr($_REQUEST['action'],-4) != 'Ajax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
						&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates')
					{
						$this->saveInventoryProductDetails($focus, $moduleName);
					}
				}
			}
		}
	}

	function saveInventoryProductDetails($focus, $module) {
		global $adb, $log;
		$id = $focus->id;
		if ($focus->mode == 'edit') {
			$adb->pquery('delete from vtiger_corebostaxinventory where invid=?',array($id));
		}
		if ($module != 'PurchaseOrder') {
			if (GlobalVariable::getVariable('B2B', '1')=='1') {
				$acvid = $focus->column_fields['account_id'];
			} else {
				$acvid = $focus->column_fields['contact_id'];
			}
		} else {
			$acvid = $focus->column_fields['vendor_id'];
		}

		$inssql = 'insert into vtiger_corebostaxinventory (taxname,invid,pdoid,taxp,shipping,cbtaxid,lineitemid) values (?,?,?,?,?,?,?)';
		$lines = $adb->pquery('select * from vtiger_inventoryproductrel where id=?', array($id));
		if ($_REQUEST['taxtype'] == 'group') {
			$all_available_taxes = coreBOSTax::getAllTaxes('available','','edit',$id);
			while ($line = $adb->fetch_array($lines)) {
				$pdoid = $line['productid'];
				$lineitemid = $line['lineitem_id'];
				for($tax_count=0;$tax_count<count($all_available_taxes);$tax_count++) {
					$cbtaxid = $all_available_taxes[$tax_count]['taxid'];
					$tax_name = $all_available_taxes[$tax_count]['taxname'];
					$tax_val = $all_available_taxes[$tax_count]['percentage'];
					$request_tax_name = $tax_name."_group_percentage";
					if(isset($_REQUEST[$request_tax_name]))
						$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
					$adb->pquery($inssql, array($tax_name,$id,$pdoid,$tax_val,'0',$cbtaxid,$lineitemid));
				}
			}
		} else {
			$i = 1;
			while ($line = $adb->fetch_array($lines)) {
				$pdoid = $line['productid'];
				$lineitemid = $line['lineitem_id'];
				$taxes_for_product = coreBOSTax::getTaxDetailsForProduct($pdoid,$acvid,'all',false);
				for($tax_count=0;$tax_count<count($taxes_for_product);$tax_count++) {
					$cbtaxid = $taxes_for_product[$tax_count]['taxid'];
					$tax_name = $taxes_for_product[$tax_count]['taxname'];
					$tax_val = $taxes_for_product[$tax_count]['percentage'];
					$request_tax_name = $tax_name."_percentage".$i;
					if(isset($_REQUEST[$request_tax_name]))
						$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
					$adb->pquery($inssql, array($tax_name,$id,$pdoid,$tax_val,'0',$cbtaxid,$lineitemid));
				}
				$i++;
			}
		}

		// to save the S&H tax details
		$sh_tax_details = coreBOSTax::getAllTaxes('all','sh');
		for($i=0;$i<count($sh_tax_details);$i++) {
			$tax_name = $sh_tax_details[$i]['taxname'];
			$cbtaxid = $sh_tax_details[$i]['taxid'];
			$tax_val = $sh_tax_details[$i]['percentage'];
			$request_tax_name = $tax_name.'_sh_percentage'.$i;
			if(isset($_REQUEST[$request_tax_name]))
				$tax_val =vtlib_purify($_REQUEST[$request_tax_name]);
			$adb->pquery($inssql, array($tax_name,$id,0,$tax_val,'1',$cbtaxid,0));
		}
	}

	function createRecurringInvoiceFromSO($focus) {
		global $adb, $log;
		$id = $focus->id;
		$inssql = 'insert into vtiger_corebostaxinventory (taxname,invid,pdoid,taxp,shipping,cbtaxid,lineitemid) ';
		$inssql.= "select taxname,$id,pdoid,taxp,shipping,cbtaxid,lineitemid where invid=".$focus->_salesorderid;
		$adb->query($inssql);
	}
}

?>

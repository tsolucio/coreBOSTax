<?php
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
 *  Module       : coreBOS Tax Events Handler
 *  Version      : 1.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/
include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/Create.php';

class coreBOSTaxEvents extends VTEventHandler {
	private $_moduleCache = array();

	/**
	 * @param $handlerType
	 * @param $entityData VTEntityData
	 */
	public function handleEvent($handlerType, $entityData) {
		global $adb, $current_user;
		switch ($handlerType) {
			case 'corebos.changestatus.tax':
				if ($entityData['tax_type'] == 'tax') {
					$adb->pquery('update vtiger_crmentity set deleted=? where crmid=?', array(($entityData['status'] == 'disabled' ? 1 : 0), $entityData['tax_id']));
				}
				break;
			case 'corebos.changelabel.tax':
				if ($entityData['tax_type'] == 'tax') {
					$res = $adb->pquery('SELECT deleted FROM vtiger_crmentity WHERE crmid=?', array($entityData['tax_id']));
					if ($res && $adb->num_rows($res)>0 && $res->fields['deleted']==0) {
						$tname = str_replace(' ', '_', $entityData['new_label']);
						vtws_revise(
							array(
								'id' => vtws_getEntityId('coreBOSTax').'x'.vtlib_purify($entityData['tax_id']),
								'taxname' => vtlib_purify($entityData['new_label']),
								'taxp' => vtlib_purify($_REQUEST[$tname]),
								'retention' => isset($_REQUEST[$tname.'retention']) ? 1 : 0,
								'tdefault' => isset($_REQUEST[$tname.'default']) ? 1 : 0,
								'qcreate' => isset($_REQUEST[$tname.'qcreate']) ? 1 : 0,
							),
							$current_user
						);
					}
				}
				break;
			case 'corebos.add.tax':
				if (isset($_REQUEST['sh_addTaxLabel'])) {
					$elem = array(
						'taxname' => vtlib_purify($_REQUEST['sh_addTaxLabel']),
						'corebostaxactive' => 1,
						'shipping' => 1,
						'taxp' => vtlib_purify($_REQUEST['sh_addTaxValue']),
						'retention' => 0,
						'tdefault' => 0,
						'qcreate' => 0,
						'assigned_user_id' => vtws_getEntityId('Users').'x'.$current_user->id,
					);
				} else {
					$elem = array(
						'taxname' => vtlib_purify($_REQUEST['addTaxLabel']),
						'corebostaxactive' => 1,
						'shipping' => 0,
						'taxp' => vtlib_purify($_REQUEST['addTaxValue']),
						'retention' => isset($_REQUEST['addTaxLabelretention']) ? 1 : 0,
						'tdefault' => isset($_REQUEST['addTaxLabeldefault']) ? 1 : 0,
						'qcreate' => isset($_REQUEST['addTaxLabelqcreate']) ? 1 : 0,
						'assigned_user_id' => vtws_getEntityId('Users').'x'.$current_user->id,
					);
				}
				vtws_create('coreBOSTax', $elem, $current_user);
				break;
		}
	}

	public function handleFilter($handlerType, $parameter) {
		global $currentModule;
		require_once 'modules/coreBOSTax/coreBOSTax.php';
		global $taxvalidationinfo;
		$taxvalidationinfo = array();
		switch ($handlerType) {
			case 'corebos.filter.TaxCalculation.getTaxDetailsForProduct':
				$pdosrvid = vtlib_purify($parameter[0]);
				$available = vtlib_purify($parameter[1]);
				$acvid = vtlib_purify($parameter[2]);
				$parameter[3] = coreBOSTax::getTaxDetailsForProduct($pdosrvid, $acvid, $available);
				break;
			case 'corebos.filter.TaxCalculation.getProductTaxPercentage':
				$taxname = vtlib_purify($parameter[0]);
				$pdosrvid = vtlib_purify($parameter[1]);
				$parameter[2] = coreBOSTax::getProductTaxPercentage($taxname, $pdosrvid);
				break;
			case 'corebos.filter.TaxCalculation.getAllTaxes':
				$available = vtlib_purify($parameter[0]);
				$sh = vtlib_purify($parameter[1]);
				$mode = vtlib_purify($parameter[2]);
				$crmid = vtlib_purify($parameter[3]);
				$parameter[4] = coreBOSTax::getAllTaxes($available, $sh, $mode, $crmid);
				break;
			case 'corebos.filter.TaxCalculation.getTaxPercentage':
				$taxname = vtlib_purify($parameter[0]);
				$parameter[1] = coreBOSTax::getTaxPercentage($taxname);
				break;
			case 'corebos.filter.TaxCalculation.getTaxId':
				$taxname = vtlib_purify($parameter[0]);
				$parameter[1] = coreBOSTax::getTaxId($taxname);
				break;
			case 'corebos.filter.TaxCalculation.getInventoryProductTaxValue':
				$id = vtlib_purify($parameter[0]);
				$productid = vtlib_purify($parameter[1]);
				$taxname = vtlib_purify($parameter[2]);
				$parameter[3] = coreBOSTax::getInventoryProductTaxValue($id, $productid, $taxname);
				break;
			case 'corebos.filter.TaxCalculation.getInventorySHTaxPercent':
				$id = vtlib_purify($parameter[0]);
				$taxname = vtlib_purify($parameter[1]);
				$parameter[2] = coreBOSTax::getInventorySHTaxPercent($id, $taxname);
				break;
		}
		return $parameter;
	}
}

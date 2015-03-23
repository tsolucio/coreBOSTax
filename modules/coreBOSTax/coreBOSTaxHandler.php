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
class coreBOSTaxEvents extends VTEventHandler {
	private $_moduleCache = array();

	/**
	 * @param $handlerType
	 * @param $entityData VTEntityData
	 */
	public function handleEvent($handlerType, $entityData) {
	}

	public function handleFilter($handlerType, $parameter) {
		global $currentModule;
		require_once 'modules/coreBOSTax/coreBOSTax.php';
		global $taxvalidationinfo;
		$taxvalidationinfo = array();
		switch($handlerType) {
			case 'corebos.filter.TaxCalculation.getTaxDetailsForProduct':
//FIXME	$acvid = vtlib_purify($_REQUEST['acc']);
				$pdosrvid = vtlib_purify($parameter[0]);
				$available = vtlib_purify($parameter[1]);
				$parameter[2] = coreBOSTax::getTaxDetailsForProduct($pdosrvid, $acvid, $available);
				break;
			case 'corebos.filter.TaxCalculation.getProductTaxPercentage':
//FIXME	$acvid = vtlib_purify($_REQUEST['acc']);
				$taxname = vtlib_purify($parameter[0]);
				$pdosrvid = vtlib_purify($parameter[1]);
				$parameter[2] = coreBOSTax::getProductTaxPercentage($taxname, $pdosrvid, $acvid);
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
		}
		return $parameter;
	}
}

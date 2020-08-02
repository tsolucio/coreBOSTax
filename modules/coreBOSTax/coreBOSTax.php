<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';

class coreBOSTax extends CRMEntity {
	public $db;

	public $table_name = 'vtiger_corebostax';
	public $table_index= 'corebostaxid';
	public $column_fields = array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	public $HasDirectImageField = false;
	public $moduleIcon = array('library' => 'standard', 'containerClass' => 'slds-icon_container slds-icon-standard-account', 'class' => 'slds-icon', 'icon'=>'partner_fund_allocation');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = array('vtiger_corebostaxcf', 'corebostaxid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = array('vtiger_crmentity', 'vtiger_corebostax', 'vtiger_corebostaxcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_corebostax'   => 'corebostaxid',
		'vtiger_corebostaxcf' => 'corebostaxid',
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'taxname'=> array('corebostax' => 'taxname'),
		'acvtaxtype'=> array('corebostax' => 'acvtaxtype'),
		'pdotaxtype'=> array('corebostax' => 'pdotaxtype'),
		'taxp'=> array('corebostax' => 'taxp'),
		'corebostaxactive'=> array('corebostax' => 'corebostaxactive')
	);
	public $list_fields_name = array(
		/* Format: Field Label => fieldname */
		'taxname'=> 'taxname',
		'acvtaxtype'=> 'acvtaxtype',
		'pdotaxtype'=> 'pdotaxtype',
		'taxp'=>  'taxp',
		'corebostaxactive'=> 'corebostaxactive'
	);

	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'taxname';

	// For Popup listview and UI type support
	public $search_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'taxname'=> array('corebostax' => 'taxname'),
		'acvtaxtype'=> array('corebostax' => 'acvtaxtype'),
		'pdotaxtype'=> array('corebostax' => 'pdotaxtype'),
		'taxp'=> array('corebostax' => 'taxp'),
		'corebostaxactive'=> array('corebostax' => 'corebostaxactive')
	);
	public $search_fields_name = array(
		/* Format: Field Label => fieldname */
		'corebostax Name'=> 'taxname'
	);

	// For Popup window record selection
	public $popup_fields = array('taxname');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	public $sortby_fields = array();

	// For Alphabetical search
	public $def_basicsearch_col = 'taxname';

	// Column value to use on detail view record text display
	public $def_detailview_recname = 'taxname';

	// Required Information for enabling Import feature
	public $required_fields = array('taxname'=>1);

	// Callback function list during Importing
	public $special_functions = array('set_import_assigned_user');

	public $default_order_by = 'taxname';
	public $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = array('createdtime', 'modifiedtime', 'taxname');

	public function save_module($module) {
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id, $module);
		}
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type) {
		if ($event_type == 'module.postinstall') {
			// Handle post installation actions
			$taxtype=Vtiger_Module::getInstance('cbTaxType');
			$module=Vtiger_Module::getInstance('coreBOSTax');
			if ($taxtype) {
				$taxtype->setRelatedList($module, $modulename, array('ADD'), 'get_dependents_list');
			}
			$this->setModuleSeqNumber('configure', $modulename, 'tax-', '0000001');
			require_once 'include/events/include.inc';
			include_once 'vtlib/Vtiger/Module.php';
			$module->addLink('HEADERSCRIPT_POPUP', 'coreBOSTaxjs', 'modules/coreBOSTax/coreBOSTax.js');
			global $adb;
			$em = new VTEventsManager($adb);
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxDetailsForProduct', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getProductTaxPercentage', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getAllTaxes', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxPercentage', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxId', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getInventoryProductTaxValue', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getInventorySHTaxPercent', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.changestatus.tax', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.changelabel.tax', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.add.tax', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
		} elseif ($event_type == 'module.disabled') {
			// Handle actions when this module is disabled.
		} elseif ($event_type == 'module.enabled') {
			// Handle actions when this module is enabled.
		} elseif ($event_type == 'module.preuninstall') {
			// Handle actions when this module is about to be deleted.
		} elseif ($event_type == 'module.preupdate') {
			// Handle actions before this module is updated.
		} elseif ($event_type == 'module.postupdate') {
			// Handle actions after this module is updated.
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// public function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/** Function used to get all the tax details which are associated to the given product
	 * @param int $productid - product/service id for which we want to get all the associated taxes
	 * @param int $acvid - account/contact/vendor id for which we want to get all the associated taxes
	 * @param string $available - available, available_associated or all. default is all
	 *    if available then the taxes which are available now will be returned,
	 *    if all then all taxes will be returned
	 *    if available_associated then all the associated taxes even if they are not available and all the available taxes will be retruned
	 * @return array $tax_details - tax details as a array with productid, taxid, taxname, percentage and deleted
	 */
	public static function getTaxDetailsForProduct($pdosrvid, $acvid, $available = 'all', $shipping = false) {
		global $adb, $taxvalidationinfo;
		if (!empty($acvid)) {
			$seacvid = getSalesEntityType($acvid);
			$acvttype = 0;
			switch ($seacvid) {
				case 'Accounts':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_account where accountid=?', array($acvid));
					if ($ttrs) {
						$acvttype = $adb->query_result($ttrs, 0, 0);
					}
					$taxvalidationinfo[] = 'Related Account found';
					break;
				case 'Contacts':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_contactdetails where contactid=?', array($acvid));
					if ($ttrs) {
						$acvttype = $adb->query_result($ttrs, 0, 0);
					}
					$taxvalidationinfo[] = 'Related Contact found';
					break;
				case 'Vendors':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_vendor where vendorid=?', array($acvid));
					if ($ttrs) {
						$acvttype = $adb->query_result($ttrs, 0, 0);
					}
					$taxvalidationinfo[] = 'Related Vendor found';
					break;
			}
			if (empty($acvttype)) {
				$taxvalidationinfo[] = 'Entity tax type not found.';
			} else {
				$taxvalidationinfo[] = 'Entity tax type found: <a href="index.php?module=cbTaxType&action=DetailView&record='.$acvttype.'">'.$acvttype.'</a>';
			}
		} else {
			$taxvalidationinfo[] = 'No related entity';
		}
		if (!empty($pdosrvid)) {
			$sepdosrvid = getSalesEntityType($pdosrvid);
			$psttype = 0;
			switch ($sepdosrvid) {
				case 'Products':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_products where productid=?', array($pdosrvid));
					if ($ttrs) {
						$psttype = $adb->query_result($ttrs, 0, 0);
					}
					$taxvalidationinfo[] = 'Related Products found';
					break;
				case 'Services':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_service where serviceid=?', array($pdosrvid));
					if ($ttrs) {
						$psttype = $adb->query_result($ttrs, 0, 0);
					}
					$taxvalidationinfo[] = 'Related Services found';
					break;
			}
			if (empty($psttype)) {
				$taxvalidationinfo[] = 'Product/Service tax type not found.';
			} else {
				$taxvalidationinfo[] = 'Product/Service tax type found: <a href="index.php?module=cbTaxType&action=DetailView&record='.$psttype.'">'.$psttype.'</a>';
			}
		} else {
			$taxvalidationinfo[] = 'No related product/service';
		}
		$sql = 'select corebostaxid as taxid, taxname, taxp as taxpercentage, deleted, retention, tdefault, qcreate
			from vtiger_corebostax
			inner join vtiger_crmentity on crmid=corebostaxid ';
		if (empty($acvttype)) {
			if (empty($psttype)) {
				$taxvalidationinfo[] = 'both empty > return all non-related taxes';
				$where = "where ((acvtaxtype is null or acvtaxtype = 0) and (pdotaxtype is null or pdotaxtype = 0)) ";
			} else {
				$taxvalidationinfo[] = 'all taxes of PdoSrv(TxTy) and empty(ACV(TxTy))';
				$where = "where ((acvtaxtype is null or acvtaxtype = 0) and (pdotaxtype = '$psttype')) ";
			}
		} else {
			if (empty($psttype)) {
				// all taxes of cta(TxTy) and empty(Pdo(TxTy))
				$taxvalidationinfo[] = 'all taxes of empty(PdoSrv(TxTy)) and ACV(TxTy)';
				$where = "where ((acvtaxtype = '$acvttype') and (pdotaxtype is null or pdotaxtype = 0)) ";
			} else {
				$taxvalidationinfo[] = 'all taxes of PdoSrv(TxTy) and ACV(TxTy)';
				$where = "where ((acvtaxtype = '$acvttype') and (pdotaxtype = '$psttype')) ";
			}
		}
		if ($available != 'all') {
			$where .= " and deleted=0 and corebostaxactive='1' ";
		}
		$where .= " and shipping='".($shipping ? '1' : '0')."' ";
		$taxvalidationinfo[] = 'looking for taxes '.$where;
		$taxrs = $adb->query($sql.$where);
		if ($adb->num_rows($taxrs)==0) {
			$taxvalidationinfo[] = 'no taxes found > we insist';
			if (!empty($acvttype) && !empty($psttype)) {
				$taxvalidationinfo[] = 'taxes of ACV(TxTy) and empty(PdoSrv(TxTy))';
				$where = "where ((acvtaxtype = '$acvttype') and (pdotaxtype is null or pdotaxtype = 0)) ";
				if ($available != 'all') {
					$where .= " and deleted=0 and corebostaxactive='1' ";
				}
				$where .= " and shipping='".($shipping ? '1' : '0')."' ";
				$taxvalidationinfo[] = 'looking for taxes '.$where;
				$taxrs = $adb->query($sql.$where);
				if ($adb->num_rows($taxrs)==0) {
					$taxvalidationinfo[] = 'taxes of empty(ACV(TxTy)) and PdoSrv(TxTy)';
					$where = "where ((acvtaxtype is null or acvtaxtype = 0) and (pdotaxtype = '$psttype')) ";
					if ($available != 'all') {
						$where .= " and deleted=0 and corebostaxactive='1' ";
					}
					$where .= " and shipping='".($shipping ? '1' : '0')."' ";
					$taxvalidationinfo[] = 'looking for taxes '.$where;
					$taxrs = $adb->query($sql.$where);
				}
			}
		}
		if ($adb->num_rows($taxrs)==0 && $available=='all') {
			$taxvalidationinfo[] = 'all non-related taxes';
			$where = "where ((acvtaxtype is null or acvtaxtype = 0) and (pdotaxtype is null or pdotaxtype = 0)) ";
			$where .= " and deleted=0 and corebostaxactive='1' and shipping='".($shipping ? '1' : '0')."' ";
			$taxvalidationinfo[] = 'looking for taxes '.$where;
			$taxrs = $adb->query($sql.$where);
		}
		$taxes = array();
		$i = 0;
		while ($tax = $adb->fetch_array($taxrs)) {
			$tax_details = array();
			$tax_details['productid'] = $pdosrvid;
			$tax_details['taxid'] = $tax['taxid'];
			$tname = html_entity_decode($tax['taxname'], ENT_QUOTES);
			$tax_details['taxname'] = $tname;
			$tax_details['taxlabel'] = $tname;
			$tax_details['percentage'] = $tax['taxpercentage'];
			$tax_details['deleted'] = $tax['deleted'];
			$tax_details['retention'] = $tax['retention'];
			$tax_details['default'] = $tax['tdefault'];
			$tax_details['qcreate'] = $tax['qcreate'];
			$taxes[$i] = $tax_details;
			$taxfound = '<a href="index.php?module=coreBOSTax&action=DetailView&record='.$tax['taxid'].'">';
			$taxfound.= $tname.'</a> '.$tax['taxpercentage'];
			$taxvalidationinfo[] = "<b>Tax found: $taxfound</b>";
			$i++;
		}
		return $taxes;
	}

	/**	function to get the product's taxpercentage
	 *	@param string $taxname - tax name (VAT or Sales or Service)
	 *	@param int $productid  - product/service id for which we want the tax percentage
	 *	@param id  $default    - ignored
	 *	return int $taxpercentage - taxpercentage corresponding to the Tax type from vtiger_inventorytaxinfo vtiger_table
	 */
	public static function getProductTaxPercentage($taxname, $pdosrvid, $default = '') {
		$taxes = self::getTaxDetailsForProduct($pdosrvid, 0, 'available');
		$taxp = 0;
		foreach ($taxes as $tax) {
			if ($tax['taxname']==$taxname) {
				$taxp = $tax['percentage'];
				break;
			}
		}
		return $taxp;
	}

	/**	Function used to get the list of Tax types as a array
	 *	@param string $available - available or empty where as default is all,
	 * 		if available then the taxes which are available now will be returned
	 * 		otherwise all taxes will be returned
	 *	@param string $sh - sh or empty, if sh passed then the shipping and handling related taxes will be returned
	 *	@param string $mode - edit or empty, if mode is edit, then it will return taxes including disabled.
	 *	@param string $crmid - crmid or empty, getting crmid to get tax values..
	 *	return array $taxtypes - return all the tax types as a array
	 */
	public static function getAllTaxes($available = 'all', $sh = '', $mode = '', $crmid = '') {
		global $adb, $taxvalidationinfo;
		if ($mode == 'edit' && $crmid != '') {
			if ($sh != '' && $sh == 'sh') {
				$ship = '1';
			} else {
				$ship = '0';
			}
			$res = $adb->pquery(
				'select * from vtiger_corebostaxinventory left join vtiger_crmentity on crmid = cbtaxid where invid=? and shipping=?',
				array($crmid,$ship)
			);
			$taxes = array();
			$i = 0;
			while ($tax=$adb->fetch_array($res)) {
				$tax_details = array();
				$tax_details['productid'] = $tax['pdoid'];
				$tax_details['taxid'] = $tax['cbtaxid'];
				$tname = html_entity_decode($tax['taxname'], ENT_QUOTES);
				$tax_details['taxname'] = $tname;
				$tax_details['taxlabel'] = $tname;
				$tax_details['percentage'] = $tax['taxp'];
				$tax_details['retention'] = $tax['retention'];
				$tax_details['deleted'] = (empty($tax['deleted']) || $tax['deleted']=='1') ? '1' : '0';
				$taxes[$i] = $tax_details;
				$taxfound = '<a href="index.php?module=coreBOSTax&action=DetailView&record='.$tax['cbtaxid'].'">';
				$taxfound.= $tname.'</a> '.$tax['taxp'];
				$taxvalidationinfo[] = "<b>getAllTaxes found: $taxfound</b>";
				$i++;
			}
		} else {
			$acvid = 0;
			if (!empty($crmid)) { // we get the related ACV
				$acvid = coreBOSTax::getParentACV($crmid, GlobalVariable::getVariable('Application_B2B', '1'));
			} else {
				$acvid = 0;
				if (!empty($_REQUEST['return_module'])) {
					$_REQUEST['invmod'] = $_REQUEST['return_module'];
				}
				if (isset($_REQUEST['invmod'])) {
					switch ($_REQUEST['invmod']) {
						case 'Vendors':
							if (isset($_REQUEST['return_id'])) {
								$acvid = $_REQUEST['return_id'];
							} elseif (isset($_REQUEST['vndid'])) {
								$acvid = $_REQUEST['vndid'];
							}
							break;
						case 'Accounts':
							if (isset($_REQUEST['return_id'])) {
								$acvid = $_REQUEST['return_id'];
							} elseif (isset($_REQUEST['accid'])) {
								$acvid = $_REQUEST['accid'];
							}
							break;
						case 'Contacts':
							if (isset($_REQUEST['return_id'])) {
								$acvid = $_REQUEST['return_id'];
							} elseif (isset($_REQUEST['ctoid'])) {
								$acvid = $_REQUEST['ctoid'];
							}
							break;
						case 'PurchaseOrder':
							if (isset($_REQUEST['vndid'])) {
								$acvid = $_REQUEST['vndid'];
							}
							break;
						default: // Quotes, SalesOrder and Invoice
							if (GlobalVariable::getVariable('Application_B2B', '1')=='1') {
								if (isset($_REQUEST['accid'])) {
									$acvid = $_REQUEST['accid'];
								}
							} else {
								if (isset($_REQUEST['ctoid'])) {
									$acvid = $_REQUEST['ctoid'];
								}
							}
							break;
					}
				}
			}
			$taxes = self::getTaxDetailsForProduct(0, $acvid, $available, (empty($sh) ? false : true));
		}
		return $taxes;
	}

	/**	function to get the taxpercentage
	 *	@param string $taxname    - tax name (VAT or Sales or Service)
	 *	return int $taxpercentage - taxpercentage corresponding to the Tax type
	 */
	public static function getTaxPercentage($taxname) {
		$taxes = self::getTaxDetailsForProduct(0, 0);
		$taxp = 0;
		foreach ($taxes as $tax) {
			if ($tax['taxname']==$taxname) {
				$taxp = $tax['percentage'];
				break;
			}
		}
		return $taxp;
	}

	/**	function to get the taxid
	 *	@param string $taxname - tax name (VAT or Sales or Service)
	 *	return int   $taxid    - taxid corresponding to the Tax type
	 */
	public static function getTaxId($taxname) {
		$taxes = self::getTaxDetailsForProduct(0, 0);
		$taxid = 0;
		foreach ($taxes as $tax) {
			if ($tax['taxname']==$taxname) {
				$taxid = $tax['taxid'];
				break;
			}
		}
		return $taxid;
	}

	/**	function used to get the taxvalue which is associated with a product for PO/SO/Quotes or Invoice
	 *	@param int $id - id of PO/SO/Quotes or Invoice
	 *	@param int $productid - product id
	 *	@param string $taxname - taxname to which we want the value
	 *	@return float $taxvalue - tax value
	 */
	public static function getInventoryProductTaxValue($id, $productid, $taxname) {
		global $adb;
		$res = $adb->pquery(
			"select taxp from vtiger_corebostaxinventory where invid=? and pdoid=? and taxname=? and shipping='0'",
			array($id, $productid,$taxname)
		);
		if ($res && $adb->num_rows($res)>0) {
			$taxvalue = $adb->query_result($res, 0, 'taxp');
		} else {
			$taxvalue = '0.00';
		}
		return $taxvalue;
	}

	/**	function used to get the shipping & handling tax percentage for the given inventory id and taxname
	 *	@param int $id - entity id which will be PO/SO/Quotes or Invoice id
	 *	@param string $taxname - shipping and handling taxname
	 *	@return float $taxpercentage - shipping and handling taxpercentage which is associated with the given entity
	 */
	public static function getInventorySHTaxPercent($id, $taxname) {
		global $adb;
		$res = $adb->pquery(
			"select taxp from vtiger_corebostaxinventory where invid=? and taxname=? and shipping='1'",
			array($id,$taxname)
		);
		if ($res && $adb->num_rows($res)>0) {
			$taxpercentage = $adb->query_result($res, 0, 'taxp');
		} else {
			$taxpercentage = '0.00';
		}
		return $taxpercentage;
	}

	public static function getParentACV($crmid, $b2b = '1') {
		global $adb, $log;
		$secrm = getSalesEntityType($crmid);
		switch ($secrm) {
			case 'Quotes':
				if ($b2b=='1') {
					$rspot = $adb->pquery('select accountid from vtiger_quotes where quoteid=?', array($crmid));
				} else {
					$rspot = $adb->pquery('select contactid from vtiger_quotes where quoteid=?', array($crmid));
				}
				break;
			case 'SalesOrder':
				if ($b2b=='1') {
					$rspot = $adb->pquery('select accountid from vtiger_salesorder where salesorderid=?', array($crmid));
				} else {
					$rspot = $adb->pquery('select contactid from vtiger_salesorder where salesorderid=?', array($crmid));
				}
				break;
			case 'Invoice':
				if ($b2b=='1') {
					$rspot = $adb->pquery('select accountid from vtiger_invoice where invoiceid=?', array($crmid));
				} else {
					$rspot = $adb->pquery('select contactid from vtiger_invoice where invoiceid=?', array($crmid));
				}
				break;
			case 'PurchaseOrder':
				$rspot = $adb->pquery('SELECT vendorid FROM vtiger_purchaseorder WHERE purchaseorderid=?', array($crmid));
				break;
			case 'Products':
			case 'Services':
				return 0;
			break;
			default:
				return $crmid;
			break;
		}
		$relid = $adb->query_result($rspot, 0, 0);
		return $relid;
	}

	public static function migrateExistingInventoryRecords() {
		global $adb, $current_user;
		$cbtaxrec = new coreBOSTax();
		$cbtaxrec->mode = '';
		$cbtaxrec->column_fields['assigned_user_id'] = $current_user->id;
		$cbtaxrec->column_fields['acvtaxtype'] = 0;
		$cbtaxrec->column_fields['pdotaxtype'] = 0;
		$_REQUEST['assigntype'] = 'U';
		$inssql = 'insert into vtiger_corebostaxinventory (taxname,invid,pdoid,taxp,shipping,cbtaxid,lineitemid,retention) values (?,?,?,?,?,?,?,?)';
		$rstax = $adb->query('select * from vtiger_inventorytaxinfo');
		$taxes = array();
		while ($tax=$adb->fetch_array($rstax)) {
			// create tax records
			$cbtaxrec->column_fields['taxname'] = $tax['taxlabel'];
			$cbtaxrec->column_fields['corebostaxactive'] = $tax['deleted'] == '1' ? '0' : '1';
			$cbtaxrec->column_fields['taxp'] = $tax['percentage'];
			$cbtaxrec->column_fields['shipping'] = '0';
			$cbtaxrec->column_fields['retention'] = $tax['retention'];
			$cbtaxrec->save('coreBOSTax');
			$taxes[$tax['taxname']] = array(
				'label'=>$tax['taxlabel'],
				'taxid'=>$cbtaxrec->id,
				'retention'=>$tax['retention'],
			);
		}
		$result = $adb->query(
			'SELECT vtiger_inventoryproductrel.*,coalesce(vtiger_salesorder.taxtype,vtiger_invoice.taxtype,vtiger_quotes.taxtype,vtiger_purchaseorder.taxtype) as taxtype
			FROM `vtiger_inventoryproductrel`
			left join vtiger_salesorder on salesorderid=id
			left join vtiger_invoice on invoiceid=id
			left join vtiger_quotes on vtiger_quotes.quoteid=id
			left join vtiger_purchaseorder on purchaseorderid=id
			order by id'
		);
		$curinvid = 0;
		while ($invline = $adb->fetch_array($result)) {
			if ($curinvid!=$invline['id']) {
				$firstline = true;
				$curinvid = $invline['id'];
			}
			if ($invline['taxtype']=='group') {
				if ($firstline) {
					foreach ($taxes as $taxn => $taxid) {
						$params = array();
						$params[] = $taxes[$taxn]['label'];
						$params[] = $invline['id'];
						$params[] = 0;
						$params[] = $invline[$taxn];
						$params[] = 0;
						$params[] = $taxes[$taxn]['taxid'];
						$params[] = $invline['lineitem_id'];
						$params[] = $taxes[$taxn]['retention'];
						$adb->pquery($inssql, $params);
					}
				}
				$firstline = false;
				continue;
			}
			if ($invline['taxtype']=='individual') {
				foreach ($taxes as $taxn => $taxid) {
					$params = array();
					$params[] = $taxes[$taxn]['label'];
					$params[] = $invline['id'];
					$params[] = $invline['productid'];
					$params[] = $invline[$taxn];
					$params[] = 0;
					$params[] = $taxes[$taxn]['taxid'];
					$params[] = $invline['lineitem_id'];
					$params[] = $taxes[$taxn]['retention'];
					$params[] = $taxes[$taxn]['tdefault'];
					$params[] = $taxes[$taxn]['qcreate'];
					$adb->pquery($inssql, $params);
				}
			}
		}
		// shipping
		$rstax = $adb->query('select * from vtiger_shippingtaxinfo');
		$taxes = array();
		while ($tax=$adb->fetch_array($rstax)) {
			// create tax records
			$cbtaxrec->column_fields['taxname'] = $tax['taxlabel'];
			$cbtaxrec->column_fields['corebostaxactive'] = $tax['deleted'] == '1' ? '0' : '1';
			$cbtaxrec->column_fields['taxp'] = $tax['percentage'];
			$cbtaxrec->column_fields['shipping'] = '1';
			$cbtaxrec->save('coreBOSTax');
			$taxes[$tax['taxname']] = array('label'=>$tax['taxlabel'],'taxid'=>$cbtaxrec->id);
		}
		$result = $adb->query('SELECT * FROM `vtiger_inventoryshippingrel` order by id');
		while ($invline = $adb->fetch_array($result)) {
			foreach ($taxes as $taxn => $taxdetail) {
				$params = array();
				$params[] = $taxdetail['label'];
				$params[] = $invline['id'];
				$params[] = 0;
				$params[] = $invline[$taxn];
				$params[] = 1;
				$params[] = $taxdetail['taxid'];
				$params[] = 0;
				$params[] = 0;
				$adb->pquery($inssql, $params);
			}
		}
	}
}
?>

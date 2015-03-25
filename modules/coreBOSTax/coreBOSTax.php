<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class coreBOSTax extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'vtiger_corebostax';
	var $table_index= 'corebostaxid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_corebostaxcf', 'corebostaxid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_corebostax', 'vtiger_corebostaxcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_corebostax'   => 'corebostaxid',
	    'vtiger_corebostaxcf' => 'corebostaxid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'taxname'=> Array('corebostax', 'taxname'),
		'acvtaxtype'=> Array('corebostax', 'acvtaxtype'),
		'pdotaxtype'=> Array('corebostax', 'pdotaxtype'),
		'taxp'=> Array('corebostax', 'taxp'),
		'corebostaxactive'=> Array('corebostax', 'corebostaxactive')
		
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'taxname'=> 'taxname',
		'acvtaxtype'=> 'acvtaxtype',
		'pdotaxtype'=> 'pdotaxtype',
		'taxp'=>  'taxp',
		'corebostaxactive'=> 'corebostaxactive'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'taxname';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		't'=> Array('corebostax', 'taxname'),
		'acvtaxtype'=> Array('corebostax', 'acvtaxtype'),
		'pdotaxtype'=> Array('corebostax', 'pdotaxtype'),
		'taxp'=> Array('corebostax', 'taxp'),
		'corebostaxactive'=> Array('corebostax', 'corebostaxactive')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'corebostax Name'=> 'taxname'
	);

	// For Popup window record selection
	var $popup_fields = Array('taxname');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'taxname';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'taxname';

	// Required Information for enabling Import feature
	var $required_fields = Array('taxname'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'taxname';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'taxname');
	
	function __construct() {
		global $log, $currentModule;
		$this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

	function getSortOrder() {
		global $currentModule;

		$sortorder = $this->default_sort_order;
		if($_REQUEST['sorder']) $sortorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else if($_SESSION[$currentModule.'_Sort_Order']) 
			$sortorder = $_SESSION[$currentModule.'_Sort_Order'];

		return $sortorder;
	}

	function getOrderBy() {
		global $currentModule;
		
		$use_default_order_by = '';		
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		
		$orderby = $use_default_order_by;
		if($_REQUEST['order_by']) $orderby = $this->db->sql_escape_string($_REQUEST['order_by']);
		else if($_SESSION[$currentModule.'_Order_By'])
			$orderby = $_SESSION[$currentModule.'_Order_By'];
		return $orderby;
	}

	function save_module($module) {
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord, $query='') {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $usewhere='') {
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";
		
		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';
		
		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0]; 
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';
		
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);
		
		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			
			$other =  CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			
			if(!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE vtiger_crmentity.deleted = 0 ".$usewhere;
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		global $current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN 
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role 
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid 
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid 
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					) 
					OR vtiger_crmentity.smownerid IN 
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per 
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					) 
					OR 
						(";
		
					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " vtiger_groups.groupid IN 
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid 
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user;
		$thismodule = $_REQUEST['module'];
		
		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");
		
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name 
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";
		
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		$rel_mods[$this->table_name] = 1;
		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			
			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			
			if($rel_mods[$other->table_name]) {
				$rel_mods[$other->table_name] = $rel_mods[$other->table_name] + 1;
				$alias = $other->table_name.$rel_mods[$other->table_name];
				$query_append = "as $alias";
			} else {
				$alias = $other->table_name;
				$query_append = '';
				$rel_mods[$other->table_name] = 1;	
			}
			
			$query .= " LEFT JOIN $other->table_name $query_append ON $alias.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Initialize this instance for importing.
	 */
	function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php
	 */
	function create_import_query($module) {
		global $current_user;
		$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_users_last_import.assigned_user_id='$current_user->id'
			AND vtiger_users_last_import.bean_type='$module'
			AND vtiger_users_last_import.deleted=0";
		return $query;
	}

	/**
	 * Delete the last imported records.
	 */
	function undo_import($module, $user_id) {
		global $adb;
		$count = 0;
		$query1 = "select bean_id from vtiger_users_last_import where assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$result1 = $adb->pquery($query1, array($user_id)) or die("Error getting last import for undo: ".mysql_error()); 
		while ( $row1 = $adb->fetchByAssoc($result1))
		{
			$query2 = "update vtiger_crmentity set deleted=1 where crmid=?";
			$result2 = $adb->pquery($query2, array($row1['bean_id'])) or die("Error undoing last import: ".mysql_error()); 
			$count++;			
		}
		return $count;
	}
	
	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	function set_import_assigned_user()
	{
		global $current_user, $adb;
		$record_user = $this->column_fields["assigned_user_id"];
		
		if($record_user != $current_user->id){
			$sqlresult = $adb->pquery("select id from vtiger_users where id = ? union select groupid as id from vtiger_groups where groupid = ?", array($record_user, $record_user));
			if($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields["assigned_user_id"] = $current_user->id;
			} else {			
				$row = $adb->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields["assigned_user_id"] = $row['id'];
				} else {
					$this->column_fields["assigned_user_id"] = $current_user->id;
				}
			}
		}
	}
	
	/** 
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, vtiger_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		
		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);
					
		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";	
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}	
		
		
		$query = $select_clause . $from_clause .
					" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";
					
		return $query;		
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
			$taxtype=Vtiger_Module::getInstance('cbTaxType');
			$module=Vtiger_Module::getInstance('coreBOSTax');
			if ($taxtype) $taxtype->setRelatedList($module, $modulename, Array('ADD'),'get_dependents_list');
			$this->setModuleSeqNumber('configure', $modulename, 'tax-', '0000001');
			require_once('include/events/include.inc');
			global $adb;
			$em = new VTEventsManager($adb);
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxDetailsForProduct', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getProductTaxPercentage', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getAllTaxes', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxPercentage', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getTaxId', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getInventoryProductTaxValue', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
			$em->registerHandler('corebos.filter.TaxCalculation.getInventorySHTaxPercent', 'modules/coreBOSTax/coreBOSTaxHandler.php', 'coreBOSTaxEvents');
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	/** 
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }
	
	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**	Function used to get all the tax details which are associated to the given product
	 *	@param int $productid - product/service id for which we want to get all the associated taxes
	 *	@param int $acvid - account/contact/vendor id for which we want to get all the associated taxes
	 *	@param string $available - available, available_associated or all. default is all
	 *    if available then the taxes which are available now will be returned,
	 *    if all then all taxes will be returned
	 *    if available_associated then all the associated taxes even if they are not available and all the available taxes will be retruned
	 *	@return array $tax_details - tax details as a array with productid, taxid, taxname, percentage and deleted
	 */
	public static function getTaxDetailsForProduct($pdosrvid, $acvid, $available='all',$shipping=false) {
		global $adb, $taxvalidationinfo;
		if (!empty($acvid)) {
			$seacvid = getSalesEntityType($acvid);
			$acvttype = 0;
			switch ($seacvid) {
				case 'Accounts':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_account where accountid=?', array($acvid));
					if ($ttrs) $acvttype = $adb->query_result($ttrs, 0, 0);
					$taxvalidationinfo[] = 'Related Account found';
					break;
				case 'Contacts':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_contactdetails where contactid=?', array($acvid));
					if ($ttrs) $acvttype = $adb->query_result($ttrs, 0, 0);
					$taxvalidationinfo[] = 'Related Contact found';
					break;
				case 'Vendors':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_vendor where vendorid=?', array($acvid));
					if ($ttrs) $acvttype = $adb->query_result($ttrs, 0, 0);
					$taxvalidationinfo[] = 'Related Vendor found';
					break;
			}
			if (empty($acvttype))
				$taxvalidationinfo[] = 'Entity tax type not found.';
			else {
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
					if ($ttrs) $psttype = $adb->query_result($ttrs, 0, 0);
					$taxvalidationinfo[] = 'Related Products found';
					break;
				case 'Services':
					$ttrs = $adb->pquery('select taxtypeid from vtiger_service where serviceid=?', array($pdosrvid));
					if ($ttrs) $psttype = $adb->query_result($ttrs, 0, 0);
					$taxvalidationinfo[] = 'Related Services found';
					break;
			}
			if (empty($psttype))
				$taxvalidationinfo[] = 'Product/Service tax type not found.';
			else {
				$taxvalidationinfo[] = 'Product/Service tax type found: <a href="index.php?module=cbTaxType&action=DetailView&record='.$psttype.'">'.$psttype.'</a>';
			}
		} else {
			$taxvalidationinfo[] = 'No related product/service';
		}
		$sql = 'select corebostaxid as taxid, taxname, taxp as taxpercentage, deleted
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
		if($available != 'all') {
			$where .= " and deleted=0 and corebostaxactive='1' ";
		}
		$where .= " and shipping='".($shipping ? '1' : '0')."' ";
		$taxvalidationinfo[] = 'looking for taxes '.$where;
		$taxrs = $adb->query($sql.$where);
		if ($adb->num_rows($taxrs)==0) {
			$taxvalidationinfo[] = 'no taxes found > we insist';
			if (!empty($acvttype) and !empty($psttype)) {
				$taxvalidationinfo[] = 'taxes of ACV(TxTy) and empty(PdoSrv(TxTy))';
				$where = "where ((acvtaxtype = '$acvttype') and (pdotaxtype is null or pdotaxtype = 0)) ";
				if($available != 'all') {
					$where .= " and deleted=0 and corebostaxactive='1' ";
				}
				$where .= " and shipping='".($shipping ? '1' : '0')."' ";
				$taxvalidationinfo[] = 'looking for taxes '.$where;
				$taxrs = $adb->query($sql.$where);
				if ($adb->num_rows($taxrs)==0) {
					$taxvalidationinfo[] = 'taxes of empty(ACV(TxTy)) and PdoSrv(TxTy)';
					$where = "where ((acvtaxtype is null or acvtaxtype = 0) and (pdotaxtype = '$psttype')) ";
					if($available != 'all') {
						$where .= " and deleted=0 and corebostaxactive='1' ";
					}
					$where .= " and shipping='".($shipping ? '1' : '0')."' ";
					$taxvalidationinfo[] = 'looking for taxes '.$where;
					$taxrs = $adb->query($sql.$where);
				}
			}
		}
		if ($adb->num_rows($taxrs)==0 and $available=='all') {
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
			$tax_details['taxname'] = $tax['taxname'];
			$tax_details['taxlabel'] = $tax['taxname'];
			$tax_details['percentage'] = $tax['taxpercentage'];
			$tax_details['deleted'] = $tax['deleted'];
			$taxes[$i] = $tax_details;
			$taxfound = '<a href="index.php?module=coreBOSTax&action=DetailView&record='.$tax['taxid'].'">';
			$taxfound.= $tax['taxname'].'</a> '.$tax['taxpercentage'];
			$taxvalidationinfo[] = "<b>Tax found: $taxfound</b>";
			$i++;
		}
		return $taxes;
	}

	/**	function to get the product's taxpercentage
	 *	@param string $taxname - tax name (VAT or Sales or Service)
	 *	@param int $productid  - product/service id for which we want the tax percentage
	 *	@param int $acvid      - account/contact/vendor id for which we want to get all the associated taxes
	 *	@param id  $default    - ignored
	 *	return int $taxpercentage - taxpercentage corresponding to the Tax type from vtiger_inventorytaxinfo vtiger_table
	 */
	public static function getProductTaxPercentage($taxname, $pdosrvid, $acvid, $default='') {
		$taxes = self::getTaxDetailsForProduct($pdosrvid, $acvid, 'available');
		$taxp = 0;
		foreach ($taxes as $key => $tax) {
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
	 *	@param string $id - crmid or empty, getting crmid to get tax values..
	 *	return array $taxtypes - return all the tax types as a array
	 */
	public static function getAllTaxes($available='all', $sh='', $mode='', $crmid='') {
		$taxes = self::getTaxDetailsForProduct(0, 0, $available, (empty($sh) ? false : true));
		return $taxes;
	}

	/**	function to get the taxpercentage
	 *	@param string $taxname    - tax name (VAT or Sales or Service)
	 *	return int $taxpercentage - taxpercentage corresponding to the Tax type
	 */
	public static function getTaxPercentage($taxname) {
		$taxes = self::getTaxDetailsForProduct(0, 0, $available);
		$taxp = 0;
		foreach ($taxes as $key => $tax) {
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
		$taxes = self::getTaxDetailsForProduct(0, 0, $available);
		$taxid = 0;
		foreach ($taxes as $key => $tax) {
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
		global $log, $adb;
		$res = $adb->pquery("select taxp from vtiger_corebostaxinventory where invid=? and pdoid=? and taxname=? and shipping='0'",
			array($id, $productid,$taxname));
		if ($res and $adb->num_rows($res)>0)
			$taxvalue = $adb->query_result($res,0,'taxp');
		else
			$taxvalue = '0.00';
		return $taxvalue;
	}
	
	/**	function used to get the shipping & handling tax percentage for the given inventory id and taxname
	 *	@param int $id - entity id which will be PO/SO/Quotes or Invoice id
	 *	@param string $taxname - shipping and handling taxname
	 *	@return float $taxpercentage - shipping and handling taxpercentage which is associated with the given entity
	 */
	public static function getInventorySHTaxPercent($id, $taxname) {
		global $log, $adb;
		$res = $adb->pquery("select taxp from vtiger_corebostaxinventory where invid=? and taxname=? and shipping='1'",
			array($id,$taxname));
		if ($res and $adb->num_rows($res)>0)
			$taxpercentage = $adb->query_result($res,0,'taxp');
		else
			$taxpercentage = '0.00';
		return $taxpercentage;
	}

}
?>

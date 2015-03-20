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

require_once 'modules/coreBOSTax/coreBOSTax.php';
$accid = vtlib_purify($_REQUEST['acc']);
$pdoid = vtlib_purify($_REQUEST['pdo']);
$available = vtlib_purify($_REQUEST['avl']);
$retval = vtlib_purify($_REQUEST['returnvalidation']);
global $taxvalidationinfo;
$taxvalidationinfo = array();
$startTime = microtime(true);
$taxes = coreBOSTax::getTaxDetailsForProduct($pdoid, $accid, $available);
$counter = (microtime(true) - $startTime);
$ret = array('taxes'=>$taxes);
if ($retval) {
	$ret['validation'] = $taxvalidationinfo;
	$ret['timespent'] = round($counter*1000, 1);
}
echo json_encode($ret);
?>
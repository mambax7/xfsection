<?php
// $Id: topten.php,v 1.4 2006/03/20 03:23:03 ohwada Exp $

// 2006-03-17 K.OHWADA
// $HTTP_POST_VARS -> $_POST
// $HTTP_GET_VARS  -> $_GET

// 2004/04/22 K.OHWADA
// go back lines 20 -> 10

// 2004/01/25 K.OHWADA
// bug fix: The article is displayed before publishing

// 2003/09/23 K.OHWADA
// easy to rename module and table
// bug fix
//   Top 10 in Popular isn't displayed
// display lines 10 -> 20

//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

include "header.php";
include_once XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->dirname().'/include/functions.php';
include_once XOOPS_ROOT_PATH.'/class/xoopstree.php';

// easy to rename module and table
//include XOOPS_ROOT_PATH.'/modules/wfsection/include/groupaccess.php';
//include XOOPS_ROOT_PATH."/modules/$wfsModule/include/groupaccess.php";
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname()."/include/groupaccess.php";

$myts =& MyTextSanitizer::getInstance(); // MyTextSanitizer object

// easy to rename module and table
//$mytree = new XoopsTree($xoopsDB->prefix('wfs_category'),"id","pid");
//$xoopsOption['template_main'] = 'wfsection_topten.html';
$mytree = new XoopsTree($xoopsDB->prefix($wfsTableCategory),"id","pid");
$xoopsOption['template_main'] = $wfsHtmlTopten;

include XOOPS_ROOT_PATH."/header.php";

//indexmainheader();

// bug fix
//$rate = intval($HTTP_GET_VARS['rate']);

//generates top 10 charts by rating and hits for each main category

// bug fix: Top 10 in Popular isn't displayed
//if(isset($rate)){
if ( isset($_GET['rate']) ){
	$sort = _WFS_RATING;
	$sortDB = 'rating';
}else{
	$sort = _WFS_HITS;
	$sortDB = 'counter';
}

$xoopsTpl->assign('lang_sortby' ,$sort);
$xoopsTpl->assign('lang_rank' , _WFS_RANK);
$xoopsTpl->assign('lang_title' , _WFS_TITLE);
$xoopsTpl->assign('lang_category' , _WFS_CATEGORY);
$xoopsTpl->assign('lang_hits' , _WFS_HITS);
$xoopsTpl->assign('lang_rating' , _WFS_RATING);
$xoopsTpl->assign('lang_vote' , _WFS_VOTE);
$arr=array();

// easy to rename module and table
//$result=$xoopsDB->query('SELECT id, title, groupid FROM '.$xoopsDB->prefix("wfs_category").' WHERE pid=0');
$result=$xoopsDB->query('SELECT id, title, groupid FROM '.$xoopsDB->prefix($wfsTableCategory).' WHERE pid=0');

$e = 0;
$rankings = array();
while(list($cid,$ctitle,$groupid)=$xoopsDB->fetchRow($result)){

if(checkAccess($groupid)) {
	$rankings[$e]['title'] = sprintf(_WFS_TOP10, $myts->htmlSpecialChars($ctitle));

// bug fix: The article is displayed before publishing
// easy to rename module and table
//	$query = "SELECT articleid, categoryid, title, counter, rating, votes, groupid FROM ".$xoopsDB->prefix("wfs_article")." WHERE (categoryid=$cid";
//	$query = "SELECT articleid, categoryid, title, counter, rating, votes, groupid FROM ".$xoopsDB->prefix($wfsTableArticle)." WHERE (categoryid=$cid";
	$query = "SELECT articleid, categoryid, title, counter, rating, votes, groupid FROM ".$xoopsDB->prefix($wfsTableArticle)." WHERE (published>0 AND published<=".time()." AND categoryid=$cid";

	//$query = "SELECT lid, cid, title, hits, rating, votes FROM ".$xoopsDB->prefix("mydownloads_downloads")." WHERE status>0 AND (cid=$cid";
// get all child cat ids for a given cat id
	$arr=$mytree->getAllChildId($cid);
	$size = count($arr);
	for($i=0;$i<$size;$i++){
		$query .= " or categoryid=".$arr[$i]."";
	}
	$query .= ") order by ".$sortDB." DESC";

// go back lines 20 -> 10
// display lines 10 -> 20
	$result2 = $xoopsDB->query($query,10,0);
//	$result2 = $xoopsDB->query($query,20,0);

	$rank = 1;
	
	while(list($darticleid, $dcategoryid, $dtitle, $counter, $rating, $votes, $dgroupid)=$xoopsDB->fetchRow($result2)){
	
	if(checkAccess($dgroupid)) {
	
		$catpath = $mytree->getPathFromId($dcategoryid, "title");
		$catpath= substr($catpath, 1);
		$catpath = str_replace("/"," <span class='fg2'>&raquo;&raquo;</span> ",$catpath);
		$dtitle = $myts->makeTboxData4Show($dtitle);
		$rankings[$e]['file'][] = array('id' => $darticleid, 'cid' => $dcategoryid, 'rank' => $rank, 'title' => $dtitle, 'category' => $catpath, 'hits' => $counter, 'rating' => number_format($rating, 2), 'votes' => $votes);
		$rank++;
		}
	}
	$e++;
	}		
}
$xoopsTpl->assign('rankings', $rankings);
include XOOPS_ROOT_PATH.'/footer.php';

include "footer.php";

?>

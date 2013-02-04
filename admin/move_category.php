<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.1 from 7th August 2009
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2009 Klemen Stirn. All Rights Reserved.
*  HESK is a trademark of Klemen Stirn.

*  The HESK may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Klemen Stirn from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America or
*  with the European Union.

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HESK copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.hesk.com/buy.php
*******************************************************************************/

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/database.inc.php');

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');
hesk_checkPermission('can_reply_tickets');

$trackingID = strtoupper(hesk_input($_POST['track'],"$hesklang[int_error]: $hesklang[no_trackID]."));
$category   = hesk_input($_POST['category']);
if (empty($category))
{
	$_SESSION['HESK_ERROR']   = true;
	$_SESSION['HESK_MESSAGE'] = $hesklang['sel_app_cat'];
	header('Location: admin_ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999));
	exit();
}

$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `category`='".hesk_dbEscape($category)."' WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1";
$result = hesk_dbQuery($sql);
if (hesk_dbAffectedRows() != 1)
{
	hesk_error("$hesklang[int_error]: $hesklang[trackID_not_found].");
}

/* Need to notify any admins? */
$admins=array();
$sql = "SELECT `email`,`isadmin`,`categories` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `notify`='1' AND `id`!=".hesk_dbEscape($_SESSION['id']);
$result = hesk_dbQuery($sql);
while ($myuser=hesk_dbFetchAssoc($result))
{
    /* Is this an administrator? */
    if ($myuser['isadmin']) {$admins[]=$myuser['email']; continue;}
    /* Not admin, is he allowed this category? */
    $cat=substr($myuser['categories'], 0, -1);
    $myuser['categories']=explode(',',$cat);
    if (in_array($category,$myuser['categories']))
    {
        $admins[]=$myuser['email']; continue;
    }
}
if (count($admins)>0)
{
	$trackingURL_admin=$hesk_settings['hesk_url'].'/admin/admin_ticket.php?track='.$trackingID;

	/* Get ticket info */
	$sql = "SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1";
	$result = hesk_dbQuery($sql);
	if (hesk_dbNumRows($result) != 1)
	{
		hesk_error($hesklang['ticket_not_found']);
	}
	$ticket = hesk_dbFetchAssoc($result);

	$msg = hesk_getEmailMessage('category_moved');
	$msg = str_replace('%%NAME%%',$ticket['name'],$msg);
	$msg = str_replace('%%SUBJECT%%',$ticket['subject'],$msg);
	$msg = str_replace('%%TRACK_ID%%',$trackingID,$msg);
	$msg = str_replace('%%TRACK_URL%%',$trackingURL_admin,$msg);
	$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
	$msg = str_replace('%%SITE_URL%%',$hesk_settings['site_url'],$msg);
	$msg = str_replace('%%MESSAGE%%',hesk_msgToPlain($ticket['message'],1),$msg);

	/* Send e-mail */
    $email=implode(',',$admins);
	$headers = "From: $hesk_settings[noreply_mail]\n";
	$headers.= "Reply-to: $hesk_settings[noreply_mail]\n";
	$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
	$headers.= "Content-type: text/plain; charset=".$hesklang['ENCODING'];
	@mail($email,$hesklang['ntmc'],$msg,$headers);
}

$_SESSION['HESK_NOTICE']  = $hesklang['moved'];
$_SESSION['HESK_MESSAGE'] = $hesklang['moved_to'];
header('Location: admin_ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999));
exit();
?>

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

if (isset($_SERVER['HTTP_REFERER']))
{
	$url = hesk_input($_SERVER['HTTP_REFERER']);
    $url = str_replace('&amp;','&',$url);
	if ($tmp = strstr($url,'show_tickets.php'))
    {
    	$referer = $tmp;
    }
	elseif ($tmp = strstr($url,'find_tickets.php'))
    {
    	$referer = $tmp;
    }
    elseif ($tmp = strstr($url,'admin_main.php'))
    {
    	$referer = $tmp;
    }
    else
    {
    	$referer = 'admin_main.php';
    }
}
else
{
	$referer = 'admin_main.php';
}

$ids = hesk_input($_POST['id']);
if (empty($ids) && !isset($_GET['delete_ticket']))
{
	$_SESSION['HESK_ERROR']   = true;
	$_SESSION['HESK_MESSAGE'] = $hesklang['no_selected'];
	header('Location: '.$referer);
	exit();
}

$i=0;

/* DELETE */
if ($_POST['a']=='delete')
{
    /* Check permissions for this feature */
	hesk_checkPermission('can_del_tickets');

    foreach ($_POST['id'] as $this_id)
    {
        $this_id = hesk_isNumber($this_id,$hesklang['id_not_valid']);
        $sql = 'SELECT `trackid`,`category` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE `id`='.hesk_dbEscape($this_id).' LIMIT 1';
        $result = hesk_dbQuery($sql);
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

        $sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'attachments` WHERE `ticket_id`=\''.hesk_dbEscape($ticket['trackid']).'\'';
        hesk_dbQuery($sql);
        $sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE `id`='.hesk_dbEscape($this_id).' LIMIT 1';
        hesk_dbQuery($sql);
        $sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'replies` WHERE `replyto`='.hesk_dbEscape($this_id);
        hesk_dbQuery($sql);
        $i++;
    }

	$_SESSION['HESK_NOTICE']  = $hesklang['tickets_deleted'];
	$_SESSION['HESK_MESSAGE'] = sprintf($hesklang['num_tickets_deleted'],$i);
}
elseif (isset($_GET['delete_ticket']))
{
    /* Check permissions for this feature */
	hesk_checkPermission('can_del_tickets');

    $trackingID = strtoupper(hesk_input($_GET['track']));

	/* Get ticket info */
	$sql = "SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1";
	$result = hesk_dbQuery($sql);
	if (hesk_dbNumRows($result) != 1)
	{
		hesk_error($hesklang['ticket_not_found']);
	}
	$ticket = hesk_dbFetchAssoc($result);

	/* Is this user allowed to delete tickets inside this category? */
	hesk_okCategory($ticket['category']);

	$this_id = $ticket['id'];
	$sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'attachments` WHERE `ticket_id`=\''.hesk_dbEscape($trackingID).'\'';
	hesk_dbQuery($sql);
	$sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE `id`='.hesk_dbEscape($this_id).' LIMIT 1';
	hesk_dbQuery($sql);
	$sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'replies` WHERE `replyto`='.hesk_dbEscape($this_id);
	hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['tickets_deleted'];
	$_SESSION['HESK_MESSAGE'] = sprintf($hesklang['num_tickets_deleted'],1);
}
else /* JUST CLOSE */
{
    /* Check permissions for this feature */
	hesk_checkPermission('can_view_tickets');
    hesk_checkPermission('can_reply_tickets');

	foreach ($_POST['id'] as $this_id)
	{
		$this_id = hesk_isNumber($this_id,$hesklang['id_not_valid']);

        $sql = 'SELECT `category` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE `id`='.hesk_dbEscape($this_id).' LIMIT 1';
        $result = hesk_dbQuery($sql);
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

		$sql = 'UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` SET `status`=\'3\' WHERE `id`='.hesk_dbEscape($this_id).' LIMIT 1';
		hesk_dbQuery($sql);
		$i++;
	}

	$_SESSION['HESK_NOTICE']  = $hesklang['tickets_closed'];
	$_SESSION['HESK_MESSAGE'] = sprintf($hesklang['num_tickets_closed'],$i);
}

header('Location: '.$referer);
exit();
?>

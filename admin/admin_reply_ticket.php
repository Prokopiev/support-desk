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
hesk_checkPermission('can_reply_tickets');

$message = hesk_input($_POST['message'],$hesklang['enter_message']);

/* Attach signature to the message? */
if (!empty($_POST['signature']))
{
    $message .= '<br /><br />'.addslashes($_SESSION['signature']).'<br />&nbsp;';
}
$message = hesk_makeURL($message);
$message = nl2br($message);
$orig_name = hesk_input($_POST['orig_name'],"$hesklang[int_error]: No orig_name");
$orig_email = hesk_validateEmail($_POST['orig_email'],"$hesklang[int_error]: No valid orig_email");
$orig_subject = hesk_input($_POST['orig_subject'],"$hesklang[int_error]: No orig_subject");
$replyto = hesk_isNumber($_POST['orig_id'],"$hesklang[int_error]: No or invalid orig_id");
$trackingID = hesk_input($_POST['orig_track'],"$hesklang[int_error]: No orig_track");
$trackingURL = $hesk_settings['hesk_url'].'/ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999);

/* Attachments */
if ($hesk_settings['attachments']['use'])
{
    require(HESK_PATH . 'inc/attachments.inc.php');
    $attachments = array();
    for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
    {
        $att = hesk_uploadFile($i);
        if (!empty($att))
        {
            $attachments[$i] = $att;
        }
    }
}
$myattachments='';

if ($hesk_settings['attachments']['use'] && !empty($attachments))
{
    foreach ($attachments as $myatt)
    {
        $sql = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES (
        '".hesk_dbEscape($trackingID)."',
        '".hesk_dbEscape($myatt['saved_name'])."',
        '".hesk_dbEscape($myatt['real_name'])."',
        '".hesk_dbEscape($myatt['size'])."'
        )";
        $result = hesk_dbQuery($sql);
        $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] .',';
    }
}

/* Add reply */
$sql = "
INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`staffid`)
VALUES (
'".hesk_dbEscape($replyto)."',
'".hesk_dbEscape(addslashes($_SESSION['name']))."',
'".hesk_dbEscape($message)."',
NOW(),
'".hesk_dbEscape($myattachments)."',
'".hesk_dbEscape($_SESSION['id'])."'
)
";
$result = hesk_dbQuery($sql);

/* Change the status of priority? */
if (!empty($_POST['set_priority']))
{
    $priority = intval($_POST['priority']);
    if ($priority < 1 || $priority > 3)
    {
    	hesk_error($hesklang['select_priority']);
    }
    $priority_sql = ",`priority`='$priority'";
}
else
{
    $priority_sql = "";
}

/* Update the original ticket */
if (!empty($_POST['close']))
{
    $sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='3',`lastreplier`='1',`lastchange`=NOW() $priority_sql WHERE `id`=".hesk_dbEscape($replyto)." LIMIT 1";
}
else
{
    $sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='2',`lastreplier`='1',`lastchange`=NOW() $priority_sql WHERE `id`=".hesk_dbEscape($replyto)." LIMIT 1";
}
hesk_dbQuery($sql);

/* Update number of replies in the users table */
$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET `replies`=`replies`+1 WHERE `id`=".hesk_dbEscape($_SESSION['id'])." LIMIT 1";
hesk_dbQuery($sql);

/*** Send "New reply added" e-mail ***/
$message = hesk_msgToPlain($message,1);

/* Get e-mail message for customer */
$msg = hesk_getEmailMessage('new_reply_by_staff');
$msg = str_replace('%%NAME%%',stripslashes($orig_name),$msg);
$msg = str_replace('%%SUBJECT%%',stripslashes($orig_subject),$msg);
$msg = str_replace('%%TRACK_ID%%',$trackingID,$msg);
$msg = str_replace('%%TRACK_URL%%',$trackingURL,$msg);
$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
$msg = str_replace('%%SITE_URL%%',$hesk_settings['site_url'],$msg);
$msg = str_replace('%%MESSAGE%%',$message,$msg);

/* Send e-mail */
$headers = "From: $hesk_settings[noreply_mail]\n";
$headers.= "Reply-to: $hesk_settings[noreply_mail]\n";
$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
$headers.= "Content-type: text/plain; charset=".$hesklang['ENCODING'];
@mail($orig_email,$hesklang['new_reply_staff'],$msg,$headers);

$_SESSION['HESK_NOTICE']  = $hesklang['reply_added'];
$_SESSION['HESK_MESSAGE'] = $hesklang['reply_submitted'];
if (!empty($_POST['close']))
{
    $_SESSION['HESK_MESSAGE'] .= '<br />'.$hesklang['ticket_marked'].' <span class="resolved">'.$hesklang['closed'].'</span>';
}

/* What to do after reply? */
if ($_SESSION['afterreply'] == 1)
{
	header('Location: admin_main.php');
}
elseif ($_SESSION['afterreply'] == 2)
{
	/* Get the next open ticket that need a reply */
	$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE ';
	$sql .= hesk_myCategories();
    $sql .= ' AND (`status`=\'0\' OR `status`=\'1\') ';
    $sql .= ' ORDER BY `priority` ASC LIMIT 1';
    $res  = hesk_dbQuery($sql);

    if (hesk_dbNumRows($res) == 1)
    {
    	$row = hesk_dbFetchAssoc($res);
        $_SESSION['HESK_MESSAGE'] .= '<br />'.$hesklang['rssn'];
        header('Location: admin_ticket.php?track='.$row['trackid'].'&amp;Refresh='.rand(10000,99999));
    }
    else
    {
		header('Location: admin_main.php');
    }
}
else
{
	header('Location: admin_ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999));
}
exit();
?>

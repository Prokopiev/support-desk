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
define('HESK_PATH','');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/database.inc.php');

/* Befor anything else block obvious spammers trying to inject email headers */
$pattern = "/\n|\r|\t|%0A|%0D|%08|%09/";
if (preg_match($pattern,$_POST['name']) || preg_match($pattern,$_POST['subject']))
{
	header('HTTP/1.1 403 Forbidden');
    exit();
}

$hesk_error_buffer = array();

hesk_session_start();

if ($hesk_settings['question_use'])
{
	$question = hesk_input($_POST['question']);
	if (empty($question))
	{
		$hesk_error_buffer[]=$hesklang['q_miss'];
	}
	elseif (strtolower($question) != strtolower($hesk_settings['question_ans']))
	{
		$hesk_error_buffer[]=$hesklang['q_wrng'];
	}
	else
	{
		$_SESSION['c_question'] = $question;
	}
}

if ($hesk_settings['secimg_use'])
{
	$mysecnum = hesk_isNumber($_POST['mysecnum']);
	if (empty($mysecnum))
	{
		$hesk_error_buffer[]=$hesklang['sec_miss'];
	}
	else
	{
		require(HESK_PATH . 'secimg.inc.php');
		$sc = new PJ_SecurityImage($hesk_settings['secimg_sum']);
		if (!($sc->checkCode($mysecnum,$_SESSION['checksum'])))
		{
			$hesk_error_buffer[]=$hesklang['sec_wrng'];
		}
	}
}

$name	  = hesk_input($_POST['name']) or $hesk_error_buffer[]=$hesklang['enter_your_name'];
$email	  = hesk_validateEmail($_POST['email'],'ERR',0) or $hesk_error_buffer[]=$hesklang['enter_valid_email'];
$category = hesk_input($_POST['category']) or $hesk_error_buffer[]=$hesklang['sel_app_cat'];
$priority = hesk_input($_POST['priority']) or $hesk_error_buffer[]=$hesklang['sel_app_priority'];
$subject  = hesk_input($_POST['subject']) or $hesk_error_buffer[]=$hesklang['enter_ticket_subject'];
$message  = hesk_input($_POST['message']) or $hesk_error_buffer[]=$hesklang['enter_message'];

/* Custom fields */
foreach ($hesk_settings['custom_fields'] as $k=>$v)
{
	if ($v['use'])
    {
        if ($v['type'] == 'checkbox')
        {
			$$k='';
            
        	if (isset($_POST[$k]))
            {
				if (is_array($_POST[$k]))
				{
					foreach ($_POST[$k] as $myCB)
					{
						$$k.=hesk_input($myCB).'<br />';
					}
					$$k=substr($$k,0,-6);
				}
            }
            else
            {
            	if ($v['req'])
                {
					$hesk_error_buffer[]=$hesklang['fill_all'].': '.$v['name'];
                }
            	$_POST[$k] = '';
            }
        }
		elseif ($v['req'])
        {
        	$$k=hesk_makeURL(nl2br(hesk_input($_POST[$k]))) or $hesk_error_buffer[]=$hesklang['fill_all'].': '.$v['name'];
        }
		else
        {
        	$$k=hesk_makeURL(nl2br(hesk_input($_POST[$k])));
        }
		$_SESSION["c_$k"]=$_POST[$k];
	}
    else
    {
    	$$k = '';
    }
}


/* If we have any errors lets store info in session to avoid re-typing everything */
if (count($hesk_error_buffer)!=0)
{
    $_SESSION['c_name']     = $_POST['name'];
    $_SESSION['c_email']    = $_POST['email'];
    $_SESSION['c_category'] = $_POST['category'];
    $_SESSION['c_priority'] = $_POST['priority'];
    $_SESSION['c_subject']  = $_POST['subject'];
    $_SESSION['c_message']  = $_POST['message'];

    $_SESSION['HESK_MESSAGE'] = '';
    foreach ($hesk_error_buffer as $error)
    {
        $_SESSION['HESK_MESSAGE'] .= "<li>$error</li>\n";
    }

    $_SESSION['HESK_ERROR'] = true;
    header('Location: index.php?a=add');
    exit();
}

$message=hesk_makeURL($message);
$message=nl2br($message);

/* Generate tracking ID */
$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
$trackingID = $useChars{mt_rand(0,29)};
for($i=1;$i<10;$i++)
{
    $trackingID .= $useChars{mt_rand(0,29)};
}
$trackingURL = $hesk_settings['hesk_url'].'/ticket.php?track='.$trackingID;

/* Attachments */
if ($hesk_settings['attachments']['use'])
{
    require_once(HESK_PATH . 'inc/attachments.inc.php');
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

/* Add to database */
hesk_dbConnect();

if ($hesk_settings['attachments']['use'] && !empty($attachments))
{
    foreach ($attachments as $myatt)
    {
        $sql = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('".hesk_dbEscape($trackingID)."', '".hesk_dbEscape($myatt['saved_name'])."', '".hesk_dbEscape($myatt['real_name'])."', '".hesk_dbEscape($myatt['size'])."')";
        $result = hesk_dbQuery($sql);
        $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] .',';
    }
}

$sql = "
INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` (
`trackid`,`name`,`email`,`category`,`priority`,`subject`,`message`,`dt`,`lastchange`,`ip`,`status`,`attachments`,`custom1`,`custom2`,`custom3`,`custom4`,`custom5`,`custom6`,`custom7`,`custom8`,`custom9`,`custom10`,`custom11`,`custom12`,`custom13`,`custom14`,`custom15`,`custom16`,`custom17`,`custom18`,`custom19`,`custom20`
)
VALUES (
'".hesk_dbEscape($trackingID)."',
'".hesk_dbEscape($name)."',
'".hesk_dbEscape($email)."',
'".hesk_dbEscape($category)."',
'".hesk_dbEscape($priority)."',
'".hesk_dbEscape($subject)."',
'".hesk_dbEscape($message)."',
NOW(),
NOW(),
'".hesk_dbEscape($_SERVER['REMOTE_ADDR'])."',
'0',
'".hesk_dbEscape($myattachments)."',
'".hesk_dbEscape($custom1)."',
'".hesk_dbEscape($custom2)."',
'".hesk_dbEscape($custom3)."',
'".hesk_dbEscape($custom4)."',
'".hesk_dbEscape($custom5)."',
'".hesk_dbEscape($custom6)."',
'".hesk_dbEscape($custom7)."',
'".hesk_dbEscape($custom8)."',
'".hesk_dbEscape($custom9)."',
'".hesk_dbEscape($custom10)."',
'".hesk_dbEscape($custom11)."',
'".hesk_dbEscape($custom12)."',
'".hesk_dbEscape($custom13)."',
'".hesk_dbEscape($custom14)."',
'".hesk_dbEscape($custom15)."',
'".hesk_dbEscape($custom16)."',
'".hesk_dbEscape($custom17)."',
'".hesk_dbEscape($custom18)."',
'".hesk_dbEscape($custom19)."',
'".hesk_dbEscape($custom20)."'
)
";

$result = hesk_dbQuery($sql);

/* Get e-mail message for customer */
$msg = hesk_getEmailMessage('new_ticket');
$msg = str_replace('%%NAME%%',hesk_msgToPlain($name,1),$msg);
$msg = str_replace('%%SUBJECT%%',hesk_msgToPlain($subject,1),$msg);
$msg = str_replace('%%TRACK_ID%%',$trackingID,$msg);
$msg = str_replace('%%TRACK_URL%%',$trackingURL,$msg);
$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
$msg = str_replace('%%SITE_URL%%',$hesk_settings['site_url'],$msg);
$msg = str_replace('%%MESSAGE%%',hesk_msgToPlain($message,1),$msg);

/* Send e-mail */
$headers = "From: $hesk_settings[noreply_mail]\n";
$headers.= "Reply-to: $hesk_settings[noreply_mail]\n";
$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
$headers.= "Content-type: text/plain; charset=".$hesklang['ENCODING'];
@mail($email,$hesklang['ticket_received'],$msg,$headers);

/* Need to notify any admins? */
$admins=array();
$sql = "SELECT `email`,`isadmin`,`categories` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `notify`='1'";
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

	$msg = hesk_getEmailMessage('new_ticket_staff');
	$msg = str_replace('%%NAME%%',hesk_msgToPlain($name,1),$msg);
	$msg = str_replace('%%SUBJECT%%',hesk_msgToPlain($subject,1),$msg);
	$msg = str_replace('%%TRACK_ID%%',$trackingID,$msg);
	$msg = str_replace('%%TRACK_URL%%',$trackingURL_admin,$msg);
	$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
	$msg = str_replace('%%SITE_URL%%',$hesk_settings['site_url'],$msg);
	$msg = str_replace('%%MESSAGE%%',hesk_msgToPlain($message,1),$msg);

	/* Send e-mail to staff */
	$email=implode(',',$admins);
	$headers = "From: $hesk_settings[noreply_mail]\n";
	$headers.= "Reply-to: $hesk_settings[noreply_mail]\n";
	$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
	$headers.= "Content-type: text/plain; charset=".$hesklang['ENCODING'];
	@mail($email,$hesklang['new_ticket_submitted'],$msg,$headers);
}

/* Next ticket show suggested articles again */
$_SESSION['ARTICLES_SUGGESTED']=false;

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

?>


<table cellspacing="0" cellpadding="0" border="0" align="center" style="position: relative; left: -60px; margin-top: 46px;">
		<tbody><tr>
			<td valign="top" style="padding-top: 20px;">
				<div><a href="http://www.66.ru"><img width="175" height="119" border="0" title="Современный портал Екатеринбурга - www.66.ru" alt="Современный портал Екатеринбурга - www.66.ru" src="http://img.66.ru/dez/reg2/logo.gif"></a></div>
			</td>
			<td width="40">&nbsp;</td>
			<td width="660" valign="top">
			
				
				
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td width="21">
							<img width="21" height="21" alt="" src="http://img.66.ru/dez/reg2/regisration_02_03.gif"></td>
						<td style="background: url('http://img.66.ru/dez/reg2/regisration_02_04.gif') repeat-x scroll 0% 0% transparent;">
							<img width="502" height="21" alt="" src="http://img.66.ru/dez/spacer.gif"></td>
						<td>
							<img width="30" height="21" alt="" src="http://img.66.ru/dez/reg2/regisration_02_05.gif"></td>
					</tr>
					<tr>
						<td style="background: url('http://img.66.ru/dez/reg2/regisration_02_07.gif') repeat-y scroll 0% 0% transparent;">
							<img width="21" height="1" alt="" src="http://img.66.ru/dez/spacer.gif"></td>							
						<td valign="top" style="padding: 10px;" align="left">									
				
								
							
				
							<div width="224" style="font-size: 22px; color: black;">Запрос в <a href="http://support.66.ru/" style="font-size: 22px; color: black;">техническую поддержку 66.ru</a>:</div><br><br>
							
												
							
							<?php echo $hesklang['ticket_submitted_success'];?><br /><br />
							<a href="<?php echo $trackingURL; ?>"><?php echo $hesklang['view_your_ticket']; ?></a>

							
							
							
							
						</td>
						<td style="background: url('http://img.66.ru/dez/reg2/regisration_02_09.gif') repeat-y scroll 0% 0% transparent;">
							<img width="30" height="1" alt="" src="http://img.66.ru/dez/spacer.gif"></td>
					</tr>
					<tr>
						<td width="21">
							<img width="21" height="27" alt="" src="http://img.66.ru/dez/reg2/regisration_02_10.gif"></td>
						<td style="background: url('http://img.66.ru/dez/reg2/regisration_02_11.gif') repeat-x scroll 0% 0% transparent;">
							<img width="502" height="27" alt="" src="http://img.66.ru/dez/spacer.gif"></td>
						<td>
							<img width="30" height="27" alt="" src="http://img.66.ru/dez/reg2/regisration_02_12.gif"></td>
					</tr>
				</tbody></table>

			</td>
		</tr>
	</tbody></table>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>

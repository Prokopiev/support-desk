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
hesk_checkPermission('can_man_canned');

/* What should we do? */
$action = isset($_REQUEST['a']) ? hesk_input($_REQUEST['a']) : '';
if ($action == 'new') {new_saved();}
elseif ($action == 'edit') {edit_saved();}
elseif ($action == 'remove') {remove();}
elseif ($action == 'order') {order_saved();}

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

</td>
</tr>
<tr>
<td>

<script language="javascript" type="text/javascript"><!--
function confirm_delete()
{
if (confirm('<?php echo $hesklang['delete_saved']; ?>')) {return true;}
else {return false;}
}

function hesk_insertTag(tag) {
var text_to_insert = '%%'+tag+'%%';
hesk_insertAtCursor(document.form1.msg, text_to_insert);
document.form1.msg.focus();
}

function hesk_insertAtCursor(myField, myValue) {
if (document.selection) {
myField.focus();
sel = document.selection.createRange();
sel.text = myValue;
}
else if (myField.selectionStart || myField.selectionStart == '0') {
var startPos = myField.selectionStart;
var endPos = myField.selectionEnd;
myField.value = myField.value.substring(0, startPos)
+ myValue
+ myField.value.substring(endPos, myField.value.length);
} else {
myField.value += myValue;                                             
}
}
//-->
</script>

	<?php
	    if(isset($_SESSION['HESK_NOTICE']))
        {
	?>
	        <div align="center">
	        <table border="0" width="600" id="ok" cellspacing="0" cellpadding="3">
		        <tr>
		        	<td align="left" class="ok_header">&nbsp;<img src="../img/ok.gif" style="vertical-align:text-bottom" width="16" height="16" alt="" />&nbsp; <?php echo $_SESSION['HESK_NOTICE']; ?></td>
		        </tr>
		        <tr>
		        	<td align="left" class="ok_body"><?php echo $_SESSION['HESK_MESSAGE']; ?></td>
		        </tr>
	        </table>
	        </div>
	<?php
	        unset($_SESSION['HESK_NOTICE']);
	        unset($_SESSION['HESK_MESSAGE']);
	    }
	?>

<h3 align="center"><?php echo $hesklang['manage_saved']; ?></h3>

<p><?php echo $hesklang['manage_intro']; ?></p>

<div align="center">
<table border="0" cellspacing="1" cellpadding="3" class="white">
<tr>
<th class="admin_white"><b><i><?php echo $hesklang['saved_title']; ?></i></b></th>
<th class="admin_white">&nbsp;</th>
</tr>

<?php
$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'std_replies` ORDER BY `reply_order` ASC';
$result = hesk_dbQuery($sql);
$options='';
$javascript_messages='';
$javascript_titles='';

$i=1;
$j=0;
$num = hesk_dbNumRows($result);

if ($num < 1)
{
    echo '
    <tr>
        <td>'.$hesklang['no_saved'].'</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    ';
}
else
{
    while ($mysaved=hesk_dbFetchAssoc($result))
    {
    	$j++;

		$color = $i ? 'admin_white' : 'admin_gray';
		$i	   = $i ? 0 : 1;

        $options .= '<option value="'.$mysaved['id'].'">'.$mysaved['title'].'</option>';
        $javascript_messages.='myMsgTxt['.$mysaved['id'].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved['message']) )."';\n";
        $javascript_titles.='myTitle['.$mysaved['id'].']=\''.addslashes($mysaved['title'])."';\n";

	    echo '
	    <tr>
	    <td class="'.$color.'">'.$mysaved['title'].'</td>
        <td class="'.$color.'" style="text-align:center; white-space:nowrap;">
        ';

        if ($num > 1)
        {
        	if ($j == 1)
            {
            	echo'<img src="../img/blank.gif" width="16" height="16" alt="" border="0" /> <a href="manage_canned.php?a=order&amp;replyid='.$mysaved['id'].'&amp;move=15"><img src="../img/move_down.png" width="16" height="16" alt="'.$hesklang['move_dn'].'" title="'.$hesklang['move_dn'].'" border="0" /></a>';
            }
            elseif ($j == $num)
            {
            	echo'<a href="manage_canned.php?a=order&amp;replyid='.$mysaved['id'].'&amp;move=-15"><img src="../img/move_up.png" width="16" height="16" alt="'.$hesklang['move_up'].'" title="'.$hesklang['move_up'].'" border="0" /></a> <img src="../img/blank.gif" width="16" height="16" alt="" border="0" />';
            }
            else
            {
            	echo'
			    <a href="manage_canned.php?a=order&amp;replyid='.$mysaved['id'].'&amp;move=-15"><img src="../img/move_up.png" width="16" height="16" alt="'.$hesklang['move_up'].'" title="'.$hesklang['move_up'].'" border="0" /></a>
			    <a href="manage_canned.php?a=order&amp;replyid='.$mysaved['id'].'&amp;move=15"><img src="../img/move_down.png" width="16" height="16" alt="'.$hesklang['move_dn'].'" title="'.$hesklang['move_dn'].'" border="0" /></a>
                ';
            }
        }
        else
        {
        	echo '';
        }

        echo '
        <a href="manage_canned.php?a=remove&amp;id='.$mysaved['id'].'" onclick="return confirm_delete();"><img src="../img/delete.png" width="16" height="16" alt="'.$hesklang['remove'].'" title="'.$hesklang['remove'].'" border="0" /></a>&nbsp;</td>
	    </tr>
		';
    } // End while
}

?>
</table>
</div>

<script language="javascript" type="text/javascript"><!--
var myMsgTxt = new Array();
myMsgTxt[0]='';
var myTitle = new Array();
myTitle[0]='';

<?php
echo $javascript_titles;
echo $javascript_messages;
?>

function setMessage(msgid) {
    if (document.getElementById) {
        document.getElementById('HeskMsg').innerHTML='<textarea name="msg" rows="15" cols="70">'+myMsgTxt[msgid]+'</textarea>';
        document.getElementById('HeskTitle').innerHTML='<input type="text" name="name" size="40" maxlength="50" value="'+myTitle[msgid]+'">';
    } else {
        document.form1.msg.value=myMsgTxt[msgid];
        document.form1.name.value=myTitle[msgid];
    }

    if (msgid==0) {
        document.form1.a[0].checked=true;
    } else {
        document.form1.a[1].checked=true;
    }
}
//-->
</script>

<p>&nbsp;</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
	<td class="roundcornersleft">&nbsp;</td>
	<td>

	<form action="manage_canned.php" method="post" name="form1">
	<h3 align="center"><?php echo $hesklang['new_saved']; ?></h3>

	<div align="center">
	<table border="0">
	<tr>
	<td>

	<p><label><input type="radio" name="a" value="new" checked="checked" /> <?php echo $hesklang['canned_add']; ?></label><br />
	<label><input type="radio" name="a" value="edit" /> <?php echo $hesklang['canned_edit']; ?></label>:

	<select name="saved_replies" onchange="setMessage(this.value)">
	<option value="0"> - <?php echo $hesklang['select_empty']; ?> - </option>
	<?php echo $options; ?>
	</select></p>

	<p><b><?php echo $hesklang['saved_title']; ?>:</b> <span id="HeskTitle"><input type="text" name="name" size="40" maxlength="50" /></span></p>
	<p><b><?php echo $hesklang['message']; ?>:</b><br />
	<span id="HeskMsg"><textarea name="msg" rows="15" cols="70"></textarea></span><br />

	<?php echo $hesklang['insert_special']; ?>:<br />
	<a href="javascript:void(0)" onclick="hesk_insertTag('HESK_NAME')"><?php echo $hesklang['name']; ?></a> |
	<a href="javascript:void(0)" onclick="hesk_insertTag('HESK_EMAIL')"><?php echo $hesklang['email']; ?></a>
	<?php
	    foreach ($hesk_settings['custom_fields'] as $k=>$v)
	    {
	        if ($v['use'])
	        {
	            echo '| <a href="javascript:void(0)" onclick="hesk_insertTag(\'HESK_'.$k.'\')">'.$v['name'].'</a> ';
	        }
	    }
	?>
	</p>

	</td>
	</tr>
	</table>
	</div>

	<p align="center"><input type="submit" value="<?php echo $hesklang['save_reply']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>
	</form>

    </td>
	<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
	<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
</table>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function edit_saved() {
	global $hesk_settings, $hesklang;

	$id=hesk_isNumber($_POST['saved_replies']);
    if (empty($id))
    {
    	hesk_error($hesklang['selcan']);
    }    
	$savename=hesk_Input($_POST['name'],$hesklang['ent_saved_title']);
	$msg=hesk_Input($_POST['msg'],$hesklang['ent_saved_msg']);

	$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` SET `title`='".hesk_dbEscape($savename)."',`message`='".hesk_dbEscape($msg)."' WHERE `id`=".hesk_dbEscape($id)." LIMIT 1";
	$result = hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['saved'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['your_saved'];
	header('Location: manage_canned.php');
	exit();
} // End edit_saved()


function new_saved() {
	global $hesk_settings, $hesklang;

	$savename=hesk_Input($_POST['name'],$hesklang['ent_saved_title']);
	$msg=hesk_Input($_POST['msg'],$hesklang['ent_saved_msg']);

	/* Get the latest reply_order */
	$sql = 'SELECT `reply_order` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'std_replies` ORDER BY `reply_order` DESC LIMIT 1';
	$result = hesk_dbQuery($sql);
	$row = hesk_dbFetchRow($result);
	$my_order = $row[0]+10;

	$sql = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` (`title`,`message`,`reply_order`) VALUES ('".hesk_dbEscape($savename)."','".hesk_dbEscape($msg)."','".hesk_dbEscape($my_order)."')";
	$result = hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['saved'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['your_saved'];
	header('Location: manage_canned.php');
	exit();
} // End new_saved()


function remove() {
	global $hesk_settings, $hesklang;

	$mysaved=hesk_isNumber($_GET['id'],$hesklang['id_not_valid']);

	$sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'std_replies` WHERE `id`='.hesk_dbEscape($mysaved).' LIMIT 1';
	$result = hesk_dbQuery($sql);
	if (hesk_dbAffectedRows() != 1)
    {
    	hesk_error("$hesklang[int_error]: $hesklang[reply_not_found].");
    }

	$_SESSION['HESK_NOTICE']  = $hesklang['saved_removed'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['saved_rem_full'];
	header('Location: manage_canned.php');
	exit();
} // End remove()


function order_saved() {
	global $hesk_settings, $hesklang;

	$replyid=hesk_isNumber($_GET['replyid'],$hesklang['reply_move_id']);
	$reply_move=intval($_GET['move']);

	$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` SET `reply_order`=`reply_order`+".hesk_dbEscape($reply_move)." WHERE `id`=".hesk_dbEscape($replyid)." LIMIT 1";
	$result = hesk_dbQuery($sql);
	if (hesk_dbAffectedRows() != 1) {hesk_error("$hesklang[int_error]: $hesklang[reply_not_found].");}

	/* Update all category fields with new order */
	$sql = 'SELECT `id` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'std_replies` ORDER BY `reply_order` ASC';
	$result = hesk_dbQuery($sql);

	$i = 10;
	while ($myreply=hesk_dbFetchAssoc($result))
	{
	    $sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` SET `reply_order`=".hesk_dbEscape($i)." WHERE `id`=".hesk_dbEscape($myreply['id'])." LIMIT 1";
	    hesk_dbQuery($sql);
	    $i += 10;
	}

	header('Location: manage_canned.php');
	exit();
} // End order_saved()

?>

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

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

if (!empty($_POST['action']))
{
	update_profile();
}

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `id` = \''.hesk_dbEscape($_SESSION['id']).'\' LIMIT 1';
$res = hesk_dbQuery($sql);
$tmp = hesk_dbFetchAssoc($res);

foreach ($tmp as $k=>$v)
{
	if ($k == 'pass' || $k == 'categories')
    {
    	continue;
    }
	$_SESSION[$k]=$v;
}
?>

</td>
</tr>
<tr>
<td>

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
            <br />
	<?php
	        unset($_SESSION['HESK_NOTICE']);
	        unset($_SESSION['HESK_MESSAGE']);
	    }

	    if(isset($_SESSION['HESK_ERROR']))
        {
	?>
	        <div align="center">
	        <table border="0" width="600" id="error" cellspacing="0" cellpadding="3">
		        <tr>
		        	<td align="left" class="error_header">&nbsp;<img src="../img/error.gif" style="vertical-align:text-bottom" width="16" height="16" alt="" />&nbsp; <?php echo $hesklang['error']; ?></td>
		        </tr>
		        <tr>
		        	<td align="left" class="error_body"><ul><?php echo $_SESSION['HESK_MESSAGE']; ?></ul></td>
		        </tr>
	        </table>
	        </div>
            <br />
	<?php
	        unset($_SESSION['HESK_ERROR']);
	        unset($_SESSION['HESK_MESSAGE']);
	    }
	?>

	<h3 align="center"><?php echo $hesklang['profile_for'].' <b>'.$_SESSION['user']; ?></b></h3>

	<p align="center"><?php echo $hesklang['req_marked_with']; ?> <span class="important">*</span></p>

	<?php
	if ($hesk_settings['can_sel_lang'])
	{
		$str  = '<form method="get" action="profile.php" style="margin:0;padding:0;border:0;white-space:nowrap;">';
        $str .= '<p>'.$hesklang['chol'].': ';
		foreach ($_GET as $k => $v)
		{
			if ($k == 'language')
			{
				continue;
			}
			$str .= '<input type="hidden" name="'.htmlentities($k).'" value="'.htmlentities($v).'" />';
		}

        $str .= '<select name="language" onchange="this.form.submit()">';
		$str .= hesk_listLanguages(0);
		$str .= '</select>';

	?>
        <script language="javascript" type="text/javascript">
		document.write('<?php echo str_replace(array('"','<','=','>'),array('\42','\74','\75','\76'),$str . '</p></form>'); ?>');
        </script>
        <noscript>
        <?php
        	echo $str . '<input type="submit" value="'.addslashes($hesklang['go']).'" /></p></form>';
        ?>
        </noscript>
	<?php
	}
    ?>

	<form method="post" action="profile.php" name="form1">

<br />

<span class="section">&raquo; <?php echo $hesklang['pinfo']; ?></span>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
	<td class="roundcornersleft">&nbsp;</td>
	<td>

	<!-- Contact info -->
	<table border="0">
	<tr>
	<td style="text-align:right" width="200"><?php echo $hesklang['name']; ?>: <font class="important">*</font></td>
	<td><input type="text" name="name" size="25" maxlength="50" value="<?php echo $_SESSION['name']; ?>" /></td>
	</tr>
	<tr>
	<td style="text-align:right" width="200"><?php echo $hesklang['email']; ?>: <font class="important">*</font></td>
	<td><input type="text" name="email" size="30" maxlength="255" value="<?php echo $_SESSION['email']; ?>" /></td>
	</tr>
	<tr>
	<td style="text-align:right" width="200"><?php echo $hesklang['new_pass']; ?>: </td>
	<td><input type="password" name="newpass" size="30" maxlength="20" /></td>
	</tr>
	<tr>
	<td style="text-align:right" width="200"><?php echo $hesklang['confirm_pass']; ?>: </td>
	<td><input type="password" name="newpass2" size="30" maxlength="20" /></td>
	</tr>
	</table>

	</td>
	<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
	<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
</table>

<br />

<span class="section">&raquo; <?php echo $hesklang['sig']; ?></span>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
	<td class="roundcornersleft">&nbsp;</td>
	<td>

	<!-- signature -->
	<table border="0">
	<tr>
	<td style="text-align:right" valign="top" width="200"><?php echo $hesklang['signature_max']; ?>:</td>
	<td><textarea name="signature" rows="6" cols="40"><?php echo $_SESSION['signature']; ?></textarea><br />
	<?php echo $hesklang['sign_extra']; ?></td>
	</tr>
	</table>

	</td>
	<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
	<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
</table>

<br />

<span class="section">&raquo; <?php echo $hesklang['pref']; ?></span>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
	<td class="roundcornersleft">&nbsp;</td>
	<td>

	<table border="0">
	<tr>
	<td style="text-align:right" valign="top" width="200"><?php echo $hesklang['aftrep']; ?>:</td>
	<td>
    <label><input type="radio" name="afterreply" value="0" <?php if (!$_SESSION['afterreply']) {echo 'checked="checked"';} ?>/> <?php echo $hesklang['showtic']; ?></label><br />
    <label><input type="radio" name="afterreply" value="1" <?php if ($_SESSION['afterreply'] == 1) {echo 'checked="checked"';} ?>/> <?php echo $hesklang['gomain']; ?></label><br />
    <label><input type="radio" name="afterreply" value="2" <?php if ($_SESSION['afterreply'] == 2) {echo 'checked="checked"';} ?>/> <?php echo $hesklang['shownext']; ?></label><br />
    </td>
	</tr>
	</table>

	<!-- Notify about new tickets and replies -->
	<p align="center"><label><input type="checkbox" name="notify" value="1" <?php if (!empty($_SESSION['notify'])) {echo 'checked="checked"';}?>  /> <?php echo $hesklang['notify_new_posts']; ?>.</label></p>

	</td>
	<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
	<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
</table>

	<!-- Submit -->
	<p align="center"><input type="hidden" name="action" value="update" />
	<input type="submit" value="<?php echo $hesklang['update_profile']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>

    </form>



<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function update_profile() {
	global $hesk_settings, $hesklang;

    $sql_pass = '';

    $hesk_error_buffer = array();
	$_SESSION['name']  = hesk_input($_POST['name']) or $hesk_error_buffer[] = $hesklang['enter_your_name'];
	$_SESSION['email'] = hesk_validateEmail($_POST['email'],'ERR',0) or $hesk_error_buffer[] = $hesklang['enter_valid_email'];
	$_SESSION['signature'] = hesk_input($_POST['signature']);
	if (!empty($_POST['notify']))
    {
    	$_SESSION['notify']=1;
    }
	else
    {
    	$_SESSION['notify']=0;
    }
	if (strlen($_SESSION['signature'])>255)
    {
		$hesk_error_buffer[] = $hesklang['signature_long'];
    }

	/* Change password? */
	if (!empty($_POST['newpass']))
	{
	    $newpass  = hesk_PasswordSyntax($_POST['newpass'],'ERR',1,0) or $hesk_error_buffer[] = $hesklang['password_not_valid'];
	    $newpass2 = hesk_input($_POST['newpass2']) or $hesk_error_buffer[] = $hesklang['confirm_user_pass'];
	    if ($newpass != $newpass2)
        {
        	$hesk_error_buffer[] = $hesklang['passwords_not_same'];
        }
        $sql_pass = ',`pass`=\''.hesk_Pass2Hash($newpass).'\'';
	}

    if (!empty($hesk_error_buffer))
    {
		$_SESSION['HESK_ERROR']   = true;
        foreach ($hesk_error_buffer as $e)
        {
			$_SESSION['HESK_MESSAGE'] .= '<li>'.$e.'</li>';
        }
        return true;
    }


    $_SESSION['afterreply'] = intval($_POST['afterreply']);
    if ($_SESSION['afterreply'] != 1 && $_SESSION['afterreply'] != 2)
    {
    	$_SESSION['afterreply'] = 0;
    }

	/* Update database */
	$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET
    `name`='".hesk_dbEscape($_SESSION['name'])."',
    `email`='".hesk_dbEscape($_SESSION['email'])."',
	`signature`='".hesk_dbEscape($_SESSION['signature'])."'
    $sql_pass ,
    `afterreply`='".hesk_dbEscape($_SESSION['afterreply'])."' ,
    `notify`='".hesk_dbEscape($_SESSION['notify'])."'
    WHERE `id`='".hesk_dbEscape($_SESSION['id'])."' LIMIT 1";
	hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['profile_updated'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['profile_updated_success'];
} // End update_profile()

?>

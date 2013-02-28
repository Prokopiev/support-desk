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
hesk_checkPermission('can_man_users');

/* What should we do? */
$action = isset($_REQUEST['a']) ? hesk_input($_REQUEST['a']) : '';
if ($action == 'new') {new_user();}
elseif ($action == 'edit') {edit_user();}
elseif ($action == 'save') {update_user();}
elseif ($action == 'remove') {remove();}
else {

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

</td>
</tr>
<tr>
<td>

<script language="Javascript" type="text/javascript"><!--
function confirm_delete()
{
if (confirm('<?php echo $hesklang['sure_remove_user']; ?>')) {return true;}
else {return false;}
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

<h3 align="center"><?php echo $hesklang['manage_users']; ?></h3>

<p><?php echo $hesklang['users_intro']; ?></p>

<div align="center">
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="white">
<tr>
<th class="admin_white"><b><i><?php echo $hesklang['name']; ?></i></b></th>
<th class="admin_white"><b><i><?php echo $hesklang['email']; ?></i></b></th>
<th class="admin_white"><b><i><?php echo $hesklang['username']; ?></i></b></th>
<th class="admin_white"><b><i><?php echo $hesklang['administrator']; ?></i></b></th>
<?php
if ($hesk_settings['rating'])
{
?>
	<th class="admin_white"><b><i><?php echo $hesklang['rating']; ?></i></b></th>
<?php
}
?>
<th class="admin_white">&nbsp;</th>
</tr>

<?php
$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` ORDER BY `id` ASC';
$result = hesk_dbQuery($sql);

$i=1;

while ($myuser=hesk_dbFetchAssoc($result))
{

	$color = $i ? 'admin_white' : 'admin_gray';
	$i	   = $i ? 0 : 1;

    if ($myuser['isadmin']) {$myuser['isadmin'] = '<font class="open">'.$hesklang['yes'].'</font>';}
    else {$myuser['isadmin'] = '<font class="resolved">'.$hesklang['no'].'</font>';}

    /* Deleting user with ID 1 (default administrator) is not allowed */
    $edit_code = '<a href="manage_users.php?a=edit&amp;id='.$myuser['id'].'"><img src="../img/edit.png" width="16" height="16" alt="'.$hesklang['edit'].'" title="'.$hesklang['edit'].'" border="0" /></a>';
    if ($myuser['id'] == 1)
    {
        $remove_code = ' <img src="../img/blank.gif" width="16" height="16" alt="" border="0" />';
    }
    else
    {
        $remove_code = ' <a href="manage_users.php?a=remove&amp;id='.$myuser['id'].'" onclick="return confirm_delete();"><img src="../img/delete.png" width="16" height="16" alt="'.$hesklang['remove'].'" title="'.$hesklang['remove'].'" border="0" /></a>';
    }

echo <<<EOC
<tr>
<td class="$color">$myuser[name]</td>
<td class="$color"><a href="mailto:$myuser[email]">$myuser[email]</a></td>
<td class="$color">$myuser[user]</td>
<td class="$color">$myuser[isadmin]</td>

EOC;

if ($hesk_settings['rating'])
{
	$alt = $myuser['rating'] ? sprintf($hesklang['rated'], sprintf("%01.1f", $myuser['rating']), ($myuser['ratingneg']+$myuser['ratingpos'])) : $hesklang['not_rated'];
	echo '<td class="'.$color.'" align="center" width="1"><img src="../img/star_'.(hesk_round_to_half($myuser['rating'])*10).'.png" width="85" height="16" alt="'.$alt.'" title="'.$alt.'" border="0" style="vertical-align:text-bottom" /></td>';
}

echo <<<EOC
<td class="$color" style="text-align:center">$edit_code $remove_code</td>
</tr>

EOC;
} // End while
?>
</table>
</div>

<br />

<hr />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
			<td class="roundcornerstop"></td>
			<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
		</tr>
		<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>
        <!-- CONTENT -->

<h3 align="center"><?php echo $hesklang['add_user']; ?></h3>

<p align="center"><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></p>

<form name="form1" action="manage_users.php" method="post">

<!-- Contact info -->
<table border="0" width="100%">
<tr>
<td width="200" style="text-align:right"><?php echo $hesklang['real_name']; ?>: <font class="important">*</font></td>
<td align="left"><input type="text" name="name" size="40" maxlength="50" /></td>
</tr>
<tr>
<td width="200" style="text-align:right"><?php echo $hesklang['email']; ?>: <font class="important">*</font></td>
<td align="left"><input type="text" name="email" size="40" maxlength="255" /></td>
</tr>
<tr>
<td width="200" style="text-align:right"><?php echo $hesklang['username']; ?>: <font class="important">*</font></td>
<td><input type="text" name="user" size="40" maxlength="20" /></td>
</tr>
<tr>
<td width="200" style="text-align:right"><?php echo $hesklang['pass']; ?>: <font class="important">*</font></td>
<td><input type="password" name="newpass" size="40" maxlength="20" /></td>
</tr>
<tr>
<td width="200" style="text-align:right"><?php echo $hesklang['confirm_pass']; ?>: <font class="important">*</font></td>
<td><input type="password" name="newpass2" size="40" maxlength="20" /></td>
</tr>
<tr>
<td valign="top" width="200" style="text-align:right"><?php echo $hesklang['administrator']; ?>: <font class="important">*</font></td>
<td valign="top">
	<p>
    <label><input type="radio" name="isadmin" value="1" onclick="Javascript:hesk_toggleLayerDisplay('options')" /> <?php echo $hesklang['yes'].' '.$hesklang['admin_can']; ?></label><br />
	<label><input type="radio" name="isadmin" value="0" onclick="Javascript:hesk_toggleLayerDisplay('options')" checked="checked" /> <?php echo $hesklang['no'].' '.$hesklang['staff_can']; ?></label>
    </p>

	<div id="options" style="display: block;">
    	<table width="100%" border="0">
		<tr>
		<td valign="top" width="100" style="text-align:right;white-space:nowrap;"><?php echo $hesklang['allowed_cat']; ?>: <font class="important">*</font></td>
		<td valign="top">
		<?php
		$sql_private = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC';
		$result = hesk_dbQuery($sql_private);

        $i = 1;
		while ($row=hesk_dbFetchAssoc($result))
		{
        	if ($i == 1)
            {
            	$selected='checked="checked"';
            }
            else
            {
            	$selected='';
            }
            $i++;
		    echo '<label><input type="checkbox" name="categories[]" value="'.$row['id'].'" '.$selected.' /> '.$row['name'].'</label><br />';
		}

		?>
        &nbsp;
		</td>
		</tr>
		<tr>
		<td valign="top" width="100" style="text-align:right;white-space:nowrap;"><?php echo $hesklang['allow_feat']; ?>: <font class="important">*</font></td>
		<td valign="top">
		<label><input type="checkbox" name="features[]" value="can_view_tickets" checked="checked" /><?php echo $hesklang['can_view_tickets']; ?><sup>1</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_reply_tickets" checked="checked" /><?php echo $hesklang['can_reply_tickets']; ?><sup>1</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_del_tickets" /><?php echo $hesklang['can_del_tickets']; ?><sup>1</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_edit_tickets" /><?php echo $hesklang['can_edit_tickets']; ?><sup>1</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_del_notes" /><?php echo $hesklang['can_del_notes']; ?><sup>1, 2</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_change_cat" checked="checked" /><?php echo $hesklang['can_change_cat']; ?><sup>1</sup></label><br />
        <label><input type="checkbox" name="features[]" value="can_man_kb" /><?php echo $hesklang['can_man_kb']; ?></label><br />
        <label><input type="checkbox" name="features[]" value="can_man_users" /><?php echo $hesklang['can_man_users']; ?></label><br />
        <label><input type="checkbox" name="features[]" value="can_man_cat" /><?php echo $hesklang['can_man_cat']; ?></label><br />
        <label><input type="checkbox" name="features[]" value="can_man_canned" /><?php echo $hesklang['can_man_canned']; ?></label><br />
        <label><input type="checkbox" name="features[]" value="can_man_settings" /><?php echo $hesklang['can_man_settings']; ?></label><br />
        <label><input type="checkbox" name="features[]" value="can_add_archive" /><?php echo $hesklang['can_add_archive']; ?></label><br />
		<sup>1</sup> <i><?php echo $hesklang['in_all_cat']; ?></i><br />
        <sup>2</sup> <i><?php echo $hesklang['dan']; ?></i><br />&nbsp;
		</td>
		</tr>
        </table>
    </div>

</td>
</tr>
<tr>
<td valign="top" width="200" style="text-align:right"><?php echo $hesklang['signature_max']; ?>:</td>
<td><textarea name="signature" rows="6" cols="40"></textarea><br />
<?php echo $hesklang['sign_extra']; ?></td>
</tr>
</table>

<!-- Submit -->
<p align="center"><input type="hidden" name="a" value="new" />
<input type="submit" value="<?php echo $hesklang['create_user']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>

</form>

<p>&nbsp;</p>

		<!-- END CONTENT -->

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

} // End else


/*** START FUNCTIONS ***/

function edit_user()
{
	global $hesk_settings, $hesklang;

	$id=hesk_isNumber($_GET['id'],"$hesklang[int_error]: $hesklang[no_valid_id]");

	$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `id`='.hesk_dbEscape($id).' LIMIT 1';
	$result = hesk_dbQuery($sql);
	$myuser=hesk_dbFetchAssoc($result);

    /* Print header */
	require_once(HESK_PATH . 'inc/header.inc.php');

	/* Print main manage users page */
	require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
	?>

	</td>
	</tr>
	<tr>
	<td>

	<p class="smaller">&nbsp;<a href="manage_users.php" class="smaller"><?php echo $hesklang['menu_users']; ?></a> &gt; <?php echo $hesklang['editing_user'].' '.$myuser['user']; ?></p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
			<td class="roundcornerstop"></td>
			<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
		</tr>
		<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>

	<h3 align="center"><?php echo $hesklang['editing_user'].' '.$myuser['user']; ?></h3>

	<p align="center"><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></p>

	<form method="post" action="manage_users.php">

	<!-- Contact info -->
	<table border="0" width="100%">
	<tr>
	<td width="200" style="text-align:right"><?php echo $hesklang['real_name']; ?>: <font class="important">*</font></td>
	<td><input type="text" name="name" size="40" maxlength="50" value="<?php echo $myuser['name']; ?>" /></td>
	</tr>
	<tr>
	<td width="200" style="text-align:right"><?php echo $hesklang['email']; ?>: <font class="important">*</font></td>
	<td><input type="text" name="email" size="40" maxlength="255" value="<?php echo $myuser['email']; ?>" /></td>
	</tr>
	<tr>
	<td width="200" style="text-align:right"><?php echo $hesklang['username']; ?>: <font class="important">*</font></td>
	<td><input type="text" name="user" size="40" maxlength="20" value="<?php echo $myuser['user']; ?>" /></td>
	</tr>
	<tr>
	<td width="200" style="text-align:right"><?php echo $hesklang['pass']; ?>: </td>
	<td><input type="password" name="newpass" size="40" maxlength="20" /></td>
	</tr>
	<tr>
	<td width="200" style="text-align:right"><?php echo $hesklang['confirm_pass']; ?>: </td>
	<td><input type="password" name="newpass2" size="40" maxlength="20" /></td>
	</tr>
	<tr>
	<td valign="top" width="200" style="text-align:right"><?php echo $hesklang['administrator']; ?>: <font class="important">*</font></td>
	<td valign="top">
		<p>
	    <label><input type="radio" name="isadmin" value="1" onclick="Javascript:hesk_toggleLayerDisplay('options')" <?php if($myuser['isadmin']) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['yes'].' '.$hesklang['admin_can']; ?></label><br />
		<label><input type="radio" name="isadmin" value="0" onclick="Javascript:hesk_toggleLayerDisplay('options')" <?php if(!$myuser['isadmin']) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['no'].' '.$hesklang['staff_can']; ?></label>
	    </p>

		<div id="options" style="display: <?php if($myuser['isadmin']) {echo 'none';} else {echo 'block';} ?>;">
	    	<table width="100%" border="0">
			<tr>
			<td valign="top" width="100" style="text-align:right;white-space:nowrap;"><?php echo $hesklang['allowed_cat']; ?>: <font class="important">*</font></td>
			<td valign="top">
			<?php
			$sql_private = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC';
			$result = hesk_dbQuery($sql_private);

			$cat=substr($myuser['categories'], 0, -1);
			$myuser['categories']=explode(',',$cat);

	        $i = 1;
			while ($row=hesk_dbFetchAssoc($result))
			{
	        	if ($myuser['isadmin'] && $i == 1)
	            {
	            	$selected='checked="checked"';
	                $myuser['heskprivileges']='can_view_tickets,can_reply_tickets,can_assign_tickets,can_change_cat,can_view_stats';
	            }
	            elseif (in_array($row['id'], $myuser['categories']))
	            {
	            	$selected='checked="checked"';
	            }
	            else
	            {
	            	$selected='';
	            }
	            $i++;
			    echo '<label><input type="checkbox" name="categories[]" value="'.$row['id'].'" '.$selected.' /> '.$row['name'].'</label><br />';
			}

			?>
	        &nbsp;
			</td>
			</tr>
			<tr>
			<td valign="top" width="100" style="text-align:right;white-space:nowrap;"><?php echo $hesklang['allow_feat']; ?>: <font class="important">*</font></td>
			<td valign="top">
			<label><input type="checkbox" name="features[]" value="can_view_tickets"   <?php if (strpos($myuser['heskprivileges'], 'can_view_tickets') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_view_tickets']; ?><sup>1</sup></label><br />
	        <label><input type="checkbox" name="features[]" value="can_reply_tickets"  <?php if (strpos($myuser['heskprivileges'], 'can_reply_tickets') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_reply_tickets']; ?><sup>1</sup></label><br />
	        <label><input type="checkbox" name="features[]" value="can_del_tickets"    <?php if (strpos($myuser['heskprivileges'], 'can_del_tickets') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_del_tickets']; ?><sup>1</sup></label><br />
	        <label><input type="checkbox" name="features[]" value="can_edit_tickets"   <?php if (strpos($myuser['heskprivileges'], 'can_edit_tickets') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_edit_tickets']; ?><sup>1</sup></label><br />
            <label><input type="checkbox" name="features[]" value="can_del_notes"	   <?php if (strpos($myuser['heskprivileges'], 'can_del_notes') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_del_notes']; ?><sup>1, 2</sup></label><br />
	        <label><input type="checkbox" name="features[]" value="can_change_cat"     <?php if (strpos($myuser['heskprivileges'], 'can_change_cat') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_change_cat']; ?><sup>1</sup></label><br />
	        <label><input type="checkbox" name="features[]" value="can_man_kb"         <?php if (strpos($myuser['heskprivileges'], 'can_man_kb') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_man_kb']; ?></label><br />
	        <label><input type="checkbox" name="features[]" value="can_man_users"      <?php if (strpos($myuser['heskprivileges'], 'can_man_users') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_man_users']; ?></label><br />
	        <label><input type="checkbox" name="features[]" value="can_man_cat"        <?php if (strpos($myuser['heskprivileges'], 'can_man_cat') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_man_cat']; ?></label><br />
	        <label><input type="checkbox" name="features[]" value="can_man_canned"     <?php if (strpos($myuser['heskprivileges'], 'can_man_canned') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_man_canned']; ?></label><br />
	        <label><input type="checkbox" name="features[]" value="can_man_settings"   <?php if (strpos($myuser['heskprivileges'], 'can_man_settings') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_man_settings']; ?></label><br />
	        <label><input type="checkbox" name="features[]" value="can_add_archive"    <?php if (strpos($myuser['heskprivileges'], 'can_add_archive') !== false) {echo 'checked="checked"';} ?> /><?php echo $hesklang['can_add_archive']; ?></label><br />
			<sup>1</sup> <i><?php echo $hesklang['in_all_cat']; ?></i><br />
			<sup>2</sup> <i><?php echo $hesklang['dan']; ?></i><br />&nbsp;
			</td>
			</tr>
	        </table>
	    </div>

	</td>
	</tr>
	<tr>
	<td style="text-align:right" valign="top" width="200"><?php echo $hesklang['signature_max']; ?>:</td>
	<td><textarea name="signature" rows="6" cols="40"><?php echo $myuser['signature']; ?></textarea><br />
	<?php echo $hesklang['sign_extra']; ?></td>
	</tr>
	</table>

	<!-- Submit -->
	<p align="center"><input type="hidden" name="a" value="save" />
	<input type="hidden" name="userid" value="<?php echo $myuser['id']; ?>" />
	<input type="submit" value="<?php echo $hesklang['save_changes']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>

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
} // End edit_user()


function new_user() {
	global $hesk_settings, $hesklang;

	$myuser = hesk_validateUserInfo();

	$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `user` = \''.hesk_dbEscape($myuser['user']).'\' LIMIT 1';
	$result = hesk_dbQuery($sql);
	if (hesk_dbNumRows($result) != 0)
	{
	    hesk_error($hesklang['duplicate_user']);
	}

    if ($myuser['isadmin'])
    {
		$sql = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (`user`,`pass`,`isadmin`,`name`,`email`,`signature`) VALUES (
        '".hesk_dbEscape($myuser['user'])."',
        '".hesk_dbEscape($myuser['pass'])."',
        '".hesk_dbEscape($myuser['isadmin'])."',
        '".hesk_dbEscape($myuser['name'])."',
        '".hesk_dbEscape($myuser['email'])."',
        '".hesk_dbEscape($myuser['signature'])."')";
    }
    else
    {
    	$sql = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (`user`,`pass`,`isadmin`,`name`,`email`,`signature`,`categories`,`heskprivileges`) VALUES (
        '".hesk_dbEscape($myuser['user'])."',
        '".hesk_dbEscape($myuser['pass'])."',
        '".hesk_dbEscape($myuser['isadmin'])."',
        '".hesk_dbEscape($myuser['name'])."',
        '".hesk_dbEscape($myuser['email'])."',
        '".hesk_dbEscape($myuser['signature'])."',
        '".hesk_dbEscape($myuser['categories'])."',
        '".hesk_dbEscape($myuser['heskprivileges'])."')";
    }

	$result = hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['user_added'];
	$_SESSION['HESK_MESSAGE'] = sprintf($hesklang['user_added_success'],$myuser['user'],$myuser['cleanpass']);
	header('Location: manage_users.php');
	exit();
} // End new_user()


function update_user() {
	global $hesk_settings, $hesklang;

	$myuser=hesk_validateUserInfo(0);
	$myuser['id']=hesk_isNumber($_POST['userid'],"$hesklang[int_error]: $hesklang[no_valid_id]");

	$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `user` = \''.hesk_dbEscape($myuser['user']).'\' LIMIT 1';
	$result = hesk_dbQuery($sql);
	if (hesk_dbNumRows($result) == 1)
	{
	    $olduser=hesk_dbFetchAssoc($result);
	    if ($olduser['id'] != $myuser['id'])
	    {
	        hesk_error($hesklang['duplicate_user']);
	    }
	}

    if ($myuser['isadmin'])
    {
        $myuser['categories']='';
        $myuser['heskprivileges']='';
    }
	$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET
    `user`='".hesk_dbEscape($myuser['user'])."',
    `name`='".hesk_dbEscape($myuser['name'])."',
    `email`='".hesk_dbEscape($myuser['email'])."',
    `signature`='".hesk_dbEscape($myuser['signature'])."',";
	if (isset($myuser['pass']))
	{
	    $sql .= "`pass`='".hesk_dbEscape($myuser['pass'])."',";
	}
	$sql .= "
    `categories`='".hesk_dbEscape($myuser['categories'])."',
    `isadmin`='".hesk_dbEscape($myuser['isadmin'])."',
    `heskprivileges`='".hesk_dbEscape($myuser['heskprivileges'])."' WHERE `id`=".hesk_dbEscape($myuser['id'])." LIMIT 1";
	$result = hesk_dbQuery($sql);

	$_SESSION['HESK_NOTICE']  = $hesklang['profile_updated'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['user_profile_updated_success'];
	header('Location: manage_users.php');
	exit();
} // End update_profile()


function hesk_validateUserInfo($pass_required = 1) {
	global $hesklang;

	$myuser['name']		 = hesk_input($_POST['name'],$hesklang['enter_real_name']);
	$myuser['email']	 = hesk_validateEmail($_POST['email'],$hesklang['enter_valid_email']);
	$myuser['user']		 = hesk_input($_POST['user'],$hesklang['enter_username']);
	$myuser['signature'] = hesk_input($_POST['signature']);
	$myuser['isadmin']	 = hesk_isNumber($_POST['isadmin'],"$hesklang[int_error]: no valid isadmin");

    /* If it's not admin at least one category and fature is required */
    $myuser['categories']		= '';
    $myuser['heskprivileges']	= '';

    if ($myuser['isadmin']==0)
    {
    	if (empty($_POST['categories']))
        {
			hesk_error($hesklang['asign_one_cat']);
        }

	    foreach ($_POST['categories'] as $cat)
	    {
			if ( !($cat = intval($cat)) )
            {
				continue;
            }

	        $myuser['categories'] .= intval($cat).',';
	    }

    	if (empty($_POST['features']))
        {
			hesk_error($hesklang['asign_one_feat']);
        }

        $possible_features = array(
        			'can_view_tickets','can_reply_tickets',
                    'can_del_tickets','can_edit_tickets',
                    'can_del_notes','can_change_cat',
                    'can_man_kb','can_man_users',
                    'can_man_cat','can_man_canned',
                    'can_man_settings','can_add_archive');

	    foreach ($_POST['features'] as $feat)
	    {
        	if ( !in_array($feat,$possible_features) )
            {
            	continue;
            }
	        $myuser['heskprivileges'] .= hesk_input($feat) .',';
	    }
	}

	if (strlen($myuser['signature'])>255)
    {
    	hesk_error($hesklang['signature_long']);
    }

	if ($pass_required || !empty($_POST['newpass']))
	{
	    $newpass  = hesk_PasswordSyntax($_POST['newpass'],$hesklang['password_not_valid']);
	    $newpass2 = hesk_input($_POST['newpass2'],$hesklang['confirm_user_pass']);
	    if ($newpass != $newpass2)
	    {
	        hesk_error($hesklang['passwords_not_same']);
	    }
	    $myuser['pass'] = hesk_Pass2Hash($newpass);
	    $myuser['cleanpass'] = $newpass;
	}

	return $myuser;

} // End hesk_validateUserInfo()


function remove() {
	global $hesk_settings, $hesklang;

	$myuser = hesk_isNumber($_GET['id'],$hesklang['no_valid_id']);
	if ($myuser == 1)
    {
    	hesk_error($hesklang['cant_del_admin']);
    }
	if ($myuser == $_SESSION['id'])
    {
    	hesk_error($hesklang['cant_del_own']);
    }

	$sql = 'DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `id`='.hesk_dbEscape($myuser).' LIMIT 1';
	$result = hesk_dbQuery($sql);
	if (hesk_dbAffectedRows() != 1)
    {
    	hesk_error("$hesklang[int_error]: $hesklang[user_not_found].");
    }

	$_SESSION['HESK_NOTICE']  = $hesklang['user_removed'];
	$_SESSION['HESK_MESSAGE'] = $hesklang['sel_user_removed'];
	header('Location: manage_users.php');
	exit();
} // End remove()

?>

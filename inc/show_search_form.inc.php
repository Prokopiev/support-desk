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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die($hesklang['attempt']);}

if (!isset($status))
{
	$status = 6;
}
?>

<div align="center">
<table border="0" width="100%" cellspacing="1" cellpadding="5">
<tr>
<td valign="top" width="50%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="height: 360px;">
		<tr>
			<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
			<td class="roundcornerstop"></td>
			<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
		</tr>
		<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td valign="top">
	        <form action="show_tickets.php" method="get">
			<h3 align="center"><?php echo $hesklang['show_tickets']; ?>:</h3>
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
			<tr>
			<td valign="top"><b><?php echo $hesklang['status']; ?></b>: &nbsp; </td>
			<td>
            	<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				<td width="50%"><label><input type="radio" name="status" value="0" <?php if ($status == 0) {echo 'checked="checked"';} ?> /><span class="open"> <?php echo $hesklang['open']; ?></span></label></td>
				<td width="50%"><label><input type="radio" name="status" value="1" <?php if ($status == 1) {echo 'checked="checked"';} ?> /><span class="waitingreply"> <?php echo $hesklang['wait_reply']; ?></span></label></td>
				</tr>
				<tr>
				<td width="50%"><label><input type="radio" name="status" value="2" <?php if ($status == 2) {echo 'checked="checked"';} ?> /><span class="replied"> <?php echo $hesklang['replied']; ?></span></label></td>
				<td width="50%"><label><input type="radio" name="status" value="6" <?php if ($status == 6) {echo 'checked="checked"';} ?> /><span class="replied"> <?php echo $hesklang['all_not_closed']; ?></span></label></td>
				</tr>
				<tr>
				<td width="50%"><label><input type="radio" name="status" value="3" <?php if ($status == 3) {echo 'checked="checked"';} ?> /><span class="resolved"> <?php echo $hesklang['closed']; ?></span></label></td>
				<td width="50%"><label><input type="radio" name="status" value="4" <?php if ($status == 4) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['any_status']; ?></label></td>
				</tr>
				</table>
            </td>
			</tr>
			<tr>
			<td valign="top"><b><?php echo $hesklang['sort_by']; ?></b>: &nbsp; </td>
			<td>
	            <table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				<td width="50%"><label><input type="radio" name="sort" value="priority" <?php if ($sort == 'priority') {echo 'checked="checked"';} ?> /> <?php echo $hesklang['priority']; ?></label></td>
				<td width="50%"><label><input type="radio" name="sort" value="lastchange" <?php if ($sort == 'lastchange') {echo 'checked="checked"';} ?> /> <?php echo $hesklang['last_update']; ?></label></td>
				</tr>
				<tr>
				<td width="50%"><label><input type="radio" name="sort" value="name" <?php if ($sort == 'name') {echo 'checked="checked"';} ?> /> <?php echo $hesklang['name']; ?></label></td>
				<td width="50%"><label><input type="radio" name="sort" value="subject" <?php if ($sort == 'subject') {echo 'checked="checked"';} ?> /> <?php echo $hesklang['subject']; ?></label></td>
				</tr>
				<tr>
				<td width="50%"><label><input type="radio" name="sort" value="status" <?php if ($sort == 'status') {echo 'checked="checked"';} ?> /> <?php echo $hesklang['status']; ?></label></td>
				<td width="50%">&nbsp;</td>
				</tr>
				</table>
            </td>
			</tr>
			<tr>
			<td valign="middle"><b><?php echo $hesklang['category']; ?></b>: &nbsp; </td>
			<td>
	            <select name="category">
				<option value="0" ><?php echo $hesklang['any_cat']; ?></option>
<?php
$sql_private = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` WHERE ';
$sql_private .= hesk_myCategories('id');
$sql_private .= ' ORDER BY `cat_order` ASC';

$result = hesk_dbQuery($sql_private);
while ($row=hesk_dbFetchAssoc($result))
{
    $selected = ($row['id'] == $category) ? 'selected="selected"' : '';
    echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
}
?>
				</select>
            </td>
			</tr>
			<tr>
			<td><b><?php echo $hesklang['order']; ?></b>: &nbsp; </td>
			<td>
            	<label><input type="radio" name="asc" value="1" <?php if ($asc) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['ascending']; ?></label>
                |
                <label><input type="radio" name="asc" value="0" <?php if (!$asc) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['descending']; ?></label></td>
			</tr>
			<tr>
			<td><b><?php echo $hesklang['display']; ?></b>: &nbsp; </td>
			<td><input type="text" name="limit" value="<?php echo $maxresults; ?>" size="4" /> <?php echo $hesklang['tickets_page']; ?></td>
			</tr>
			</table>
			<p><label><input type="checkbox" name="archive2" value="1" <?php if ($archive) echo 'checked="checked"'; ?> /> <?php echo $hesklang['disp_only_archived']; ?></label></p>
			<p align="center"><input type="submit" value="<?php echo $hesklang['show_tickets']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>
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
</td>
<?php
$what = isset($_GET['what']) ? hesk_input($_GET['what']) : 'trackid';
?>
<td valign="top" width="50%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="height: 360px;">
		<tr>
			<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
			<td class="roundcornerstop"></td>
			<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
		</tr>
		<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td valign="top">
	    <form action="find_tickets.php" method="get" name="findby" id="findby">
		<h3 align="center"><?php echo $hesklang['find_ticket_by']; ?>:</h3>
		<table border="0" cellpadding="3" cellspacing="0" width="100%">
		<tr>
		<td><label><input type="radio" name="what" value="trackid"
        <?php
        $value='';
        if ($what == 'trackid')
        {
	        echo 'checked="checked"';
			if (isset($_GET['trackid']))
	        {
		        $value = hesk_input($_GET['trackid']);
	        }
        }
        ?> /> <b><?php echo $hesklang['trackID']; ?></b></label>: &nbsp; </td>
		<td><input type="text" name="trackid" value="<?php echo $value; ?>" size="12" maxlength="10" onfocus="Javascript:document.findby.what[0].checked=true;" /></td>
		</tr>
		<tr>
		<td><label><input type="radio" name="what" value="name"
        <?php
        $value='';
        if ($what == 'name')
        {
	        echo 'checked="checked"';
			if (isset($_GET['name']))
	        {
		        $value = hesk_input($_GET['name']);
	        }
        }
        ?> /> <b><?php echo $hesklang['name']; ?></b></label>: &nbsp; </td>
		<td><input type="text" name="name" value="<?php echo $value; ?>" size="20" onfocus="Javascript:document.findby.what[1].checked=true;" /></td>
		</tr>
		<tr>
		<td><label><input type="radio" name="what" value="dt"
        <?php
        $value='';
        if ($what == 'dt')
        {
	        echo 'checked="checked"';
			if (isset($_GET['dt']))
	        {
		        $value = hesk_input($_GET['dt']);
	        }
        }
        ?> /> <b><?php echo $hesklang['date_posted']; ?></b></label>: &nbsp; </td>
		<td><input type="text" name="dt" value="<?php echo $value; ?>" size="12" maxlength="10" onfocus="Javascript:document.findby.what[2].checked=true;" /></td>
		</tr>
		<tr>
		<td><label><input type="radio" name="what" value="subject"
        <?php
        $value='';
        if ($what == 'subject')
        {
	        echo 'checked="checked"';
			if (isset($_GET['subject']))
	        {
		        $value = hesk_input($_GET['subject']);
	        }
        }
        ?> /> <b><?php echo $hesklang['subject']; ?></b></label>: &nbsp; </td>
		<td><input type="text" name="subject" value="<?php echo $value; ?>" size="25" onfocus="Javascript:document.findby.what[3].checked=true;" /></td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td><b><?php echo $hesklang['display']; ?></b>: &nbsp; </td>
		<td><input type="text" name="limit" value="<?php echo $maxresults; ?>" size="4" /> <?php echo $hesklang['results_page']; ?></td>
		</tr>
		</table>
		<p><label><input type="checkbox" name="archive" value="1" /> <?php echo $hesklang['search_only_archived']; ?></label></p>
		<p align="center"><input type="submit" value="<?php echo $hesklang['find_ticket']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');"  /></p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
		</form>
    </td>
	<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
	<td width="6"><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>
</td>
</tr>
</table>
</div>

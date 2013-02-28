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
require(HESK_PATH . 'memcached.config.php');

if (!isset($_REQUEST['a']))
{
	$_REQUEST['a'] = '';
}

/* Will we use the anti-SPAM image? */
if ($_REQUEST['a']=='add')
{
	hesk_session_start();
	if ($hesk_settings['secimg_use'])
	{
		$_SESSION['secnum']=rand(10000,99999);
		$_SESSION['checksum']=crypt($_SESSION['secnum'],$hesk_settings['secimg_sum']);
	}
    if (!isset($_SESSION['ARTICLES_SUGGESTED']))
    {
    	$_SESSION['ARTICLES_SUGGESTED']=false;
    }
}
else header('Location: /index.php?a=add');

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

/* What should we do? */
$action=hesk_input($_REQUEST['a']) or $action='start';
if ($action == 'start') {print_start();}
elseif ($action == 'add') {print_add_ticket();}
elseif ($action == 'forgot_tid') {forgot_tid();}
else {hesk_error($hesklang['invalid_action']);}

/* Print footer */
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

/*** START FUNCTIONS ***/

function print_add_ticket() {
global $hesk_settings, $hesklang, $memcached;
?>


<table cellspacing="0" cellpadding="0" border="0" align="center" style="position: relative; left: -60px; margin-top: 46px;">
		<tbody><tr>
			<td valign="top" style="padding-top: 20px;">
				<div><a href="http://<?=$memcached->get('outerHostName');?>"><img width="175" height="119" border="0" title="<?=$memcached->get('title');?> - <?=$memcached->get('siteName');?>" alt="<?=$memcached->get('title');?> - <?=$memcached->get('siteName');?>" src="<?=$memcached->get('logoRegistration');?>"></a></div>
			</td>
			<td width="40">&nbsp;</td>
			<td width="660" valign="top">
			
				
				
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td width="21">
							<img width="21" height="21" alt="" src="/img/regisration_02_03.gif"></td>
						<td style="background: url('/img/regisration_02_04.gif') repeat-x scroll 0% 0% transparent;">
							<img width="502" height="21" alt="" src="/img/spacer.gif"></td>
						<td>
							<img width="30" height="21" alt="" src="/img/regisration_02_05.gif"></td>
					</tr>
					<tr>
						<td style="background: url('/img/regisration_02_07.gif') repeat-y scroll 0% 0% transparent;">
							<img width="21" height="1" alt="" src="/img/spacer.gif"></td>
						<td valign="top" style="padding: 10px;" align="left">									
				
								
							
				
							<div width="224" style="font-size: 22px; color: black;">Запрос в <a href="/" style="font-size: 22px; color: black;">техническую поддержку <?=$memcached->get('siteName');?></a>:</div><br><br>

				
							
				
							
								<?php
								    if(isset($_SESSION['HESK_ERROR']))
							        {
								?>
								        <div align="center">
								        <table border="0" width="600" id="error" cellspacing="0" cellpadding="3">
									        <tr>
									        	<td align="left" class="error_header">&nbsp;<img src="img/error.gif" style="vertical-align:text-bottom" width="16" height="16" alt="" />&nbsp; <?php echo $hesklang['error']; ?></td>
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
				
							    <!-- START FORM -->
							
								<p><?php echo $hesklang['use_form_below']; ?>
								<font class="important"> *</font></p>
							
								<form method="post" action="submit_ticket.php" name="form1" enctype="multipart/form-data"
								<?php
								if ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers'] && $_SESSION['ARTICLES_SUGGESTED'] == false)
								{
									echo 'onsubmit="Javascript: return hesk_suggestKB();"';
								}
								?>
							    >
								
								<!-- Department and priority -->
								<table border="0" width="100%">
								<tr>
								<td width="150"><?php echo $hesklang['category']; ?>: <font class="important">*</font></td>
								<td width="80%"><select name="category">
								<?php
								require(HESK_PATH . 'inc/database.inc.php');
							
								hesk_dbConnect();
								$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC';
								$result = hesk_dbQuery($sql);
								while ($row=hesk_dbFetchAssoc($result))
								{
									//if ($row['id']==1) continue;
								    if (isset($_SESSION['c_category']) && $_SESSION['c_category'] == $row['id']) {$selected = ' selected="selected"';}
								    else {$selected = '';}
								    echo '<option value="'.$row['id'].'"'.$selected.'>'.$row['name'].'</option>';
								}
							
								?>
								</select></td>
								</tr>
								<tr style="display: none">
								<td width="150"><?php echo $hesklang['priority']; ?>: <font class="important">*</font></td>
								<td width="80%"><select name="priority">
								<option value="3" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==3) {echo 'selected="selected"';} ?>><?php echo $hesklang['low']; ?></option>
								<option value="2" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==2) {echo 'selected="selected"';} ?>><?php echo $hesklang['medium']; ?></option>
								<option value="1" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==1) {echo 'selected="selected"';} ?>><?php echo $hesklang['high']; ?></option>
								</select></td>
								</tr>
								</table>
							
								<!-- ticket info -->
								<table border="0" width="100%">
								<tr>
								<td width="150"><?php echo $hesklang['subject']; ?>: <font class="important">*</font></td>
								<td width="80%"><input type="text" name="subject" size="40" maxlength="40" value="<?php if (isset($_SESSION['c_subject'])) {echo stripslashes(hesk_input($_SESSION['c_subject']));} ?>" /></td>
								</tr>
								<tr>
								<td width="150" valign="top"><?php echo $hesklang['message']; ?>: <font class="important">*</font></td>
								<td width="80%"><textarea name="message" rows="12" cols="40"><?php if (isset($_SESSION['c_message'])) {echo stripslashes(hesk_input($_SESSION['c_message']));} ?></textarea></td>
								</tr>
								</table>	
							
								
								
								
								
								<?php
								/* attachments */
								if ($hesk_settings['attachments']['use']) {
							
								?>
								<table border="0" width="100%">
								<tr>
								<td width="150" valign="top"><?php echo $hesklang['attachments']; ?>:</td>
								<td width="80%" valign="top">
								<?php
								for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
							    {
									echo '<input type="file" name="attachment['.$i.']" size="50" /><br />';
								}
								?>
								<!--
								<?php echo$hesklang['accepted_types']; ?>: <?php echo '*'.implode(', *', $hesk_settings['attachments']['allowed_types']); ?><br />
								-->
								<?php echo $hesklang['max_file_size']; ?>: <?php echo $hesk_settings['attachments']['max_size']; ?> Kb
								(<?php echo sprintf("%01.2f",($hesk_settings['attachments']['max_size']/1024)); ?> Mb)
								</td>
								</tr>
								</table>
								<br>
							
							
								<?php
								}
								?>	
								
								
								
								
							
								<!-- Contact info -->
								<table border="0" width="100%">
								<tr>
								<td width="150"><?php echo $hesklang['name']; ?>: <font class="important">*</font></td>
								<td width="80%"><input type="text" name="name" size="40" maxlength="30" value="<?php if (isset($_SESSION['c_name'])) {echo stripslashes(hesk_input($_SESSION['c_name']));} ?>" /></td>
								</tr>
								<tr>
								<td width="150"><?php echo $hesklang['email']; ?>: <font class="important">*</font></td>
								<td width="80%"><input type="text" name="email" size="40" maxlength="50" value="<?php if (isset($_SESSION['c_email'])) {echo stripslashes(hesk_input($_SESSION['c_email']));} ?>" /></td>
								</tr>
								</table>
								
							
							
							
							
							
								<!-- START CUSTOM BEFORE -->
								<?php
								/* custom fields BEFORE comments */
							
								$print_table = 0;
							
								foreach ($hesk_settings['custom_fields'] as $k=>$v)
								{
									if ($v['use'] && $v['place']==0)
								    {
								    	if ($print_table == 0)
								        {
								        	echo '<table border="0" width="100%">';
								        	$print_table = 1;
								        }
							
										$v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
							
										if ($v['type'] == 'checkbox')
							            {
							            	$k_value = array();
							                if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"]))
							                {
								                foreach ($_SESSION["c_$k"] as $myCB)
								                {
								                	$k_value[] = stripslashes(hesk_input($myCB));
								                }
							                }
							            }
							            elseif (isset($_SESSION["c_$k"]))
							            {
							            	$k_value  = stripslashes(hesk_input($_SESSION["c_$k"]));
							            }
							            else
							            {
							            	$k_value  = '';
							            }
							
								        switch ($v['type'])
								        {
								        	/* Radio box */
								        	case 'radio':
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (strlen($k_value) == 0 || $k_value == $option)
									                {
								                    	$k_value = $option;
														$checked = 'checked="checked"';
								                    }
								                    else
								                    {
								                    	$checked = '';
								                    }
							
								                	echo '<label><input type="radio" name="'.$k.'" value="'.$option.'" '.$checked.' /> '.$option.'</label><br />';
								                }
							
								                echo '</td>
												</tr>
												';
								            break;
							
								            /* Select drop-down box */
								            case 'select':
												echo '
												<tr>
												<td style="text-align:right" width="150">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%"><select name="'.$k.'">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (strlen($k_value) == 0 || $k_value == $option)
									                {
								                    	$k_value = $option;
								                        $selected = 'selected="selected"';
									                }
								                    else
								                    {
								                    	$selected = '';
								                    }
							
								                	echo '<option '.$selected.'>'.$option.'</option>';
								                }
							
								                echo '</select></td>
												</tr>
												';
								            break;
							
								            /* Checkbox */
								        	case 'checkbox':
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (in_array($option,$k_value))
									                {
														$checked = 'checked="checked"';
								                    }
								                    else
								                    {
								                    	$checked = '';
								                    }
							
								                	echo '<label><input type="checkbox" name="'.$k.'[]" value="'.$option.'" '.$checked.' /> '.$option.'</label><br />';
								                }
							
								                echo '</td>
												</tr>
												';
								            break;
							
								            /* Large text box */
								            case 'textarea':
								                $size = explode('#',$v['value']);
							
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
												<td width="80%"><textarea name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'">'.$k_value.'</textarea></td>
												</tr>
								                ';
								            break;
							
								            /* Default text input */
								            default:
							                	if (strlen($k_value) != 0)
							                    {
							                    	$v['value'] = $k_value;
							                    }
												echo '
												<tr>
												<td width="150">'.$v['name'].': '.$v['req'].'</td>
												<td width="80%"><input type="text" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" /></td>
												</tr>
												';
								        }
								    }
								}
							
								/* If table was started we need to close it */
								if ($print_table)
								{
									echo '</table> <hr />';
									$print_table = 0;
								}
								?>
								<!-- END CUSTOM BEFORE -->
							
														
								<!-- START CUSTOM AFTER -->
								<?php
								/* custom fields AFTER comments */
								$print_table = 0;
							
								foreach ($hesk_settings['custom_fields'] as $k=>$v)
								{
									if ($v['use'] && $v['place'])
								    {
								    	if ($print_table == 0)
								        {
								        	echo '<table border="0" width="100%">';
								        	$print_table = 1;
								        }
							
										$v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
							
										if ($v['type'] == 'checkbox')
							            {
							            	$k_value = array();
							                if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"]))
							                {
								                foreach ($_SESSION["c_$k"] as $myCB)
								                {
								                	$k_value[] = stripslashes(hesk_input($myCB));
								                }
							                }
							            }
							            elseif (isset($_SESSION["c_$k"]))
							            {
							            	$k_value  = stripslashes(hesk_input($_SESSION["c_$k"]));
							            }
							            else
							            {
							            	$k_value  = '';
							            }
							
							
								        switch ($v['type'])
								        {
								        	/* Radio box */
								        	case 'radio':
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (strlen($k_value) == 0 || $k_value == $option)
									                {
								                    	$k_value = $option;
														$checked = 'checked="checked"';
								                    }
								                    else
								                    {
								                    	$checked = '';
								                    }
							
								                	echo '<label><input type="radio" name="'.$k.'" value="'.$option.'" '.$checked.' /> '.$option.'</label><br />';
								                }
							
								                echo '</td>
												</tr>
												';
								            break;
							
								            /* Select drop-down box */
								            case 'select':
												echo '
												<tr>
												<td style="text-align:right" width="150">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%"><select name="'.$k.'">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (strlen($k_value) == 0 || $k_value == $option)
									                {
								                    	$k_value = $option;
								                        $selected = 'selected="selected"';
									                }
								                    else
								                    {
								                    	$selected = '';
								                    }
							
								                	echo '<option '.$selected.'>'.$option.'</option>';
								                }
							
								                echo '</select></td>
												</tr>
												';
								            break;
							
								            /* Checkbox */
								        	case 'checkbox':
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
								                <td width="80%">';
							
								            	$options = explode('#HESK#',$v['value']);
							
								                foreach ($options as $option)
								                {
							
									            	if (in_array($option,$k_value))
									                {
														$checked = 'checked="checked"';
								                    }
								                    else
								                    {
								                    	$checked = '';
								                    }
							
								                	echo '<label><input type="checkbox" name="'.$k.'[]" value="'.$option.'" '.$checked.' /> '.$option.'</label><br />';
								                }
							
								                echo '</td>
												</tr>
												';
								            break;
							
								            /* Large text box */
								            case 'textarea':
								                $size = explode('#',$v['value']);
							
												echo '
												<tr>
												<td style="text-align:right" width="150" valign="top">'.$v['name'].': '.$v['req'].'</td>
												<td width="80%"><textarea name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'">'.$k_value.'</textarea></td>
												</tr>
								                ';
								            break;
							
								            /* Default text input */
								            default:
							                	if (strlen($k_value) != 0)
							                    {
							                    	$v['value'] = $k_value;
							                    }                
												echo '
												<tr>
												<td style="text-align:right" width="150">'.$v['name'].': '.$v['req'].'</td>
												<td width="80%"><input type="text" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" /></td>
												</tr>
												';
								        }
								    }
								}
							
								/* If table was started we need to close it */
								if ($print_table)
								{
									echo '</table> <hr />';
									$print_table = 0;
								}
								?>
								<!-- END CUSTOM AFTER -->
							
							
								
								<?
								if ($hesk_settings['question_use'] || $hesk_settings['secimg_use'])
							    {
								?>
									<!-- Security checks -->
									<div align="center">
									<table border="0">
									<tr>
									<td>                            
							
									<?php
									if ($hesk_settings['question_use'])
								    {
							        	$value = '';
							        	if (isset($_SESSION['c_question']))
							            {
								        	$value = stripslashes(hesk_input($_SESSION['c_question']));
							            }
									    echo '<p>'.$hesk_settings['question_ask'].' <font class="important">*</font><br /><input type="text" name="question" size="10" value="'.$value.'"  /></p>';
									}
							
									if ($hesk_settings['secimg_use'])
								    {
									    echo '<p><img src="print_sec_img.php?'.rand(10000,99999).'" width="150" height="40" alt="'.$hesklang['sec_img'].'" border="1" /><br />'.
									    $hesklang['sec_enter'].': <font class="important">*</font> <input type="text" name="mysecnum" size="10" maxlength="5" /></p>';
									}
									?>
							
								    </td>
									</tr>
									</table>
									</div>
							
							        <hr />
							    <?php
							    }
								?>
							
								<!-- Submit -->
								<div align="center">
								<table border="0">
								<tr>
								<td>
							<!-- 
							    <b><?php echo $hesklang['before_submit']; ?></b>
							    <ul>
							    <li><?php echo $hesklang['all_info_in']; ?>.</li>
								<li><?php echo $hesklang['all_error_free']; ?>.</li>
							    </ul>
							
							
								<b><?php echo $hesklang['we_have']; ?>:</b>
							    <ul>
							    <li><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']).' '.$hesklang['recorded_ip']; ?></li>
								<li><?php echo $hesklang['recorded_time']; ?></li>
								</ul>
								 -->
								<!--<p align="center"><input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" class="orangebutton"  onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>-->
								
<table cellspacing="0" cellpadding="0" bgcolor="#ffca00" align="center" style="margin-top: 10px;">
	<tbody><tr>
		<td width="10"><img alt="" src="/img/l.gif"></td>
		<td style="background: url(&quot;/img/bb.gif&quot;) repeat-x scroll 0% 0% transparent;"><input type="submit" style="width: 167px; height: 32px; color: rgb(87, 66, 0); padding-bottom: 5px; font-size: 14px; font-weight: bold;" value="Отправить заявку"></td>
		<td width="10"><img alt="" src="/img/r.gif"></td>
	</tr>
</tbody></table>								
								
							
							    </td>
								</tr>
								</table>
								</div>
							
								</form>
							
							    <!-- END FORM -->
				
				
						</td>
						<td style="background: url('/img/regisration_02_09.gif') repeat-y scroll 0% 0% transparent;">
							<img width="30" height="1" alt="" src="/img/spacer.gif"></td>
					</tr>
					<tr>
						<td width="21">
							<img width="21" height="27" alt="" src="/img/regisration_02_10.gif"></td>
						<td style="background: url('/img/regisration_02_11.gif') repeat-x scroll 0% 0% transparent;">
							<img width="502" height="27" alt="" src="/img/spacer.gif"></td>
						<td>
							<img width="30" height="27" alt="" src="/img/regisration_02_12.gif"></td>
					</tr>
				</tbody></table>

			</td>
		</tr>
	</tbody></table>


<?php
} // End print_add_ticket()


function print_start() {
global $hesk_settings, $hesklang;
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="3"><img src="img/headerleftsm.jpg" width="3" height="25" alt="" /></td>
<td class="headersm"><?php hesk_showTopBar($hesk_settings['hesk_title']); ?></td>
<td width="3"><img src="img/headerrightsm.jpg" width="3" height="25" alt="" /></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr>
<td><span class="smaller"><a href="<?php echo $hesk_settings['site_url']; ?>" class="smaller"><?php echo $hesk_settings['site_title']; ?></a> &gt;
<?php echo $hesk_settings['hesk_title']; ?></span></td>
<?php
if ($hesk_settings['kb_enable'] && $hesk_settings['kb_search'])
{
	echo '
	<td style="text-align:right" valign="top" width="300">
    <div style="display: inline;">
        <form action="knowledgebase.php" method="get" style="display: inline; margin: 0;">
		<input type="text" name="search" size="20" />
		<input type="submit" value="'.$hesklang['search'].'" class="greenbutton" onmouseover="hesk_btn(this,\'greenbuttonover\');" onmouseout="hesk_btn(this,\'greenbutton\');" />
		</form>
	</div>
	</td>
    ';
}
?>
</tr>
</table>

</td>
</tr>
<tr>
<td>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td width="50%">
<!-- START SUBMIT -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>
	    <table width="100%" border="0" cellspacing="0" cellpadding="0">
	    <tr>
	    	<td width="1"><img src="img/newticket.png" alt="" width="60" height="60" /></td>
	        <td>
	        <p><b><a href="index.php?a=add"><?php echo $hesklang['sub_support']; ?></a></b><br />
            <?php echo $hesklang['open_ticket']; ?></p>
	        </td>
	    </tr>
	    </table>
		</td>
		<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
		<td><img src="img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornersbottom"></td>
		<td width="7" height="7"><img src="img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>
<!-- END SUBMIT -->
</td>
<td width="1"><img src="img/blank.gif" width="5" height="1" alt="" /></td>
<td width="50%">
<!-- START VIEW -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>
	    <table width="100%" border="0" cellspacing="0" cellpadding="0">
	    <tr>
	    	<td width="1"><img src="img/existingticket.png" alt="" width="60" height="60" /></td>
	        <td>
	        <p><b><a href="ticket.php"><?php echo $hesklang['view_existing']; ?></a></b><br />
            <?php echo $hesklang['vet']; ?></p>
	        </td>
	    </tr>
	    </table>
		</td>
		<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
		<td><img src="img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornersbottom"></td>
		<td width="7" height="7"><img src="img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>
<!-- END VIEW -->
</td>
</tr>
</table>

<?php
if ($hesk_settings['kb_enable'])
{
	require(HESK_PATH . 'inc/database.inc.php');
    hesk_dbConnect();
?>
	<br />

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>

        <p><span class="homepageh3"><?php echo $hesklang['kb_text']; ?></span></p>

    <?php
	if ($hesk_settings['kb_index_popart'])
	{
		?>
        <table border="0" width="100%">
        <tr>
        <td>&raquo; <i><?php echo $hesklang['popart']; ?></i></td>
        <td style="text-align:right"><i><?php echo $hesklang['views']; ?></i></td>
        </tr>
        </table>
		<?php
		$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_articles` WHERE `type`=\'0\' ORDER BY `views` DESC, `art_order` ASC LIMIT '.hesk_dbEscape($hesk_settings['kb_index_popart']);
		$res = hesk_dbQuery($sql);
		if (hesk_dbNumRows($res) == 0)
		{
			echo '<p><i>'.$hesklang['noa'].'</i></p>';
		}
	    else
	    {
			echo '<div align="center"><table border="0" cellspacing="1" cellpadding="3" width="100%">';
			while ($article = hesk_dbFetchAssoc($res))
			{
				echo '
				<tr>
				<td>
	                <table border="0" width="100%" cellspacing="0" cellpadding="0">
	                <tr>
	                <td width="1" valign="top"><img src="img/article_text.png" width="16" height="16" border="0" alt="" style="vertical-align:middle" /></td>
	                <td valign="top">&nbsp;<a href="knowledgebase.php?article='.$article['id'].'">'.$article['subject'].'</a></td>
                    <td valign="top" style="text-align:right" width="200">'.$article['views'].'</td>
                    </tr>
	                </table>
	            </td>
				</tr>';
			}
		    echo '</table></div>';
	    }
	}


	if ($hesk_settings['kb_index_latest'])
	{
		?>
		&nbsp;
        <table border="0" width="100%">
        <tr>
        <td>&raquo; <i><?php echo $hesklang['latart']; ?></i></td>
        <td style="text-align:right"><i><?php echo $hesklang['dta']; ?></i></td>
        </tr>
        </table>
		<?php
		$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_articles` WHERE `type`=\'0\' ORDER BY `dt` DESC LIMIT '.hesk_dbEscape($hesk_settings['kb_index_latest']);
		$res = hesk_dbQuery($sql);
		if (hesk_dbNumRows($res) == 0)
		{
			echo '<p><i>'.$hesklang['noa'].'</i></p>';
		}
	    else
	    {
			echo '<div align="center"><table border="0" cellspacing="1" cellpadding="3" width="100%">';
			while ($article = hesk_dbFetchAssoc($res))
			{
				echo '
				<tr>
				<td>
	                <table border="0" width="100%" cellspacing="0" cellpadding="0">
	                <tr>
	                <td width="1" valign="top"><img src="img/article_text.png" width="16" height="16" border="0" alt="" style="vertical-align:middle" /></td>
	                <td valign="top">&nbsp;<a href="knowledgebase.php?article='.$article['id'].'">'.$article['subject'].'</a></td>
                    <td valign="top" style="text-align:right" width="200">'.hesk_date($article['dt']).'</td>
                    </tr>
	                </table>
	            </td>
				</tr>';
			}
		    echo '</table></div>';
	    }
	}

    ?>

        <p>&raquo; <b><a href="knowledgebase.php"><?php echo $hesklang['viewkb']; ?></a></b></p>

		</td>
		<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
		<td><img src="img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornersbottom"></td>
		<td width="7" height="7"><img src="img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>

    <br />
	<?php
	}

	/* Show a link to admin panel? */
    if ($hesk_settings['alink'])
    {
	    ?>
	    <p style="text-align:center"><a href="admin/" class="smaller"><?php echo $hesklang['ap']; ?></a></p>
	    <?php
    }

} // End print_start()


function forgot_tid() {
global $hesk_settings, $hesklang;

if (!isset($_POST['email']))
{
	hesk_error($hesklang['enter_valid_email']);
}

$email=hesk_validateEmail($_POST['email'],$hesklang['enter_valid_email']);

/* Prepare ticket statuses */
$my_status = array(
    0 => $hesklang['open'],
    1 => $hesklang['wait_staff_reply'],
    2 => $hesklang['wait_cust_reply'],
    3 => $hesklang['closed']
);

/* Get ticket(s) from database */
require(HESK_PATH . 'inc/database.inc.php');
hesk_dbConnect();

$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE `email` LIKE \''.hesk_dbEscape($email).'\'';
$result = hesk_dbQuery($sql);
$num=hesk_dbNumRows($result);
if ($num < 1)
{
    hesk_error($hesklang['tid_not_found']);
}

$tid_list='';
$name='';
while ($my_ticket=hesk_dbFetchAssoc($result))
{
$name = $name ? $name : $my_ticket['name'];
$tid_list .= "
$hesklang[trackID]: $my_ticket[trackid]
$hesklang[subject]: $my_ticket[subject]
$hesklang[status]: ".$my_status[$my_ticket['status']]."
$hesk_settings[hesk_url]/ticket.php?track=$my_ticket[trackid]
";
}

/* Get e-mail message for customer */
$msg = hesk_getEmailMessage('forgot_ticket_id');
$msg = str_replace('%%NAME%%',$name,$msg);
$msg = str_replace('%%NUM%%',$num,$msg);
$msg = str_replace('%%LIST_TICKETS%%',$tid_list,$msg);
$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
$msg = str_replace('%%SITE_URL%%',$hesk_settings['site_url'],$msg);

/* Send e-mail */
$headers = "From: $hesk_settings[noreply_mail]\n";
$headers.= "Reply-to: $hesk_settings[noreply_mail]\n";
$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
$headers.= "Content-type: text/plain; charset=".$hesklang['ENCODING'];
@mail($email,$hesklang['tid_email_subject'],$msg,$headers);

?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="3"><img src="img/headerleftsm.jpg" width="3" height="25" alt="" /></td>
<td class="headersm"><?php hesk_showTopBar($hesklang['tid_sent']); ?></td>
<td width="3"><img src="img/headerrightsm.jpg" width="3" height="25" alt="" /></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr>
<td><span class="smaller"><a href="<?php echo $hesk_settings['site_url']; ?>" class="smaller"><?php echo $hesk_settings['site_title']; ?></a> &gt;
<a href="<?php echo $hesk_settings['hesk_url']; ?>" class="smaller"><?php echo $hesk_settings['hesk_title']; ?></a>
&gt; <?php echo $hesklang['tid_sent']; ?></span></td>
</tr>
</table>

</td>
</tr>
<tr>
<td>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="7" height="7"><img src="img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornerstop"></td>
	<td><img src="img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
</tr>
<tr>
	<td class="roundcornersleft">&nbsp;</td>
	<td>

	<p>&nbsp;</p>
	<p align="center"><?php echo $hesklang['tid_sent2']; ?></p>
	<p align="center"><b><?php echo $hesklang['check_spambox']; ?></b></p>
	<p>&nbsp;</p>
	<p align="center"><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></p>
	<p>&nbsp;</p>

	</td>
	<td class="roundcornersright">&nbsp;</td>
</tr>
<tr>
	<td><img src="img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
	<td class="roundcornersbottom"></td>
	<td width="7" height="7"><img src="img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
</tr>
</table>

<?php
} // End forgot_tid()

?>

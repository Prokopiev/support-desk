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

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE ';

if (!empty($_GET['archive']))
{
	$archive=1;
    $sql .= "`archive`='1' AND ";
}
else
{
	$archive=0;
}

$sql .= hesk_myCategories();
$sql .= " AND ";

/* Get all the SQL sorting preferences */
if (!isset($_GET['what']))
{
	hesk_error($hesklang['wsel']);
}
$what = hesk_input($_GET['what'],$hesklang['wsel']);

switch ($what)
{
	case 'trackid':
	    $extra = hesk_input($_GET['trackid'],$hesklang['enter_id']);
	    $sql  .= "`trackid` = '".hesk_dbEscape($extra)."' ";
	    break;
	case 'name':
	    $extra = hesk_input($_GET['name'],$hesklang['enter_name']);
	    $sql  .= "`name` LIKE '%".hesk_dbEscape($extra)."%' ";
	    break;
	case 'dt':
	    $extra = hesk_input($_GET['dt'],$hesklang['enter_date']);
	    if (!preg_match("/\d{4}-\d{2}-\d{2}/",$extra))
	    {
	    	hesk_error($hesklang['date_not_valid']);
	    }
	    $sql .= "(`dt` LIKE '".hesk_dbEscape($extra)."%' OR `lastchange` LIKE '".hesk_dbEscape($extra)."%')";
	    break;
	case 'subject':
	    $extra = hesk_input($_GET['subject'],$hesklang['enter_subject']);
	    $sql  .= "`subject` LIKE '%".hesk_dbEscape($extra)."%' ";
	    break;
	default:
	    hesk_error($hesklang['invalid_search']);
}

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

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
		        	<td align="left" class="error_body"><?php echo $_SESSION['HESK_MESSAGE']; ?></td>
		        </tr>
	        </table>
	        </div>
	<?php
	        unset($_SESSION['HESK_ERROR']);
	        unset($_SESSION['HESK_MESSAGE']);
	    }
	?>

<h3 align="center"><?php echo $hesklang['tickets_found']; ?></h3>

<?php
$tmp = (isset($_GET['limit'])) ? intval($_GET['limit']) : 0;
$maxresults = ($tmp > 0) ? $tmp : $hesk_settings['max_listings'];

$tmp  = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$page = ($tmp > 1) ? $tmp : 1;

$sort_possible = array('trackid','lastchange','name','subject','status','lastreplier','priority','category','dt','id');

if (isset($_GET['sort']) && in_array($_GET['sort'],$sort_possible))
{
	$sort = hesk_input($_GET['sort']);
    $sql .= ' ORDER BY `'.hesk_dbEscape($sort).'` ';
}
else
{
    $sql .= ' ORDER BY `status` ASC, `priority`';
    $sort = 'status';
}

if (isset($_GET['asc']) && $_GET['asc']==0)
{
    $sql .= ' DESC ';
    $asc = 0;
    $asc_rev = 1;
}
else
{
    $sql .= ' ASC ';
    $asc = 1;
    $asc_rev = 0;
    if (!isset($_GET['asc']))
    {
    	$is_default = 1;
    }
}

$query = "what=$what&amp;trackid=$extra&amp;name=$extra&amp;date=$extra&amp;subject=$extra&amp;limit=$maxresults&amp;archive=$archive&amp;asc=$asc&amp;sort=$sort&amp;page=";

/* Get number of tickets and page number */
$result = hesk_dbQuery($sql);
$total  = hesk_dbNumRows($result);

if ($total > 0)
{
    $pages = ceil($total/$maxresults) or $pages = 1;
    if ($page > $pages)
    {
        $page = $pages;
    }
    $limit_down = ($page * $maxresults) - $maxresults;

    $prev_page = ($page - 1 <= 0) ? 0 : $page - 1;
    $next_page = ($page + 1 > $pages) ? 0 : $page + 1;

    if ($pages > 1)
    {
        echo '<p align="center">'.sprintf($hesklang['tickets_on_pages'],$total,$pages).' '.$hesklang['jump_page'].' <select name="myHpage" id="myHpage">';
        for ($i=1;$i<=$pages;$i++)
        {
            echo '<option value="'.$i.'">'.$i.'</option>';
        }
        echo'</select> <input type="button" value="'.$hesklang['go'].'" onclick="javascript:window.location=\'find_tickets.php?'.$query.'\'+document.getElementById(\'myHpage\').value" class="orangebutton" onmouseover="hesk_btn(this,\'orangebuttonover\');" onmouseout="hesk_btn(this,\'orangebutton\');" /><br />';

        /* List pages */
        if ($pages > 7)
        {
            if ($page > 2)
            {
                echo '<a href="find_tickets.php?'.$query.'1"><b>&laquo;</b></a> &nbsp; ';
            }

            if ($prev_page)
            {
                echo '<a href="find_tickets.php?'.$query.$prev_page.'"><b>&lsaquo;</b></a> &nbsp; ';
            }
        }

        for ($i=1; $i<=$pages; $i++)
        {
            if ($i <= ($page+5) && $i >= ($page-5))
            {
                if ($i == $page)
                {
                    echo ' <b>'.$i.'</b> ';
                }
                else
                {
                    echo ' <a href="find_tickets.php?'.$query.$i.'">'.$i.'</a> ';
                }
            }
        }

        if ($pages > 7)
        {
            if ($next_page)
            {
                echo ' &nbsp; <a href="find_tickets.php?'.$query.$next_page.'"><b>&rsaquo;</b></a> ';
            }

            if ($page < ($pages - 1))
            {
                echo ' &nbsp; <a href="find_tickets.php?'.$query.$pages.'"><b>&raquo;</b></a>';
            }
        }

        echo '</p>';

    } // end PAGES > 1
    else
    {
        echo '<p align="center">'.sprintf($hesklang['tickets_on_pages'],$total,$pages).' </p>';
    }

	/* We have the full SQL query now, get tickets */
	$sql .= " LIMIT ".hesk_dbEscape($limit_down)." , ".hesk_dbEscape($maxresults)." ";
	$result = hesk_dbQuery($sql);

	$query = "what=$what&amp;trackid=$extra&amp;name=$extra&amp;date=$extra&amp;subject=$extra&amp;limit=$maxresults&amp;archive=$archive&amp;page=1&amp;asc=" . (isset($is_default) ? 1 : $asc_rev) . "&amp;sort=";

	/* Print the table with tickets */
	$random=rand(10000,99999);
	?>

	<form name="form1" action="delete_tickets.php" method="post">

	    <div align="center">
	    <table border="0" width="100%" cellspacing="1" cellpadding="3" class="white">
	    <tr>
	    <th class="admin_white"><input type="checkbox" name="checkall" value="2" onclick="hesk_changeAll()" /></th>
	    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>trackid"><?php echo $hesklang['trackID']; ?></a></th>
	    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>lastchange"><?php echo $hesklang['last_update']; ?></a></th>
	    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>name"><?php echo $hesklang['name']; ?></a></th>
	    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>subject"><?php echo $hesklang['subject']; ?></a></th>
	    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>status"><?php echo $hesklang['status']; ?></a></th>
	    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>lastreplier"><?php echo $hesklang['last_replier']; ?></a></th>
	    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="find_tickets.php?<?php echo $query; ?>priority"><img src="../img/sort_priority_<?php echo (($asc) ? 'asc' : 'desc'); ?>.png" width="16" height="16" alt="<?php echo $hesklang['sort_by'].' '.$hesklang['priority']; ?>" title="<?php echo $hesklang['sort_by'].' '.$hesklang['priority']; ?>" border="0" /></a></th>
	    <!--
	    <th class="admin_white" align="center"><a href="find_tickets.php?<?php echo $query; ?>archive"><?php echo $hesklang['archived']; ?></a></th>
	    -->
	    </tr>

	<?php
	while ($ticket=hesk_dbFetchAssoc($result))
	{
	    if ($i) {$color="admin_gray"; $i=0;}
	    else {$color="admin_white"; $i=1;}

	    switch ($ticket['status'])
	    {
		    case 0:
		        $ticket['status']='<span class="open">'.$hesklang['open'].'</span>';
		        break;
		    case 1:
		        $ticket['status']='<span class="waitingreply">'.$hesklang['wait_reply'].'</span>';
		        break;
		    case 2:
		        $ticket['status']='<span class="replied">'.$hesklang['replied'].'</span>';
		        break;
		    default:
		        $ticket['status']='<span class="resolved">'.$hesklang['closed'].'</span>';
	    }

		switch ($ticket['priority'])
		{
			case 1:
				$ticket['priority']='<img src="../img/flag_high.png" width="16" height="16" alt="'.$hesklang['priority'].': '.$hesklang['high'].'" title="'.$hesklang['priority'].': '.$hesklang['high'].'" border="0" />';
				break;
			case 2:
				$ticket['priority']='<img src="../img/flag_medium.png" width="16" height="16" alt="'.$hesklang['priority'].': '.$hesklang['medium'].'" title="'.$hesklang['priority'].': '.$hesklang['medium'].'" border="0" />';
				break;
			default:
				$ticket['priority']='<img src="../img/flag_low.png" width="16" height="16" alt="'.$hesklang['priority'].': '.$hesklang['low'].'" title="'.$hesklang['priority'].': '.$hesklang['low'].'" border="0" />';
		}

	    $ticket['lastchange']=hesk_formatDate($ticket['lastchange']);

	    if ($ticket['lastreplier']=='1') {$ticket['lastreplier']=$hesklang['staff'];}
	    else {$ticket['lastreplier']=$hesklang['customer'];}

	    if ($ticket['archive']) {$ticket['archive']=$hesklang['yes'];}
	    else {$ticket['archive']=$hesklang['no'];}

	echo <<<EOC
	    <tr>
	    <td class="$color"><input type="checkbox" name="id[]" value="$ticket[id]" /></td>
	    <td class="$color"><a href="admin_ticket.php?track=$ticket[trackid]&amp;Refresh=$random">$ticket[trackid]</a></td>
	    <td class="$color">$ticket[lastchange]</td>
	    <td class="$color">$ticket[name]</td>
	    <td class="$color"><a href="admin_ticket.php?track=$ticket[trackid]&amp;Refresh=$random">$ticket[subject]</a></td>
	    <td class="$color">$ticket[status]</td>
	    <td class="$color">$ticket[lastreplier]</td>
	    <td class="$color" style="text-align:center; white-space:nowrap;">$ticket[priority]&nbsp;</td>
	    <!--
	    <td class="$color">$ticket[archive]</td>
	    -->
	    </tr>

EOC;

	} // End while
	?>
		</table>
		</div>

	    <p align="center"><select name="a">
	    <option value="close" selected="selected"><?php echo $hesklang['close_selected']; ?></option>
	    <option value="delete"><?php echo $hesklang['del_selected']; ?></option>
	    </select><input type="submit" value="<?php echo $hesklang['execute']; ?>" class="orangebutton"  onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>

		</form>

    <?php

} // end total > 0
else
{
	echo '<p>&nbsp;<br />&nbsp;<b><i>'.$hesklang['no_tickets_crit'].'</i></b><br />&nbsp;</p>';
}
?>

<hr />

<?php
require_once(HESK_PATH . 'inc/show_search_form.inc.php');

/* Print footer */
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

?>

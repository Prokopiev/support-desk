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

$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` WHERE ';

if (!empty($_GET['archive']))
{
    $archive=1;
    $sql .= '`archive`=\'1\' AND ';
}
else
{
    $archive=0;
}

$sql .= hesk_myCategories();

/* Get all the SQL sorting preferences */
/*
STATUS NUMBER MEANING
0 = NEW
1 = WAITING REPLY
2 = REPLIED
3 = RESOLVED (CLOSED)
4 = ANY STATUS
5 = 0 + 1
6 = 0 + 1 + 2
*/
if (!isset($_GET['status']))
{
    $status=6;
    $sql .= ' AND (`status`=\'0\' OR `status`=\'1\' OR `status`=\'2\') ';
}
else
{
    $status = hesk_isNumber($_GET['status']);

    if ($status==5)
    {
        $sql .= ' AND (`status`=\'0\' OR `status`=\'1\') ';
    }
    elseif ($status==6)
    {
        $sql .= ' AND (`status`=\'0\' OR `status`=\'1\' OR `status`=\'2\') ';
    }
    elseif ($status!=4)
    {
        $sql .= ' AND `status`=\''.hesk_dbEscape($status).'\' ';
    }

}

$category = (isset($_GET['category'])) ? hesk_isNumber($_GET['category']) : 0;
if ($category)
{
    $sql .= ' AND `category`=\''.hesk_dbEscape($category).'\' ';
}

$sql_copy=$sql;

$tmp = (isset($_GET['limit'])) ? intval($_GET['limit']) : 0;
$maxresults = ($tmp > 0) ? $tmp : $hesk_settings['max_listings'];

$tmp  = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$page = ($tmp > 1) ? $tmp : 1;

/* Acceptable $sort values */
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

/* This query string will be used to browse pages */
$query = 'status='.$status.'&amp;sort='.$sort.'&amp;category='.$category.'&amp;asc='.$asc.'&amp;limit='.$maxresults.'&amp;archive='.$archive.'&amp;page=';

/* Get number of tickets and page number */
$result = hesk_dbQuery($sql_copy);
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
        echo'</select> <input type="button" value="'.$hesklang['go'].'" onclick="javascript:window.location=\'show_tickets.php?'.$query.'\'+document.getElementById(\'myHpage\').value" class="orangebutton" onmouseover="hesk_btn(this,\'orangebuttonover\');" onmouseout="hesk_btn(this,\'orangebutton\');" /><br />';

        /* List pages */
        if ($pages > 7)
        {
            if ($page > 2)
            {
                echo '<a href="show_tickets.php?'.$query.'1"><b>&laquo;</b></a> &nbsp; ';
            }

            if ($prev_page)
            {
                echo '<a href="show_tickets.php?'.$query.$prev_page.'"><b>&lsaquo;</b></a> &nbsp; ';
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
                    echo ' <a href="show_tickets.php?'.$query.$i.'">'.$i.'</a> ';
                }
            }
        }

        if ($pages > 7)
        {
            if ($next_page)
            {
                echo ' &nbsp; <a href="show_tickets.php?'.$query.$next_page.'"><b>&rsaquo;</b></a> ';
            }

            if ($page < ($pages - 1))
            {
                echo ' &nbsp; <a href="show_tickets.php?'.$query.$pages.'"><b>&raquo;</b></a>';
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

    /* This query string will be used to order and reverse display */
    $query = "status=$status&amp;category=$category&amp;asc=" . (isset($is_default) ? 1 : $asc_rev) . "&amp;limit=$maxresults&amp;page=1&amp;archive=$archive&amp;sort=";

    /* Print the table with tickets */
    $random=rand(10000,99999);
    ?>

    <form name="form1" action="delete_tickets.php" method="post" onsubmit="return hesk_confirmExecute('<?php echo $hesklang['confirm_execute']; ?>')">

    <div align="center">
    <table border="0" width="100%" cellspacing="1" cellpadding="3" class="white">
    <tr>
    <th class="admin_white"><input type="checkbox" name="checkall" value="2" onclick="hesk_changeAll()" /></th>
    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>trackid"><?php echo $hesklang['trackID']; ?></a></th>
    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>lastchange"><?php echo $hesklang['last_update']; ?></a></th>
    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>name"><?php echo $hesklang['name']; ?></a></th>
    <th class="admin_white" style="text-align:left; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>subject"><?php echo $hesklang['subject']; ?></a></th>
    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>status"><?php echo $hesklang['status']; ?></a></th>
    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>lastreplier"><?php echo $hesklang['last_replier']; ?></a></th>
    <th class="admin_white" style="text-align:center; white-space:nowrap;"><a href="show_tickets.php?<?php echo $query; ?>priority"><img src="../img/sort_priority_<?php echo (($asc) ? 'asc' : 'desc'); ?>.png" width="16" height="16" alt="<?php echo $hesklang['sort_by'].' '.$hesklang['priority']; ?>" title="<?php echo $hesklang['sort_by'].' '.$hesklang['priority']; ?>" border="0" /></a></th>
    <!--
    <th class="admin_white" align="center"><a href="show_tickets.php?<?php echo $query; ?>archive"><?php echo $hesklang['archived']; ?></a></th>
    -->
    </tr>

    <?php
    $i = 0;
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
    if (isset($is_search))
    {
        echo '<p>&nbsp;<br />&nbsp;<b><i>'.$hesklang['no_tickets_crit'].'</i></b><br />&nbsp;</p>';
    }
    else
    {
        echo '<p>&nbsp;<br />&nbsp;<b><i>'.$hesklang['no_tickets_open'].'</i></b><br />&nbsp;</p>';
    }
}
?>

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

hesk_dbConnect();

/* The user should only be suggested articles one per each ticket submitted */
hesk_session_start();
$_SESSION['ARTICLES_SUGGESTED'] = true;

$query = (isset($_GET['q'])) ? hesk_input($_GET['q']) : 0;

if (!$query)
{
	hesk_noArticles();
}
else
{
	$query = substr($query,0,2000);
}

$sql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_articles` WHERE `type`=\'0\' AND MATCH(`subject`,`content`) AGAINST (\''.hesk_dbEscape($query).'\') LIMIT '.hesk_dbEscape($hesk_settings['kb_search_limit']);
$res = hesk_dbQuery($sql);
$num = hesk_dbNumRows($res);

if ($num)
{
    ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<title><?php echo $hesk_settings['hesk_title']; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>" />
	<link href="hesk_style.css" type="text/css" rel="stylesheet" />
	<script language="Javascript" type="text/javascript" src="hesk_javascript.js"></script>
	</head>
    <body>

	<div align="center">
	<table border="0" cellspacing="0" cellpadding="5" class="enclosing">
	<tr>
	<td>

    <span class="section">&raquo; <?php echo $hesklang['sc']; ?> (<?php echo $num; ?>)</span>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="7" height="7"><img src="img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>

			<div align="center">
        	<table border="0" cellspacing="1" cellpadding="3" width="100%">

    		<?php
			while ($article = hesk_dbFetchAssoc($res))
			{
	            $txt = strip_tags($article['content']);
	            if (strlen($txt) > $hesk_settings['kb_substrart'])
	            {
	            	$txt = substr(strip_tags($article['content']),0,$hesk_settings['kb_substrart']).'...';
	            }

	            if ($hesk_settings['kb_rating'])
	            {
	            	$alt = $article['rating'] ? sprintf($hesklang['kb_rated'], sprintf("%01.1f", $article['rating'])) : $hesklang['kb_not_rated'];
	                $rat = '<td width="1" valign="top"><img src="img/star_'.(hesk_round_to_half($article['rating'])*10).'.png" width="85" height="16" alt="'.$alt.'" border="0" style="vertical-align:text-bottom" /></td>';
	            }
	            else
	            {
	            	$rat = '';
	            }

				echo '
				<tr>
				<td>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><img src="img/article_text.png" width="16" height="16" border="0" alt="" style="vertical-align:middle" /></td>
	                <td valign="top"><a href="knowledgebase.php?article='.$article['id'].'" target="_blank">'.$article['subject'].'</a></td>
	                '.$rat.'
                    </tr>
	                </table>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><img src="img/blank.gif" width="16" height="10" style="vertical-align:middle" alt="" /></td>
	                <td><span class="article_list">'.$txt.'</span></td>
                    </tr>
	                </table>

	            </td>
				</tr>';
			}
            ?>
            </table>
            </div>

            <p align="center"><a href="javascript:void(0)" onclick="window.opener.document.form1.submit();window.close();"><b><?php echo $hesklang['cw2'];?></b></a></p>
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

    </td>
    </tr>
    </table>
    </div>

	</body>
	</html>

	<?php
	exit();
}
else
{
	hesk_noArticles();
}

function hesk_noArticles() {
	global $hesk_settings, $hesklang;
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<title><?php echo $hesk_settings['hesk_title']; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>" />
	<link href="hesk_style.css" type="text/css" rel="stylesheet" />
	<script language="Javascript" type="text/javascript" src="hesk_javascript.js"></script>
	</head>
	<body onload="window.opener.document.form1.submit();window.close()">
	<p><?php echo $hesklang['nsfo']; ?></p>
	<p align="center"><a href="javascript:void(0)" onclick="window.opener.document.form1.submit();window.close()"><?php echo $hesklang['cw']; ?></a></p>
	</body>
	</html>
	<?php
	exit();
}
?>

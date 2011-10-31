<?php
// -------------------------------------------------------------------------//
// Nuked-KlaN - PHP Portal                                                  //
// http://www.nuked-klan.org                                                //
// -------------------------------------------------------------------------//
// This program is free software. you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License.           //
// -------------------------------------------------------------------------//
if (!defined("INDEX_CHECK"))
{
    die ("<div style=\"text-align: center;\">You cannot open this page directly</div>");
}

global $user, $language;
translate("modules/AlertePost/lang/" . $language . ".lang.php");
include("modules/Admin/design.php");

echo "<script  type=\"text/javascript\" src=\"modules/AlertePost/js/functions.js\"></script>";

admintop();

if (!$user)
{
    $visiteur = 0;
}
else
{
    $visiteur = $user[1];
}
$ModName = basename(dirname(__FILE__));
$level_admin = admin_mod($ModName);
if ($visiteur >= $level_admin && $level_admin > -1)
{
	
	function menu($param)
	{
		switch($param) {
			case "main":
				 echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
				. "<div class=\"content-box-header\"><h3>" . _ADMINALERTEPOST . "</h3>\n"
				. "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/AlertePost.php\" rel=\"modal\">\n"
				. "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
				. "</div></div>\n"
				. "<div class=\"tab-content\" id=\"tab2\"><div style=\"text-align: center;\">" . _GLOBALPREF . "<b> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=post\">" . _NEWPOSTPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=reply\">" . _REPLYPOSTPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=edit\">" . _EDITPOSTPREF . "</a></b></div><br />\n";
				break;
			case "post":
				echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
				. "<div class=\"content-box-header\"><h3>" . _ADMINALERTEPOST . "</h3>\n"
				. "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/AlertePost.php\" rel=\"modal\">\n"
				. "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
				. "</div></div>\n"
				. "<div class=\"tab-content\" id=\"tab2\"><div style=\"text-align: center;\">\n"
				. "<b><a href=\"index.php?file=AlertePost&amp;page=admin\">" . _GLOBALPREF . "</a> | "
				. "</b>" . _NEWPOSTPREF . "<b> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=reply\">" . _REPLYPOSTPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=edit\">" . _EDITPOSTPREF . "</a></b></div><br />\n";
				break;
			case "reply":
				echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
				. "<div class=\"content-box-header\"><h3>" . _ADMINALERTEPOST . "</h3>\n"
				. "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/AlertePost.php\" rel=\"modal\">\n"
				. "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
				. "</div></div>\n"
				. "<div class=\"tab-content\" id=\"tab2\"><div style=\"text-align: center;\">\n"
				. "<b><a href=\"index.php?file=AlertePost&amp;page=admin\">" . _GLOBALPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=post\">" . _NEWPOSTPREF . "</a> | "
				. "</b>" . _REPLYPOSTPREF . "<b> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=edit\">" . _EDITPOSTPREF . "</a></b></div><br />\n";
				break;
			case "edit":
				echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
				. "<div class=\"content-box-header\"><h3>" . _ADMINALERTEPOST . "</h3>\n"
				. "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/AlertePost.php\" rel=\"modal\">\n"
				. "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
				. "</div></div>\n"
				. "<div class=\"tab-content\" id=\"tab2\"><div style=\"text-align: center;\">\n"
				. "<b><a href=\"index.php?file=AlertePost&amp;page=admin\">" . _GLOBALPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=post\">" . _NEWPOSTPREF . "</a> | "
				. "<a href=\"index.php?file=AlertePost&amp;page=admin&amp;op=reply\">" . _REPLYPOSTPREF . "</a> | "
				. "</b>" . _EDITPOSTPREF . "</div><br />\n";
				break;
		}
	}
	
	function select_user($selected)
    {
        global $nuked, $liste_users;
		
        $sql = mysql_query("SELECT id, pseudo, niveau FROM " . USER_TABLE . " WHERE niveau > 0 ORDER BY niveau DESC,date ,pseudo");
        while (list($id_user, $pseudo, $niveau) = mysql_fetch_array($sql))
        {
			$selected_user ="";
			if(in_array("$id_user", $liste_users) && $selected) {
				$selected_user = "selected=\"selected\"";
			}
	    	$pseudo = stripslashes($pseudo);
            echo "<option value=\"" . $id_user . "\" ".$selected_user.">" . $pseudo . " ( " . $niveau . " )</option>\n";
        } 
    }
	
	function getTableName($param)
	{
		switch($param)
		{
			case "post":$table_name=$nuked[prefix]."_alerteposte_pref";break;
			case "reply":$table_name=$nuked[prefix]."_alerteposte_pref_reply";break;
			case "edit":$table_name=$nuked[prefix]."_alerteposte_pref_edit";break;
		}
		
		return $table_name;
	}
	
	function main() {
		global $nuked;
		
		menu("main");
		
		$table_pref = $nuked['prefix'] . "_alerteposte_pref";
		
		$sql1 = mysql_query("SELECT value FROM " . $table_pref . " WHERE name='alerteActive'");
		list($alerteActive) = mysql_fetch_row($sql1);
		
		$sql2 = mysql_query("SELECT value FROM " . $table_pref . " WHERE name='alertePostActive'");
		list($alertePostActive) = mysql_fetch_row($sql2);
		
		$sql3 = mysql_query("SELECT value FROM " . $table_pref . " WHERE name='alerteReplyActive'");
		list($alerteReplyActive) = mysql_fetch_row($sql3);
		
		$sql4 = mysql_query("SELECT value FROM " . $table_pref . " WHERE name='alerteEditActive'");
		list($alerteEditActive) = mysql_fetch_row($sql4);
		
		$checked_alerte_active = "";
		if($alerteActive == "on"){
			$checked_alerte_active = "checked=\"checked\"";
		}
		
		$checked_alerte_post_active = "";
		if($alertePostActive == "on"){
			$checked_alerte_post_active = "checked=\"checked\"";
		}
		
		$checked_alerte_reply_active = "";
		if($alerteReplyActive == "on"){
			$checked_alerte_reply_active = "checked=\"checked\"";
		}
		
		$checked_alerte_edit_active = "";
		if($alerteEditActive == "on"){
			$checked_alerte_edit_active = "checked=\"checked\"";
		}
		
		echo "<form method=\"post\" action=\"index.php?file=AlertePost&amp;page=admin&amp;op=send_pref_main\">\n"
	  	. "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
		. "<tr><td><b>" . _ACTIVATEALERT . " :</b> <input id=\"alerteActive\" class=\"checkbox\" type=\"checkbox\" name=\"alerteActive\" value=\"on\" " . $checked_alerte_active . " onclick=\"gestionCases(this);\"/></td></tr>\n"
		. "<tr><td><b>" . _ACTIVATEALERTPOST . " :</b> <input id=\"alertePostActive\" class=\"checkbox\" type=\"checkbox\" name=\"alertePostActive\" value=\"on\" " . $checked_alerte_post_active . " onclick=\"gestionCases(this);\"/></td></tr>\n"
		. "<tr><td><b>" . _ACTIVATEALERTREPLY . " :</b> <input id=\"alerteReplyActive\" class=\"checkbox\" type=\"checkbox\" name=\"alerteReplyActive\" value=\"on\" " . $checked_alerte_reply_active . " onclick=\"gestionCases(this);\"/></td></tr>\n"
		. "<tr><td><b>" . _ACTIVATEALERTEDIT . " :</b> <input id=\"alerteEditActive\" class=\"checkbox\" type=\"checkbox\" name=\"alerteEditActive\" value=\"on\" " . $checked_alerte_edit_active . " onclick=\"gestionCases(this);\"/></td></tr>\n"
		. "</table>\n"
		. "<div style=\"text-align: center;\"><br /><input type=\"submit\" value=\"" . _SEND . "\" /></div>\n"
		. "<div style=\"text-align: center;\"><br />[ <a href=\"index.php?file=Admin\"><b>" . _BACK . "</b></a> ]</div></form><br /></div>\n";
	}
	
	function do_pref($param)
	{
		global $user, $nuked, $liste_users;
		
		menu($param);
		
		$table_name = getTableName($param);
		
		$sql2 = mysql_query("SELECT value FROM ".$table_name." WHERE name='userFor'");
		list($userFor) = mysql_fetch_row($sql2);
		
		$sql3 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='niveauSel'");
		list($niveau_sel) = mysql_fetch_row($sql3);
		
		$sql4 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='sujet'");
		list($sujet) = mysql_fetch_row($sql4);
		
		$sql5 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='message'");
		list($message) = mysql_fetch_row($sql5);
		
		$sql6 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='sendUser'");
		list($send_user) = mysql_fetch_row($sql6);
		
		$sql7 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='sendTitre'");
		list($send_titre) = mysql_fetch_row($sql7);
		
		$sql8 = mysql_query("SELECT value FROM ".$table_name ." WHERE name='sendUrl'");
		list($send_url) = mysql_fetch_row($sql8);
	
		$liste_users = explode("|", $userFor);
		
		switch($niveau_sel){
			case "0":$selected_niv_0 ="selected=\"selected\"";break;
			case "1":$selected_niv_1 ="selected=\"selected\"";break;
			case "2":$selected_niv_2 ="selected=\"selected\"";break;
			case "3":$selected_niv_3 ="selected=\"selected\"";break;
			case "4":$selected_niv_4 ="selected=\"selected\"";break;
			case "5":$selected_niv_5 ="selected=\"selected\"";break;
			case "6":$selected_niv_6 ="selected=\"selected\"";break;
			case "7":$selected_niv_7 ="selected=\"selected\"";break;
			case "8":$selected_niv_8 ="selected=\"selected\"";break;
			case "9":$selected_niv_9 ="selected=\"selected\"";break;
			case "10":$selected_niv_10 ="selected=\"selected\"";break;
			case "11":$selected_niv_11 ="selected=\"selected\"";break;
			default : $selected_niv_0 ="selected=\"selected\"";break;
		}
		
		$checked_send_user = "";
		if($send_user == "on"){
			$checked_send_user = "checked=\"checked\"";
		}
		
		$checked_send_titre = "";
		if($send_titre == "on"){
			$checked_send_titre = "checked=\"checked\"";
		}
		
		$checked_send_url = "";
		if($send_url == "on"){
			$checked_send_url = "checked=\"checked\"";
		}
		
		echo "<form method=\"post\" action=\"index.php?file=AlertePost&amp;page=admin&amp;op=send_pref\">\n"
		. "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
		. "  <tr>\n"
		. "    <td align=\"center\">\n"
		. "<script type=\"text/javascript\">\n"
		. "function showorhide(faire, id) { \n"
		. "	if (faire == 10) {\n"
		. "		faire = 'block';\n"
		. "	} else {\n"
		. "		faire = 'none';\n"
		. "	}\n"
		. "	if (document.getElementById) { // DOM3 = IE5, NS6\n"
		. "			document.getElementById(id).style.display = faire;\n"
		. "	} else { \n"
		. "		if (document.layers) {	// Netscape 4\n"
		. "				document.id.display = faire;\n"
		. "		} else { // IE 4\n"
		. "				document.all.id.style.display = faire;\n"
		. "		}\n"
		. "	}\n"
		. "}\n"
		. "</script>\n</td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><b>"._USERFOR."</b> : <select id=\"niveau\" name=\"niveau\" onchange=\"javascript:showorhide(this.value, 'perso');\">\n"
		. "      <option value=\"0\" ".$selected_niv_0.">"._ALLMEMBERS."</option>\n"
		. "      <option value=\"1\" ".$selected_niv_1.">"._SITEMEMBERS."</option>\n"
		. "      <option value=\"11\" ".$selected_niv_11.">"._TEAMMEMBERS."</option>\n"
		. "      <option value=\"2\" ".$selected_niv_2.">"._NIVOMEMBERS."2)</option>\n"
		. "      <option value=\"3\" ".$selected_niv_3.">"._NIVOMEMBERS."3)</option>\n"
		. "      <option value=\"4\" ".$selected_niv_4.">"._NIVOMEMBERS."4)</option>\n"
		. "      <option value=\"5\" ".$selected_niv_5.">"._NIVOMEMBERS."5)</option>\n"
		. "      <option value=\"6\" ".$selected_niv_6.">"._NIVOMEMBERS."6)</option>\n"
		. "      <option value=\"7\" ".$selected_niv_7.">"._NIVOMEMBERS."7)</option>\n"
		. "      <option value=\"8\" ".$selected_niv_8.">"._NIVOMEMBERS."8)</option>\n"
		. "      <option value=\"9\" ".$selected_niv_9.">"._ADMINMEMBERS."</option>\n"
		. "      <option value=\"10\" ".$selected_niv_10.">"._PERSONALISE."</option></select>\n"
		. "    </td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><div id=\"perso\" style=\"display:none;position:relative;\">"
		. "      <table>\n<tr>\n<td><b>"._PERSONALISE."</b> : <br />"._AIDE."</td><td align=\"left\"><select name=\"user_for[]\" size=\"8\" multiple=\"multiple\">\n";
		if($niveau_sel == 10){
			echo "<script>showorhide(10, 'perso');</script>";
			select_user(true);
		}else {
			select_user(false);
		}
		echo "</select>\n</td>\n</tr>\n</table></div></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td height=\"30\" align=\"left\"><b>"._SUBJECT."</b> : <input type=\"text\" name=\"subject\" maxlength=\"100\" size=\"55\" value=\"$sujet\"/></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><b>"._USERMESS." :</b><br /><br /><textarea id=\"mess_pv\" name=\"corps\" cols=\"65\" rows=\"15\">$message</textarea></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><b>"._SENDUSER." : </b><input id=\"senduser\" class=\"checkbox\" type=\"checkbox\" name=\"senduser\" value=\"on\" " . $checked_send_user . "/></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><b>"._SENDTITRE." : </b><input id=\"sendtitre\" class=\"checkbox\" type=\"checkbox\" name=\"sendtitre\" value=\"on\" " . $checked_send_titre . "/></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td align=\"left\"><b>"._SENDURL." : </b><input id=\"sendtitre\" class=\"checkbox\" type=\"checkbox\" name=\"sendurl\" value=\"on\" " . $checked_send_url . "/></td>\n"
		. "  </tr>\n"
		. "</table>\n"
		. "<input type=\"hidden\" name=\"type_pref\" value=\"$param\"/>"
		. "<div style=\"text-align: center;\"><br /><input type=\"submit\" value=\"" . _SEND . "\" /></div>\n"
		. "<div style=\"text-align: center;\"><br />[ <a href=\"index.php?file=Admin\"><b>" . _BACK . "</b></a> ]</div></form><br /></div>\n";
	}
	
	switch($_REQUEST['op'])
	{
		case"send_pref":
			send_pref();
			break;
		case"send_pref_main":
			send_pref_main();
			break;
		case"post":
			do_pref("post");
			break;
		case"reply":
			do_pref("reply");
			break;
		case"edit":
			do_pref("edit");
			break;
		default:
        	main();
			echo "<script>gestionCases(document.getElementById('alerteActive'));</script>";
			break;
	}
}
else if ($level_admin == -1)
{
    echo "<div class=\"notification error png_bg\">\n"
	. "<div>\n"
	. "<br /><br /><div style=\"text-align: center;\">" . _MODULEOFF . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a></div><br /><br />"
	. "</div>\n"
	. "</div>\n";
}
else if ($visiteur > 1)
{
    echo "<div class=\"notification error png_bg\">\n"
	. "<div>\n"
	. "<br /><br /><div style=\"text-align: center;\">" . _NOENTRANCE . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a></div><br /><br />"
	. "</div>\n"
	. "</div>\n";
}
else
{
    echo "<div class=\"notification error png_bg\">\n"
	. "<div>\n"
	. "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a></div><br /><br />"
	. "</div>\n"
	. "</div>\n";
}
adminfoot();
?>
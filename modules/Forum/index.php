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

global $nuked, $language, $user, $op, $cookie_captcha;
translate("modules/Forum/lang/" . $language . ".lang.php");

// Inclusion système Captcha
include_once("Includes/nkCaptcha.php");

// On determine si le captcha est actif ou non
if (_NKCAPTCHA == "off") $captcha = 0;
else if (_NKCAPTCHA == "auto" && $user[1] > 0)  $captcha = 0;
else if (_NKCAPTCHA == "auto" && isset($_COOKIE[$cookie_captcha])) $captcha = 0;
else $captcha = 1;


if (!$user)
{
    $visiteur = 0;
} 
else
{
    $visiteur = $user[1];
} 
$ModName = basename(dirname(__FILE__));
$level_access = nivo_mod($ModName);
if (($visiteur >= $level_access && $level_access > -1) || $op == "bbcodehelp")
{
    compteur("Forum");

    function index()
    {
        opentable();
        include("modules/Forum/main.php");
        closetable();
    } 

    function edit($mess_id)
    {
        global $visiteur, $user, $nuked, $forum_id, $titre, $texte, $author, $usersig, $emailnotify, $bbcodeoff, $smileyoff, $css, $edit_text;

        opentable();

        if ($titre == "" || $texte == "" || @ctype_space($titre) || @ctype_space($texte))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _FIELDEMPTY . "</div><br /><br />";
            $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id . "&mess_id=" . $mess_id. "&do=edit";
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= level AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($author == $user[2] || $visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            $date = time();
            $date = strftime("%d/%m/%Y %H:%M", $date);

            if ($edit_text == 1)
            {	
                $texte_edit = _EDITBY . "&nbsp;" . $user[2] . "&nbsp;" . _THE . "&nbsp;" . $date;
                $edition = ", edition = '" . $texte_edit ."'";
            }
            else
            {
                $edition = "";
            }

            $titre = addslashes($titre);
            $texte = addslashes($texte);
            
            if (!is_numeric($usersig)) $usersig = 0;
            if (!is_numeric($emailnotify)) $emailnotify = 0;
            if (!is_numeric($bbcodeoff)) $bbcodeoff = 0;
            if (!is_numeric($smileyoff)) $smileyoff = 0;
            if (($visiteur < admin_mod("Forum") && $administrator == 0) || !is_numeric($css)) $css = 0;

            $sql = mysql_query("UPDATE " . FORUM_MESSAGES_TABLE . " SET titre = '" . $titre . "', txt = '" . $texte . "'" . $edition . ", usersig = '" . $usersig . "', emailnotify = '" . $emailnotify . "', bbcodeoff = '" . $bbcodeoff . "', smileyoff = '" . $smileyoff . "', cssoff = '" . $css . "'WHERE id = '" . $mess_id . "'");

            $sql2 = mysql_query("SELECT thread_id FROM " . FORUM_MESSAGES_TABLE . " WHERE id = '" . $mess_id . "'");
            list($thread_id) = mysql_fetch_row($sql2);

            $sql3 = mysql_query("SELECT id FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "' ORDER BY id LIMIT 0, 1");
            list($mid) = mysql_fetch_row($sql3);

            if ($mid == $mess_id)
            {
                $upd = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET titre = '" . $titre . "' WHERE id = '" . $thread_id . "'");
            } 

            $sql_page = mysql_query("SELECT id FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "'");
            $nb_rep = mysql_num_rows($sql_page);

            if ($nb_rep > $nuked['mess_forum_page'])
            {
                $topicpages = $nb_rep / $nuked['mess_forum_page'];
                $topicpages = ceil($topicpages);

                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id . "&p=" . $topicpages . "#" . $mess_id;
            } 
            else
            {
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id . "#" . $mess_id;
            } 

            echo "<br /><br /><div style=\"text-align: center;\">" . _MESSMODIF . "</div><br /><br />";
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
        }
		
		/** Ajout module AlertPost */
		alertePost("edit", $url, $titre);
		/** Fin ajout module AlertPost. */
		
        redirect($url, 2);
        closetable();
    } 

    function del($mess_id)
    {
        global $visiteur, $user, $nuked, $forum_id, $thread_id, $confirm;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            if ($confirm == _YES)
            {
                $sql2 = mysql_query("SELECT id, file FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "' ORDER BY id LIMIT 0, 1");
                list($mid, $filename) = mysql_fetch_row($sql2);

                if ($filename != "")
                {
                    $path = "upload/Forum/" . $filename;

                    if (is_file($path))
                    {
                        $filesys = str_replace("/", "\\", $path);
                        @chmod ($path, 0775);
                        @unlink($path);
                        @system("del $filesys");
                    } 
                } 

                if ($mid == $mess_id)
                {
                    $sql_survey = mysql_query("SELECT sondage FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
                    list($sondage) = mysql_fetch_row($sql_survey);

                    if ($sondage == 1)
                    {
                        $sql_poll = mysql_query("SELECT id FROM " . FORUM_POLL_TABLE . " WHERE thread_id = '" . $thread_id . "'");
                        list($poll_id) = mysql_fetch_row($sql_poll);

                        $sup1 = mysql_query("DELETE FROM " . FORUM_POLL_TABLE . " WHERE id = '" . $poll_id . "'");
                        $sup2 = mysql_query("DELETE FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                        $sup3 = mysql_query("DELETE FROM " . FORUM_VOTE_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                    } 

                    $del = mysql_query("DELETE FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
                    $del2 = mysql_query("DELETE FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "'");
                    $del3 = mysql_query("DELETE FROM " . FORUM_READ_TABLE . " WHERE thread_id = '" . $thread_id . "' AND forum_id = '" . $forum_id . "'");
                    $url = "index.php?file=Forum&page=viewforum&forum_id=" . $forum_id;
                } 
                else
                {
                    $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                } 

                $sql = mysql_query("DELETE FROM " . FORUM_MESSAGES_TABLE . " WHERE id = '" . $mess_id . "'");

                echo "<br /><br /><div style=\"text-align: center;\">" . _MESSDELETED . "</div><br /><br />";
                redirect($url, 2);
            } 

            else if ($confirm == _NO)
            {
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                echo "<br /><br /><div style=\"text-align: center;\">" . _DELCANCEL . "</div><br /><br />";
                redirect($url, 2);
            } 

            else
            {
                echo "<form method=\"post\" action=\"index.php?file=Forum&amp;op=del\">\n"
                . "<div style=\"text-align: center;\"><br /><br />" . _CONFIRMDELMESS . "<br />\n"
                . "<input type=\"hidden\" name=\"forum_id\" value=\"" . $forum_id . "\" />\n"
                . "<input type=\"hidden\" name=\"thread_id\" value=\"" . $thread_id . "\" />\n"
                . "<input type=\"hidden\" name=\"mess_id\" value=\"" . $mess_id . "\" />\n"
                . "<input type=\"submit\" name=\"confirm\" value=\"" . _YES . "\" />"
                . "&nbsp;<input type=\"submit\" name=\"confirm\" value=\"" . _NO . "\" /></div></form><br />\n";
            } 
        } 
        else
        {
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            redirect($url, 2);
        } 

        closetable();
    } 

    function del_topic($thread_id)
    {
        global $visiteur, $user, $nuked, $forum_id, $confirm;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            if ($confirm == _YES)
            {
                $sql = mysql_query("SELECT sondage FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
                list($sondage) = mysql_fetch_row($sql);

                if ($sondage == 1)
                {
                    $sql_poll = mysql_query("SELECT id FROM " . FORUM_POLL_TABLE . " WHERE thread_id = '" . $thread_id . "'");
                    list($poll_id) = mysql_fetch_row($sql_poll);

                    $sup1 = mysql_query("DELETE FROM " . FORUM_POLL_TABLE . " WHERE id = '" . $poll_id . "'");
                    $sup2 = mysql_query("DELETE FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                    $sup3 = mysql_query("DELETE FROM " . FORUM_VOTE_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                } 

                $sql2 = mysql_query("SELECT file FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "'");
                while (list($filename) = mysql_fetch_row($sql2))
                {
                    if ($filename != "")
                    {
                        $path = "upload/Forum/" . $filename;
                        if (is_file($path))
                        {
                            $filesys = str_replace("/", "\\", $path);
                            @chmod ($path, 0775);
                            @unlink($path);
                            @system("del $filesys");
                        } 
                    } 
                } 

                $del1 = mysql_query("DELETE FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "' AND forum_id = '" . $forum_id . "'");
                $del2 = mysql_query("DELETE FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "' AND forum_id = '" . $forum_id . "'");
                $del3 = mysql_query("DELETE FROM " . FORUM_READ_TABLE . " WHERE thread_id = '" . $thread_id . "' AND forum_id = '" . $forum_id . "'");

                $url = "index.php?file=Forum&page=viewforum&forum_id=" . $forum_id;
                echo "<br /><br /><div style=\"text-align: center;\">" . _TOPICDELETED . "</div><br /><br />";
                redirect($url, 2);
            } 

            else if ($confirm == _NO)
            {
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                echo "<br /><br /><div style=\"text-align: center;\">" . _DELCANCEL . "</div><br /><br />";
                redirect($url, 2);
            } 

            else
            {
                echo "<form method=\"post\" action=\"index.php?file=Forum&amp;op=del_topic\">\n"
                . "<div style=\"text-align: center;\"><br /><br />" . _CONFIRMDELTOPIC . "<br />\n"
                . "<input type=\"hidden\" name=\"forum_id\" value=\"" . $forum_id . "\" />\n"
                . "<input type=\"hidden\" name=\"thread_id\" value=\"" . $thread_id . "\" />\n"
                . "<input type=\"submit\" name=\"confirm\" value=\"" . _YES . "\" />"
                . "&nbsp;<input type=\"submit\" name=\"confirm\" value=\"" . _NO . "\" /></div></form><br />\n";
            } 

        } 
        else
        {
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            redirect($url, 2);
        } 

        closetable();
    } 

    function move()
    {
        global $visiteur, $user, $nuked, $thread_id, $forum_id, $newforum, $confirm;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            if ($confirm == _YES && $newforum != "")
            {
                echo"<br /><br /><div style=\"text-align: center;\">" . _TOPICMOVED . "</div><br /><br />";

                $sql1 = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET forum_id = '" . $newforum . "' WHERE id = '" . $thread_id . "'");
                $sql2 = mysql_query("UPDATE " . FORUM_MESSAGES_TABLE . " SET forum_id = '" . $newforum . "' WHERE thread_id = '" . $thread_id . "'");
                $sql3 = mysql_query("UPDATE " . FORUM_READ_TABLE . " SET forum_id = '" . $newforum . "' WHERE thread_id = '" . $thread_id . "'");

                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $newforum . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 

            else if ($confirm == _NO)
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _DELCANCEL . "</div><br /><br />";

                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 

            else
            {
                echo "<form action=\"index.php?file=Forum&amp;op=move\" method=\"post\">\n"
                . "<div style=\"text-align: center;\"><br /><br />" . _MOVETOPIC . " : <select name=\"newforum\">\n";

                $sql_cat = mysql_query("SELECT id, nom FROM " . FORUM_CAT_TABLE . " WHERE " . $visiteur . " >= niveau ORDER BY ordre, nom");
                while (list($cat, $cat_name) = mysql_fetch_row($sql_cat))
                {
                    $cat_name = stripslashes($cat_name);
                    $cat_name = htmlentities($cat_name);

                    echo "<option value=\"\">* " . $cat_name . "</option>\n";

                    $sql_forum = mysql_query("SELECT nom, id FROM " . FORUM_TABLE . " WHERE cat = '" . $cat . "' AND " . $visiteur . " >= niveau ORDER BY ordre, nom");
                    while (list($forum_name, $fid) = mysql_fetch_row($sql_forum))
                    {
                        $forum_name = stripslashes($forum_name);
                        $forum_name = htmlentities($forum_name);

                        echo "<option value=\"" . $fid . "\">&nbsp;&nbsp;&nbsp;" . $forum_name . "</option>\n";
                    } 
                } 

                echo "</select><br /><br /><input type=\"submit\" name=\"confirm\" value=\"" . _YES . "\" />"
                . "&nbsp;<input type=\"submit\" name=\"confirm\" value=\"" . _NO . "\" />\n"
                . "<input type=\"hidden\" name=\"forum_id\" value=\"$forum_id\" />\n"
                . "<input type=\"hidden\" name=\"thread_id\" value=\"$thread_id\" /></div></form><br />\n";
            } 
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function lock()
    {
        global $visiteur, $user, $nuked, $thread_id, $forum_id, $do;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($do == "close")
        {
            $lock_text = _TOPICLOCKED;
            $lock_type = 1;
        } 

        else if ($do == "open")
        {
            $lock_text = _TOPICUNLOCKED;
            $lock_type = 0;
        } 

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . $lock_text . "</div><br /><br />";

            $sql = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET closed = '" . $lock_type . "' WHERE id = '" . $thread_id . "'");

            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";

            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function announce()
    {
        global $visiteur, $user, $nuked, $thread_id, $forum_id, $do;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        if ($do == "up")
        {
            $announce = 1;
        } 
        else if ($do == "down")
        {
            $announce = 0;
        } 

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _TOPICMODIFIED . "</div><br /><br />";

            $sql = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET annonce = '" . $announce . "' WHERE id = '" . $thread_id . "'");

            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";

            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function reply()
    {
        global $visiteur, $user, $nuked, $thread_id, $forum_id, $titre, $texte, $auteur, $usersig, $emailnotify, $bbcodeoff, $smileyoff, $css, $user_ip, $fichiernom, $captcha;

        opentable();

        if ($captcha == 1 && $_POST['code_confirm'] != crypt_captcha($_POST['code']))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _BADCODECONFIRM . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
            closetable();
            footer();
            exit();
        } 

        if ($auteur == "" || $titre == "" || $texte == "" || @ctype_space($titre) || @ctype_space($texte))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _FIELDEMPTY . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
            closetable();
            footer();
            exit();
        } 

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        $lock = mysql_query("SELECT closed FROM " . FORUM_THREADS_TABLE . " WHERE forum_id = '" . $forum_id . "' AND id = '" . $thread_id . "'");
        list($closed) = mysql_fetch_array($lock);

        $forum = mysql_query("SELECT FT.level FROM " . FORUM_TABLE . " AS FT INNER JOIN " . FORUM_THREADS_TABLE . " AS FTT ON FT.id = FTT.forum_id WHERE FTT.id = '" . $thread_id . "'");
        list($level) = mysql_fetch_array($forum);

        if ($visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            $auth = 1;
        } 
        else if ($closed > 0 || $level > $visiteur)
        {
            $auth = 0;
        } 

        if ($auth == "0")
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";

            $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        if ($user[2] != "")
        {
            $autor = $user[2];
            $auteur_id = $user[0];
        } 
        else
        {
            $auteur = htmlentities($auteur, ENT_QUOTES);
            $auteur = verif_pseudo($auteur);

            if ($auteur == "error1")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _PSEUDOFAILDED . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
                closetable();
                footer();
                exit();
            } 
            else if ($auteur == "error2")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _RESERVNICK . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
                closetable();
                footer();
                exit();
            } 
            else if ($auteur == "error3")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _BANNEDNICK . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
                closetable();
                footer();
                exit();
            } 
            else
            {
                $autor = $auteur;
            } 

        } 

        $flood = mysql_query("SELECT date FROM " . FORUM_MESSAGES_TABLE . " WHERE auteur = '" . $autor . "' OR auteur_ip = '" . $user_ip . "' ORDER BY date DESC LIMIT 0, 1");
        list($flood_date) = mysql_fetch_row($flood);
        $anti_flood = $flood_date + $nuked['post_flood'];

        $date = time();

        if ($date < $anti_flood && $visiteur < admin_mod("Forum"))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _NOFLOOD . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        $titre = addslashes($titre);
        $texte = addslashes($texte);
        $autor = addslashes($autor);

        if (!is_numeric($usersig)) $usersig = 0;
        if (!is_numeric($emailnotify)) $emailnotify = 0;
        if (!is_numeric($bbcodeoff)) $bbcodeoff = 0;
        if (!is_numeric($smileyoff)) $smileyoff = 0;
        if (($visiteur < admin_mod("Forum") && $administrator == 0) || !is_numeric($css)) $css = 0;

        $filename = $_FILES['fichiernom']['name'];
		$filesize = $_FILES['fichiernom']['size'] / 1000;

        if ($visiteur >= $nuked['forum_file_level'] && $filename != "" && $nuked['forum_file'] == "on" && $nuked['forum_file_maxsize'] >= $filesize)
        {
            $file = explode(".", $filename);
            $end = count($file) - 1;
            $ext = $file[$end];

            if (eregi("php", $ext) || eregi("htm", $ext)) $type = "txt";
            else $type = $ext;

            $file_name = $date . "." . $type;
            $url_file = "upload/Forum/" . $file_name;
            if (!eregi("swf", $type)) move_uploaded_file($_FILES['fichiernom']['tmp_name'], $url_file) or die ("<br /><br /><div style=\"text-align: center;\"><big><b>" . _UPLOADFAILED . "</b></big></div><br /><br />");
            @chmod ($url_file, 0644);
        } 
        else
        {
            $url_file = "";
        } 
		
		
        $sql1 = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET last_post = '" . $date . "' WHERE id = '" . $thread_id . "'");
        if (mysql_affected_rows() > 0) 
		{
			$sql2 = mysql_query("INSERT INTO " . FORUM_MESSAGES_TABLE . " ( `id` , `titre` , `txt` , `date` , `edition` , `auteur` , `auteur_id` , `auteur_ip` , `bbcodeoff` , `smileyoff` , `cssoff` , `usersig` , `emailnotify` , `thread_id` , `forum_id` , `file` ) VALUES ( '' , '" . $titre . "' , '" . $texte . "' , '" . $date . "' , '' , '" . $autor . "' , '" . $auteur_id . "' , '" . $user_ip . "' , '" . $bbcodeoff . "' , '" . $smileyoff . "' , '" . $css . "' , '" . $usersig . "' , '" . $emailnotify . "' , '" . $thread_id . "' , '" . $forum_id . "' , '" . $file_name . "' )");

			$notify = mysql_query("SELECT auteur_id FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "' AND emailnotify = 1 GROUP BY auteur_id");
			$nbusers = mysql_num_rows($notify);
	
			if ($nbusers > 0)
			{
				while (list($usermail) = mysql_fetch_row($notify))
				{
					if($usermail != $auteur_id)
					{
								$getmail = mysql_query("SELECT mail FROM " . USER_TABLE . " WHERE id = '" . $usermail . "'");
								list($email) = mysql_fetch_row($getmail);
								$titre = stripslashes($titre);
								$subject = _MESSAGE . " : " . $titre;
								$corps = _EMAILNOTIFYMAIL . "\r\n" . $nuked['url'] . "/index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id . "\r\n\r\n\r\n" . $nuked['name'] . " - " . $nuked['slogan'];
								$from = "From: " . $nuked['name'] . " <" . $nuked['mail'] . ">\r\nReply-To: " . $nuked['mail'];
			
								$subject = @html_entity_decode($subject);
								$corps = @html_entity_decode($corps);
								$from = @html_entity_decode($from);	
			
								mail($email, $subject, $corps, $from);
					}
				} 
			} 
	
			if ($user)
			{
				$sql_count = mysql_query("SELECT count FROM " . USER_TABLE . " WHERE id = '" . $user[0] . "'");
				list($count) = mysql_fetch_row($sql_count);
				$newcount = $count + 1;
				$upd = mysql_query("UPDATE " . USER_TABLE . " SET count = '" . $newcount . "' WHERE id = '" . $user[0] . "'");
			} 
	
			$sql_page = mysql_query("SELECT id FROM " . FORUM_MESSAGES_TABLE . " WHERE thread_id = '" . $thread_id . "'");
			list($mess_id) = mysql_fetch_row($sql_page);
			$nb_rep = mysql_num_rows($sql_page);
	
			if ($nb_rep > $nuked['mess_forum_page'])
			{
				$topicpages = $nb_rep / $nuked['mess_forum_page'];
				$topicpages = ceil($topicpages);
				$link_post = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id . "&p=" . $topicpages . "#" . $mess_id;
			} 
			else
			{
				$link_post = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id . "#" . $mess_id;
			} 
	
			echo "<br /><br /><div style=\"text-align: center;\">" . _MESSAGESEND . "</div><br /><br />";
			/** Ajout module AlertPost */
			alertePost("reply", $link_post, $titre);
			/** Fin ajout module AlertPost. */
		    redirect($link_post, 2);	
		}
		else echo "<br /><br /><div style=\"text-align: center;\">" . _NOENTRANCE . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a><br /><br /></div>";
        closetable();
    } 

    function post()
    {
        global $visiteur, $user, $nuked, $forum_id, $titre, $texte, $auteur, $usersig, $emailnotify, $bbcodeoff, $smileyoff, $css, $annonce, $user_ip, $fichiernom, $survey, $survey_field, $captcha;
	
        opentable();
		
        if ($captcha == 1 && $_POST['code_confirm'] != crypt_captcha($_POST['code']))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _BADCODECONFIRM . "<br /><br /><a href=\"javascript:history.back()\">[ <b>" . _BACK . "</b> ]</a></div><br /><br />";
            closetable();
            footer();
            exit();
        } 

        if ($auteur == "" || $titre == "" || $texte == "" || @ctype_space($titre) || @ctype_space($texte))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _FIELDEMPTY . "</div><br /><br />";
            $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id;
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        $forum = mysql_query("SELECT level, level_poll FROM " . FORUM_TABLE . " WHERE id = '" . $forum_id . "'");
        list($level, $level_poll) = mysql_fetch_array($forum);

        if ($level > $visiteur)
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id;
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        if ($user[2] != "")
        {
            $autor = $user[2];
            $auteur_id = $user[0];
        } 
        else
        {
            $auteur = htmlentities($auteur, ENT_QUOTES);
            $auteur = verif_pseudo($auteur);

            if ($auteur == "error1")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _PSEUDOFAILDED . "</div><br /><br />";
                $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id;
                redirect($url, 2);
                closetable();
                footer();
                exit();
            } 
            else if ($auteur == "error2")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _RESERVNICK . "</div><br /><br />";
                $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id;
                redirect($url, 2);
                closetable();
                footer();
                exit();
            } 
            else if ($auteur == "error3")
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _BANNEDNICK . "</div><br /><br />";
                $url = "index.php?file=Forum&page=post&forum_id=" . $forum_id;
                redirect($url, 2);
                closetable();
                footer();
                exit();
            } 
            else
            {
                $autor = $auteur;
            } 
        } 

        $flood = mysql_query("SELECT date FROM " . FORUM_MESSAGES_TABLE . " WHERE auteur = '" . $autor . "' OR auteur_ip = '" . $user_ip . "' ORDER BY date DESC LIMIT 0, 1");
        list($flood_date) = mysql_fetch_row($flood);
        $anti_flood = $flood_date + $nuked[post_flood];

        $date = time();

        if ($date < $anti_flood && $user[1] < admin_mod("Forum"))
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _NOFLOOD . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewforum&forum_id=" . $forum_id;
            redirect($url, 2);
            closetable();
            footer();
            exit();
        } 

        $titre = addslashes($titre);
        $texte = addslashes($texte);
        $autor = addslashes($autor);

        if (!is_numeric($usersig)) $usersig = 0;
        if (!is_numeric($emailnotify)) $emailnotify = 0;
        if (!is_numeric($bbcodeoff)) $bbcodeoff = 0;
        if (!is_numeric($smileyoff)) $smileyoff = 0;
        if (($visiteur < admin_mod("Forum") && $administrator == 0) || !is_numeric($css)) $css = 0;
        if (($visiteur < admin_mod("Forum") && $administrator == 0) || !is_numeric($annonce)) $annonce = 0;

        if ($survey == 1 && $survey_field > 0 && $visiteur >= $level_poll)
        {
            $sondage = 1;
        } 
        else
        {
            $sondage = 0;
        } 

        $sql = mysql_query("INSERT INTO " . FORUM_THREADS_TABLE . " ( `id` , `titre` , `date` , `closed` , `auteur` , `auteur_id` , `forum_id` , `last_post` , `view` , `annonce` , `sondage` ) VALUES ( '' , '" . $titre . "' , '" . $date . "' , '' , '" . $autor . "' , '" . $auteur_id . "' , '" . $forum_id . "' , '" . $date . "' , '' , '" . $annonce . "' , '" . $sondage . "' )");
        $req4 = mysql_query("SELECT MAX(id) FROM " . FORUM_THREADS_TABLE . " WHERE forum_id = '" . $forum_id . "' AND titre = '" . $titre . "' AND date = '" . $date . "' AND auteur = '" . $auteur . "'");
        $idmax = mysql_result($req4, 0, "MAX(id)");

        $thread_id = $idmax;
        $filename = $_FILES['fichiernom']['name'];
		$filesize = $_FILES['fichiernom']['size'] / 1000;

        if ($visiteur >= $nuked['forum_file_level'] && $filename != "" && $nuked['forum_file'] == "on" && $nuked['forum_file_maxsize'] >= $filesize)
        {
            $file = explode(".", $filename);
            $end = count($file) - 1;
            $ext = $file[$end];

            if (eregi("php", $ext) || eregi("htm", $ext)) $type = "txt";
            else $type = $ext;

            $file_name = $date . "." . $type;
            $url_file = "upload/Forum/" . $file_name;
            if (!eregi("swf", $type)) move_uploaded_file($_FILES['fichiernom']['tmp_name'], $url_file) or die ("<br /><br /><div style=\"text-align: center;\"><big><b>" . _UPLOADFAILED . "</b></big></div><br /><br />");
            @chmod ($url_file, 0644);
        } 
        else
        {
            $url_file = "";
        } 

        $sql2 = mysql_query("INSERT INTO " . FORUM_MESSAGES_TABLE . " ( `id` , `titre` , `txt` , `date` , `edition` , `auteur` , `auteur_id` , `auteur_ip` , `bbcodeoff` , `smileyoff` , `cssoff` , `usersig` , `emailnotify` , `thread_id` , `forum_id` , `file` ) VALUES ( '' , '" . $titre . "' , '" . $texte . "' , '" . $date . "' , '' , '" . $autor . "' , '" . $auteur_id . "' , '" . $user_ip . "' , '" . $bbcodeoff . "' , '" . $smileyoff . "' , '" . $css . "' , '" . $usersig . "' , '" . $emailnotify . "' , '" . $thread_id . "' , '" . $forum_id . "' , '" . $file_name . "' )");

        if ($user)
        {
            $sql_count = mysql_query("SELECT count FROM " . USER_TABLE . " WHERE id = '" . $user[0] . "'");
            list($count) = mysql_fetch_row($sql_count);
            $newcount = $count + 1;
            $upd = mysql_query("UPDATE " . USER_TABLE . " SET count = '" . $newcount . "' WHERE id = '" . $user[0] . "'");
        } 

        if ($survey == 1 && $survey_field > 0 && $visiteur >= $level_poll)
        {
            $url = "index.php?file=Forum&op=add_poll&survey_field=" . $survey_field . "&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
        } 
        else
        {
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
        } 
		
		/** Ajout module AlertPost */
		alertePost("post", $url, $titre);
		/** Fin ajout module AlertPost. */
		
        echo "<br /><br /><div style=\"text-align: center;\">" . _MESSAGESEND . "</div><br /><br />";
        redirect($url, 2);
        closetable();
    }
	
	/** Ajout module AlertPost : On envoie les mails comme le paramétrage du module AlertPost l'indique. */
	function alertePost($param, $url, $titre)
	{
		global $nuked;
		
		$table_name = getTableName($param);
		
		$sql5 = @mysql_query("SELECT value FROM $nuked[prefix]"._alerteposte_pref." WHERE name='alerteActive'");
		list($alerteActive) = @mysql_fetch_row($sql5);
		
		$sql6 = @mysql_query("SELECT value FROM $nuked[prefix]"._alerteposte_pref." WHERE name='alertePostActive'");
		list($alertePostActive) = @mysql_fetch_row($sql6);
		
		$sql7 = @mysql_query("SELECT value FROM $nuked[prefix]"._alerteposte_pref." WHERE name='alerteReplyActive'");
		list($alerteReplyActive) = @mysql_fetch_row($sql7);
		
		$sql8 = @mysql_query("SELECT value FROM $nuked[prefix]"._alerteposte_pref." WHERE name='alerteEditActive'");
		list($alerteEditActive) = @mysql_fetch_row($sql8);
		
		if(($alerteActive != "" && $alerteActive == "on") || ($param == "post" && $alertePostActive == "on") || ($param == "reply" && $alerteReplyActive == "on") || ($param == "edit" && $alerteEditActive == "on"))
		{			
			$sql_niveau = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='niveauSel'");
			list($niveau) = mysql_fetch_row($sql_niveau);
			
			if ($niveau==0)
			{
				$sql_mails=mysql_query("SELECT mail FROM $nuked[prefix]"._users."");
			}
			if ($niveau>0&&$niveau<10)
			{
				$sql_mails=mysql_query("SELECT mail FROM $nuked[prefix]"._users." WHERE niveau='" . $niveau . "'");
			}
			if ($niveau==11)
			{
				$sql_mails=mysql_query("SELECT mail FROM $nuked[prefix]"._users." WHERE team>0");
			}
			if ($niveau==9)
			{
				$sql_mails=mysql_query("SELECT mail FROM $nuked[prefix]"._users." WHERE niveau='9'");
			}
			if ($niveau==10)
			{
				$sql_userfor = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='userFor'");
				list($userFor) = mysql_fetch_row($sql_userfor);
				$liste_users = explode("|", $userFor);
				
				$sql_string_mails="SELECT mail FROM $nuked[prefix]"._users." WHERE id='";
				
				for ($i = 0; $i < sizeof($liste_users); $i++) {
					if($i==(sizeof($liste_users)-1)){
						$sql_string_mails=$sql_string_mails.$liste_users[$i]."'";
					} else {
						$sql_string_mails=$sql_string_mails.$liste_users[$i]."' OR id='";
					}
				}
				$sql_mails=mysql_query($sql_string_mails);
			}
			
			while (list($mail) = mysql_fetch_array($sql_mails))
			{
				sendmail($mail, $titre, $url, $table_name);
			}
		}
	}

	function sendmail($mail, $titre_new_mess, $lien, $table_name){
		global $nuked, $user_ip, $user;
		
		$time = time();
		$date = strftime("%x %H:%M", $time);
		$mail = trim($mail);
		
		$sql_sujet = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='sujet'");
		list($sujet) = mysql_fetch_row($sql_sujet);
		
		$sql_message = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='message'");
		list($message) = mysql_fetch_row($sql_message);
		
		$sql_user = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='sendUser'");
		list($send_user) = mysql_fetch_row($sql_user);
		
		$sql_titre = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='sendTitre'");
		list($send_titre) = mysql_fetch_row($sql_titre);
		
		$sql_url = mysql_query("SELECT value FROM $nuked[prefix]".$table_name ." WHERE name='sendUrl'");
		list($send_url) = mysql_fetch_row($sql_url);
		
		if($send_user == "on"){
			$message = $message."<br /><br />Nom du poster : " . $user[2];
		}
		
		if($send_titre == "on"){
			$message = $message."<br /><br />Titre du post : " . $titre_new_mess;
		}
		
		if($send_url == "on"){
			$url_final = $nuked[url]."/".$lien;
			$message = $message."<br /><br /><a href=\"$url_final\"<<-- Lien du post -->></a>";
		}
		
		$sujet = trim($sujet);
		$subject = stripslashes($sujet) . ", " . $date;
		$corps = stripslashes($message) . "<br /><br /><br />" . $nuked['name'] . " - " . $nuked['slogan'];
		$entete = "MIME-Version: 1.0\r\n";
		$entete .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$entete .= "From: " . $nuked['name'] . " <" . $nuked['mail'] . ">\r\nReply-To: " . $nuked['mail'];

                $mail = @html_entity_decode($mail);
                $subject = @html_entity_decode($subject);
                $corps = @html_entity_decode($corps);
                $from = @html_entity_decode($from);

		mail($mail, $subject, $corps, $entete);
	}
	
	function getTableName($param)
	{
		switch($param)
		{
			case "post":return "_alerteposte_pref";break;
			case "reply":return "_alerteposte_pref_reply";break;
			case "edit":return "_alerteposte_pref_edit";break;
		}
	}
	
	/** Fin ajout module AlertPost */
	
    function mark()
    {
        global $user, $nuked, $forum_id, $cookie_forum;

        

        if ($user)
        {
			if ($forum_id > 0)
			{
				$new_id = '';
				$table_read_forum = array();
				$id_read_forum = '';
				
				if (isset($_COOKIE[$cookie_forum]) && $_COOKIE[$cookie_forum] != "")
				{
					$id_read_forum = $_COOKIE[$cookie_forum];
					if (eregi("[^0-9,]", $id_read_forum)) $id_read_forum = "";
					$table_read_forum = explode(',',$id_read_forum);						
				}
				
				$req = "SELECT MAX(id) FROM " . FORUM_MESSAGES_TABLE . " WHERE forum_id = " . $forum_id . " AND date > " . $user[4] . " GROUP BY thread_id";
				$sql = mysql_query($req);
				while (list($max_id) = mysql_fetch_array($sql))
				{
					if (!in_array($max_id,$table_read_forum)) 
					{
						if ($new_id != '')  $new_id .= ',';
						$new_id .= $max_id;
					}
				}
				
				if ($id_read_forum != '' && $new_id != '') $id_read_forum .= ',';
				setcookie($cookie_forum, $id_read_forum . $new_id, $timelimit);
			}
			else 
			{
				setcookie($cookie_forum, "");
				$req = "UPDATE " . SESSIONS_TABLE . " SET last_used = date WHERE user_id = '" . $user[0] . "'";
				$sql = mysql_query($req);
			}
			
        } 
		opentable();
        echo "<br /><br /><div style=\"text-align: center;\">" . _MESSAGESMARK . "</div><br /><br />";
        redirect("index.php?file=Forum", 2);
        closetable();
    } 


    function preview()
    {
        global $nuked, $user, $theme, $language, $bgcolor3, $bgcolor2, $texte, $titre, $usersig, $bbcodeoff, $smileyoff, $css, $visiteur;

	if ($language == "french" && ereg("WIN", PHP_OS)) setlocale (LC_TIME, "french");
	else if ($language == "french" && ereg("BSD", PHP_OS)) setlocale (LC_TIME, "fr_FR.ISO8859-1");
	else if ($language == "french") setlocale (LC_TIME, "fr_FR");
	else setlocale (LC_TIME, $language);

        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
        . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\">\n"
        . "<head><title>" . _PREVIEW  . "</title>\n"
        . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\n"
        . "<meta http-equiv=\"content-style-type\" content=\"text/css\" />\n"
        . "<link title=\"style\" type=\"text/css\" rel=\"stylesheet\" href=\"themes/" . $theme . "/style.css\" /></head>\n"
        . "<body style=\"background: " . $bgcolor2 . ";\">\n";

        if ($texte != "")
        {
            $date = strftime("%x %H:%M", time());
            $texte = str_replace("<br />", "\n", $texte);
            $texte = str_replace("_PLUS_", "+", $texte);
            $texte = stripslashes($texte);

            if ($bbcodeoff == 0)
            {
                $texte = htmlentities($texte);
            } 
            else
            {
                $texte = htmlentities($texte, ENT_NOQUOTES);

                $texte = eregi_replace("&lt;", "<", $texte);
                $texte = eregi_replace("&gt;", ">", $texte);
            }

            if ($smileyoff == 0)
            {
                $texte = icon($texte);
            } 

            if ($css == 0 || $visiteur < admin_mod("Forum"))
            {
                $texte = nk_CSS($texte);
            }

            if ($bbcodeoff == 0) $texte = BBcode($texte);

            if ($titre != "")
            {
                $titre = stripslashes($titre);
                $titre = htmlentities($titre);
                $title = "<b>" . $titre . "</b><br /><br />\n";
            } 
            else
            {
                $title = "";
            } 

            if ($user && $usersig == 1)
            {
                $sql = mysql_query("SELECT signature FROM " . USER_TABLE . " WHERE id = '" . $user[0] . "'");
                list($signature) = mysql_fetch_array($sql);

                if ($signature != "")
                {
                    $signature = stripslashes($signature);
                    $signature = nk_CSS($signature);
                    $signature = BBcode($signature);
                    $sign = "<br /><br /><table width=\"100%\"><tr style=\"background: " . $bgcolor2 . ";\"><td style=\"border-top: 1px dashed " . $bgcolor3 . ";\" colspan=\"2\">" . $signature . "</td></tr></table>\n";
                } 
                else
                {
                    $sign = "";
                } 
            } 

            echo "<table style=\"background: ". $bgcolor3 . "\" width=\"100%\" cellspacing=\"1\" cellpadding=\"4\">\n"
            . "<tr style=\"background: ". $bgcolor3 . "\"><td align=\"center\"><b>" . _MESSAGE . "</b></td></tr>\n"
            . "<tr style=\"background: ". $bgcolor2 . "\"><td><img src=\"images/posticon.gif\" alt=\"\" />" . _POSTEDON . "&nbsp;" . $date . "&nbsp;&nbsp;<br /><br />" . $title . $texte . $sign . "</td></tr></table>\n";
        } 
        else
        {
            echo "<div style=\"text-align: center;\"><br /><br /><b>" . _NOTEXTPREVIEW . "</b><br /><br /></div>\n";
        } 
        echo "<div style=\"text-align: center;\"><br /><a href=\"#\" onclick=\"javascript:window.close()\"><b>" . _CLOSEWINDOW . "</a></b><br /></div></body></html>";
    } 


    function bbcodehelp()
    {
        global $nuked, $theme, $bgcolor3, $bgcolor2;

        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
        . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\">\n"
        . "<head><title>" . _PREVIEW  . "</title>\n"
        . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\n"
        . "<meta http-equiv=\"content-style-type\" content=\"text/css\" />\n"
        . "<link title=\"style\" type=\"text/css\" rel=\"stylesheet\" href=\"themes/" . $theme . "/style.css\" /></head>\n"
        . "<body style=\"background: " . $bgcolor2 . ";\">\n"
	. "<div style=\"text-align: center;\"><br /><big><b>" . _LISTBBCODE . "</b></big></div><br />\n"
	. "<table width=\"100%\" cellpadding=\"3\" cellspacing=\"0\">\n"
	. "<tr><td>" . _BOLDBB ." :</td><td><b>[b]</b><i>" . _YOURTEXT ."</i><b>[/b]</b></td></tr>\n"
	. "<tr><td>" . _ITALICBB ." :</td><td><b>[i]</b><i>" . _YOURTEXT ."</i><b>[/i]</b></td></tr>\n"
	. "<tr><td>" . _UNDERLINEBB ." :</td><td><b>[u]</b><i>" . _YOURTEXT ."</i><b>[/u]</b></td></tr>\n"
	. "<tr><td>" . _COLORBB ." :</td><td><b>[color=#FF0000]</b><i>" . _YOURTEXT ."</i><b>[/color]</b></td></tr>\n"
	. "<tr><td>" . _SIZEBB ." :</td><td><b>[size=10]</b><i>" . _YOURTEXT ."</i><b>[/size]</b></td></tr>\n"
	. "<tr><td>" . _FONTBB ." :</td><td><b>[font=arial]</b><i>" . _YOURTEXT ."</i><b>[/font]</b></td></tr>\n"
	. "<tr><td>" . _STRIKEBB ." :</td><td><b>[strike]</b><i>" . _YOURTEXT ."</i><b>[/strike]</b></td></tr>\n"
	. "<tr><td>" . _BLINKBB ." :</td><td><b>[blink]</b><i>" . _YOURTEXT ."</i><b>[/blink]</b></td></tr>\n"
	. "<tr><td>" . _CENTERBB ." :</td><td><b>[center]</b><i>" . _YOURTEXT ."</i><b>[/center]</b></td></tr>\n"
	. "<tr><td>" . _ALIGNBB ." :</td><td><b>[align=right]</b><i>" . _YOURTEXT ."</i><b>[/align]</b></td></tr>\n"
	. "<tr><td>" . _LISTBB ." :</td><td><b>[li]</b><i>" . _YOURTEXT ."</i><b>[/li]</b></td></tr>\n"
	. "<tr><td>" . _URLBB ." :</td><td><b>[url]</b><i>http://site.com</i><b>[/url]</b></td></tr>\n"
	. "<tr><td>" . _URLTEXTBB ." :</td><td><b>[url=http://site.com ]</b><i>" . _YOURTEXT ."</i><b>[/url]</b></td></tr>\n"
	. "<tr><td>" . _EMAILBB ." :</td><td><b>[email]</b><i>mail@site.com</i><b>[/email]</b></td></tr>\n"
	. "<tr><td>" . _EMAILTEXTBB ." :</td><td><b>[email=mail@site.com]</b><i>" . _YOURTEXT ."</i><b>[/url]</b></td></tr>\n"
	. "<tr><td>" . _IMGBB ." :</td><td><b>[img]</b><i>http://site.com/image.jpg</i><b>[/img]</b></td></tr>\n"
	. "<tr><td>" . _IMGSIZEBB ." :</td><td><b>[img=50x50]</b><i>http://site.com/image.jpg</i><b>[/img]</b></td></tr>\n"
	. "<tr><td>" . _FLASHBB ." :</td><td><b>[flash]</b><i>http://site.com/anim.swf</i><b>[/flash]</b></td></tr>\n"
	. "<tr><td>" . _FLASHSIZEBB ." :</td><td><b>[flash=100x100]</b><i>http://site.com/anim.swf</i><b>[/flash]</b></td></tr>\n"
	. "<tr><td>" . _QUOTEBB ." :</td><td><b>[quote]</b><i>" . _YOURTEXT ."</i><b>[/quote]</b></td></tr>\n"
	. "<tr><td>" . _QUOTENAMEBB ." :</td><td><b>[quote=<i>name</i>]</b><i>" . _YOURTEXT ."</i><b>[/quote]</b></td></tr>\n"
	. "<tr><td>" . _CODEBB ." :</td><td><b>[code]</b><i>" . _YOURTEXT ."</i><b>[/code]</b></td></tr>\n"
	. "<tr><td>" . _FLIPBB ." :</td><td><b>[flip]</b><i>" . _YOURTEXT ."</i><b>[/flip]</b></td></tr>\n"
	. "<tr><td>" . _BLURBB ." :</td><td><b>[blur]</b><i>" . _YOURTEXT ."</i><b>[/blur]</b></td></tr>\n"
	. "<tr><td>" . _GLOWBB ." :</td><td><b>[glow]</b><i>" . _YOURTEXT ."</i><b>[/glow]</b></td></tr>\n"
	. "<tr><td>" . _GLOWCOLORBB ." :</td><td><b>[glow=red]</b><i>" . _YOURTEXT ."</i><b>[/glow]</b></td></tr>\n"
	. "<tr><td>" . _SHADOWBB ." :</td><td><b>[shadow]</b><i>" . _YOURTEXT ."</i><b>[/shadow]</b></td></tr>\n"
	. "<tr><td>" . _SHADOWCOLORBB ." :</td><td><b>[shadow=red]</b><i>" . _YOURTEXT ."</i><b>[/shadow]</b></td></tr>\n"
	. "</table><div style=\"text-align: center;\"><br /><br /><a href=\"#\" onclick=\"javascript:window.close()\"><b>" . _CLOSEWINDOW . "</a></b></div></body></html>";
    } 


    function del_file()
    {
        global $visiteur, $user, $nuked, $forum_id, $thread_id, $mess_id;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        $sql = mysql_query("SELECT file, auteur_id FROM " . FORUM_MESSAGES_TABLE . " WHERE id = '" . $mess_id . "'");
        list($filename, $auteur_id) = mysql_fetch_array($sql);

        if ($user && $auteur_id == $user[0] || $visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            $path = "upload/Forum/" . $filename;
            if (is_file($path))
            {
                $filesys = str_replace("/", "\\", $path);
                @chmod ($path, 0775);
                @unlink($path);
                @system("del $filesys");

                $upd = mysql_query("UPDATE " . FORUM_MESSAGES_TABLE . " SET file = '' WHERE id = '" . $mess_id . "'");
                echo "<br /><br /><div style=\"text-align: center;\">" . _FILEDELETED . "</div><br /><br />";
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function add_poll()
    {
        global $visiteur, $user, $nuked, $forum_id, $thread_id, $survey_field;

        opentable();

        $sql = mysql_query("SELECT auteur_id, sondage FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
        list($auteur_id, $sondage) = mysql_fetch_array($sql);

        $sql_poll = mysql_query("SELECT level_poll FROM " . FORUM_TABLE . " WHERE id = '" . $forum_id . "'");
        list($level_poll) = mysql_fetch_array($sql_poll);

        if ($user && $user[0] == $auteur_id && $sondage == 1 && $visiteur >= $level_poll)
        {
            if ($survey_field > $nuked['forum_field_max'])
            {
                $max = $nuked['forum_field_max'];
            } 
            else
            {
                $max = $survey_field;
            } 

            echo "<br /><div style=\"text-align: center;\"><big><b>" . _POSTSURVEY . "</b></big></div><br />\n"
            . "<form method=\"post\" action=\"index.php?file=Forum&amp;op=send_poll\">\n"
            . "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n"
            . "<tr><td align=\"right\"><b>" . _QUESTION . " :</b> <input type=\"text\" name=\"titre\" size=\"40\" /></td></tr>\n"
            . "<tr><td>&nbsp;</td></tr>\n";

            $r = 0;
            while ($r < $max)
            {
                $r++;
                echo "<tr><td align=\"right\">" . _OPTION . "&nbsp;" . $r . " : <input type=\"text\" name=\"option[]\" size=\"40\" /></td></tr>\n";

            } 

            echo "<tr><td>&nbsp;<input type=\"hidden\" name=\"thread_id\" value=\"" . $thread_id . "\" />\n"
            . "<input type=\"hidden\" name=\"forum_id\" value=\"" . $forum_id . "\" />\n"
            . "<input type=\"hidden\" name=\"max_option\" value=\"" . $max . "\" /></td></tr>\n"
            . "<tr><td align=\"center\"><input type=\"submit\" value=\"" . _ADDTHISPOLL . "\" /></td></tr></table></form><br />\n";
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function send_poll($titre, $option, $thread_id, $forum_id, $max_option)
    {
        global $visiteur, $user, $nuked;

        opentable();

        $sql = mysql_query("SELECT auteur_id, sondage FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
        list($auteur_id, $sondage) = mysql_fetch_array($sql);

        $sql_poll = mysql_query("SELECT level_poll FROM " . FORUM_TABLE . " WHERE id = '" . $forum_id . "'");
        list($level_poll) = mysql_fetch_array($sql_poll);

        if ($user && $user[0] == $auteur_id && $sondage == 1 && $visiteur >= $level_poll)
        {
            if ($option[1] != "")
            {
                $titre = addslashes($titre);

                $add = mysql_query("INSERT INTO " . FORUM_POLL_TABLE . " ( `id` , `thread_id` , `titre` ) VALUES ( '' , '" . $thread_id . "' , '" . $titre . "' )");

                $sql2 = mysql_query("SELECT id FROM " . FORUM_POLL_TABLE . " WHERE thread_id = '" . $thread_id . "'");
                list($poll_id) = mysql_fetch_array($sql2);

                if ($max_option > $nuked['forum_field_max'])
                {
                    $max = $nuked['forum_field_max'];
                } 
                else
                {
                    $max = $max_option;
                } 

                $r = 0;
                while ($r < $max)
                {
                    $vid = $r + 1;
                    $options = $option[$r];
                    $options = addslashes($options);

                    if ($options != "")
                    {
                        $sql3 = mysql_query("INSERT INTO " . FORUM_OPTIONS_TABLE . " ( `id` , `poll_id` , `option_text` , `option_vote` ) VALUES ( '" . $vid . "' , '" . $poll_id . "' , '" . $options . "' , '' )");
                    } 
                    $r++;
                } 

                echo "<br /><br /><div style=\"text-align: center;\">" . _POLLADD . "</div><br /><br />";
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 
            else
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _2OPTIONMIN . "</div><br /><br />";
                $url = "index.php?file=Forum&op=add_poll&survey_field=" . $max_option . "&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 

        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function vote($poll_id)
    {
        global $visiteur, $user, $nuked, $voteid, $forum_id, $thread_id, $user_ip;

        opentable();

        if ($voteid != "")
        {
            if ($visiteur > 0)
            {
                $sql_poll = mysql_query("SELECT level_vote FROM " . FORUM_TABLE . " WHERE id = '" . $forum_id . "'");
                list($level_vote) = mysql_fetch_array($sql_poll);

                if ($visiteur >= $level_vote)
                {
                    $sql = mysql_query("SELECT auteur_ip FROM " . FORUM_VOTE_TABLE . " WHERE auteur_id = '" . $user[0] . "' AND poll_id = '" . $poll_id . "'");
                    $check = mysql_num_rows($sql);

                    if ($check == 0)
                    {
                        $upd = mysql_query("UPDATE " . FORUM_OPTIONS_TABLE . " SET option_vote = option_vote + 1 WHERE id = '" . $voteid . "' AND poll_id = '" . $poll_id . "'");
                        $insert = mysql_query("INSERT INTO " . FORUM_VOTE_TABLE . " ( `poll_id` , `auteur_id` , `auteur_ip` ) VALUES ( '" . $poll_id . "' , '" . $user[0] . "' , '" . $user_ip . "' )");

                        echo  "<br /><br /><div style=\"text-align: center;\">" . _VOTESUCCES . "</div><br /><br />";
                    } 
                    else
                    {
                        echo "<br /><br /><div style=\"text-align: center;\">" . _ALREADYVOTE . "</div><br /><br />";
                    } 

                } 
                else
                {
                    echo "<br /><br /><div style=\"text-align: center;\">" . _BADLEVEL . "</div><br /><br />";
                } 

            } 
            else
            {
                echo "<br /><br /><div style=\"text-align: center;\">" . _ONLYMEMBERSVOTE . "</div><br /><br />";
            } 

        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _NOOPTION . "</div><br /><br />";
        } 

        $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
        redirect($url, 2);

        closetable();
    } 

    function del_poll($poll_id, $thread_id, $forum_id)
    {
        global $visiteur, $user, $nuked, $forum_id, $thread_id, $confirm;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        $sql = mysql_query("SELECT auteur_id FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
        list($auteur_id) = mysql_fetch_array($sql);

        if ($user && $user[0] == $auteur_id || $visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            if ($confirm == _YES)
            {
                $del1 = mysql_query("DELETE FROM " . FORUM_POLL_TABLE . " WHERE id = '" . $poll_id . "'");
                $del2 = mysql_query("DELETE FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                $del2 = mysql_query("DELETE FROM " . FORUM_VOTE_TABLE . " WHERE poll_id = '" . $poll_id . "'");
                $upd = mysql_query("UPDATE " . FORUM_THREADS_TABLE . " SET sondage = 0 WHERE id = '" . $thread_id . "'");

                echo "<br /><br /><div style=\"text-align: center;\">" . _POLLDELETE . "</div><br /><br />";
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 
            else if ($confirm == _NO)
            {
                echo "<br /><br /><br><center>" . _CONFIRMDELPOLL . "" . _DELCANCEL . "</div><br /><br />";
                $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
                redirect($url, 2);
            } 
            else
            {
                echo "<form method=\"post\" action=\"index.php?file=Forum&amp;op=del_poll\">\n"
                 . "<div style=\"text-align: center;\"><br />" . _CONFIRMDELPOLL . "<br /><br />\n"
                 . "<input type=\"hidden\" name=\"poll_id\" value=\"" . $poll_id . "\" />\n"
                 . "<input type=\"hidden\" name=\"thread_id\" value=\"" . $thread_id . "\" />\n"
                 . "<input type=\"hidden\" name=\"forum_id\" value=\"" . $forum_id . "\" />\n"
                 . "<input type=\"submit\" name=\"confirm\" value=\"" . _YES . "\" />\n"
                 . "&nbsp;<input type=\"submit\" name=\"confirm\" value=\"" . _NO . "\" /><br /></div></form>\n";
            } 

        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function edit_poll($poll_id)
    {
        global $visiteur, $user, $nuked, $forum_id, $thread_id;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        $sql = mysql_query("SELECT auteur_id FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
        list($auteur_id) = mysql_fetch_array($sql);

        if ($user && $user[0] == $auteur_id || $visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            $sql1 = mysql_query("SELECT titre FROM " . FORUM_POLL_TABLE . " WHERE id = '" . $poll_id . "'");
            list($titre) = mysql_fetch_array($sql1);
            $titre = stripslashes($titre);

            echo "<br /><div style=\"text-align: center;\"><big><b>" . _POSTSURVEY . "</b></big></div><br />\n"
            . "<form method=\"post\" action=\"index.php?file=Forum&amp;op=modif_poll\">\n"
            . "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n"
            . "<tr><td align=\"right\"><b>" . _QUESTION . " :</b> <input type=\"text\" name=\"titre\" size=\"40\" value=\"" . $titre . "\" /></td></tr>\n"
            . "<tr><td>&nbsp;</td></tr>\n";

            $sql2 = mysql_query("SELECT id, option_text FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "' ORDER BY id ASC");
            $r = 0;
            while (list($option_id, $option_text) = mysql_fetch_array($sql2))
            {
                $r++;
                $option_text = stripslashes($option_text);
                echo "<tr><td align=\"right\">" . _OPTION . "&nbsp;" . $r . " : <input type=\"text\" name=\"option[" . $r . "]\" size=\"40\" value=\"" . $option_text . "\" /></td></tr>\n";
            }

            $r++;

            echo "<tr><td align=\"right\">" . _OPTION . "&nbsp;" . $r . " : <input type=\"text\" name=\"newoption\" size=\"40\" /></td></tr>\n"
            . "<tr><td>&nbsp;<input type=\"hidden\" name=\"poll_id\" value=\"" . $poll_id . "\" />\n"
            . "<input type=\"hidden\" name=\"thread_id\" value=\"" . $thread_id . "\" />\n"
            . "<input type=\"hidden\" name=\"forum_id\" value=\"" . $forum_id . "\" /></td></tr>\n"
            . "<tr><td align=\"center\"><input type=\"submit\" value=\"" . _MODIFTHISPOLL . "\" /></td></tr></table></form><br />\n";

        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
            $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
            redirect($url, 2);
        } 

        closetable();
    } 

    function modif_poll($poll_id, $titre, $option, $newoption, $thread_id, $forum_id)
    {
        global $visiteur, $user, $nuked;

        opentable();

        $result = mysql_query("SELECT moderateurs FROM " . FORUM_TABLE . " WHERE " . $visiteur . " >= niveau AND id = '" . $forum_id . "'");
        list($modos) = mysql_fetch_array($result);

        if ($user && $modos != "" && ereg($user[0], $modos))
        {
            $administrator = 1;
        } 
        else
        {
            $administrator = 0;
        } 

        $sql = mysql_query("SELECT auteur_id FROM " . FORUM_THREADS_TABLE . " WHERE id = '" . $thread_id . "'");
        list($auteur_id) = mysql_fetch_array($sql);

        if ($user && $user[0] == $auteur_id || $visiteur >= admin_mod("Forum") || $administrator == 1)
        {
            $titre = addslashes($titre);

            $upd1 = mysql_query("UPDATE " . FORUM_POLL_TABLE . " SET titre = '" . $titre . "' WHERE id = '" . $poll_id . "'");

            $r = 0;
            while ($r < $nuked['forum_field_max'])
            {
                $r++;
                $options = $option[$r];
                $options = addslashes($options);

                if ($options != "")
                {
                    $upd2 = mysql_query("UPDATE " . FORUM_OPTIONS_TABLE . " SET option_text = '" . $options . "' WHERE poll_id = '" . $poll_id . "' AND id = '" . $r . "'");
                } 
                else
                {
                    $del = mysql_query("DELETE FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "' AND id = '" . $r . "'");
                } 

            } 

            if ($newoption != "")
            {
                $newoption = addslashes($newoption);
                $sql2 = mysql_query("SELECT id FROM " . FORUM_OPTIONS_TABLE . " WHERE poll_id = '" . $poll_id . "' ORDER BY id DESC LIMIT 0, 1");
                list($option_id) = mysql_fetch_array($sql2);
                $s = $option_id + 1;

                $sql3 = mysql_query("INSERT INTO " . FORUM_OPTIONS_TABLE . " ( `id` , `poll_id` , `option_text` , `option_vote` ) VALUES ( '" . $s . "' , '" . $poll_id . "' , '" . $newoption . "', '0')");
            } 

            echo "<br /><br /><div style=\"text-align: center;\">" . _POLLMODIF . "</div><br /><br />";
        } 
        else
        {
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
        } 

        $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
        redirect($url, 2);
        closetable();
    } 


    function notify()
    {
        global $user, $nuked, $do, $forum_id, $thread_id;

        opentable();

	if ($user[0] != "")
	{

            if ($do == "on")
            {
            $notify = 1;
            $notify_texte = _NOTIFYISON;
            }
            else
            {
            $notify = 0;
            $notify_texte = _NOTIFYISOFF;
            }
       
		$upd = mysql_query("UPDATE " . FORUM_MESSAGES_TABLE . " SET emailnotify = '" . $notify . "' WHERE thread_id = '" . $thread_id . "' AND auteur_id = '" . $user[0] . "'");

		echo "<br /><br /><div style=\"text-align: center;\">" . $notify_texte . "</div><br /><br />";

	}
	else
	{
            echo "<br /><br /><div style=\"text-align: center;\">" . _ZONEADMIN . "</div><br /><br />";
	}

        $url = "index.php?file=Forum&page=viewtopic&forum_id=" . $forum_id . "&thread_id=" . $thread_id;
        redirect($url, 2);
        closetable();
    } 


    switch ($op)
    {
        case"index":
            index();
            break;

        case"post":
            post();
            break;

        case"reply":
            reply();
            break;

        case"edit":
            edit($mess_id);
            break;

        case"del":
            del($mess_id);
            break;

        case"del_topic":
            del_topic($thread_id);
            break;

        case"move":
            move();
            break;

        case"lock":
            lock();
            break;

        case"announce":
            announce();
            break;

        case"mark":
            mark();
            break;

        case"preview":
            preview();
            break;

        case"smilies":
            smilies();
            break;

        case"bbcodehelp":
            bbcodehelp();
            break;

        case"del_file":
            del_file();
            break;

        case"add_poll":
            add_poll();
            break;

        case"send_poll":
            send_poll($titre, $option, $thread_id, $forum_id, $max_option);
            break;

        case"vote":
            vote($poll_id);
            break;

        case"del_poll":
            del_poll($poll_id, $thread_id, $forum_id);
            break;

        case"edit_poll":
            edit_poll($poll_id);
            break;

        case"modif_poll":
            modif_poll($poll_id, $titre, $option, $newoption, $thread_id, $forum_id);
            break;

        case"notify":
            notify();
            break;

        default:
            index();
            break;
    } 

} 
else if ($level_access == -1)
{
    opentable();
    echo "<br /><br /><div style=\"text-align: center;\">" . _MODULEOFF . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a><br /><br /></div>";
    closetable();
} 
else if ($level_access == 1 && $visiteur == 0)
{
    opentable();
    echo "<br /><br /><div style=\"text-align: center;\">" . _USERENTRANCE . "<br /><br /><b><a href=\"index.php?file=User&amp;op=login_screen\">" . _LOGINUSER . "</a> | <a href=\"index.php?file=User&amp;op=reg_screen\">" . _REGISTERUSER . "</a></b><br /><br /></div>";
    closetable();
} 
else
{
    opentable();
    echo "<br /><br /><div style=\"text-align: center;\">" . _NOENTRANCE . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a><br /><br /></div>";
    closetable();
}

?>
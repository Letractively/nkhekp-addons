<?php 
//******************************************************************//
//   Module AlertePost pour SP 4.4 / NK 1.7.7                       //
//   par Tassin  tassin@clancvrd.com                                //    
//   wwww.nkhelp.free.fr                                            //
//   update.php réalisé par H@D3S http://www.protech-studio.com/    //
//   modifié par Tassin pour ce module                              //
//   CMS Nuked-Klan wwww.nuked-klan.org                             //
//                                                                  //
//******************************************************************//

if ( is_file('nuked.php') ) include('nuked.php');
else die( 'update.php must be near nuked.php' );

function head()
{
	?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Mise à jour du module AlertePost en v1.1.0</title>
    <style  type="text/css">
	  body { font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;color: #999;background:#FFF;margin:0 auto;padding:0;}
      h1 { margin:0;padding:0;font-size:14px;font-weight:normal; }
      h2 { margin:0;padding:0;font-size:11px;font-weight:bold;color:#0099CC; }
      h3 { margin:0;padding:0;font-size:10px;font-weight:normal; }
      a:link, a:visited, a:active { font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;color: #666;text-decoration:none; }
      a:hover{ font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;text-decoration:underline; }
	  p { margin:0;padding:0; }
	</style>
    </head>
    <body>
    <?php
}

function index()
{
   head();
   ?>
   <h1 style="font-size:14px; text-align:center; padding-top:35px;">Vous allez mettre à jour le module AlertePost en v1.1.0 de <a href="http://www.phabryss.free.fr" target="_blank" title="Tassin" style="font-size:14px; color:#0099CC;">Tassin</a></h1>
   <div style="width:550px; margin:20px auto 0 auto;">
   <h2 style="padding-top:20px;">Mise à jour du module AlertPost en v1.1.0 :</h2>
   <p style="padding-top:15px;">
     <font color="#0099CC">1-</font> Dézipper l'archive<br />
     <font color="#0099CC">2-</font> Uploader le contenu du dossier Module_AlertPost_v1.1.0 à la racine de votre FTP<br />
	 <font color="#0099CC">3-</font> Lancer la mise à jour du module par : http://<em><b>votre_adresse</b></em>/update.php<br />
   </p>
   <br/>
   <br/>
   <form action="update.php?op=send" method="post">
   <p style="text-align:center;">
     <input type="submit" name="conf" value="Poursuivre la mise à jour" />&nbsp;&nbsp;<input type="submit" name="nul" value="Annuler" />
   </p>
   </form>
   </div>
   <?php
}



function send()
{
  global $nuked,$db_prefix;
  
  if ( isset( $_POST['conf'] ) )
  {
    head();
	
	$sql = "DROP TABLE IF EXISTS $db_prefix". _alerteposte_pref_edit ."";
	$req = mysql_query($sql);
	
	$sql = "CREATE TABLE $db_prefix". _alerteposte_pref_edit ." (
	`name` varchar(255) NOT NULL default '',
	`value` text NOT NULL	)";
	$req = mysql_query($sql);
	
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('userFor', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('niveauSel', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('sujet', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('message', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('sendUser', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('sendTitre', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_edit ." VALUES ('sendUrl', '');";
	$req = mysql_query($inst);
	
	$sql = "DROP TABLE IF EXISTS $db_prefix". _alerteposte_pref_reply ."";
	$req = mysql_query($sql);
	
	$sql = "CREATE TABLE $db_prefix". _alerteposte_pref_reply ." (
	`name` varchar(255) NOT NULL default '',
	`value` text NOT NULL	)";
	$req = mysql_query($sql);
	
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('userFor', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('niveauSel', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('sujet', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('message', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('sendUser', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('sendTitre', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref_reply ." VALUES ('sendUrl', '');";
	$req = mysql_query($inst);
	
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref ." VALUES ('alertePostActive', '');";
	$req = mysql_query($inst);
	
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref ." VALUES ('alerteReplyActive', '');";
	$req = mysql_query($inst);
	
	$inst = "INSERT INTO $db_prefix". _alerteposte_pref ." VALUES ('alerteEditActive', '');";
	$req = mysql_query($inst);
	
	?><h1 style="text-align:center;padding-top:50px;">Mise à jour du module AlertePost en v1.1.0 effectuée avec succès.<br /><br />Redirection...</h1><?php
	//Supression automatique du fichier update.php
	@unlink("update.php");
	redirect("index.php", 3);

  }
  else if ( isset( $_POST['nul'] ) )
  {
    head();
	@unlink("update.php");
    ?><h1 style="text-align:center;padding-top:50px;">Mise à jour  annulée. Redirection vers l'index.</h1><?php
    redirect("index.php", 3);
  }
  else
  {
    header('Location: update.php');
  }
}

switch($op)
{
	case"index":
	index();
	break;
	
	case"send":
	send();
	break;

	default:
	index();
	break;
}
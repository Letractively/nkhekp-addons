<?php 
//******************************************************************//
//   Module AlertePost						                        //
//   par Tassin tassin@gmail.com                                    //    
//   wwww.nkhelp.fr		                                            //
//   install.php réalisé par H@D3S http://www.protech-studio.com/   //
//   modifié par Tassin pour ce module                              //
//   CMS Nuked-Klan wwww.nuked-klan.eu                              //
//                                                                  //
//******************************************************************//

define ("INDEX_CHECK", 1);

if (is_file('globals.php')) include ("globals.php");
else die('<br /><br /><div style=\"text-align: center;\"><b>install.php must be near globals.php</b></div>');
if (is_file('conf.inc.php')) @include ("conf.inc.php");
else die('<br /><br /><div style=\"text-align: center;\"><b>install.php must be near conf.inc.php</b></div>');
if (is_file('nuked.php')) include('nuked.php');
else die('<br /><br /><div style=\"text-align: center;\"><b>install.php must be near nuked.php</b></div>');

function head()
{
?>
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Installation du module AlertePost v1.0.0 NK 1.7.9</title>
    <style  type="text/css">
	  body { font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;color: #999;background:#FFF;margin:0 auto;padding:0;text-align:center;}
      h1 { margin:15px 0 0;padding:0;font-size:14px;font-weight:normal;text-align:center;padding-bottom: 10px;border-bottom:1px dashed #0099CC }
	  h1.confirm { border: none;padding:0;margin:0;}
      h2 { margin:0;padding:0;font-size:11px;font-weight:bold;color:#0099CC; padding-top:20px;text-align:left}
      h3 { margin:0;padding:0;font-size:10px;font-weight:normal; }
      a:link, a:visited, a:active { font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;color: #666;text-decoration:none; }
      a:hover{ font-family:Verdana, Arial, Helvetica, sans-serif;font-size: 10px;text-decoration:underline; }
	  p { margin:0;padding:0 0 10px 0; text-align:left; border-bottom: 1px dashed #0099CC; }
	  div.head { width:500px; margin:20px auto; border:1px dashed #0099CC; padding: 15px; }
	  .attention { color:red; font-style:italic; font-size:10px;margin-top: 5px; }
	  p.description { border: none;margin-top: 20px;}
	</style>
    </head>
    <body>
<?php
}

function index()
{
   head();
?>
   <div class="head">
   <a href="http://www.nkhelp.fr"><img src="http://www.nkhelp.fr/images/pages/banners/banniere_468.png" alt="" title="Site communautaire d'entraide consacré à la création et modification de modules, patchs, blocks et divers pour le CMS Nuked-KlaN" /></a>
   <h1>Vous allez installer le module AlertePost v1.0.0 de <a href="mailto:tassin@gmail.com?subject=Module Alerte Post v1.0.0 NK 1.7.9&body=Bonjour," target="_blank" title="Tassin" style="font-size:14px; color:#0099CC;">Tassin</a><div class="attention">Module compatible avec la version nk 1.7.9 uniquement *</div></h1>
   <h2>Ce module contient :</h2>
   <p style="padding-top:15px;">
     - 1 dossier " <font color="#0099CC">modules</font> " (contenant : 2 dossiers)<br />
     - 1 fichier " <font color="#0099CC">readme.txt</font> "<br />
     - 1 fichier " <font color="#0099CC">licence.txt</font> "<br />
     - 1 fichier " <font color="#0099CC">install.php</font> "<br />
   </p>
   <h2>Installation du module :</h2>
   <p style="padding-top:15px;">
     <font color="#0099CC">1-</font> Dézipper l'archive<br />
     <font color="#0099CC">2-</font> Uploader le contenu du dossier <b><em>"modules"</em></b> à la racine de votre FTP<br />
     <span class="attention">&rarr; Ce module &eacute;crase un fichier du module Forum. Attention donc si vous aviez d&eacute;j&agrave; install&eacute; un autre patch auparavant.</span><br />
	 <font color="#0099CC">3-</font> Lancer l'installation du module par : http://<em><b>votre_adresse</b></em>/install.php<br />
   </p>
   <p class="description">
   * pour les versions nk 1.7.7 et nk sp 4.4 t&eacute;l&eacute;chargez<a href="http://nkhekp-addons.googlecode.com/files/Module_AlertPost_v1.1.0_177_sp44.rar"> <b><em>cette archive</em></b></a>
   </p>
   <br/>
   <form action="install.php?op=send" method="post">
   <p style="text-align:center;">
     <input type="submit" name="conf" value="Poursuivre l'installation" />&nbsp;&nbsp;<input type="submit" name="nul" value="Annuler" />
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
	$table_pref = $nuked['prefix'] . "_alerteposte_pref";
	$table_pref_edit = $nuked['prefix'] . "_alerteposte_pref_edit";
	$table_pref_reply = $nuked['prefix'] . "_alerteposte_pref_reply";
	$sql = "DROP TABLE IF EXISTS " . $table_pref;
	$req = mysql_query($sql);
	
	$sql = "CREATE TABLE " . $table_pref . " (
	`name` varchar(20) NOT NULL default '',
	`value` text NOT NULL,
	PRIMARY KEY  (`name`))";
	$req = mysql_query($sql);
	
	$inst = "INSERT INTO " . $table_pref . " VALUES ('alerteActive', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('userFor', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('niveauSel', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('sujet', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('message', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('sendUser', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('sendTitre', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('sendUrl', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('alertePostActive', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('alerteReplyActive', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref . " VALUES ('alerteEditActive', '');";
	$req = mysql_query($inst);
	
	$sql = "DROP TABLE IF EXISTS ". $table_pref_edit;
	$req = mysql_query($sql);
	
	$sql = "CREATE TABLE " . $table_pref_edit . " (
	`name` varchar(20) NOT NULL default '',
	`value` text NOT NULL,
	PRIMARY KEY  (`name`))";
	$req = mysql_query($sql);
	
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('userFor', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('niveauSel', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('sujet', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('message', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('sendUser', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('sendTitre', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_edit . " VALUES ('sendUrl', '');";
	$req = mysql_query($inst);
	
	$sql = "DROP TABLE IF EXISTS " . $table_pref_reply;
	$req = mysql_query($sql);
	
	$sql = "CREATE TABLE " . $table_pref_reply . " (
	`name` varchar(20) NOT NULL default '',
	`value` text NOT NULL,
	PRIMARY KEY  (`name`))";
	$req = mysql_query($sql);
	
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('userFor', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('niveauSel', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('sujet', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('message', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('sendUser', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('sendTitre', '');";
	$req = mysql_query($inst);
	$inst = "INSERT INTO " . $table_pref_reply . " VALUES ('sendUrl', '');";
	$req = mysql_query($inst);
	
	// ajout du nouveau module si il n'existe pas déjà
	$test = mysql_query("SELECT id FROM " . $nuked['prefix'] . "_modules WHERE nom='AlertePost'");
	$req = mysql_num_rows($test);
	if($req == 0) {
		$sql = "INSERT INTO " . $nuked['prefix'] . "_modules ( `id` , `nom` , `niveau` , `admin` ) VALUES ('', 'AlertePost', '0', '9');";
    	$req = mysql_query($sql);
	}
	
	?>
    <div class="head">
    <h1 class="confirm">Installation du module AlertePost éffectuée avec succès.<br /><br />Redirection vers l'index...</h1>
	</div><?php
	//Supression automatique du fichier install.php
	@unlink("install.php");
	redirect("index.php", 3);

  }
  else if ( isset( $_POST['nul'] ) )
  {
    head();
	@unlink("install.php");
    ?>
    <div class="head">
    <h1 class="confirm">Installation annulée. Redirection vers l'index...</h1>
	</div>
	<?php
    redirect("index.php", 3);
  }
  else
  {
    header('Location: install.php');
  }
}

switch($_GET['op'])
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
?>
</body>
</html>
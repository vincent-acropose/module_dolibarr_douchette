<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/douchette/class/OFTache.class.php');
dol_include_once('/douchette/lib/douchette.lib.php');

if(empty($user->rights->of->of->lire)) accessforbidden();

$langs->load('douchette@douchette');

$action = GETPOST('action');
$fk_user = GETPOST('fk_user');
$fk_of_post = GETPOST('fk_of_post');

$temps = GETPOST('tempsHeure').":".GETPOST('tempsMinute').":".GETPOST('tempsSeconde');

$openclose = (int)GETPOST('openclose');

$object = new OFTache($db);

$hookmanager->initHooks(array('douchettecard'));

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$fk_of_post = explode("_", $fk_of_post);
			$object->fk_user = $fk_user;
			$object->fk_of = $fk_of_post[0];
			$object->fk_post = $fk_of_post[1];

			if ($openclose == $object::OPEN) {

				if ($object->open()) {
					header('Location: '.dol_buildpath('/douchette/card.php', 1));
					exit;
				}
				else {
					setEventMessage("Problème lors de la sauvegarde", 'errors');
				}
				break;
			}
			elseif ($openclose == $object::PARTIALCLOSE) {
				if ($object->close(1, $temps) != -1) {
					header('Location: '.dol_buildpath('/douchette/card.php', 1));
					exit;
				}
				else {
					setEventMessage("Problème lors de la sauvegarde", 'errors');
				}
				break;
			}
			elseif ($openclose == $object::FINALCLOSE) {
				if ($object->close(2, $temps)) {
					header('Location: '.dol_buildpath('/douchette/card.php', 1));
					exit;
				}
				else {
					setEventMessage("Problème lors de la sauvegarde", 'errors');
				}
				break;
			}
	}
}


/**
 * View
 */

$title=$langs->trans("douchette");
llxHeader('',$title);

$head = douchette_prepare_head($object);
$picto = 'douchette@douchette';
dol_fiche_head($head, 'card', $langs->trans("douchette"), 0, $picto);

$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified 

if (empty($reshook)) {

	if (empty($fk_user)) {
		print("<form id='searchFormList' method='POST' action='card.php'");
		print("<table class='tagtable liste listwithfilterbefore'>");
		print("<tr class='liste_titre_filter'>");
		print("<td class='liste_titre'><input class='flat' size=10 name='fk_user' type='text' placeholder='Collaborateur' autofocus /></td>");
		print('</tr>');
		print("</table>");
		print("</form>");
	}
	else if (empty($fk_of_post)) {
		print("<form id='searchFormList' method='POST' action='card.php'");
		print("<table class='tagtable liste listwithfilterbefore'>");
		print("<tr class='liste_titre_filter'>");
		print("<input type=hidden name=fk_user value=".$fk_user." >");
		print("<td class='liste_titre'><input class='flat' size=10 name='fk_of_post' type='text' autofocus /></td>");
		print("<td class='liste_titre'><input type=submit value=ok /></td>");
		print('</tr>');
		print("</table>");
		print("</form>");
	}
	else {
		print("<form id='searchFormList' method='POST' action='card.php'");
		print("<table class='tagtable liste listwithfilterbefore'>");
		print("<tr class='liste_titre_filter'>");
		print("<input type=hidden name=fk_user value=".$fk_user." >");
		print("<input type=hidden name=fk_of_post value=".$fk_of_post." >");
		print "<input type=hidden name=action value=save />";
		print("<td class='liste_titre'>Ouvrir <input name='openclose' type='radio' value='0'/></td><br>");
		print("<td class='liste_titre'>Fermeture Partiel <input name='openclose' type='radio' value='1'/></td><br>");
		print("<td class='liste_titre'>Fermeture Total <input name='openclose' type='radio' value='2'/></td><br>");
		print("<td class='liste_titre'>Temps (Facultatif) <input name='tempsHeure' type='text' value=0 style='width: 20px;' placeholder='Heure'/> <input name='tempsMinute' value=0 type='text' style='width: 20px;' placeholder='Minute'/> <input name='tempsSeconde' value=0 type='text' style='width: 20px;' placeholder='Seconde'/></td><br>");
		print("<td class='liste_titre'><input type=submit value=ok /></td>");
		print('</tr>');
		print("</table>");
		print("</form>");
	}
}

llxFooter();
<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/douchette/class/douchette.class.php');
dol_include_once('/douchette/lib/douchette.lib.php');

if(empty($user->rights->of->of->lire)) accessforbidden();

$langs->load('douchette@douchette');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

$mode = 'view';
if (empty($user->rights->of->of->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$PDOdb = new TPDOdb;
$object = new Tdouchette;

if (!empty($id)) $object->load($PDOdb, $id);
elseif (!empty($ref)) $object->loadBy($PDOdb, $ref, 'ref');

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
		case 'modifyOF':

			break;
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
	if ($action == "modifyOF") {
	}

	else if ($action == "searchOF") {
		dol_include_once('/of/class/actions_of.class.php');
		$ref = GETPOST('search_ref');
		$posteOF = (!empty($_POST['poste'])) ? GETPOST('poste') : "";
		$closeOF = (!empty($_POST['closeOF'])) ? True : False;

		$sqlStatus = $db->query("SELECT of.status
			FROM ".MAIN_DB_PREFIX."assetOf of 
			WHERE of.numero='".$ref."'");

		$sqlPoste = $db->query("SELECT w.rowid, w.name 
			FROM ".MAIN_DB_PREFIX."assetOf of 
			JOIN ".MAIN_DB_PREFIX."asset_workstation_of wof ON (of.rowid = wof.fk_assetOf) 
			JOIN ".MAIN_DB_PREFIX."workstation w ON (wof.fk_asset_workstation = w.rowid) 
			WHERE of.numero='".$ref."'");

		$sqlProducts = $db->query("SELECT p.ref, p.label 
			FROM ".MAIN_DB_PREFIX."assetOf_line ofl
			JOIN ".MAIN_DB_PREFIX."product p ON (ofl.fk_product = p.rowid)
			JOIN ".MAIN_DB_PREFIX."assetOf of ON (ofl.fk_assetOf = of.rowid)
			WHERE of.numero='".$ref."'");

		if ($sqlStatus->num_rows > 0) {

			while ($status = $db->fetch_object($sqlStatus)) {
				if ($status->status == "OPEN") {
					if ($posteOF != "") {
						while ($postes = $db->fetch_object($sqlPoste)) {
							if ($postes->rowid == $posteOF) {
								$detPoste = "";
								while ($product = $db->fetch_object($sqlProducts)) {
									$detPoste .= "<td class='liste_titre'>".$product->label."</td>";
								}
							}
						}
					}

					else {
						$detPoste = '<td class="liste_titre"><select name="poste" onchange="this.parentNode.submit()">';
						$detPoste .= "<option selected > -- Selectionner un poste -- </option>";
						while ($postes = $db->fetch_object($sqlPoste)) {
							$detPoste .= "<option value='".$postes->rowid."'>".$postes->name."</option>";
						}
						$detPoste .= "</select></td>";
					}
				}
				elseif($status->status == "CLOSE") {
					$ref = "";
					setEventMessages("L'OF est clos");
				}
				else {
					$ref = "";
					setEventMessages("Erreur lors de la récupération de l'OF.", $hookmanager->errors, 'errors');
				}
			}
		}
		else {
			$ref = "";
			setEventMessages("L'OF n'existe pas.", $hookmanager->errors, 'errors');
		}
	}

	print("<form id='searchFormList' method='POST' action='card.php?action=searchOF'");
	print("<table class='tagtable liste listwithfilterbefore'>");
	print("<tr class='liste_titre_filter'>");
	print("<td class='liste_titre'><input class='flat' size=10 name='search_ref' type='text' value='".$ref."' autofocus /></td>");
	print('</tr>');
	print("<tr class='liste_titre_filter'>");
	print($detPoste);
	print('</tr>');
	print("</table>");
	print("</form>");
}

llxFooter();
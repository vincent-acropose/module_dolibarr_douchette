<?php

require 'config.php';
dol_include_once('/douchette/class/douchette.class.php');

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

if(empty($user->rights->douchette->write->modify_stock)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('douchette@douchette');

$PDOdb = new TPDOdb;
$object = new Tdouchette;
$product = new Product($db);

$hookmanager->initHooks(array('douchettelist'));
$idProduct = GETPOST('TListTBS')['douchette']['search']['ref'];
$stock = GETPOST("modifyStock");

if ($idProduct > 0) {
	$product->fetch($idProduct);
	$product->load_virtual_stock();
}

if ($stock || $stock==="0") {
	$correct = $stock - $product->stock_reel;
	if ($correct != 0) {
		$product->correct_stock($user, 1, $correct, 0, 'Inventaire Tournant '.date('d/m/Y'));
	}

	header('Location: stock.php?TListTBS[douchette][search][ref]='.$product->id);
}

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('',$langs->trans('douchetteList'),'','');

$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_douchette', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new TListviewTBS('douchette');
echo $r->render($PDOdb, $sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
	)
	,'search' => array(
		'ref' => array('recherche' => true, 'table' => 't', 'field' => 'ref')
	)
	,'translate' => array()
	,'hide' => array(
		'rowid'
	)
	,'liste' => array(
		'titre' => $langs->trans('douchetteStock')
		,'image' => img_picto('','title_generic.png', '', 0)
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => ""
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'ref' => $langs->trans('refProduct')
	)
	,'eval'=>array(
//		'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));

var_dump($product);

if ($product->id > 0) {
	
	print '<table class="border centpercent">';
	print '<tr>';
	print '<td>Produit</td>';
	print '<td>'.$product->ref.'</td>';
	print '</tr>';
	print '<tr>';
	print '<td>Stock Théorique</td>';
	print '<td>'.$product->stock_theorique.'</td>';
	print '</tr>';
	print '<td>Stock Réél</td>';
	print '<td>'.$product->stock_reel.'</td>';
	print '</tr>';
	print '<tr>';
	print '<td>Modification Stock</td>';
	print '<td><input class="minwidth300 maxwidth400onsmartphone" name="modifyStock"></td>';
	print '</tr>';
	print '</table>';
	print '<div class="center"><input class="button" value="Envoyer" type="submit"></div>';

}

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;
	
	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}
	
	return '';
}
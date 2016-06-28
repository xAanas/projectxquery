<?php

include '../../lib/getSearchResults.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$person = getSearchResults($_REQUEST['query']);
//$suggestions=null;
// On parcour les résultats de la requête SQL
for ($index = 0; $index < count($person); $index++) {

    // On ajoute les données dans un tableau
    $cls=array();
    $cls=$person[$index]->Classes->Class;
    $types='';
    for ($index1 = 0; $index1 < count($cls); $index1++){
        $types = $types.' '.$cls[$index1]->Label;
    }
    $suggestions['suggestions'][] =$person[$index]->Label.'';// .' ('.$types.')';
       
    //$person[$index]->Label . ' '.$cls[0]->Class->Label.'';
    //$uri['uri'][] = $person[$index]->URI . '';
    //$type['type'][] = $person[$index]->Classes->Class->Label . '';
}
// On renvoie le données au format JSON pour le plugin
//$GLOBALS['resss']=$uri;
echo json_encode($suggestions);

?>
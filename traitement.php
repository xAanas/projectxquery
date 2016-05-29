<?php

include("BaseXClient.php");

//récuperer les keywords
$keywords = explode("+",$_POST['keywords']);
//récuperer les types de fichier à traiter
if(isset($_POST['returntype'])) $returntype = $_POST['returntype'];
//récuperer le fichier.xml de recherche
$searchfile = $_POST['searchfile'];
//récuperer la liste de fichier.xml dans notre base à traiter
$xml_list = create_xml_list_files("C:\Program Files (x86)\BaseX\bin\bdxml");
foreach ($xml_list['file_list'] as $xml_lis) echo $xml_lis;
foreach($keywords as $keyword)  print_r($keyword);
//foreach($returntype as $return)  print_r($return);
print_r($searchfile);

try {
  // create session
  $session = new Session("localhost", 1984, "admin", "admin");

  foreach($keywords as $keyword)  {
  // run query on database
  print "<br/>";
  print "<br/>";
  $result = $session->execute('xquery for $x in doc("bdxml/foo.xml")/bookstore/book
where $x/author contains text "'.$keyword.'"
order by $x/title
return $x/author');
  print '*********************************************<br/> ';
  print '<author>'.htmlentities($result).'</author>';
  print '<br/> ***************************************';

  }
  // close session
  $session->close();
}
catch (Exception $e) {
  // print exception
  print $e->getMessage();
}


/*
 * create_xml_list_files une fonction pour contruire un tableau des fichiers.xml de notre base à traiter
 * $path représente le chemin de notre base (le dossier qui contient les fichier.xml)
 */
function create_xml_list_files($path){
  //nombre de fichier à traiter
  $nb_file = 0;
  //les noms des fichiers à traiter
  $file_list = array();
  //le retour qui contier le nombre et les noms des fichiers
  $result = array();
  //ouvrir le dossier qui contient les fichiers à traiter
  if($dossier = opendir($path)){
    //charger les fichiers à traiter un par un
    while(false !== ($fichier = readdir($dossier))){
      //controller les noms des fichiers
      if($fichier != 'listxml' && $fichier != 'relation' && $fichier != '.' && $fichier != '..' && $fichier != 'index.php'){
        $nb_file++;
        $file_list []= $fichier;
      }
    }
  }
  $result['file_list'] = $file_list;
  $result['nb_file'] = $nb_file;
  return $result;
}
      
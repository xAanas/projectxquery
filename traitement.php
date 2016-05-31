<?php

include("BaseXClient.php");



// tableau contenant les keywords du searchfile
$search_file_keywords = array();
// récuperer les keywords
$keywords = explode("+", $_POST['keywords']);
// récuperer les types de fichier à traiter
if (isset($_POST['returntype']))
  $returntype = $_POST['returntype'];

try {
  // create session
  $session = new Session("localhost", 1984, "admin", "admin");
  // récuperer le fichier.xml de recherche
  if ($_FILES['searchfile']['name'] != "") {
    $search_file_name = $_FILES['searchfile']['name'];     //Le nom original du fichier, comme sur le disque du visiteur (exemple : mon_icone.png).
    $search_file_type = $_FILES['searchfile']['type'];     //Le type du fichier. Par exemple, cela peut être « image/png ».
    $search_file_tmp_name = $_FILES['searchfile']['tmp_name']; //L'adresse vers le fichier uploadé dans le répertoire temporaire.
    $search_file_error = $_FILES['searchfile']['error'];    //Le code d'erreur, qui permet de savoir si le fichier a bien été uploadé.
    $search_file_upload = move_uploaded_file($_FILES['searchfile']['tmp_name'], "C:/Program Files (x86)/BaseX\bin/bdxml/searchdir/" . $search_file_name);
    if ($search_file_upload)
      echo " succ   upload ";
    $search_file_keywords = extact_file_keywords("bdxml/searchdir/" . $search_file_name, $session, $search_file_name);
    foreach ($search_file_keywords as $key)
      echo $key;
  }
  // récuperer la liste de fichier.xml dans notre base à traiter
  $xml_db_list = create_xml_list_files("C:\Program Files (x86)\BaseX\bin\bdxml");
  $xml_file_list = $xml_db_list['xml_file_list'];
  $xml_file_nbr = $xml_db_list['xml_file_nbr'];


  for ($i = 0; $i < $xml_file_nbr; $i++) {
    foreach ($keywords as $keyword) {
      // run query on database
      $resultat = recherche_keyword_in_xmlfile($keyword, $xml_file_list[$i], $session);
      echo '#############'.$xml_file_list[$i].'#####################';
      if ($resultat != "") {
        foreach($resultat as $result){
          print '*********************************************<br/> ';
          print htmlentities($result);
          print '<br/> ***************************************';
        }
      }
      else {
        print "nothing";
      }
    }
  }
  // close session
  $session->close();
}
catch (Exception $e) {
  // print exception
  print $e->getMessage();
}

/*
 * extraction des keywords du fichier uploadé par l'utilisateur
 */

function extact_file_keywords($path, $session, $filename) {
  // construire la requête 
  $xquery = 'xquery for $x in doc("' . $path . '")/newsItem/contentMeta/keyword
  return data($x)';
  // executer la requête
  $result = $session->execute($xquery);
  // effacer le fichier uploadé
  unlink('C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\' . $filename);
  return $result;
}

/*
 * create_xml_list_files 
 * une fonction pour contruire un tableau des fichiers.xml de notre base à traiter
 * $path représente le chemin de notre base (le dossier qui contient les fichier.xml)
 */

function create_xml_list_files($path) {
  //nombre de fichier à traiter
  $nb_file = 0;
  //les noms des fichiers à traiter
  $file_list = array();
  //le retour qui contier le nombre et les noms des fichiers
  $result = array();
  //ouvrir le dossier qui contient les fichiers à traiter
  if ($dossier = opendir($path)) {
    //charger les fichiers à traiter un par un
    while (false !== ($fichier = readdir($dossier))) {
      //controller les noms des fichiers
      if ($fichier != 'searchdir' && $fichier != 'relation' && $fichier != '.' && $fichier != '..' && $fichier != 'index.php') {
        $nb_file++;
        $file_list [] = $fichier;
      }
    }
    closedir($dossier);
    // construire notre résultat qui contient les fichiers et leur nombre
    $result['xml_file_list'] = $file_list;
    $result['xml_file_nbr'] = $nb_file;
    return $result;
  }
  else {
    return false;
  }
}

function recherche_keyword_in_xmlfile($keyword, $xml_file, $session) {
  // dans ce tableau on va mettre les balises qui contiennent le mots clé
  $resultat = array();
  
  // vérifier si le mot clé existe dans la balise <catalogRef>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem
    where $x/catalogRef contains text "' . $keyword . '"
    return $x/catalogRef');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <rightsInfo><copyrightHolder>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/rightsInfo
    where $x/copyrightHolder contains text "' . $keyword . '"
    return $x/copyrightHolder');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <rightsInfo><copyrightHolder>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/rightsInfo
    where $x/copyrightNotice contains text "' . $keyword . '"
    return $x/copyrightNotice');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><itemClass>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/itemClass contains text "' . $keyword . '"
    return $x/itemClass');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><provider>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/provider contains text "' . $keyword . '"
    return $x/provider');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><versionCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/versionCreated contains text "' . $keyword . '"
    return $x/versionCreated');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><firstCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/firstCreated contains text "' . $keyword . '"
    return $x/firstCreated');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><pubStatus>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/pubStatus contains text "' . $keyword . '"
    return $x/pubStatus');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <itemMeta><title>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/itemMeta
    where $x/title contains text "' . $keyword . '"
    return $x/title');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><contentCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/contentCreated contains text "' . $keyword . '"
    return $x/contentCreated');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><contentModified>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/contentModified contains text "' . $keyword . '"
    return $x/contentModified');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><located><name>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/located
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><creator>[literal]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/creator
    where $x/@literal contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><contributor>[role]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/contributor
    where $x/@role contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><contributor>[literal]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/contributor
    where $x/@literal contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><altId>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/altId contains text "' . $keyword . '"
    return $x/altId');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><altId>[type]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/altId
    where $x/@type contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><language>[tag]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/language
    where $x/@tag contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><genre>[qcode]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/genre
    where $x/@qcode contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><genre><name>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/genre
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><genre><name>[xml:lang]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/genre/name
    where $x/@xml:lang contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><keyword>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/keyword contains text "' . $keyword . '"
    return $x/keyword');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><subject>[type]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/subject
    where $x/@type contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><subject>[qcode]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/subject
    where $x/@qcode contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><subject><name>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/subject
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><creditline>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/creditline contains text "' . $keyword . '"
    return $x/creditline');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><headline>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/headline contains text "' . $keyword . '"
    return $x/headline');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><description>
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta
    where $x/description contains text "' . $keyword . '"
    return $x/description');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentMeta><description>[role]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentMeta/description
    where $x/@role contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[rendition]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@rendition contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[contenttype]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@contenttype contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[href]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@href contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[size]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@size contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[width]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@width contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[height]
  $result = $session->execute('xquery for $x in doc("bdxml/'.$xml_file.'")/newsItem/contentSet/remoteContent
    where $x/@height contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = $result;
  }
  
  return $resultat;
}

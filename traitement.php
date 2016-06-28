<?php

include("BaseXClient.php");
//include('fcontext.php');
// tableau contenant les keywords du searchfile
$search_file_keywords = array();

// le tableau qui contiendra les résultats de la recherche
$tableau_a_afficher = array();
$tableau_de_key_word_trouver = array();
$nombre_de_key_word_trouve = array();
$tableau_de_relation_semantique = array();
$relationsemantique_for_json = array();
$construire_tableau = 0;

// variable qui contient les contextes des mots clés saisis c'est un tableau de tableau
$context_des_mots_cles_saisis = array();

// variable de similarité
// extraire l'indice de similarité depuis le fichier de config
$indice_de_similarite = 0;
$similarity_indice_file = fopen('config/similarity_indice.txt', 'r+');
$indice_de_similarite = fgets($similarity_indice_file);
fclose($similarity_indice_file);

$tableau_des_similarite = array();
$construire_tableau_similarite = 0;
// indicateur s'il y a un fichié uploadé par le client
$file_uploaded = 0;

// récuperer les keywords
$keywords = explode("+", $_POST['keywords']);

// extraire le nombre minimal de keyword à trouver depuis le fichier de config
$nombre_de_keyword_a_trouve = 1;
$keyword_indice_file = fopen('config/keywords_indice.txt', 'r+');
$nombre_de_keyword_a_trouve = fgets($keyword_indice_file) ;
fclose($keyword_indice_file);

// le context des keywords
//$keywords_context = calcul_context($keywords);
//print_r($keywords_context);
// récuperer les types de fichier à traiter
$returntype = array();
if (isset($_POST['returntype']))
  $returntype = $_POST['returntype'];

//récupérer les choix de relation sémantique
$choixRelationSemantique = $_POST['choixRelationSemantique'];
if ($choixRelationSemantique == 'oui') {
  if (isset($_POST['relationsemantique'])) {
    $relationSemantiqueAExtraire = $_POST['relationsemantique'];
  }
  else {
    $relationSemantiqueAExtraire = "all";
  }
}

// récuperer la liste de fichier.xml dans notre base à traiter
$xml_db_list = create_xml_list_files("C:\Program Files (x86)\BaseX\bin\bdxml");
$xml_file_list = $xml_db_list['xml_file_list'];
$xml_file_nbr = $xml_db_list['xml_file_nbr'];

try {
  // create session
  $session = new Session("localhost", 1984, "admin", "admin");

// filtrer selon le type de fichier
  if (count($returntype) > 0) {
    $tableau_intermediaire = array();
    foreach ($xml_file_list as $xml_file) {
      // récuperer le type du fichier
      $type = type_du_fichier("bdxml\\" . $xml_file, $session);
      // vérifier s'il existe dans la liste des types demander par l'utilisateur
      if (in_array($type, $returntype))
        $tableau_intermediaire [] = $xml_file;
    }
    $xml_file_list = $tableau_intermediaire;
    $xml_file_nbr = count($xml_file_list);
  }

// récuperer le fichier.xml de recherche
  if ($_FILES['searchfile']['name'] != "") {
    $search_file_name = $_FILES['searchfile']['name'];     //Le nom original du fichier, comme sur le disque du visiteur (exemple : mon_icone.png).
    $search_file_type = $_FILES['searchfile']['type'];     //Le type du fichier. Par exemple, cela peut être « image/png ».
    $search_file_tmp_name = $_FILES['searchfile']['tmp_name']; //L'adresse vers le fichier uploadé dans le répertoire temporaire.
    $search_file_error = $_FILES['searchfile']['error'];    //Le code d'erreur, qui permet de savoir si le fichier a bien été uploadé.
    $search_file_upload = move_uploaded_file($_FILES['searchfile']['tmp_name'], "C:/Program Files (x86)/BaseX\bin/bdxml/searchdir/" . $search_file_name);
    if ($search_file_upload)
      $file_uploaded = 1; // si on a réussi à charger le fichier
    else
      print "upload error";
    $search_file_keywords = extact_file_keywords_php($search_file_name);
    $keywords = array_merge($keywords, $search_file_keywords);
    // tableau intermediaire 
    $xml_file_list_final = array();
    // récuperer les critère de calcul de similarité
    if ($file_uploaded == 1) {
      if (isset($_POST['similarityoption']))
        $similarityOption = $_POST['similarityoption'];
      else
        $similarityOption = ['headline', 'keyword', 'description'];
    }


    // calcul de similarité
    foreach ($xml_file_list as $xml_file) {
      $similarite = 0;
      foreach ($similarityOption as $similarity) {
        if (calcul_similarite("C:\Program Files (x86)\BaseX\bin\bdxml\\" . $xml_file, "C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\" . $search_file_name, $similarity) > 0) {
          $similarite += calcul_similarite("C:\Program Files (x86)\BaseX\bin\bdxml\\" . $xml_file, "C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\" . $search_file_name, $similarity);
        }
      }
      if (\count($similarityOption) > 0) {
        $similarite = $similarite / (\count($similarityOption));
        if ($similarite > $indice_de_similarite) {
          $xml_file_list_final[] = $xml_file;
          $tableau_des_similarite[$xml_file] = $similarite;
          $construire_tableau_similarite = 1;
        }
      }
    }
    $xml_file_list = $xml_file_list_final;
    $xml_file_nbr = \count($xml_file_list);
    // effacer le fichier uploadé
    unlink('C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\' . $search_file_name);
  }

  // recherche des keywords dans les fichiers xml
  for ($i = 0; $i < $xml_file_nbr; $i++) {
    // les balises résultat de la recherche dans le fichier.xml
    $tableau_a_afficher[$xml_file_list[$i]] = array();
    // les keyword trouvés dans le fichier.xml
    $tableau_de_key_word_trouver[$xml_file_list[$i]] = array();
    // le nombre keyword trouvés dans le fichier.xml
    $nombre_de_key_word_trouve[$xml_file_list[$i]] = 0;
    foreach ($keywords as $keyword) {
      // run query on xml files
      $resultat = recherche_keyword_in_xmlfile($keyword, $xml_file_list[$i], $session);
      if ($resultat != "" && $resultat != NULL) {
        $tableau_a_afficher[$xml_file_list[$i]] = array_merge($tableau_a_afficher[$xml_file_list[$i]], $resultat);
        $tableau_de_key_word_trouver[$xml_file_list[$i]][] = $keyword;
        $nombre_de_key_word_trouve[$xml_file_list[$i]] ++;
        // chercher les relations sémantique
        if ($choixRelationSemantique == "oui") {
          $tableau_de_relation_semantique[$xml_file_list[$i]] = relation_semantique($xml_file_list[$i], $relationSemantiqueAExtraire, $session);
          if ($tableau_de_relation_semantique[$xml_file_list[$i]] != "No relation available") {
            $intermediaire = relation_semantique($xml_file_list[$i], $relationSemantiqueAExtraire, $session);
            $var_taw = explode("|", trim($intermediaire, " |\t\n\r\0\x0B"));
            foreach ($var_taw as $var_t) {
              $var_post = explode(",", $var_t);
              // constuction du tableau des relation pour le json sous la forme [fichier1,relation,fichier2]
              $relationsemantique_for_json[] = array(trim($var_post[0], " \t\n\r\0\x0B"), $var_post[2], array("color" => "#00A0B0", "label" => $var_post[1]));
            }
          }
        }
        $construire_tableau = 1;
      }
    }
  }
  if ($construire_tableau > 0) {
    // Construction du tableau du resultat
    echo '<table border="1"><tr><th>fichier</th><th>mot cle</th><th>balise</th>';
    if ($choixRelationSemantique == "oui")
      echo "<th>relation semantique</th>";
    echo '</tr>';
    foreach ($tableau_a_afficher as $key => $resultats_a_afficher) {
      if (\count($resultats_a_afficher) > 0) {
        if ($nombre_de_key_word_trouve[$key] > $nombre_de_keyword_a_trouve) {
          // insertion d'un ligne dans le tableau
          echo '<tr><td width="10%">' . $key . '</td><td width="15%">';
          foreach ($tableau_de_key_word_trouver[$key] as $word)
            echo ' - ' . $word;
          echo ' </td><td>';
          foreach ($resultats_a_afficher as $resultat_a_afficher) {
            foreach ($tableau_de_key_word_trouver[$key] as $word) {
              $resultat_a_afficher = str_replace($word, '<div style="color:red;display:inline">' . $word . '</div>', $resultat_a_afficher);
            }
            echo $resultat_a_afficher;
          }
          echo '</td>';
          if ($choixRelationSemantique == "oui")
            echo '<td width="15%">' . str_replace(",", " ", str_replace("|", " ", $tableau_de_relation_semantique[$key])) . '</td>';
          echo '</tr>';
        }
      }
    }
    echo '</table>';
  }
  else {
    echo " no result :( ";
  }

  if ($construire_tableau_similarite > 0) {
    // Construction du tableau de similarité 
    echo '<table border="1"><tr><th>fichier</th><th>similarity</th></tr>';
    foreach ($tableau_des_similarite as $key => $one_similarity) {
      echo '<tr><td>' . $key . '</td><td>' . $one_similarity . '</td></tr>';
    }
    echo '</table>';
  }
  else {
    echo "no similarity calculated";
  }

  // construction du retour json
  $fichier_xml_trouver = array();
  foreach ($tableau_a_afficher as $key => $value) {
    if (\count($value) > 0) {
      if ($nombre_de_key_word_trouve[$key] > 1) {
        $fichier_xml_trouver[] = $key;
      }
    }
  }
  $resultat_json = "";
  if (\count($relationsemantique_for_json) > 0) {
    $resultat_json = array("nodes" => $fichier_xml_trouver, "edges" => $relationsemantique_for_json);
  }
  else {
    $resultat_json = array("nodes" => $fichier_xml_trouver,"edges" => "no relation available");
  }
  
  // ecrire le json dans un fichier json/xquery_json_result.txt
  $xquery_json_result_file = fopen('json/xquery_json_result.txt', 'r+');
  ftruncate($xquery_json_result_file, 0);
  fseek($xquery_json_result_file, 0);
  fputs($xquery_json_result_file,  json_encode($resultat_json));
  fclose($xquery_json_result_file);
  //var_dump(json_encode($resultat_json));


  // close session
  $session->close();
}
catch (Exception $e) {
  // print exception
  print $e->getMessage();
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

/*
 * retourner le type du fichier.xml (video,text,audio ou image)
 */

function type_du_fichier($file, $session) {
  // construire la requête 
  $xquery = 'xquery for $x in doc("' . $file . '")/newsItem/itemMeta/itemClass
             return fn:substring(data($x/@qcode),7)';
  // executer la requête
  $result = $session->execute($xquery);
  return $result;
}

/*
 * extraction des keywords du fichier uploadé par l'utilisateur avec xquery
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
 * extraction des keywords du fichier uploadé par l'utilisateur avec php
 */

function extact_file_keywords_php($filename) {
  $doc1 = new DOMDocument();
  $doc1->load("C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\" . $filename);
  $result = array();

  $nodes1 = $doc1->getElementsByTagName("keyword");
  foreach ($nodes1 as $element1) {
    $result[] = $element1->firstChild->nodeValue;
  }
  // effacer le fichier uploadé
  //unlink('C:\Program Files (x86)\BaseX\bin\bdxml\searchdir\\' . $filename);
  return $result;
}

/*
 * recherche les fichiers qui contiennent les keyword inserer par l'utilisateur
 */

function recherche_keyword_in_xmlfile($keyword, $xml_file, $session) {
  // dans ce tableau on va mettre les balises qui contiennent le mots clé
  $resultat = array();

  // vérifier si le mot clé existe dans la balise <catalogRef>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem
    where $x/catalogRef contains text "' . $keyword . '"
    return $x/catalogRef');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <rightsInfo><copyrightHolder>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/rightsInfo
    where $x/copyrightHolder contains text "' . $keyword . '"
    return $x/copyrightHolder');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <rightsInfo><copyrightHolder>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/rightsInfo
    where $x/copyrightNotice contains text "' . $keyword . '"
    return $x/copyrightNotice');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><itemClass>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/itemClass contains text "' . $keyword . '"
    return $x/itemClass');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><provider>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/provider contains text "' . $keyword . '"
    return $x/provider');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><versionCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/versionCreated contains text "' . $keyword . '"
    return $x/versionCreated');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><firstCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/firstCreated contains text "' . $keyword . '"
    return $x/firstCreated');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><pubStatus>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/pubStatus contains text "' . $keyword . '"
    return $x/pubStatus');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <itemMeta><title>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/itemMeta
    where $x/title contains text "' . $keyword . '"
    return $x/title');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><contentCreated>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/contentCreated contains text "' . $keyword . '"
    return $x/contentCreated');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><contentModified>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/contentModified contains text "' . $keyword . '"
    return $x/contentModified');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><located><name>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/located
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><creator>[literal]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/creator
    where $x/@literal contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><contributor>[role]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/contributor
    where $x/@role contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><contributor>[literal]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/contributor
    where $x/@literal contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><altId>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/altId contains text "' . $keyword . '"
    return $x/altId');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><altId>[type]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/altId
    where $x/@type contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><language>[tag]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/language
    where $x/@tag contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><genre>[qcode]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/genre
    where $x/@qcode contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><genre><name>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/genre
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><genre><name>[xml:lang]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/genre/name
    where $x/@xml:lang contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><keyword>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/keyword contains text "' . $keyword . '"
    return $x/keyword');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><subject>[type]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/subject
    where $x/@type contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><subject>[qcode]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/subject
    where $x/@qcode contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><subject><name>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/subject
    where $x/name contains text "' . $keyword . '"
    return $x/name');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><creditline>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/creditline contains text "' . $keyword . '"
    return $x/creditline');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><headline>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/headline contains text "' . $keyword . '"
    return $x/headline');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><description>
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta
    where $x/description contains text "' . $keyword . '"
    return $x/description');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentMeta><description>[role]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentMeta/description
    where $x/@role contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[rendition]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@rendition contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[contenttype]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@contenttype contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[href]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@href contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[size]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@size contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[width]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@width contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  // vérifier si le mot clé existe dans la balise <contentSet><remoteContent>[height]
  $result = $session->execute('xquery for $x in doc("bdxml/' . $xml_file . '")/newsItem/contentSet/remoteContent
    where $x/@height contains text "' . $keyword . '"
    return $x');
  if ($result != "") {
    $resultat [] = '<li>' . htmlentities($result) . '</li>';
  }

  return $resultat;
}

/*
 * fonction qui renvoi les relations semantiques relatives à un fichier ($file)
 */

function relation_semantique($file, $relations, $session) {
  $response = "";
  if ($relations == "all" || $relations == "") {
    $response = "";
    $xquery = 'xquery declare function local:talk_about_fn($x as element()?,$ressources as element()?,$link as element()?,$res as element()?,$p				as xs:string? ,$l as xs:string?)
        as xs:string* {
        let $r :=$res/@id
              where ($x/@id= $p or  $res/@id=$p) and $link/@name=$l
              return data(concat($ressources/@id,",", " talk_about ",",", $r,"|")) 
   
 };
 
 
declare function local:showOrAppear_fn($x as element()?,$ressources as element()?,$link as 					element()?,$res as 			element()?,$p as xs:string? ,$l as xs:string) 
as xs:string*{
          let $r :=$ressources/@id
          where $r = $p
          and $link/@name = $l
          return       data (concat($p ,",", $l,"," , $res/@id,"|"))
   
 };
 
 
 
declare function local:otherLink_fn($x as element()?,$ressources as element()?,$link as 					element()?,$res as 						element()?,$p as xs:string? ,$l as xs:string) 
as xs:string*{
        let $r := $link/@name
         where $r = $l
         and $res/@id=$p
         return  data (concat(  $ressources/@id,",",$r,",", $res/@id,"|"))
 };
 
 
declare function local:GlobalRelation($x as element()?, $p as xs:string?, $l as xs:string?)
        as xs:string*
                      { 
                      
                     
                      for $ressources in $x/ressource
                      for $link in $ressources/link
                      for $res in $link/ressource   
                return
                  
                   if(compare($l , "all")=0) then(
                     
                      if(compare($link/@name,"talk_about")=0)then(
                         local:talk_about_fn($x,$ressources,$link,$res,$p,"talk_about")
                        
                      ) else(
                              if(compare($link/@name,"show")=0)
                              then(
                                local:showOrAppear_fn($x,$ressources,$link,$res,$p,"show")
                              ) else(
                                if(  compare( $link/@name, "Appear_In")=0 )  then(
                                  local:showOrAppear_fn($x,$ressources,$link,$res,$p,"Appear_In")
                                )else( if(compare($link/@name,"speak_about")=0) then(
                                   local:otherLink_fn($x,$ressources,$link,$res,$p,"speak_about")
                                ) else(
                                 
                               
                                local:otherLink_fn($x,$ressources,$link,$res,$p,"speak")
                              ) )
                        )
                      )
                     
                     
                   )else(
                    
                     
                      if(compare($l,"talk_about")=0)then(
                         local:talk_about_fn($x,$ressources,$link,$res,$p,$l)
                        
                      ) else(
                              if(compare($l,"show")=0  or(compare( $l , "Appear_In")=0)) 
                              then(
                                local:showOrAppear_fn($x,$ressources,$link,$res,$p,$l)
                              ) else(
                                local:otherLink_fn($x,$ressources,$link,$res,$p,$l)
                              )
                        
                      )
                   )
                  
    
  };
      local:GlobalRelation(doc("bdxml\relation\result.xml")/ressources,"' . $file . '","all") ';
    $result = $session->execute($xquery);
    if ($result != "") {
      $response = $result;
    }
  }
  else {
    $response = "";
    foreach ($relations as $relation) {
      $xquery = 'xquery declare function local:talk_about_fn($x as element()?,$ressources as element()?,$link as element()?,$res as element()?,$p				as xs:string? ,$l as xs:string?)
        as xs:string* {
        let $r :=$res/@id
              where ($x/@id= $p or  $res/@id=$p) and $link/@name=$l
              return data(concat($ressources/@id, "," , " talk_about ", "," , $r,"|"))
   
 };
 
 
declare function local:showOrAppear_fn($x as element()?,$ressources as element()?,$link as 					element()?,$res as 			element()?,$p as xs:string? ,$l as xs:string) 
as xs:string*{
          let $r :=$ressources/@id
          where $r = $p
          and $link/@name = $l
          return       data (concat($p ,",", $l,"," , $res/@id,"|"))
   
 };
 
 
 
declare function local:otherLink_fn($x as element()?,$ressources as element()?,$link as 					element()?,$res as 						element()?,$p as xs:string? ,$l as xs:string) 
as xs:string*{
        let $r := $link/@name
         where $r = $l
         and $res/@id=$p
         return  data (concat(  $ressources/@id,",",$r,",", $res/@id,"|"))
 };
 
 
declare function local:GlobalRelation($x as element()?, $p as xs:string?, $l as xs:string?)
        as xs:string*
                      { 
                      
                     
                      for $ressources in $x/ressource
                      for $link in $ressources/link
                      for $res in $link/ressource   
                return
                  
                   if(compare($l , "all")=0) then(
                     
                      if(compare($link/@name,"talk_about")=0)then(
                         local:talk_about_fn($x,$ressources,$link,$res,$p,"talk_about")
                        
                      ) else(
                              if(compare($link/@name,"show")=0)
                              then(
                                local:showOrAppear_fn($x,$ressources,$link,$res,$p,"show")
                              ) else(
                                if(  compare( $link/@name, "Appear_In")=0 )  then(
                                  local:showOrAppear_fn($x,$ressources,$link,$res,$p,"Appear_In")
                                )else( if(compare($link/@name,"speak_about")=0) then(
                                   local:otherLink_fn($x,$ressources,$link,$res,$p,"speak_about")
                                ) else(
                                 
                               
                                local:otherLink_fn($x,$ressources,$link,$res,$p,"speak")
                              ) )
                        )
                      )
                     
                     
                   )else(
                    
                     
                      if(compare($l,"talk_about")=0)then(
                         local:talk_about_fn($x,$ressources,$link,$res,$p,$l)
                        
                      ) else(
                              if(compare($l,"show")=0  or(compare( $l , "Appear_In")=0)) 
                              then(
                                local:showOrAppear_fn($x,$ressources,$link,$res,$p,$l)
                              ) else(
                                local:otherLink_fn($x,$ressources,$link,$res,$p,$l)
                              )
                        
                      )
                   )
                  
    
  };
      local:GlobalRelation(doc("bdxml\relation\result.xml")/ressources,"' . $file . '","' . $relation . '") ';
      $result = $session->execute($xquery);
      if ($result != "") {
        $response .= $result;
      }
    }
  }

  if ($response == "")
    $response = "No relation available";
  return $response;
}

/*
 * c'est la fonction qui calcule la similarité entre deux fichier.xml selon une balise donnée
 */

function calcul_similarite($chemin_doc1, $chemin_doc2, $baliseName) {
  $doc1 = new DOMDocument();
  $doc1->load($chemin_doc1);
  $doc2 = new DOMDocument();
  $doc2->load($chemin_doc2);

  $tab1 = array();
  $tab2 = array();

  $nodes1 = $doc1->getElementsByTagName($baliseName);
  foreach ($nodes1 as $element1) {
    $tab1[] = $element1->firstChild->nodeValue;
  }

  $nodes2 = $doc2->getElementsByTagName($baliseName);
  foreach ($nodes2 as $element2) {
    $tab2[] = $element2->firstChild->nodeValue;
  }

  $arr_intersection = array_intersect($tab1, $tab2);
  $var1 = count($arr_intersection);
  $arr_union = array_merge($tab1, $tab2);
  $var2 = count($arr_union);
  $coefficient = count($arr_intersection) / count($arr_union);
  return $coefficient;
}

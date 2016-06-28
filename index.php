<!DOCTYPE html>
<!--
Developped by xAnas
-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>xml</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <style>
            .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
            .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
            .autocomplete-selected { background: #F0F0F0; }
            .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
        </style> 
    </head>
    <body>
        <!--<form name="searchform" method="get" action="index.php" ><br/>-->
        <form name="searchform" action="traitement.php" method="POST" enctype="multipart/form-data"><br/>
            Entrer vos mots clés : 
            <input value="<?php if (isset($_GET['keywords'])) echo $_GET['keywords']; ?>" name="keywords" id="keyword" placeholder="key words...">
            <br/>
            <hr>
            Type de retour :
            <input type="checkbox" name="returntype[]" value="video" c>Vidéo 
            <input type="checkbox" name="returntype[]" value="image" >Image 
            <input type="checkbox" name="returntype[]" value="audio" >Audio 
            <input type="checkbox" name="returntype[]" value="T ext" >Texte 
            <br/>
            <hr>
            Afficher les relations 
            <select name="choixRelationSemantique">
                <option value="non">Non</option>
                <option value="oui">Oui</option>
            </select>
            <input type="checkbox" name="relationsemantique[]" value="speak">Speak
            <input type="checkbox" name="relationsemantique[]" value="speak_about">Speak about
            <input type="checkbox" name="relationsemantique[]" value="talk">Talk
            <input type="checkbox" name="relationsemantique[]" value="talk_about">Talk about
            <input type="checkbox" name="relationsemantique[]" value="show">Show
            <input type="checkbox" name="relationsemantique[]" value="Appear_In">Appear in
            <br/>
            <hr>
            <br/>
            <input type="file" name="searchfile" value="searchfile" accept=".xml"><br/>
            <hr>
            Options de similarité 
            <input type="checkbox" name="similarityoption[]" value="headline">Headline
            <input type="checkbox" name="similarityoption[]" value="keyword">Keyword
            <input type="checkbox" name="similarityoption[]" value="description">Description
            <br/>
            <hr>
            <br/>
            <input type="submit" value="chercher">
            <input type="reset" value="annuler">
        </form>
        <div class="tablespace"></div>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
        <script src="js/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.autocomplete.min.js"></script>
        <script type="text/javascript">
          $(document).ready(function () {
              /*$('#keyword').autocomplete({
                  serviceUrl: 'dbpedia/lookup.php',
                  dataType: 'json'
              });*/
          });
        </script>
    </body>
</html>

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
    </head>
    <body>
            <form name="searchform" action="traitement.php" method="POST" enctype="multipart/form-data"><br/>
            <input name="keywords" id="keyword" placeholder="key words...">
            Type de retour :
            <input type="checkbox" name="returntype[]" value="video" c>Vidéo 
            <input type="checkbox" name="returntype[]" value="image" >Image 
            <input type="checkbox" name="returntype[]" value="audio" >Audio 
            <input type="checkbox" name="returntype[]" value="texte" >Texte 
            <br/>
            <hr>
            <br/>
            Afficher les relations 
            <select name="choixRelationSemantique">
                <option value="oui">Non</option>
                <option value="non">Oui</option>
            </select>
            <input type="checkbox" name="relationsemantique[]" value="speak">Speak
            <input type="checkbox" name="relationsemantique[]" value="speak_about">Speak about
            <input type="checkbox" name="relationsemantique[]" value="talk">Talk
            <input type="checkbox" name="relationsemantique[]" value="talk_about">Talk about
            <input type="checkbox" name="relationsemantique[]" value="show">Show
            <input type="checkbox" name="relationsemantique[]" value="appear_in">Appear in
            <br/>
            <hr>
            <br/>
            <input type="file" name="searchfile" value="searchfile"><br/>
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    </body>
</html>

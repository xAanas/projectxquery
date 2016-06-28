<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Set configuration</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form name="searchform" action="setconfiguration.php" method="POST" enctype="multipart/form-data"><br/>
        <input value="<?php if (isset($_POST['keywords'])) echo $_POST['keywords']; ?>" name="keywords" id="keyword" placeholder="key words indice">
        <input value="<?php if (isset($_POST['similarity'])) echo $_POST['similarity']; ?>" name="similarity" id="similarity" placeholder="similarity indice">
        <input type="submit">
        </form>
        <?php 
        if(isset($_POST['keywords'])){
          $key_indice_file = fopen('config/keywords_indice.txt', 'w+');
          fputs($key_indice_file,$_POST['keywords']);
          fclose($key_indice_file);
        }
        if(isset($_POST['similarity'])){
          $similarity_indice_file = fopen('config/similarity_indice.txt', 'w+');
          fputs($similarity_indice_file,$_POST['similarity']);
          fclose($similarity_indice_file);
        }        
        ?>
    </body>
</html>

<?php

/*
 * fcontextv4
 */
require('opencalais2.php');

unset($T);
unset($opencalais_result);
$T = array('paris', 'decision', 'Fran�ois hollande', 'Meeting'); //'Francois hollande','Meeting',,'Paris');

/*$com_result = calcul_context($T);

echo '</br>';
echo '<pre>';
print_r($com_result);
echo '</pre>';
echo '</br>';
echo $com_result;*/

function calcul_context($T) {
  $T_result = fcontext($T);
  $com_result = com_result($T_result);

  if ($com_result[0] == null) {
    $com_result = fcontext_score($T);
  }
  return $com_result;
}

function getOpencalaisTopic($opencalais_result) {
  $i = 0;
  $open_tab = null;
  foreach ($opencalais_result as $key => $e) {
    if ($e->_typeGroup == 'topics') {
      $open_tab[$i] = $e->name;
      $i++;
    }
  }
  if (isset($open_tab)) {
    return $open_tab;
  }
  else
    return null;
}

function getOpencalaisTopic_score($opencalais_result) {
  $i = 0;
  $open_tab = null;
  foreach ($opencalais_result as $cle => $e) {

    if ($e->_typeGroup == 'topics' && $e->score == 1) {

      $open_tab[$i] = $e->name;
      $i++;
    }
  }
  if (isset($open_tab)) {
    return $open_tab;
  }
  else
    return null;
}

function fcontext($T) {
  $i = 0;
  sort($T);
  $opencalais_result = null;
  FOREACH ($T as $key) {

    usleep(100);
    $opencalais_result[$i] = getOpencalaisTopic(getOpenCalais($key));
    $i++;
  }
  return $opencalais_result;
}

function fcontext_score($T) {
  $i = 0;
  $opencalais_result = null;
  FOREACH ($T as $key) {
    $opencalais_result[$i] = getOpencalaisTopic_score(getOpenCalais($key));
    $i++;
  }
  return $opencalais_result;
}

function com_result($T_result) {
  $k = 0;
  $l = 0;
  sort($T_result);
  $count = count($T_result) - 1;
  $com = null;
  foreach ($T_result as $key => $v) {
    for ($i = 1; $i < $count; $i++) {
      if ($key != $i && $T_result[$key] != null) {
        $val[$l] = array_intersect($T_result[$key], $T_result[$i]);

        if ($val[$l] != null) {

          $variable = $val[$l];
          $com[$k] = $variable[$l];
          if (!in_array($com[$k], $val[$k])) {
            $k++;
          }
          $l++;
        }
      }
    }
  }
  return $com;
}
?>




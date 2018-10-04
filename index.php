<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta content="text/html; charset=utf-8" http-equiv="content-type">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Geonovum specificaties</title>
    <link rel='shortcut icon' type='image/x-icon' href='https://tools.geostandaarden.nl/respec/style/logos/Geonovum.ico' />
    <!-- TODO: css to external doc and/or use geonovum css file -->
    <style>
    body {
      line-height: 1.5;

      font-family: sans-serif;

      color: black;

      width: 70rem;

      margin-left: auto;

      margin-right: auto;

      font-size: 0.8rem;

      margin-top: 2rem;
    }
    
    h1, h2 {
      color: #005a9c;
    }
    h1 {
      font-weight: bold;
      margin: 0 0 .1em;
      font-size: 220%;
    }
    h2 {
      font-size: 140%;
    }
    a {
      color: #005a9c;
    }
    span.final, span.final a {
      font-weight: normal;
      color: #005a9c;
    }
    span.def, span.def a {
      color: #005a9c;
      /*font-size: 0.85em;*/
    }
    span.cv, span.cv a {
      color: orange;
      /*font-size: 0.85em;*/
    }
    span.vv, span.vv a {
      color: green;
      /*font-size: 0.85em;*/
    }
    </style>
  </head>
<body>
<h1><img class="block-sitebranding__logo" src="https://www.geonovum.nl/logo.svg" alt="Home"> Standaarden en technische documenten</h1>
Op <a href="https://docs.geostandaarden.nl/">https://docs.geostandaarden.nl/</a> zijn standaarden en technische documenten te vinden die Geonovum publiceert. Onderstaande documenten zijn op dit moment beschikbaar:
<!-- <ul> -->

<?php
// directories to be ignored. Uppercase
$ignoreList = ['BRO', 'NWBBGT', '.GIT'];

// directories for which all files should be shown (containing PDFs instead of ReSpec docs)
$publishAllList = ['G4W', 'KL', 'MIM', 'OOV','RO','SERV'];

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

$path = '.';
$pubDomains = scandir($path);

foreach ($pubDomains as $pubDomain) {
    if ($pubDomain === '.' or $pubDomain === '..') continue;
    if (is_dir($path . '/' . $pubDomain) and !in_array(strtoupper($pubDomain), $ignoreList)) {
        echo "<h2><a href='".$pubDomain."'>".strtoupper($pubDomain)."</a></h2>";
        // loop over the folders again to find docs
        $lookintodir = $path . "/" . $pubDomain;
        $subdirs = scandir($lookintodir);
        $lastVersion = [];
        $ul = False;
        foreach ($subdirs as $subdir) {
            if ($subdir === '.' or $subdir === '..') continue;
            if (is_dir($lookintodir . "/" . $subdir)) {
                // TODO: order on types. If no def-, ... ..., then it could be a basedir
                $docType="Laatste versie";
                $cls="final";
                if (startsWith($subdir, "def-")) {
                  $docType="Definitieve versie";
                  $cls="def";
                } elseif (startsWith($subdir, "cv-")) {
                  $docType="Consultatie versie";
                  $cls="cv";
                } elseif (startsWith($subdir, "vv-")) {
                  $docType="Vastgestelde versie";
                  $cls="vv";
                }
                if ($docType=="Laatste versie" or (in_array(strtoupper($pubDomain), $publishAllList) and $docType="Definitieve versie")) {
                  if ($ul == False) {
                    echo "<ul>";
                    $ul = True;
                  }
                  echo "<li><span class='".$cls."'><a href='".$lookintodir . "/" . $subdir."'>".$docType.": ".$subdir."</a></span></li>";
                }
            }
        }
        if ($ul == True){
          echo "</ul>";
          $ul == False;
        }
    }
}
?>

<!-- </ul> -->
</body>

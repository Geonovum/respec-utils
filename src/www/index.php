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
      background-color: #ddd;
      padding-left: 0.5em;
    }
    h3 {
        margin-left: 1.8em;
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

    .warning {
      background-color: #ffbb66;
      border: 1px solid black;
      padding: 1em;
      margin: 1em;
    }
    </style>
  </head>
<body>
<h1><img class="block-sitebranding__logo" src="https://www.geonovum.nl/logo.svg" alt="Home"> Standaarden en technische documenten</h1>
<p>Op <a href="https://docs.geostandaarden.nl/">https://docs.geostandaarden.nl/</a> zijn standaarden en technische documenten te vinden die Geonovum publiceert. </p>

<p class="warning">Deze pagina is slechts een inhoudsopgave van de documenten op <a href="https://docs.geostandaarden.nl/">https://docs.geostandaarden.nl/</a>. Op de website van <a href="https://www.geonovum.nl/">Geonovum</a> staan achtergronden en toelichtingen over de documenten.</p>
<p>Onderstaande documenten zijn op dit moment beschikbaar:</p>
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

function subDirsAsList($subdirs, $pubDomain, $lookintodir, $publishAllList)
{
  $ul = False;
  $htmlList = "";
  // $publishAllList = ['G4W', 'KL', 'MIM', 'OOV','RO','SERV'];
  foreach ($subdirs as $subdir) {
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
    // TODO: why only definitieve versie?
    // show cv versions too?
    // if ($docType=="Laatste versie" or (in_array(strtoupper($pubDomain), $publishAllList) and $docType="Definitieve versie")) {
    if ($docType=="Laatste versie" or (in_array(strtoupper($pubDomain), $publishAllList)) or $docType=="Definitieve versie") {
      if ($ul == False) {
        $htmlList .= "<ul>";
        $ul = True;
      }
      $htmlList .= "<li><span class='".$cls."'><a href='".$lookintodir . "/" . $subdir."'>".$docType.": ".$subdir."</a></span></li>";
    }
  }
  if ($ul == True){
    $htmlList .= "</ul>";
    $ul == False;
  }
  return $htmlList;
}

$path = '.';
$pubDomains = scandir($path);

foreach ($pubDomains as $pubDomain) {
    if ($pubDomain === '.' or $pubDomain === '..') continue;
    // reset the arrays for types
    $norm = [];
    $toelichting = [];
    $documentatie = [];
    $unknown = [];
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
                // find the specType https://github.com/Geonovum/respec/wiki/specType
                // no, st, hr, im, pr, hr, wa
                // split on -, get the second patt
                // example: def-im-ro2012-20120418
                $specType="";
                // default: laatste versie?
                $specGroup="laatste"; // norm, toelichting, documentatie, laatste
                $dirParts = explode("-", $subdir);
                if (count($dirParts) > 3) {
                  $specType=$dirParts[1];
                  switch ($specType) {
                      case 'no':
                          $norm[] = $subdir;
                          break;
                      case 'st':
                          $norm[] = $subdir;
                          break;
                      case 'im':
                          $norm[] = $subdir;
                          break;
                      case 'pr':
                          $toelichting[] = $subdir;
                          break;
                      case 'hr':
                          $documentatie[] = $subdir;
                          break;
                      case 'wa':
                          $documentatie[] = $subdir;
                          break;
                      default:
                          $unknown[] = $subdir;
                          // reset spectype
                          $specType = "";
                  }
                } else {
                  $unknown[] = $subdir;
                }
            }
        }
        // reorder the types:
        // loop over all docs
        // TODO: order on types. If no def-, ... ..., then it could be a basedir
        // now loop over all arrays with $subdirs
        if (count($unknown) > 0) {
          echo "<h3>Laatste versies</h3>";
          echo subDirsAsList($unknown, $pubDomain, $lookintodir, $publishAllList);
        }
        if (count($norm) > 0) {
          echo "<h3>Normen</h3>";
          echo subDirsAsList($norm, $pubDomain, $lookintodir, $publishAllList);
        }
        if (count($toelichting) > 0) {
          echo "<h3>Toelichtingen</h3>";
          echo subDirsAsList($toelichting, $pubDomain, $lookintodir, $publishAllList);
        }
        if (count($documentatie) > 0) {
          echo "<h3>Documentatie</h3>";
          echo subDirsAsList($documentatie, $pubDomain, $lookintodir, $publishAllList);
        }
    }
}
?>

<!-- </ul> -->
</body>

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
      font-family: "Open Sans", sans-serif;
      color: #5e5e5e;
      font-size: .875rem;
      line-height: 1.5;
    }

    .page {
      max-width: 88rem;
      margin-left: auto;
      margin-right: auto;
      margin-top: 2rem;
    }


    h1, h2, h3, h4, h5, h6 {
      font-family: Montserrat, "Open Sans", sans-serif;
    }

    h1 {
      margin: 1em 0 2em;
      font-size: 1.25rem;
      line-height: 4em;
      border-bottom: 1px solid rgba(94,94,94,.2);
      font-weight: 500;
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 500;
        /*background-color: #f7f7f7;*/
        padding-left: 0.5em;
        border-bottom: 4px solid rgb(141, 182, 63);
        line-height: 4rem;
        width: 34rem;
    }

    h3 {
        margin-left: 1.8em;
        font-weight: 500;
    }

    a {
      color: #005a9c;
    }

    h2 > a {
      text-decoration: none;
      color: rgb(94,94,94);
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

    div.pubDomain {
      width: 44rem;
      display: inline-grid;
    }

    span.pubDomainAbbr{
      font-size: 0.8rem;
      margin-right: 0.5rem;
    }

    ul.docs {
      border-bottom: 1px solid rgba(94, 94, 94, 0.2);
      width: 36rem;
      padding-bottom: 2rem;
    }

    </style>
  </head>
<body>
<div class="page">
<img class="block-sitebranding__logo" src="https://www.geonovum.nl/logo.svg" alt="Home">
<h1>Standaarden en technische documenten</h1>
<p>Op <a href="https://docs.geostandaarden.nl/">https://docs.geostandaarden.nl/</a> publiceert Geonovum standaarden en technische documenten.</p>

<p class="warning">Deze pagina is slechts een inhoudsopgave van documentatie die wij beheren. Ga naar de <a href="https://www.geonovum.nl">website van Geonovum</a> voor toelichting op de documentatie.</p>
<p>Onderstaande documenten zijn op dit moment beschikbaar:</p>

<?php
$pubDomainListURL = 'https://raw.githubusercontent.com/Geonovum/respec-utils/master/src/autodeploy/config/pubDomainList.json';
// directories to be ignored. Uppercase
$ignoreList = ['BRO', 'NWBBGT', '.GIT'];
// directories for which all files should be shown (containing PDFs instead of ReSpec docs)
$publishAllList = ['G4W', 'KL', 'MIM', 'OOV', 'RO', 'SERV'];

// -------------------------------
// get the list of pubDomains:
$pubDomainsJson = file_get_contents($pubDomainListURL);
$pubDomainsArr = json_decode($pubDomainsJson, true);
$allPubDomains = $pubDomainsArr['Geonovum'];
// -------------------------------

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

function getPubDomainTitle($pubDomain, $allPubDomains)
{
  // default to pubdomain
  $title = strtoupper($pubDomain);
  foreach ($allPubDomains as $pd) {
    if ($pd['pubDomain'] == $pubDomain) {
      if (strlen($pd['pubDomainTitle']) > 0) {
        $title = $pd['pubDomainTitle'];
      }
    }
  }
  $titleFull = "<span class='pubDomainAbbr'>(".strtolower($pubDomain).")</span>".$title;
  return $titleFull;
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
        $htmlList .= "<ul class='docs'>";
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
        echo "<div class='pubDomain'><h2><a href='".$pubDomain."'>".getPubDomainTitle($pubDomain, $allPubDomains)."</a></h2>";
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
                      case 'al':
                          $documentatie[] = $subdir;
                          break;
                      case 'bd':
                          $documentatie[] = $subdir;
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
        echo "</div>";
    }
}
?>
</div>
</body>

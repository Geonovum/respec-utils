<?php
// Thijs Brentjens (thijs@brentjensgeoict.nl), 2018, for Geonovum The Netherlands
// TODO: licensing this file? BSD?
// PHP script to receive a release name of a Github Webhook

// get settings
include 'githubConfig.php';

// security check first

$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
$messageBody = file_get_contents('php://input');

list($algo, $hash) = explode('=', $hubSignature, 2) + ['', ''];
if ($hash !== hash_hmac($algo, $messageBody, $hubSecret)) {
   header('HTTP/1.0 403 Forbidden');
   echo "Invalid Signature!";
   trigger_error("403 forbidden, secret is missing", E_USER_WARNING);
   return;
}

// Prevent accidental XSS
header('Content-type: text/plain');

/*
sample json:

*/


// Instead of git commands, use a download and unzip (no git needed)

// utils function. Just include it for now.
function rmdir_recursive($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir('$dir/$file')) rmdir_recursive('$dir/$file');
        else unlink('$dir/$file');
    }
    rmdir($dir);
}

function cpdir_recursive($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                cpdir_recursive($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/* **************************
Instructions
------------
Requirements
1. the respec doc has a separate github repository. In the root dir of the source, there is an index.html file for the respec doc
2. create a webhook in the github repository that fires when a release is created (using www-form encoded)
3. the shortname for the github repo is known in the file XXXXX

Per release:
1. create a release on github
2. IMPORTANT: use the last part of the URL "Deze versie" as the tag name of the release. E.g.: def-hr-wpgs-20171222. No trailing slashes are allowed.
3. publish the release. Now automatically the respec doc will be copied to https://docs.geostandaarden.nl/{pubDomain}/{tagname} and a copy will be made for the latest version


*************************** */

if ( $_POST['payload'] ) {
  $evt = json_decode($_POST['payload']);
  // a release
  if ($evt->{'action'}=='published') {
      // if this is okay, continue creating everything
    $tagName = $evt->release->tag_name;
    $repoName = $evt->repository->name;
    $repoFullname = $evt->repository->full_name;
    // repo organisation: TODO: do we need to use another property from the Github payload?
    // for now get it from the fullname
    $repoFullnameParts = preg_split('#/#', $repoFullname);
    $repoOrganisation = $repoFullnameParts[0];

    $pubDomain=$repoName; // default to $repoName
    $shortName=$tagName; //default to tagname? $tagName
    // check if it is okay to use this pubDomain for this rep
    /// 1. get the list of pubDomains
    $pubDomainsJson = file_get_contents($pubDomainListURL);
    $pubDomainsArr = json_decode($pubDomainsJson, true);
    // 2. try to find a pubDomain in the list, using the repo organisation and repoName
    $allPubDomains = $pubDomainsArr[$repoOrganisation];
    foreach ($allPubDomains as $pd) {
      if ($pd['repoName'] == $repoName) {
        $pubDomain = $pd['pubDomain'];
        $shortName = $pd['shortName'];
      }
    }
    $latestDir = $pubDomain.'/'.$shortName;
    // TODO: make it more robust
    // make sure the pubDomain directory is there
    if (!file_exists($pubDomain)) {
        mkdir($pubDomain, 0777, true);
    }
    // remove the dir if it already exists (if the same tagname is used)
    if (file_exists($pubDomain.'/'.$tagName)) {
      rmdir_recursive($pubDomain.'/'.$tagName);
    }
    // now proceed to create everything needed
    if (!file_exists($pubDomain.'/'.$tagName)) {
        $tempZipName = $pubDomain.'_'.$tagName.'.zip';
        // the github zipball url does not seem to work properly, co fetch the zip based on the URL of the tagname
        // example:
        // https://github.com/thijsbrentjens/wfs-storedqueries/archive/def-hr-wpgs-20171223.zip
        $zipballUrl = $evt->release->zipball_url;
        $zipballUrl = 'https://github.com/'.$repoOrganisation.'/'.$repoName.'/archive/'.$tagName.'.zip';
        file_put_contents($tempZipName, file_get_contents($zipballUrl));
        $zip = new ZipArchive;
        $res = $zip->open($tempZipName);
        if ($res === TRUE) {
            // Use a staging dir to get all the documents
            $stagingDir = '_staging_'.$pubDomain.'_'.$tagName;
            $zip->extractTo($stagingDir);
            $zip->close();
            // zipfile is of format {repo}-{tagnumber-without-v}.zip.
            // get the name of the extracted zip from the staging dir and move the file to the repoName:
            $results = scandir($stagingDir);
            foreach ($results as $result) {
                if ($result === '.' or $result === '..') continue;
                if (is_dir($stagingDir. '/' . $result)) {
                   // also copy to the latest version:
                   if ($shortName!=$tagName) {
                      // first remove the current latest version
                      if (is_dir($latestDir)) {
                        rmdir_recursive($latestDir);
                      }
                      cpdir_recursive($stagingDir.'/'.$result, $latestDir);
                   }
                   rename($stagingDir.'/'.$result, $pubDomain.'/'.$tagName);
                }
            }
            // delete all files from the previous version and recreate the dir using the pubDomain and the shortName
            // for now: the shortName is part of the config in the json file?
            // Remove zip-file
            unlink($tempZipName);
            rmdir_recursive($stagingDir);
        } else {
          // TODO: handle errors better
          trigger_error("Something went wrong in processing the zip file", E_USER_WARNING);
        }
    }
  }
}
?>

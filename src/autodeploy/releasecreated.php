<?php
/*
MIT License

Copyright (c) 2018-2019 Thijs Brentjens (thijs@brentjensgeoict.nl), for Geonovum The Netherlands

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/*
PHP script to receive a release of a Github Webhook and copy relevant files and directories for Geonovum ReSpec documents.
See the README https://github.com/Geonovum/respec-utils/blob/master/README.md for more details

*/

// get settings
include 'githubConfig.php';
include 'utils.php';

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

// process the GitHub release information
try {
  if ( $_POST['payload'] ) {
    $evt = json_decode($_POST['payload']);
    // a release:
    if ($evt->{'action'}=='published') {
      // if the release has been published, continue creating the directories and copying files
      $tagName = $evt->release->tag_name;
      $repoName = $evt->repository->name;
      $repoFullname = $evt->repository->full_name;

      // the organisation is taken from the repoFullname
      $repoFullnameParts = preg_split('#/#', $repoFullname);
      $repoOrganisation = $repoFullnameParts[0];

      $pubDomain=$repoName; // default to repository name
      $shortName=$tagName; // default to tag name
      // check if it is okay to use this pubDomain for this repository
      // 1. get the list of pubDomains from the JSON config
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
      $newDir = $pubDomain.'/'.$tagName;
      // make sure the pubDomain directory is there
      if (!file_exists($pubDomain)) {
          mkdir($pubDomain, 0777, true);
      }
      // remove the tag / release dir if it already exists (if the same tagname is used)
      if (file_exists($newDir)) {
        rmdir_recursive($newDir);
      }
      $tempZipName = $pubDomain.'_'.$tagName.'.zip';
      // the github zipball url does not seem to work properly, so fetch the zip based on the URL of the tagname
      // example:
      // https://github.com/thijsbrentjens/wfs-storedqueries/archive/def-hr-wpgs-20171223.zip
      $zipballUrl = $evt->release->zipball_url;
      $zipballUrl = 'https://github.com/'.$repoOrganisation.'/'.$repoName.'/archive/'.$tagName.'.zip';
      // NOTE: 2020-01-17: use a streaming writer for processing large files. So use fopen() instead of file_get_contents
      file_put_contents($tempZipName, fopen($zipballUrl, 'r'));
      $zip = new ZipArchive;
      $res = $zip->open($tempZipName);
      if ($res === TRUE) {
          // Use a staging dir to get all the documents
          $stagingDir = '_staging_'.$pubDomain.'_'.$tagName;
          $zip->extractTo($stagingDir);
          $zip->close();
          // zipfile is of format {repo}-{tagnumber-without-v}.zip.
          // get the name of the extracted zip from the staging dir and copy the allowed files to the repoName:
          $results = scandir($stagingDir);
          foreach ($results as $result) {
              if ($result === '.' or $result === '..') continue;
              if (is_dir($stagingDir. '/' . $result)) {
                 if ($shortName!=$tagName) {
                    // make sure the new release dir is created and only copy the media-dir and rename the snapshot.html file to index.html. These are the only files allowed for Geonovum releases
                    mkdir($newDir, 0777, true);
                    cpdir_recursive($stagingDir.'/'.$result.'/media', $newDir.'/media');
                    try {
                      if (is_dir($stagingDir.'/'.$result.'/data/Images')) {
                        $dataDir = $newDir.'/data';
                        $dataImagesDir = $dataDir.'/Images';
                        if (!is_dir($dataImagesDir)) {
                          if (!is_dir($dataDir)) {
                            mkdir($dataDir, 0777, true);
                          }
                          mkdir($dataImagesDir, 0777, true);
                        }
                        cpdir_recursive($stagingDir.'/'.$result.'/data/Images', $newDir.'/data/Images');
                      }
                    } catch (Exception $e) {
                        // not present probably
                        echo "Error in copying data/Images:";
                        echo $e;
                    }
                    copy($stagingDir.'/'.$result.'/snapshot.html', $newDir.'/index.html');
                    // update the latest dir too
                    // delete all files from the previous version and copy the files from the new release
                    if (is_dir($latestDir)) {
                      rmdir_recursive($latestDir);
                    }
                    cpdir_recursive($newDir, $latestDir);
                 }
              }
          }
          // Remove working files
          unlink($tempZipName);
          rmdir_recursive($stagingDir);
      } else {
        trigger_error("Something went wrong in processing the zip file", E_USER_WARNING);
      }
    }
  } else {
    echo "Nothing to do";
  }
} catch (Exception $e) {
    // not present probably
    echo "Error in copying";
    echo $e;
}
?>

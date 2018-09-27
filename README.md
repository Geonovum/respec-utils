# ReSpec utilities for Geonovum
For publishing ReSpec documents on the Geonovum website https://docs.geostandaarden.nl a few ReSpec utilities have been created:
1. automatically publish the document after a GitHub release has been made (using a webhook)
1. creating an index page of published documents

These utilities are still in early development.

## Automatically publish Respec docs
Originated from github issue https://github.com/Geonovum/respec/issues/148, this PHP script publishes the ReSpec documents if on Github a release is made.

Prerequisites:
1. a webserver that runs PHP
1. a writable directory for the PHP script to create directories and documents in
1. a list of publication domain (directories) per ReSpec document (pubDomainList.json). This document could be in this GitHub repo for example.
  1. this list is important to make sure that existing other ReSpec documents are not (accidentally) overwritten.
  1. a publication domain is used as directory on the webserver
  1. the shortName of the ReSpec doc, to update the latest version copy (TODO: read it from the ReSpec config?)

Example pubDomainList.json, for 2 github accounts, with several github repositories:
```
{
  "Geonovum": [{
      "repoName": "NEN3610-Linkeddata",
      "pubDomain": "nen3610",
      "shortName": "nldp"
  },{
      "repoName": "whitepaper-standaarden",
      "pubDomain": "wp",
      "shortName": "wpgs"
  }],
  "thijsbrentjens": [{
      "repoName": "wfs-storedqueries",
      "pubDomain": "wfs",
      "shortName": "wfs-sq"
  }]
}
```

1. a config file for github named ```githubConfig.php``` in the same directory as ```releasecreated.php```, that contains:
  1. the Github webhook secret
  1. the full URI to the file pubDomainList.json

Example ```githubConfig.php``` file:

```
<?php
// the full URL to the json file with the pubDomains per github repo
$pubDomainListURL = 'https://raw.githubusercontent.com/Geonovum/respec-utils/master/config/pubDomainList.json';

// configure the secret in GitHub's webhook
$hubSecret = 'My secret';
?>
```


For a ReSpec document to be published, the following has to be done:
1. make sure the ReSpec document has it's own GitHub repository
1. the (root) ReSpec document shall be named ```index.html```
1. a webhook to the proper PHP page is created, for events of type ```release```
1. to publish:
  1. create a new release (via Github)
  1. make sure that the tagname of the release is identical to the last part of the "This version" URL. E.g. (TODO)

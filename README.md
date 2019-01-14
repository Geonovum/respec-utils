# ReSpec utilities for Geonovum
For publishing ReSpec documents on the Geonovum website https://docs.geostandaarden.nl a few ReSpec utilities have been created:
1. automatically publish the document after a GitHub release has been made (using a webhook)
1. creating an index page of published documents

## Automatically publish Respec docs
Originated from github issue https://github.com/Geonovum/respec/issues/148, this PHP script automatically publishes the ReSpec documents if a release is made on GitHub and updates the last version of the ReSpec document on https://docs.geostandaarden.nl. To use this method of publishing, the ReSpec document and the Github repo of the document have to meet some requirements. If these are not met, manual publication is the only option.

### Requirements for the publication server
1. a webserver that runs PHP (for Geonovum on https://docs.geostandaarden.nl), where the script ```releasecreated.php``` and ```utils.php``` are placed
1. a writable directory for the PHP script to create directories and documents in (for Geonovum on https://docs.geostandaarden.nl)
1. a config file for github named ```githubConfig.php``` in the same directory as ```releasecreated.php```, that contains:
  1. the Github webhook secret (ask the github maintainers for this secret when setting it up)
  1. the full URL to the file pubDomainList.json (see below)
1. a web accessible list of publication domain (directories) per ReSpec document (pubDomainList.json in this repository). This document could be in this GitHub repo for example.
  1. this list is important to make sure that existing other ReSpec documents are not (accidentally) overwritten.
  1. a publication domain is used as directory on the webserver
  1. the shortName of the ReSpec doc, to update the latest version copy

#### Example config files
Example ```pubDomainList.json```, for 2 different github accounts, with several github repositories:
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

Example ```githubConfig.php``` file:

```
<?php
// the full URL to the json file with the pubDomains per github repo
$pubDomainListURL = 'https://raw.githubusercontent.com/Geonovum/respec-utils/master/config/pubDomainList.json';

// configure the secret in GitHub's webhook
$hubSecret = 'My secret';
?>
```

### Requirements for the GitHub repository for publishing
For a ReSpec document to be published, the following requirements have to be met.

#### Initial setup (once)
For the Github repository:
1. make sure the ReSpec document has it's **own, exclusive GitHub repository** and the document is in the root directory of the repository
  1. all media files (e.g. images) shall be placed in the directory ```./media/``` in the repository. This is the only directory that will be copied the publication server
1. a webhook to the proper PHP page is created in the GitHub repository, for events of type ```release```. Go to the Settings of the repository and add a webhook:
  1. set the Payload URL to the full URL of the ```releasecreated.php``` script
  1. content type ```application\x-www-form-urlencoded```
  1. make sure that the secret is set correctly (ask the Geonovum ReSpec maintainers for this secret when setting up the webhook)
  1. set individual events to: Releases (others won't be processed)

Configure the domain and document shortname:
1. make sure there is an entry in ```pubDomainList.json``` for the publication domain and the shortname of the document. Ask the Geonovum ReSpec maintainers to create this.

#### Publishing a (new) release of a ReSpec document
1. the document to publish on https://docs.geostandaarden.nl shall be a **snapshot** of the ReSpec document named ```snapshot.html``` in the root directory of the repo
  1. again: only images or other resources in the ```./media/```-directory will be copied
1. create a new release (via Github's website)
1. make sure that the tagname of the release is identical to the last part of the "This version" URL. E.g. ```cv-im-imvg-20180718```

SolrBundle - Object Document Mapper bundle for Solr
==========
## Introduction
SolrBundle provides ODM support for solr

## Installation

### Step 1: Install SolrBundle

Add the following dependency to your composer.json file:
``` json
{
    "require": {
		...,
        "realestateconz/solr-bundle": "master-dev"
    }
}
```


### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Realestate\\SolrBundle\RealestateCoNz\SolrBundle(),
    );
}
```


### Step 3: Configure

``` yaml
# app/config/config.yml
realestate_solr:
    solarium:
        connection:
            adapter: Realestate\SolrBundle\Bridge\Solarium\Adapter\ZendHttp
            adapteroptions:
                host: localhost
                port: 8080
                path: /solr
                timeout: 10
                adapter: Zend_Http_Client_Adapter_Curl
```



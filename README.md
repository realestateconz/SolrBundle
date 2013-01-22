SolrBundle - Object Document Mapper bundle for Solr
==========

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


<?php

require_once(dirname(__FILE__).'/../../../../test/bootstrap/unit.php');
echo dirname(__FILE__).'/../../../../test/bootstrap/unit.php',"\n",
    realpath(dirname(__FILE__).'/../../../../test/bootstrap/unit.php'),"\n";

// horrible hardcoding of relative symfony path fixme
require_once  dirname(__FILE__).'/../../../../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$configuration = new sfProjectConfiguration(getcwd());
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

require_once dirname(__FILE__).'/../../config/sfDomFeedPluginConfiguration.class.php';
$plugin_configuration = new sfDomFeedPluginConfiguration($configuration, dirname(__FILE__).'/../..');

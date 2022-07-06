<?php

require dirname(__DIR__).'/vendor/autoload.php';

(new \Symfony\Component\Filesystem\Filesystem())->remove(__DIR__.'/../var/cache/test');
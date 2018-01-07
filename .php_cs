<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('bootstrap/cache')
    ->exclude('resources/assets')
    ->exclude('resources/views')
    ->exclude('storage')
    ->exclude('node_modules')
    ->in(__DIR__);

$fixers = [
  '-psr0',
  '-phpdoc_no_empty_return',
  '-phpdoc_no_package',
  '-phpdoc_params',
  '-phpdoc_short_description',
  '-unalign_double_arrow',
  '-unalign_equals',
  'ereg_to_preg',
  'ordered_use',
  'php_unit_construct',
  'php_unit_strict',
  'phpdoc_order',
  'short_array_syntax',
  'strict',
  'strict_param',
];

return Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder)
    ->setUsingCache(true);
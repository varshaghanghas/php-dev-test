<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new Finder())
    ->in(__DIR__);

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect(null, null, 2**18-1))
    ->setRules([
        '@PER-CS'          => true,
        '@PHP82Migration'  => true,
    ])
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder);

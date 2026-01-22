<?php

use TwigCsFixer\Config\Config;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Twig;

$ruleset = new Ruleset();
$ruleset->addStandard(new Twig());

// Règles personnalisées pour Sylius
$ruleset->overrideRule('block_name', [
    'space_before_naming' => true,
    'space_after_naming' => true,
]);

$config = new Config();
$config->setRuleset($ruleset);

// Répertoires à analyser
$config->addFinder(
    (new Finder())
        ->in(__DIR__ . '/app/Resources')
        ->exclude('cache')
        ->name('*.twig')
);

return $config;

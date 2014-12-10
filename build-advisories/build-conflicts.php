<?php

use Roave\SecurityAdvisories\Advisory;
use Roave\SecurityAdvisories\Component;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Advisory.php';
require_once __DIR__ . '/Component.php';

$advisoriesRepository = 'git@github.com:sensiolabs/security-advisories.git';
$advisoriesExtension  = 'yaml';
$buildDir             = __DIR__ . '/../build';
$baseComposerJson     = [
    'name' => 'roave/roave-security-advisories',
    //'type' => 'meta'
    'description' => 'Conflict rules based on sensiolab\'s security advisories:'
        . ' prevents installation of packages with known security vulnerabilities.'
        . ' Please only use dev-master@DEV from this package.'
        . ' Commits on this package are GPG-signed.',
    'license' => 'MIT',
    'authors' => [
        'name'  => 'Marco Pivetta',
        'role'  => 'maintainer',
        'email' => 'ocramius@gmail.com',
    ],
];

$cleanBuildDir = function () use ($buildDir) {
    system('rm -rf ' . escapeshellarg($buildDir));
    system('mkdir ' . escapeshellarg($buildDir));
};

$cloneAdvisories = function () use ($advisoriesRepository, $buildDir) {
    system(
        'git clone '
        . escapeshellarg($advisoriesRepository)
        . ' ' . escapeshellarg($buildDir . '/security-advisories')
    );
};

/**
 * @param string $path
 *
 * @return Advisory[]
 */
$findAdvisories = function ($path) use ($advisoriesExtension) {
    $yaml = new Yaml();
    return array_map(
        function (\SplFileInfo $advisoryFile) use ($yaml) {
            return Advisory::fromArrayData(
                $yaml->parse(file_get_contents($advisoryFile->getRealPath()), true)
            );
        },
        iterator_to_array(new \CallbackFilterIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ),
            function (\SplFileInfo $advisoryFile) use ($advisoriesExtension) {
                // @todo skip `vendor` dir
                return $advisoryFile->isFile() && $advisoryFile->getExtension() === $advisoriesExtension;
            }
        ))
    );
};

/**
 * @param Advisory[] $advisories
 *
 * @return Component[]
 */
$buildComponents = function (array $advisories) {
    // @todo need a functional way to do this, somehow
    $indexedAdvisories = [];
    $components        = [];

    foreach ($advisories as $advisory) {
        if (! isset($indexedAdvisories[$advisory->getComponentName()])) {
            $indexedAdvisories[$advisory->getComponentName()] = [];
        }

        $indexedAdvisories[$advisory->getComponentName()][] = $advisory;
    }

    foreach ($indexedAdvisories as $compoentName => $advisories) {
        $components[$compoentName] = new Component($compoentName, $advisories);
    }

    return $components;
};

/**
 * @param Component[] $components
 *
 * @return string[]
 */
$buildConflicts = function (array $components) {
    $conflicts = [];

    foreach ($components as $component) {
        $conflicts[$component->getName()] = $component->getConflictConstraint();
    }

    return array_filter($conflicts);
};

$buildConflictsJson = function (array $baseConfig, array $conflicts) {
    return json_encode(
        array_merge(
            $baseConfig,
            ['conflicts' => $conflicts]
        ),
        JSON_PRETTY_PRINT
    );
};

$commitJson = function ($jsonString) {
    file_put_contents(__DIR__ . '/../composer.json', $jsonString);

    // @TODO add `git commit` logic here
};



// cleanup:
$cleanBuildDir();
$cloneAdvisories();

// actual work:
$commitJson(
    $buildConflictsJson(
        $baseComposerJson,
        $buildConflicts(
            $buildComponents(
                $findAdvisories($buildDir . '/security-advisories')
            )
        )
    )
);

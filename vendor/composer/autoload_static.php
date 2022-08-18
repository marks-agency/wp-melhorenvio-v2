<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit358d1ccd365a004e53a50c1bf3ba1b22
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MelhorEnvio\\Services\\' => 21,
            'MelhorEnvio\\Models\\' => 19,
            'MelhorEnvio\\Interfaces\\' => 23,
            'MelhorEnvio\\Helpers\\' => 20,
            'MelhorEnvio\\Controllers\\' => 24,
            'MelhorEnvio\\Bases\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MelhorEnvio\\Services\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Services',
        ),
        'MelhorEnvio\\Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Models',
        ),
        'MelhorEnvio\\Interfaces\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core/interfaces',
        ),
        'MelhorEnvio\\Helpers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Helpers',
        ),
        'MelhorEnvio\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Controllers',
        ),
        'MelhorEnvio\\Bases\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core/bases',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit358d1ccd365a004e53a50c1bf3ba1b22::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit358d1ccd365a004e53a50c1bf3ba1b22::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit358d1ccd365a004e53a50c1bf3ba1b22::$classMap;

        }, null, ClassLoader::class);
    }
}
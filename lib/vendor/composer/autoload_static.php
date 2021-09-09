<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit74dcd96d8dead0f0924ad952897a64ae
{
    public static $files = array (
        '538ca81a9a966a6716601ecf48f4eaef' => __DIR__ . '/..' . '/opis/closure/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'Opis\\Closure\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Opis\\Closure\\' => 
        array (
            0 => __DIR__ . '/..' . '/opis/closure/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit74dcd96d8dead0f0924ad952897a64ae::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit74dcd96d8dead0f0924ad952897a64ae::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit74dcd96d8dead0f0924ad952897a64ae::$classMap;

        }, null, ClassLoader::class);
    }
}
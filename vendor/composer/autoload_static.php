<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit99a1035136b0e973e5a2871496af016e
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'C' => 
        array (
            'CodeMaster\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'CodeMaster\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit99a1035136b0e973e5a2871496af016e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit99a1035136b0e973e5a2871496af016e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit99a1035136b0e973e5a2871496af016e::$classMap;

        }, null, ClassLoader::class);
    }
}

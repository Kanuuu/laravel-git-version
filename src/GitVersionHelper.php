<?php
namespace Kanuuu\LaravelGitVersion;

use Config;
use Carbon\Carbon;

class GitVersionHelper
{
    private static function versionFile()
    {
        return base_path() . '/version';
    }

    private static function appName()
    {
        return Config::get('app.name', 'app');
    }

    /**
     * Get the app's version string
     *
     * If a file <base>/version exists, its contents are trimmed and used.
     * Otherwise we get a suitable string from `git describe`.
     *
     * @throws Exception\CouldNotGetVersionException if there is no version file and `git
     * describe` fails
     * @return string Version string
     */
    public static function getVersion($withdate = false)
    {
        // If we have a version file, just return its contents
        if (file_exists(self::versionFile())) {
            return trim(file_get_contents(self::versionFile()));
        }
        $date = '';
        // Remember current directory
        $dir = getcwd();

        // Change to base directory
        chdir(base_path());

        // Get version string from git
        $output = shell_exec('git describe --always --tags');

        if ($output === null) {
            throw new Exception\CouldNotGetVersionException;
        }

        // Get date of commit
        if ($withdate){
            $date = shell_exec('git show -s --format=%ci '. $output);
        }

        if ($date === null) {
            throw new Exception\CouldNotGetVersionException;
        }

        // Change back
        chdir($dir);


        return ($withdate ? Carbon::parse(trim($date))->format('Ymd') : '') .' | '. trim($output);
    }

    /**
     * Get a string identifying the app and version
     *
     * @see getVersion
     * @throws Exception\CouldNotGetVersionException if there is no version file and `git
     * describe` fails
     * @return string App name and version string
     */
    public static function getNameAndVersion()
    {
        return self::appName() . '/' . self::getVersion();
    }
    /**
     * Get a string identifying the app version and version or git commit
     *
     * @see getVersion
     * @throws CouldNotGetVersionException if there is no version file and `git
     * describe` fails
     * @return string App name and version string
     */
    public static function getAppVerAndVersion()
    {
        return 'ver.'.Config::get('app.version') . ' ' . self::getVersion(true);
    }
   

}

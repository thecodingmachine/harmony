<?php
namespace Harmony\Services;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * A service that opens a browser from the PHP CLI.
 * Borrowed from Composer code.
 */
class BrowserService {

    private $output;

    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * opens a url in your system default browser
     *
     * @param string $url
     */
    public function openBrowser($url)
    {
        $url = self::escapeArgument($url);

        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            return passthru('start "web" explorer "' . $url . '"');
        }

        passthru('which xdg-open', $linux);
        passthru('which open', $osx);

        if (0 === $linux) {
            passthru('xdg-open ' . $url);
        } elseif (0 === $osx) {
            passthru('open ' . $url);
        } else {
            $this->output->write('<error>No suitable browser opening command found, open yourself: ' . $url.'</error>');
        }
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * @param string $argument The argument that will be escaped
     *
     * @return string The escaped argument
     */
    private static function escapeArgument($argument)
    {
        //Fix for PHP bug #43784 escapeshellarg removes % from given string
        //Fix for PHP bug #49446 escapeshellarg doesn't work on Windows
        //@see https://bugs.php.net/bug.php?id=43784
        //@see https://bugs.php.net/bug.php?id=49446
        if ('\\' === DIRECTORY_SEPARATOR) {
            if ('' === $argument) {
                return escapeshellarg($argument);
            }

            $escapedArgument = '';
            $quote =  false;
            foreach (preg_split('/(")/i', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
                if ('"' === $part) {
                    $escapedArgument .= '\\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    // Avoid environment variable expansion
                    $escapedArgument .= '^%"'.substr($part, 1, -1).'"^%';
                } else {
                    // escape trailing backslash
                    if ('\\' === substr($part, -1)) {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgument .= $part;
                }
            }
            if ($quote) {
                $escapedArgument = '"'.$escapedArgument.'"';
            }

            return $escapedArgument;
        }

        return escapeshellarg($argument);
    }

    private static function isSurroundedBy($arg, $char)
    {
        return 2 < strlen($arg) && $char === $arg[0] && $char === $arg[strlen($arg) - 1];
    }
}
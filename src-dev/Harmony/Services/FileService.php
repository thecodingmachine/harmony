<?php
namespace Harmony\Services;

use Symfony\Component\Filesystem\Filesystem;

/**
 * This file is in charge of ensuring files can possibly be written.
 *
 * @author David Negrier <david@mouf-php.com>
 */
class FileService
{

    /**
     * Tests if a file can be written.
     * Will throw a FileNotWritableException if the file is not writable or the directory that might contain the file
     * is not writable.
     *
     * @param string $filename
     */
    public static function detectWriteIssues($filename)
    {
        $iterablefilename = $filename;

        do {
            if (file_exists($iterablefilename)) {
                if (!is_writable($iterablefilename)) {
                    $message = "File system error: ";
                    if (is_dir($iterablefilename)) {
                        $message .= "Directory ";
                    } else {
                        $message .= "File ";
                    }
                    $message .= "'$iterablefilename' is not writable.";

                    throw new FileNotWritableException($message, 0, null, $iterablefilename);
                } else {
                    return;
                }
            }
        } while ($iterablefilename = dirname($iterablefilename));
    }

    /**
     * Tests if a file can be created.
     * Will create the file's directory if needed with 775 rights.
     * Will throw an exception if file cannot be created.
     *
     * @param string $file
     */
    public static function prepareDirectory($filename)
    {
        self::detectWriteIssues($filename);

        $fs = new Filesystem();

        $dirname = dirname($filename);

        if (!is_dir($dirname)) {
            $fs->mkdir($dirname, 0775);
        }
    }
}

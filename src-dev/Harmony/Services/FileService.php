<?php
namespace Harmony\Services;

/**
 * This file is in charge of ensuring files can possibly be written.
 *
 * @author David Negrier <david@mouf-php.com>
 */
class FileService {

    /**
     * Tests if a file can be written.
     * Will throw a FileNotWritableException if the file is not writtable or the directory that might contain the file
     * is not writable.
     *
     * @param string $filename
     */
    public static function detectWriteIssues($filename) {
        $iterablefilename = $filename;

        do {
            if (file_exists($iterablefilename)) {
                if (!is_writable($iterablefilename)) {
                    throw new FileNotWritableException($iterablefilename);
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
    public static function setupFile($filename) {
        $fs = new Filesystem();

        $dirname = dirname($filename);

        if (!is_dir($dirname)) {
            try {
                $fs->mkdir($dirname, 0775);
            } catch (IOExceptionInterface $e) {
                throw new MoufException("An error occurred while creating your directory at '".$e->getPath()."'", 1, $e);
            }
        }

        if ($fs->exists($filename) && !is_writable($filename)) {
            throw new MoufException("Error, unable to write file '".$filename."'");
        }

        if (!$fs->exists($filename) && !is_writable($dirname)) {
            throw new MoufException("Error, unable to write a file in directory '".$dirname."'");
        }
    }
}

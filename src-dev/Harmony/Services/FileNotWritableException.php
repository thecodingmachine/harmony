<?php
namespace Harmony\Services;

/**
 * Exception throw if a file is expected to be writable.
 *
 * @author David Negrier <david@mouf-php.com>
 */
class FileNotWritableException extends \Exception {

    private $notWritableFile;

    public function __construct($notWritableFile, $message = "", $code = 0, $exception = null) {
        if (empty($message)) {
            $message = "File system error: ";
            if (is_dir($notWritableFile)) {
                $message .= "Directory ";
            } else {
                $message .= "File ";
            }
            $message .= "'$notWritableFile' is not writable.";
        }
        parent::__construct($message, $code, $exception);
        $this->notWritableFile = $notWritableFile;
    }

    public function getNotWritableFile() {
        return $this->notWritableFile;
    }
}
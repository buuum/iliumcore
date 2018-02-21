<?php

namespace Ilium\Dependency;

class ErrorHandler
{

    /**
     * @var ErrorHandlerInterface
     */
    private $shutdown_handler;
    /**
     * @var ErrorHandlerInterface
     */
    private $error_handler;

    public function __construct($development = true)
    {
        if ($development) {
            set_error_handler([$this, 'handleErrors']);
            register_shutdown_function([$this, "shutdownFunction"]);

            $display_errors = "1";
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', $display_errors);
            ini_set('html_errors', $display_errors);

        }

        ob_start('ob_gzhandler');
        ob_start([$this, "sanitize_output"]);
    }

    public function setErrorHandler(ErrorHandlerInterface $handleError)
    {
        $this->error_handler = $handleError;
    }

    public function setShutdownFunction(ErrorHandlerInterface $handleError)
    {
        $this->shutdown_handler = $handleError;
    }

    public function sanitize_output($buffer)
    {

        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

    public function shutdownFunction()
    {
        $error = error_get_last();

        $save_errors = array(
            E_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR
        );
        if (in_array($error['type'], $save_errors)) {
            $errortypes = array(
                E_ERROR         => 'Fatal error',
                E_CORE_ERROR    => 'Fatal error (Core Error)',
                E_COMPILE_ERROR => 'Fatal error (Compile Error)'
            );

            if ($this->shutdown_handler) {
                $this->shutdown_handler->parseError(
                    $errortypes[$error['type']],
                    $error['type'],
                    $error['message'],
                    $error['file'],
                    $error['line']);
            } else {
                echo $errortypes[$error['type']] . ' ' . $error['message'] . ' ' . $error['file'] . ' ' . $error['line'];
            }

        }
    }

    public function handleErrors($errno, $errmsg, $filename, $linenum)
    {
        if (0 == error_reporting()) {
            return true;
        }

        $errortype = array(
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parsing Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Runtime Notice',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated'
        );

        $errtype = (isset($errortype[$errno])) ? $errortype[$errno] : 'Unknow';

        if ($this->error_handler) {
            $this->error_handler->parseError($errtype, $errno, $errmsg, $filename, $linenum);
        } else {
            echo $errtype . ' ' . $errmsg . ' ' . $filename . ' ' . $linenum;
        }

        return true;
    }

}
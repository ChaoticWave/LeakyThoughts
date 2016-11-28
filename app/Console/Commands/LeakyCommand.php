<?php namespace ChaoticWave\LeakyThoughts\Console\Commands;

use Carbon\Carbon;
use ChaoticWave\BlueVelvet\Console\Commands\BaseCommand;
use ChaoticWave\LeakyThoughts\MailParser;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

abstract class LeakyCommand extends BaseCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var Client
     */
    protected static $client;
    /**
     * @var MailParser
     */
    protected static $parser;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns the --index option or argument, if one is available
     *
     * @return string
     */
    protected function getIndexOption()
    {
        $_index = null;

        if ($this->hasOption('index')) {
            $_index = $this->option('index');
        } elseif ($this->hasArgument('index')) {
            $_index = $this->argument('index');
        }

        return $_index ?: config('leaky.default_index');
    }

    /**
     * @param string $value      The value
     * @param string $to         To encoding, defaults to 'UTF-8'
     * @param bool   $asciiFirst If TRUE, will convert to 'ASCII' before converting to "$to"
     *
     * @return mixed|string
     */
    protected function encodeValue($value, $to = 'UTF-8', $asciiFirst = true)
    {
        //  Convert to ASCII before converting to $to
        if ($value && $asciiFirst && false !== ($_encoded = mb_convert_encoding($value, 'ASCII')) && $_encoded !== $value) {
            $value = $_encoded;

            if ($value && false !== ($_encoded = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH)) && $_encoded !== $value) {
                $value = $_encoded;
            }
        }

        return $value ? mb_convert_encoding($value, strtoupper($to)) : $value;
    }

    /**
     * @param \Traversable|mixed $array
     *
     * @return mixed|string
     */
    protected function utf8_encode_array(&$array = null)
    {
        if (is_scalar($array)) {
            return $this->encodeValue($array);
        }

        if ($array instanceof \Traversable) {
            foreach ($array as $_key => $_value) {
                $_raw = $_value;
                $this->utf8_encode_array($_value);

                if ($_raw && false !== $_value && $_value !== $_raw) {
                    data_set($array, $_key, $_value);
                }
            }
        }

        return $array;
    }

    /**
     * @param bool  $reload
     * @param array $config
     *
     * @return \Elasticsearch\Client
     */
    protected static function getClient($reload = false, $config = [])
    {
        if (empty($config)) {
            if (null !== ($_hosts = config('leaky.elastic.hosts'))) {
                $config = ['hosts' => $_hosts];
            }
        }

        return (!static::$client || $reload) ? static::$client = ClientBuilder::fromConfig($config) : static::$client;
    }

    /**
     * @param bool $reload
     *
     * @return \ChaoticWave\LeakyThoughts\MailParser
     */
    protected static function getParser($reload = false)
    {
        return (!static::$parser || $reload) ? static::$parser = new MailParser() : static::$parser;
    }

    /**
     * @param array|string $message
     *
     * @return array|bool
     */
    protected function toLogstash($message)
    {
        $_date = new Carbon(array_get($message, 'date', time()));

        $_template = [
            'message'    => array_pull($message, 'subject'),
            '@version'   => '1',
            '@timestamp' => $_date->toW3cString(),
            'type'       => 'stdin',
            'host'       => config('app.name') . '-' . gethostname(),
        ];

        return array_merge($message, $_template);
    }
}

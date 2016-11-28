<?php namespace ChaoticWave\LeakyThoughts\Console\Commands;

use Carbon\Carbon;
use ChaoticWave\BlueVelvet\Enums\GlobFlags;
use ChaoticWave\BlueVelvet\Utility\Disk;

class Load extends LeakyCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $signature = 'leaky:load {--path= : The path from which to read files} {--index= : The name of the Elasticsearch index to use} {--json : Create a JSON version of each file} {--headers : Include file headers in index content} {--html : Include HTML bodies in index content} {--attachments : Dump attachments} {--no-index : If specified, no Elasticsearch functions will be performed}';
    /** @inheritdoc */
    protected $description = 'Loads email files from "path" into configured database';
    /**
     * @var bool If true, everything BUT indexing  into ES will occur
     */
    protected $noIndex = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        try {
            $this->output->writeln('Leaky v' . config('app.version') . '; leaky:load - mbox file loader' . PHP_EOL);
            $this->noIndex = $this->option('no-index');

            $_index = $this->option('index') ?: 'leaky';
            $_path = Disk::path([config('leaky.data_path'), $this->option('path') ?: config('leaky.output_path', 'dump')]);

            //  Call the admin command to create the index
            if (!$this->noIndex) {
                \Artisan::call('leaky:admin', ['operation' => 'create']);
            }

            if (Disk::ensurePath($_path) && !is_readable($_path)) {
                throw new \InvalidArgumentException('The path "' . $_path . '" is not readable.');
            }

            \Log::info('Starting at ' . Carbon::now()->toDateTimeString() . ' (' . ($_startTime = microtime(true)) . ')');
            $this->parseMail($_index, $_path);
            \Log::info('Finished in ' . number_format((microtime(true) - $_startTime), 4) . 's');
        } catch (\Exception $_ex) {
            $this->error('Exception processing mail file: ' . $_ex->getMessage());

            return -1;
        }

        return 0;
    }

    /**
     * @param string $index
     * @param string $path
     *
     * @return bool
     */
    protected function parseMail($index, $path)
    {
        if (false === ($_files = Disk::glob(Disk::path([$path, '*.txt']), GlobFlags::GLOB_NODIR | GlobFlags::GLOB_NODOTS)) || empty($_files)) {
            return false;
        }

        $_error = $_count = 0;

        Disk::ensurePath($_jsonPath = $path . '-json');
        Disk::ensurePath($_jsonPath = $path . '-attachments');

        \Log::info('Found ' . number_format(count($_files), 0) . ' files in "' . $path . '"');

        foreach ($_files as $_file) {
            $_filename = $path . DIRECTORY_SEPARATOR . $_file;

            if (false === ($_part = $this->parseMailFile($_filename))) {
                $_error++;
                $this->output->write('<error>.</error>');
                continue;
            }

            //  Write out a JSON file
            if ($this->option('json')) {
                $this->writeJsonFile($_part, $_filename . '.json');
            }

            if ($this->option('attachments')) {
                $this->writeAttachments();
            }

            //  Write out to elasticsearch
            if (!$this->noIndex) {
                if ($this->indexData($index, $_part)) {
                    $_count++;

                    if (0 == $_count % 250) {
                        $this->output->write('.');
                    }
                } else {
                    //  error
                    $_error++;
                    $this->output->write('<error>.</error>');
                }
            }
        }

        $this->output->writeln('');
        $this->output->writeln('Complete. Indexed ' . number_format($_count, 0) . ' file(s) with ' . number_format($_error, 0) . ' error(s)');

        return true;
    }

    /**
     * Massage the parts being stored
     * based on options
     *
     * @param array $parts
     *
     * @return array|string
     */
    protected function checkParts(array &$parts)
    {
        static $_forgotten;

        if (null === $_forgotten) {
            //  Remove stuff from payload
            $_forgotten = [];

            if (!$this->option('html')) {
                $_forgotten[] = 'body.html';
                $_forgotten[] = 'body.htmlEmbedded';
            }

            !$this->option('headers') and $_forgotten[] = 'headers';
            !$this->option('attachments') and $_forgotten[] = 'attachments';
        }

        //  Remove HTML from payload if not wanted
        if ($_forgotten) {
            array_forget($parts, $_forgotten);
        }

        //  Encode long shit
        $_body = array_get($parts, 'body_text');

        if ($_body && false !== ($_encoded = $this->encodeValue($_body))) {
            $parts['body_text'] = $_encoded;
        }

        //  Encode funky names
        foreach ($parts['addresses'] as $_which => $_list) {
            foreach ($_list as $_index => $_addy) {
                if (null !== ($_value = array_get($_addy, 'display'))) {
                    if ($_value && false !== ($_encoded = $this->encodeValue($_value)) && $_encoded !== $_value) {
                        $parts['addresses'][$_which][$_index]['display'] = $_encoded;
                    }
                }
            }
        }

        return $this->utf8_encode_array($parts);
    }

    /**
     * @param string $jsonFile
     * @param array  $data
     */
    protected function writeJsonFile($jsonFile, $data)
    {
        try {
            if (false === ($_json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
                throw new \Exception('Cannot encode to JSON. Error code: ' . json_last_error() . ' - ' . json_last_error_msg());
            }

            file_put_contents($jsonFile, $_json);
        } catch (\Exception $_ex) {
            \Log::warning('Error writing json file "' . $jsonFile . '": ' . $_ex->getMessage());
            file_put_contents($jsonFile . '-bad', print_r($data, true));
        }
    }

    /**
     * Index data into elasticsearch in logstash format
     *
     * @param string $index
     * @param array  $data
     *
     * @return bool
     */
    protected function indexData($index, $data)
    {
        try {
            $_body = $this->toLogstash($data);

            static::getClient()->index([
                'index' => $index,
                'type'  => 'mail',
                'body'  => $_body,
            ]);
        } catch (\Exception $_ex) {
            \Log::error('Exception indexing data: ' . $_ex->getMessage() . PHP_EOL . print_r(['data' => $data], true) . PHP_EOL);

            return false;
        }

        return true;
    }

    /**
     * @param string $file File to read and parse
     *
     * @return array|bool|null|string
     */
    protected function parseMailFile($file)
    {
        $_mail = false;
        $_parts = $_part = null;

        try {
            static::getParser()->setPath($file);
            $_parts = static::getParser()->explodeParts();
            $_mail = $this->checkParts($_parts);
        } catch (\Exception $_ex) {
            if (empty($_mail) && !empty($_parts)) {
                return $_parts;
            }

            if (empty($_parts) && empty($_mail)) {
                \Log::error('Exception getting parts on "' . $file . '": ' . $_ex->getMessage());
            }

            return false;
        }

        return $_mail;
    }

    /**
     * Write out attachments to disk
     *
     * @return array
     */
    protected function writeAttachments()
    {
        $_path = Disk::path([config('leaky.data_path'), config('leaky.attachment_path', 'attachments')]) . DIRECTORY_SEPARATOR;

        return static::$parser->saveAttachments($_path);
    }
}

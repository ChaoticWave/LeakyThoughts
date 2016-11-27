<?php namespace ChaoticWave\LeakyThoughts\Console\Commands;

use ChaoticWave\LeakyThoughts\MailParser;

class Admin extends LeakyCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $signature = 'leaky:admin {--index= : The name of the Elasticsearch index to use} {operation?}';
    /** @inheritdoc */
    protected $description = 'Runs various admin actions on the LT index';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        $this->output->writeln('Leaky v' . config('app.version') . '; leaky:admin - index admin' . PHP_EOL);

        switch ($_op = $this->argument('operation')) {
            case 'create':
                $this->createIndex();
                break;

            case 'info':
            case null:
                $this->output->writeln(json_encode(static::getClient()->info(), JSON_PRETTY_PRINT));
                break;

            default:
                throw new \InvalidArgumentException('The operation "' . $_op . '" is not valid');
        }

        return 1;
    }

    /**
     * @return array
     */
    protected function createIndex()
    {
        $_result = null;

        $index = $this->getIndexOption();
        $_ixClient = static::getClient()->indices();
        $_params = ['index' => $index];

        if ($_ixClient->exists($_params)) {
            $_ixClient->delete($_params);
            $this->output->writeln('<comment>NOTE</comment> existing index deleted.');
        }

        if (is_readable($_setFile = resource_path('/views/elastic/analysis/' . config('app.locale') . '.json'))) {
            $_settings = file_get_contents($_setFile);

            $_ixClient->create(array_merge($_params, ['body' => $_settings]));
            $this->output->writeln('Index <info>' . $index . '</info> analysis configured.');

            $_ixClient->putMapping(array_merge($_params, ['type' => 'mail', 'body' => MailParser::getMapping()]));
            $this->output->writeln('Index < info>' . $index . ' </info > mapped . ');
        }

        return $_result;
    }
}

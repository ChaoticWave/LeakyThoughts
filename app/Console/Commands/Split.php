<?php namespace ChaoticWave\LeakyThoughts\Console\Commands;

use ChaoticWave\BlueVelvet\Exceptions\DiskException;
use ChaoticWave\BlueVelvet\Utility\Disk;
use Symfony\Component\Console\Output\OutputInterface;

class Split extends LeakyCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $signature = 'leaky:split {filename}';
    /** @inheritdoc */
    protected $description = 'Splits out an SMTP inbox into individual JSON files';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        $this->output->writeln('Leaky v' . config('app.version') . '; leaky:split - mbox file splitter' . PHP_EOL);

        $_destination = Disk::path([config('leaky.data_path'), $this->option('path') ?: config('leaky.output_path', 'dump')]);

        if (!Disk::ensurePath($_destination)) {
            throw new \InvalidArgumentException('Cannot create destination path "' . $_destination . '".');
        }

        $_filename = $this->argument('filename');

        if (!is_readable($_filename)) {
            $_filename = $_destination . DIRECTORY_SEPARATOR . $_filename;

            if (!is_readable($_filename)) {
                throw new \InvalidArgumentException('The file "' . $_filename . '" is not readable.');
            }
        }

        return $this->parseMail($_filename, $_destination);
    }

    /**
     * @param string $filename    Absolute /path/to/source/mbox/file
     * @param string $destination Absolute /path/to/write/mail/files
     */
    protected function parseMail($filename, $destination)
    {
        if (false === ($_fp = fopen($filename, 'r'))) {
            throw new DiskException('Unable to open file "' . $filename . '"');
        }

        $_kbTotal = 0;
        $_msg = null;
        $_counter = $_msgLines = 0;
        $_lineCount = false;

        while (true) {
            if (false === ($_line = fgets($_fp))) {
                break;
            }

            //  Add this line to the array
            $_msg[] = $_line;

            //  If we have a line count, increment it
            if (false !== $_lineCount) {
                $_msgLines++;

                if ($_msgLines == $_lineCount) {
                    //  We have a full message!
                    $_fileMask = sprintf('%06d', ++$_counter);
                    $_outfile = Disk::path([$destination, 'mf' . $_fileMask . '.txt']);

                    if (false !== ($_bytes = file_put_contents($_outfile, implode('', $_msg)))) {
                        if (OutputInterface::VERBOSITY_VERBOSE == $this->output->getVerbosity()) {
                            $_kbTotal += ($_size = $_bytes / 1024);
                            $this->output->writeln('Wrote: ' . $_outfile . ' (' . number_format($_size, 2) . ' kb)');
                        } elseif (0 == $_counter % 500) {
                            $this->output->write('.');
                        }
                    }

                    //  Reset
                    $_lineCount = false;
                    $_msgLines = 0;
                    $_msg = PHP_EOL !== $_line ? [$_line] : null;
                }
            } else {
                //  See if this is a lines line
                if (0 === strcasecmp('lines: ', substr($_line, 0, 7))) {
                    //  Add expected two blank lines before and after body
                    $_lineCount = 2 + intval(str_ireplace('lines: ', null, $_line));
                }
            }
        }

        $this->output->writeln('');
        $this->output->writeln('Complete. Created ' . number_format($_counter, 0) . ' file(s) with a total of ' . number_format($_kbTotal, 4) . ' kb');
    }
}

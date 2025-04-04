<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use Laminas\Log\Exception;
use Laminas\Log\Formatter\ChromePhp as ChromePhpFormatter;
use Laminas\Log\Logger;
use Laminas\Log\Writer\ChromePhp\ChromePhpBridge;
use Laminas\Log\Writer\ChromePhp\ChromePhpInterface;
use Traversable;

use function class_exists;
use function is_array;
use function iterator_to_array;

class ChromePhp extends AbstractWriter
{
    /**
     * The instance of ChromePhpInterface that is used to log messages to.
     *
     * @var ChromePhpInterface
     */
    protected $chromephp;

    /**
     * Initializes a new instance of this class.
     *
     * @param null|ChromePhpInterface|array|Traversable $instance An instance of ChromePhpInterface
     *        that should be used for logging
     */
    public function __construct($instance = null)
    {
        if ($instance instanceof Traversable) {
            $instance = iterator_to_array($instance);
        }

        if (is_array($instance)) {
            parent::__construct($instance);
            $instance = $instance['instance'] ?? null;
        }

        if (!$instance instanceof ChromePhpInterface && $instance !== null) {
            throw new Exception\InvalidArgumentException(
                'You must pass a valid Laminas\Log\Writer\ChromePhp\ChromePhpInterface'
            );
        }

        $this->chromephp = $instance ?? $this->getChromePhp();
        $this->formatter = new ChromePhpFormatter();
    }

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        $line = $this->formatter->format($event);

        match ($event['priority']) {
            Logger::EMERG, Logger::ALERT, Logger::CRIT, Logger::ERR => $this->chromephp->error($line),
            Logger::WARN => $this->chromephp->warn($line),
            Logger::NOTICE, Logger::INFO => $this->chromephp->info($line),
            Logger::DEBUG => $this->chromephp->trace($line),
            default => $this->chromephp->log($line),
        };
    }

    /**
     * Gets the ChromePhpInterface instance that is used for logging.
     *
     * @return ChromePhpInterface
     */
    public function getChromePhp()
    {
        // Remember: class names in strings are absolute; thus the class_exists
        // here references the canonical name for the ChromePhp class
        if (
            ! $this->chromephp instanceof ChromePhpInterface
            && class_exists('ChromePhp')
        ) {
            $this->setChromePhp(new ChromePhpBridge());
        }

        return $this->chromephp;
    }

    /**
     * Sets the ChromePhpInterface instance that is used for logging.
     *
     * @param  ChromePhpInterface $instance The instance to set.
     * @return ChromePhp
     */
    public function setChromePhp(ChromePhpInterface $instance)
    {
        $this->chromephp = $instance;
        return $this;
    }
}

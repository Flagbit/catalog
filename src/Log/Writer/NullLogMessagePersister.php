<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class NullLogMessagePersister implements LogMessagePersister
{
    public function persist(LogMessage $logMessage)
    {
        // Do nothing
    }
}

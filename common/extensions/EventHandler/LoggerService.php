<?php

namespace common\extensions\EventHandler;

class LoggerService {

    private string $logFilePath;
    private bool $debug;
    private int $nestedLevel = 0;

    public function __construct(string $logFilePath, bool $debug = true) {
        $this->logFilePath = $logFilePath;
        $this->debug = $debug;
    }

    public function log(string $message, mixed $dump = null, string $level = 'info'): void {
        if (!$this->debug) {
            return;
        }
        // Use $this->logFilePath instead of hardcoded path
        $myfile = fopen($this->logFilePath, "a");
        if (!$myfile) {
            // Optionally, handle error if file cannot be opened, e.g., log to stderr or throw an exception
            error_log("Unable to open log file: {$this->logFilePath}");
            return;
        }

        $offset = str_repeat("    ", max($this->nestedLevel, 0));
        $date = date('Y-m-d H:i:s');
        $txt = "{$date} {$level}: {$this->nestedLevel}.{$offset}{$message}\n";
        fwrite($myfile, $txt);

        if ($dump) {
            if (is_array($dump) || is_object($dump)) {
                $output = print_r($dump, true);
            } else {
                $output = (string) $dump;
            }
            fwrite($myfile, "{$output}\n");
        }

        fclose($myfile);
    }

    public function logStart(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->nestedLevel++;
        $this->log("----------> start {$message}", $dump, $level);
    }

    public function logEnd(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->log("----------< end {$message}\n", $dump, $level);
        $this->nestedLevel--;
    }

    // logQuestSession has been moved to QuestSessionManager.php
    // Ensure QuestSession model is imported there if direct model calls are made.

    /**
     * Checks if debugging is enabled.
     * Helper method for QuestSessionManager or other services that depend on this logger's debug status.
     * @return bool
     */
    public function isDebugEnabled(): bool {
        return $this->debug;
    }
}

<?php

namespace common\helpers;

use common\helpers\ModelHelper;
use Yii;

final class DebugHelper
{

    private const DEBUG_FILE_NAME = 'c:\temp\DebugGameplay';

    /**
     *
     * @param mixed $log
     * @return void
     */
    public static function log(mixed $log): void
    {
        $questId = Yii::$app->session->get('questId');
        $fileName = self::DEBUG_FILE_NAME . "{$questId}.log";
        $myfile = fopen($fileName, 'a');
        if (!$myfile) {
// Optionally, handle error if file cannot be opened, e.g., log to stderr or throw an exception
            Yii::debug("Unable to open debug file '{$fileName}'");
            return;
        }

        if ($log) {
            Yii::debug($log);
            if (is_array($log)) {
                $output = print_r($log, true);
            } elseif (is_object($log)) {
                $output = get_class($log) . " - " . print_r($log->attributes, true);
            } else {
                $output = $log;
            }

            $backTrace = debug_backtrace();
            $message = is_string($output) ? (string) $output : '';
            $date = date('Y-m-d H:i:s');
            $caller = self::getCaller($backTrace);
            $txt = "[{$date}]------------------------------\n"
                    . "{$caller}\n"
                    . "{$message}\n";
            fwrite($myfile, $txt);
        }

        fclose($myfile);
    }

    /**
     * Get primary key columns declared by the model class.
     *
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $className Fully qualified class name
     * @return array<string>
     * @throws InvalidArgumentException when primaryKey() returns empty
     */
    private static function getPrimaryKeyColumns(string $className): array
    {
        /** @var array<string> $pkColumns */
        try {
            $pkColumns = $className::primaryKey();
        } catch (\Throwable $e) {
            return [];
        }

        if (empty($pkColumns)) {
            throw new \yii\base\InvalidArgumentException(
                            "Model {$className} does not declare primary key columns via primaryKey().",
                    );
        }
        return $pkColumns;
    }

    private static function logObjectArg(mixed $object): string
    {
        $className = get_class($object);
        $path = ModelHelper::path($className);
        $fullyQualifiedClassName = "{$path}\\{$className}";

        Yii::debug($fullyQualifiedClassName);
        if ($fullyQualifiedClassName === '\common\components\AppStatus') {
            return "status={$object->name}";
        }
        $pkColumns = self::getPrimaryKeyColumns($fullyQualifiedClassName);
        $logObject = [];
        foreach ($pkColumns as $property) {
            $value = self::logArgValue($object->$property);
            $logObject[] = "{$property} => {$value}";
        }

        if (isset($object->name)) {
            $logObject[] = "name => {$object->name}";
        }

        return $fullyQualifiedClassName . '[' . implode(', ', $logObject) . ']';
    }

    private static function logArgValue(mixed $arg, int $nestedLevel = 0): string
    {
        $filler = str_repeat('  ', $nestedLevel);
        return match (get_debug_type($arg)) {
            'null' => 'null',
            'bool' => $arg ? 'true' : 'false',
            'string' => "'{$arg}'",
            'array' => "[\n"
            . self::logArgs($arg, $nestedLevel + 1) . "\n"
            . "{$filler}]",
            default => $arg
        };
    }

    private static function logArgs(array $args, int $nestedLevel = 0): string
    {
        $argsLog = [];
        $filler = str_repeat('  ', $nestedLevel);
        foreach ($args as $arg) {
            if (is_object($arg)) {
                $argsLog[] = '{' . self::logObjectArg($arg) . '}';
            } else {
                $argsLog[] = self::logArgValue($arg, $nestedLevel);
            }
        }
        return $filler . implode("\n{$filler}", $argsLog);
    }

    private static function getCaller(array $backTrace): string
    {
        $callStack = [];
        foreach ($backTrace as $index => $call) {

            /*
              if ($index < 1) {
              continue;
              }
             *
             */

            $callStack[] = self::traceCall($call, $index);

            $class = $call['class'] ?? null;

            if ($class !== null && is_a($class, \yii\web\Controller::class, true)) {
                break;
            }
        }
        return implode("\n", $callStack);
    }

    private static function traceCall(array $call, int $index): string
    {
        $fileName = substr($call['file'] ?? '', 42);
        $filler = "   ";
        $class = $call['class'] ?? '';
        $type = $call['type'] ?? '';
        $line = $call['line'] ?? '';
        $caller = "{$index}. {$fileName} at line {$line}\n"
                . "{$filler}{$class}{$type}{$call['function']}";

        if (isset($call['args'])) {
            $caller .= "\n{$filler}" . self::logArgs($call['args']);
        }

        return $caller;
    }
}

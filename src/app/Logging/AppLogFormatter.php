<?php

namespace Paw\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class AppLogFormatter extends NormalizerFormatter {

    public function format(LogRecord $record): string {
        $vars = $this->normalizeRecord($record);

        $output = sprintf(
            "\n\n[%s] %s.%s:\n",
            $vars['datetime'],
            $vars['channel'],
            $vars['level_name']
        );

        // Agrega el bloque formateado del contexto de ejecución.
        $output .= "\n" . $this->formatJsonBlock('Contexto de ejecución', $this->formatExtra($vars['extra']));
        
        // Agrega el bloque formateado del mensaje, si no está vacío.
        $message = (string) $vars['message'];
        if ($message !== '') {
            $output .= "\n" . $this->formatMessageBlock('Mensaje', $message);
        }
        
        // Agrega el bloque formateado del contexto del mensaje.
        if (!empty($vars['context'])) {
            $output .= "\n" . $this->formatJsonBlock('Contexto de mensaje', $vars['context']);
        }

        return $output;
    }

    private function formatExtra(array $extra): array {
        $result = [];

        if (isset($extra['file'])) {
            $result['file'] = $extra['file'];
        }

        if (!empty($extra['class'])) {
            $result['method'] = $extra['class'] . ($extra['callType'] ?? '') . ($extra['function'] ?? '');
        }

        return $result;
    }

    private function formatMessageBlock(string $label, string $message): string {
        $message = rtrim($message, "\r\n");

        return sprintf(
            "    %s:\n%s\n",
            $label,
            $this->indentBlock($message)
        );
    }

    private function formatJsonBlock(string $label, array $data): string {
        $json = str_replace('\\\\', '\\', $this->toJson($data, true));

        return sprintf(
            "    %s:\n%s\n",
            $label,
            $this->indentBlock($json)
        );
    }

    private function indentBlock(string $content): string {
        $indent = '    ';

        return $indent . str_replace("\n", "\n" . $indent, $content);
    }
}

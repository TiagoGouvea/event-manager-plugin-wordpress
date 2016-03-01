<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 14/08/15
 * Time: 14:34
 */

namespace lib;


use Monolog\Formatter\FormatterInterface;

class MonologFormatter implements FormatterInterface
{

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $record['session'] = print_r($_SESSION, true);
        $record['GET'] = print_r($_GET, true);
        $record['POST'] = print_r($_POST, true);

        $return = "URL: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] \r\n\r\n";
        if (count($_POST)>0)
            $return.="[[[ POST ]]]";
        else
            $return.="[[[ GET ]]]";
        $return.="\r\n\r\n".print_r($record, true);

        return $return;
    }

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $return = array();
        foreach ($records as $k => $record)
            $return[$k] = $this->format($record);
        return $return;
    }
}
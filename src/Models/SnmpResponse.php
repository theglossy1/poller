<?php

namespace Poller\Models;

use Poller\Exceptions\SnmpException;
use Poller\Log;

class SnmpResponse
{
    private array $results = [];

    public function __construct(array $results)
    {
        $this->formatResults($results);
    }

    public function get(string $oid):string
    {
        $oid = trim(ltrim(trim($oid), '.'));
        if (isset($this->results[$oid])) {
            return $this->results[$oid];
        }

        throw new SnmpException("$oid is not found.");
    }

    public function getAll():array
    {
        return $this->results;
    }

    private function formatResults(array $results)
    {
        $log = new Log();
        foreach ($results as $line) {
            $boom = explode('=', $line, 2);
            if (count($boom) !== 2) {
                $log->error("Unable to split response $line");
                continue;
            }
            $oid = trim(ltrim(trim($boom[0]), '.'));
            $value = trim($boom[1]);
            if ($value[0] === '"') {
                $value = substr($value, 1, -1);
            }
            $value = trim($value);
            if (strpos($value, 'No Such Object available on this agent at this OID') === false) {
                $this->results[$oid] = $value;
            }
        }
    }
}

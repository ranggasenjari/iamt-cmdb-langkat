<?php

namespace App\Support\Ping;

use Symfony\Component\Process\Process;

class IpPingService
{
    public function ping(string $ip): PingResult
    {
        $command = PHP_OS_FAMILY === 'Windows'
            ? ['ping', '-n', '1', '-w', '1000', $ip]
            : ['ping', '-c', '1', '-W', '1', $ip];

        $process = new Process($command);
        $process->setTimeout(2);
        $process->run();

        $output = $process->getOutput()."\n".$process->getErrorOutput();
        $latency = $this->latencyFromOutput($output);

        return new PingResult(
            $process->isSuccessful() ? 'up' : 'down',
            $process->isSuccessful() ? $latency : null,
        );
    }

    private function latencyFromOutput(string $output): ?float
    {
        if (preg_match('/time[=<]?\s*([0-9]+(?:[.,][0-9]+)?)\s*ms/i', $output, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        if (preg_match('/Average\s*=\s*([0-9]+)\s*ms/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }
}

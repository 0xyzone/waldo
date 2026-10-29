<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class WebsocketStatusIndicator extends Component
{
    public bool $isRunning = false;

    public bool $showInfoModal = false;

    public int $port = 8080;

    public string $host = '127.0.0.1';

    public ?string $lastCheckedAt = null;

    public bool $isLocal = false;

    public function mount(): void
    {
        $this->port = (int) config('reverb.servers.reverb.port', 8080);
        $this->host = '127.0.0.1';
        $this->isLocal = app()->environment('local');
        $this->checkStatus();
    }

    public function checkStatus(bool $forceFresh = false): void
    {
        if ($forceFresh) {
            Cache::forget('reverb_status_check');
        }

        $this->isRunning = Cache::remember('reverb_status_check', 30, function () {
            $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);

                return true;
            }

            return false;
        });

        $this->lastCheckedAt = now()->format('H:i:s');
    }

    public function startWebsocket(): void
    {
        $phpBinary = PHP_BINARY;
        $basePath = base_path();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (class_exists('COM')) {
                try {
                    $wsh = new \COM('WScript.Shell');
                    $cmd = "cmd.exe /c start \"ReverbServer\" /min \"{$phpBinary}\" \"{$basePath}\\artisan\" reverb:start";
                    $wsh->Run($cmd, 0, false);
                } catch (\Throwable) {
                    pclose(popen("start /B \"\" \"{$phpBinary}\" \"{$basePath}\\artisan\" reverb:start", 'r'));
                }
            } else {
                pclose(popen("start /B \"\" \"{$phpBinary}\" \"{$basePath}\\artisan\" reverb:start", 'r'));
            }
        } else {
            exec("{$phpBinary} {$basePath}/artisan reverb:start > /dev/null 2>&1 &");
        }

        // Wait briefly for server to bind port
        usleep(1200000);
        $this->checkStatus(true);

        if ($this->isRunning) {
            Notification::make()
                ->success()
                ->title('WebSocket Started!')
                ->body('Laravel Reverb server is now active on port '.$this->port.'. Real-time chat events are connected.')
                ->send();
        } else {
            Notification::make()
                ->warning()
                ->title('Starting WebSocket Server...')
                ->body('If it does not show online shortly, open a terminal and run: php artisan reverb:start')
                ->send();
        }
    }

    public function toggleModal(): void
    {
        $this->checkStatus(true);
        $this->showInfoModal = ! $this->showInfoModal;
    }

    public function closeModal(): void
    {
        $this->showInfoModal = false;
    }

    public function render()
    {
        return view('livewire.websocket-status-indicator');
    }
}

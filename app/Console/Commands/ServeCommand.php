<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server and run Vite';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->components->info('Starting Vite development server...');

        // Start Vite in the background with output streaming
        $viteProcess = Process::fromShellCommandline('npm run dev');
        $viteProcess->setTimeout(null);
        $viteProcess->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$viteProcess->isStarted()) {
            $this->components->error('Failed to start Vite.');
        }

        try {
            // Call the parent handle method to start the PHP server
            return parent::handle();
        } finally {
            // Ensure Vite is stopped when the PHP server stops
            if ($viteProcess->isRunning()) {
                $this->components->info('Stopping Vite development server...');
                $viteProcess->stop();
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class encrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temporalcms:encrypt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt production environment variable file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crypt = \Illuminate\Encryption\Encrypter(Decrypt::getKeyFileContents());

        $path = \base_path('.env.production');
        $text = \file_get_contents($path);
        $enc = $crypt->encrypt($text);
        \file_put_contents($path . '.enc', $enc);

        $this->info("Encrypted enviroment file");
    }
}

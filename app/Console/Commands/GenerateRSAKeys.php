<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use phpseclib3\Crypt\RSA;

class GenerateRSAKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsa:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA public and private keys';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Generate RSA Keys
        $rsa = RSA::createKey(2048);
        $privateKey = $rsa->toString('PKCS1'); // Private Key
        $publicKey = $rsa->getPublicKey()->toString('PKCS1'); // Public Key

        // Buat folder jika belum ada
        $keysFolderPath = storage_path('app/keys');
        if (!is_dir($keysFolderPath)) {
            mkdir($keysFolderPath, 0755, true);
        }

        // Simpan kunci ke dalam folder
        $privateKeyPath = $keysFolderPath . '/private_key.pem';
        $publicKeyPath = $keysFolderPath . '/public_key.pem';


        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);

        $this->info("RSA Keys have been generated:");
        $this->info("Private Key: {$privateKeyPath}");
        $this->info("Public Key: {$publicKeyPath}");

        return Command::SUCCESS;
    }
}

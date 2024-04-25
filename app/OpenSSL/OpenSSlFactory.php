<?php

namespace App\OpenSSL;

use Illuminate\Support\Facades\Storage;

class OpenSSlFactory
{
    public function __construct(
        public array $pkeyOptions = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ],
        public array $csrOptions = [
            'digest_alg' => 'sha256'
        ]
    )
    {
        if (!isset($this->pkeyOptions['config'])) {
            $this->pkeyOptions['config'] = storage_path('app/openssl_rsb.cnf');
        }
    }

    public function createPair(array $dn): OpenSSLPair
    {
        $privkey = openssl_pkey_new($this->pkeyOptions);
        $csr = openssl_csr_new($dn, $privkey, $this->csrOptions);

        return new OpenSSLPair($csr, $privkey);
    }
}

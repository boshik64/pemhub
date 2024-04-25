<?php

namespace App\OpenSSL;
class OpenSSLPair
{
    public function __construct(
        private \OpenSSLCertificateSigningRequest $csr,
        private \OpenSSLAsymmetricKey             $private,
    )
    {

    }

    public function getRequest()
    {
        openssl_csr_export($this->csr, $csr);

        return $csr;
    }

    public function getPrivate()
    {
        openssl_pkey_export($this->private, $private);

        return $private;
    }
}

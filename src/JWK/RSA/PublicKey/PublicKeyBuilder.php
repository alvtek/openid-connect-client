<?php

declare(strict_types=1); 

namespace Alvtek\OpenIdConnect\JWK\RSA\PublicKey;

use Alvtek\OpenIdConnect\JWK\JWKBuilder;
use Alvtek\OpenIdConnect\JWK\RSA\PublicKey;
use Alvtek\OpenIdConnect\BigInteger;
use Alvtek\OpenIdConnect\JWK\KeyType;
use phpseclib\Crypt\RSA as phpseclibRSA;

use Assert\Assert;

class PublicKeyBuilder extends JWKBuilder
{
    /** @var BigInteger */
    protected $n;

    /** @var BigInteger */
    protected $e;
    
    /** @var phpseclibRSA */
    protected $rsaToolkit;

    public function __construct(phpseclibRSA $rsaToolkit, BigInteger $n, BigInteger $e)
    {
        parent::__construct(new KeyType(KeyType::RSA));

        $this->n = clone $n;
        $this->e = clone $e;
        $this->rsaToolkit = $rsaToolkit;
    }

    /**
     *
     * @param array $data
     * @return static
     */
    public static function fromJWKData(array $data)
    {
        $decoded = [];

        if (isset($data['n'])) {
            $decoded['n'] = BigInteger::fromBase64UrlSafe($data['n']);
        }
        
        if (isset($data['e'])) {
            $decoded['e'] = BigInteger::fromBase64UrlSafe($data['e']);
        }
        
        return parent::fromJWKData(array_merge($data, $decoded));
    }

    public static function fromResource($keyResource)
    {
        if (!is_resource($keyResource)) {
            throw new InvalidArgumentException("Argument must be a resource");
        }

        $details = openssl_pkey_get_details($keyResource);

        Assert::that($details)
            ->isArray()
            ->keyExists('rsa');
        
        Assert::that($details['rsa'])
            ->isArray()
            ->choicesNotEmpty(['n', 'e',]);

        $rsa = $details['rsa'];

        return static::fromArray([
            'n'  => new BigInteger($rsa['n'], 256),
            'e'  => new BigInteger($rsa['e'], 256),
        ]);
    }

    public static function fromArray(array $data)
    {
        Assert::that($data)
            ->choicesNotEmpty(['n', 'e']);
        
        if (!isset($data['rsaToolkit'])) {
            $data['rsaToolkit'] = new phpseclibRSA();
        }
        
        $builder = new static($data['rsaToolkit'], $data['n'], $data['e']);
        
        foreach (static::$arrayMappings as $key => $method) {
            if (array_key_exists($key, $data)) {
                $builder->{$method}($data[$key]);
            }
        }

        return $builder;
    }

    public function build() : PublicKey
    {
        return new PublicKey($this);
    }
}
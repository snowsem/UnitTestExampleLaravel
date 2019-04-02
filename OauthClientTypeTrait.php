<?php

namespace App\GraphQL\v31;

use App\Db\OAuth\Token;
use Lcobucci\JWT\Parser;

/**
 * Trait OauthClientTypeTrait
 * @package App\GraphQL\v31
 */

//test
trait OauthClientTypeTrait
{
    /**
     * @var string
     */
    protected $fallbackSource = 'current';

    /**
     * @return bool
     * @deprecated use getTokenClient
     */
    protected function isCashierClient()
    {
        return (bool) $this->getCashierClientId() === $this->getClientId();
    }

    /**
     * @return int|null
     */
    private function getClientId()
    {
        try {
            $tokenId = (new Parser())->parse(\Request::bearerToken())->getHeader('jti');
            $clientId = Token::find($tokenId)->client_id ?? null;

            return (int) $clientId;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return int
     * @deprecated use getTokenClient
     */
    protected function getCashierClientId()
    {
        return config('citylife_settings.cashier_client_id');
    }

    /**
     * @return string
     */
    public function getTokenClient()
    {
        $client = null;

        if ($clientId = $this->getClientId()) {
            $client = array_search($clientId, $this->getClients());
        }

        return $client ? $client : $this->fallbackSource;
    }

    /**
     * @return array
     */
    private function getClients()
    {
        $clients = [
            'cashier_android' => config('citylife_settings.cashier_android_client_id'),
            'cashier_ios' => config('citylife_settings.cashier_ios_client_id'),
        ];

        return $clients;
    }
}
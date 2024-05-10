<?php
declare(strict_types=1);

namespace SocialiteProviders\Bungie;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'BUNGIE';

    /**
     * Base URL relative to which all HTTP request should be made.
     */
    private const BASE_URL = 'https://www.bungie.net';

    /**
     * The base URL for the Bungie.net API.
     *
     * @var string
     */
    private $apiRootPath = self::BASE_URL . '/Platform';

    /**
     * Get the authentication URL for Bungie.net.
     *
     * @param  string  $state  String used for session state.
     * @return string An authentication URL.
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(self::BASE_URL . "/en/OAuth/Authorize", $state);
    }

    /**
     * Get the token URL for Bungie.net.
     *
     * @return string A token URL.
     */
    protected function getTokenUrl(): string
    {
        return self::BASE_URL . "/platform/app/oauth/token/";
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token  An access token.
     * @return array Raw user data.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get("{$this->apiRootPath}/User/GetMembershipsForCurrentUser/", [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
                'X-API-Key'     => $this->clientId,
            ],
        ]);

        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR)['Response']['bungieNetUser'];
    }

    /**
     * @param  array  $user  Raw user data.
     * @return \SocialiteProviders\Manager\OAuth2\User
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['membershipId'],
            'nickname' => $user['displayName'],
            'name'     => $user['uniqueName'],
            'email'    => null,
            'avatar'   => self::BASE_URL . $user['profilePicturePath'],
        ]);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null): array
    {
        $fields = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }
}

<?php

namespace REST\Auth\Actions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Aura\Payload\Payload;

use League\OAuth2\Server\Exception\OAuthServerException;

use Core\Responders\Responder;

class AccessTokenAction implements \Core\ActionInterface
{
    public function __invoke(RequestInterface $request, Payload $payload) : ResponseInterface
    {
        $server = $request->getAttribute('clubman.container')['authorizationServer'];
        $response = (new Responder())->respond();
        try {
            $application = \Core\Clubman::getApplication();
            $request = $request->withAttribute('client_secret', $application->getConfig()->oauth2->client->secret);
            return $server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
            /*
                    } catch (\Exception $exception) {
                        // Catch unexpected exceptions
                        $body = $response->getBody();
                        $body->write($exception->getMessage());
                        return $response->withStatus(500)->withBody($body);
            */
        }
    }
}

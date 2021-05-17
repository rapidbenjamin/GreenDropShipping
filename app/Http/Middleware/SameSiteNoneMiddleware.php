<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use const ARRAY_FILTER_USE_KEY;
use function array_filter;
use function array_values;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;

final class SameSiteNoneMiddleware
{
    /** @var string */
    private $fallbackSuffix = '__ssn-fallback';

    /**
     * Use fallback alternatives to SameSite=None cookies to maintain
     * compatibility with legacy clients that don't yet support the stricter
     * interpretation of the SameSite attribute.
     *
     * @see https://web.dev/samesite-cookie-recipes/#handling-incompatible-clients
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $request = $this->promoteFallbackCookies($request);

        /** @var Response $response */
        $response = $next($request);

        return $this->setFallbackCookies($response);
    }

    private function promoteFallbackCookies(Request $request) : Request
    {
        foreach ($this->getFallbackCookies($request) as $fallbackCookieName => $fallbackCookieValue) {
            $primaryCookieName = $this->convertToPrimaryCookieName($fallbackCookieName);

            if (! $request->cookies->has($primaryCookieName)) {
                $request->cookies->add([$primaryCookieName => $fallbackCookieValue]);
            }

            $request->cookies->remove($fallbackCookieName);
        }

        return $request;
    }

    private function getFallbackCookies(Request $request) : array
    {
        return array_filter(
            $request->cookies->all(),
            function (string $name) {
                return $this->cookieNameHasFallbackSuffix($name);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function cookieNameHasFallbackSuffix(string $cookieName) : bool
    {
        return mb_substr($cookieName, -mb_strlen($this->fallbackSuffix)) === $this->fallbackSuffix;
    }

    private function convertToPrimaryCookieName(string $fallbackCookieName) : string
    {
        return mb_substr($fallbackCookieName, 0, -mb_strlen($this->fallbackSuffix));
    }

    private function setFallbackCookies(Response $response) : Response
    {
        foreach ($this->getSameSiteNoneCookies($response) as $cookie) {
            $response->headers->setCookie($this->convertToFallbackCookie($cookie));
        }

        return $response;
    }

    private function getSameSiteNoneCookies(Response $response) : array
    {
        return array_values(
            array_filter(
                $response->headers->getCookies(),
                static function (Cookie $cookie) {
                    if (! $cookie->isSecure()) {
                        return false;
                    }

                    return mb_strtolower($cookie->getSameSite() ?? '') === 'none';
                }
            )
        );
    }

    private function convertToFallbackCookie(Cookie $cookie) : Cookie
    {
        return new Cookie(
            $cookie->getName() . $this->fallbackSuffix,
            $cookie->getValue(),
            $cookie->getExpiresTime(),
            $cookie->getPath(),
            $cookie->getDomain(),
            $cookie->isSecure(),
            $cookie->isHttpOnly(),
            $cookie->isRaw(),
            null
        );
    }
}
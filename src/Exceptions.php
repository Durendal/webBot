<?php

class webBotException extends Exception {}

class ProxyException extends webBotException {}
class SessionException extends webBotException {}
class ParserException extends webBotException {}
class RequestException extends webBotException {}
class ResponseException extends  webBotException {}
class cURLHandleException extends webBotException {}
class CookiesException extends webBotException {}
class HeadersException extends webBotException {}

// Proxy Exceptions
class InvalidProxyHostException extends ProxyException {}
class InvalidProxyPortException extends ProxyException {}
class InvalidProxyTypeException extends ProxyException {}
class UninitializedProxyException extends ProxyException {}

// Cookie Exceptions
class InvalidCookieKeyException extends CookiesException {}
class DuplicateCookieException extends CookiesException {}
class UninitializedCookieException extends CookiesException {}

// Header Exceptions
class InvalidHeaderException extends HeadersException {}
class DuplicateHeaderException extends HeadersException {}
class UninitializedHeadersException extends HeadersException {}

// cURL Handle Exceptions
class URLException extends cURLHandleException {}
class InvalidURLException extends URLException {}
class MissingURLException extends URLException {}

?>

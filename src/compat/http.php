<?php
/**
 * Backwards-compatibility functions
 *
 * @package phpunk\compat\http
 */

defined( 'HTTP_COOKIE_PARSE_RAW' ) or define( 'HTTP_COOKIE_PARSE_RAW', 1 );
defined( 'HTTP_COOKIE_SECURE' )    or define( 'HTTP_COOKIE_SECURE',   16 );
defined( 'HTTP_COOKIE_HTTPONLY' )  or define( 'HTTP_COOKIE_HTTPONLY', 32 );

defined( 'HTTP_PARAMS_ALLOW_COMMA' )   or define( 'HTTP_PARAMS_ALLOW_COMMA',   1 );
defined( 'HTTP_PARAMS_ALLOW_FAILURE' ) or define( 'HTTP_PARAMS_ALLOW_FAILURE', 2 );
defined( 'HTTP_PARAMS_RAISE_ERROR' )   or define( 'HTTP_PARAMS_RAISE_ERROR',   4 );

defined( 'HTTP_PARAMS_DEFAULT' ) or define( 'HTTP_PARAMS_DEFAULT',
	HTTP_PARAMS_ALLOW_COMMA | HTTP_PARAMS_ALLOW_FAILURE | HTTP_PARAMS_RAISE_ERROR );

if ( ! function_exists( 'http_parse_cookie' ) ) {
	/**
	 * Parses an HTTP cookie
	 *
	 * @param string $cookie
	 * @param integer $flags
	 * @param array $extras
	 * @return object
	 */
	function http_parse_cookie( $cookie, $flags = HTTP_COOKIE_PARSE_RAW, $extras = [] ) {
		$attr = [ 'expires', 'path', 'domain', 'secure', 'httponly' ];

		$output = [
			'cookies' => [],
			'extras'  => [],
			'flags'   => 0,
			'expires' => 0,
			'path'    => '',
			'domain'  => ''
		];

		$parts = explode( ';', $cookie );

		foreach ( $parts as $data ) {
			@list( $key, $value ) = explode( '=', $data );

			$key   = trim( $key );
			$value = trim( $value );

			if ( strtolower( $key ) == 'expires' )
				$value = strtotime( $value );

			if ( in_array( strtolower( $key ), [ 'secure', 'httponly' ] ) )
				$value = true;

			if ( in_array( strtolower( $key ), $attr ) )
				$output[ strtolower( $key ) ] = $value;
			elseif ( in_array( $key, $extras ) )
				$output['extras'][ $key ] = $value;
			else
				$output['cookies'][ $key ] = $value;
		}

		return (object) $output;
	}
}

if ( ! function_exists( 'http_parse_params' ) ) {
	/**
	 * Parses a parameterized HTTP header
	 *
	 * @param string param
	 * @param integer $flags
	 * @return object
	 */
	function http_parse_params( $param, $flags = HTTP_PARAMS_DEFAULT ) {
		$parts = explode( ',', $param );
		$result = (object) [ 'params' => [] ];

		foreach ( $parts as $part ) {
			if ( strpos( $part, ';' ) !== false ) {
				$args = explode( ';', trim( $part ) );

				foreach ( $args as $arg ) {
					if ( strpos( $arg, '=' ) !== false ) {
						list( $key, $value ) = array_pad( explode( '=', $arg ), 2, '' );

						$key   = trim( $key );
						$value = trim( $value );

						if ( $count = count( $result->params ) ) {
							if ( is_array( $result->params[ $count - 1 ] ) )
								$result->params[ $count - 1 ][ $key ] = $value;
							else
								$result->params[] = [ $key => $value ];
						} else {
							$result->params[] = [ $key => $value ];
						}
					} else {
						$result->params[] = $arg;
					}
				}
			} else {
				$result->params[] = $part;
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'http_parse_headers' ) ) {
	/**
	 * @param string $header Raw HTTP header
	 * @return array Sssociative array of HTTP headers
	 */
    function http_parse_headers( $header ) {
        $headers = [];
        $prev_key = '';

        foreach( explode( "\n", $header ) as $line ) {
			@list( $key, $value ) = explode( ':', $line, 2 );

			$key   = trim( $key );
			$value = trim( $value );

			if ( $value !== '' ) {
				$key = strtolower( $key );

				if ( ! isset( $headers[ $key ] ) ) {
					$headers[ $key ] = $value;
				} elseif ( is_array( $headers[ $key ] ) ) {
					$headers[ $key ][] = $value;
				} else {
					$headers[ $key ] = [ $headers[ $key ], $value ];
				}

				$prev_key = $key;
			} elseif ( $prev_key && "\t" == substr( $key, 0, 1 ) ) {
				$value = "\r\n\t" . trim( $key );

				if ( is_array( $headers[ $key ] ) )
					$headers[ $key ][ count( $headers[ $key ] ) - 1 ] .= $value;
				else
					$headers[ $key ] .= $value;
			} else {
				$headers[] = $key;
			}
		}

		return $headers;
	}
}

if ( ! function_exists( 'http_response_code' ) ) {
	/**
	 * Gets or sets the HTTP response status code.
	 *
	 * @param integer $code OPTIONAL The optional **code** will set the response code.
	 * @return integer If response_code is provided, then the previous status code will be returned. If response_code is not provided, then the current status code will be returned.
	 */
    function http_response_code( $code = null ) {
		static $http_response_code = 200;

        if (!is_null($code)) {
            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
            }

            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

            header("$protocol $code $text");

            $http_response_code = $code;
        }

        return $http_response_code;
    }
}

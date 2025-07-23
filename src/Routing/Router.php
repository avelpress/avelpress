<?php

namespace AvelPress\Routing;

use AvelPress\AvelPress;
use AvelPress\Http\Json\JsonResource;
use AvelPress\Http\Json\ResourceCollection;

defined( 'ABSPATH' ) || exit;

class Router {

	protected $routes = [];
	protected $prefixStack = [];
	protected $guardStack = [];

	public function get( $uri, $action = null ) {
		return $this->addRoute( 'GET', $uri, $action );
	}

	public function post( $uri, $action = null ) {
		return $this->addRoute( 'POST', $uri, $action );
	}

	public function put( $uri, $action = null ) {
		return $this->addRoute( 'PUT', $uri, $action );
	}

	public function delete( $uri, $action = null ) {
		return $this->addRoute( 'DELETE', $uri, $action );
	}

	public function addRoute( $httpMethod, $uri, $action ) {

		$uri = $this->parseUriParameters( $uri );

		register_rest_route(
			trim( $this->applyPrefix(), '/' ),
			$uri,
			[ 
				'methods' => $httpMethod,
				'callback' => function (\WP_REST_Request $request) use ($action) {
					try {
						return $this->processRequest( $action, $request );
					} catch (\Exception $e) {
						return new \WP_Error( 'server_error', $e->getMessage(), [ 'status' => 500 ] );
					}
				},
				'permission_callback' => function () {
					foreach ( $this->guardStack as $guard ) {
						if ( is_callable( $guard ) ) {
							if ( ! call_user_func( $guard ) ) {
								return false;
							}
						} elseif ( is_string( $guard ) ) {
							if ( ! current_user_can( $guard ) ) {
								return false;
							}
						} elseif ( is_array( $guard ) ) {
							foreach ( $guard as $g ) {
								if ( is_string( $g ) && ! current_user_can( $g ) ) {
									return false;
								}
							}
						}
					}
					return true;
				}
			]
		);

		return $this->routes[] = [ 
			'method' => $httpMethod,
			'uri' => $uri,
			'action' => $action,
			'guards' => $this->guardStack,
		];
	}

	private function processRequest( $action, $request ) {
		[ $controllerClass, $method ] = $action;

		$reflector = new \ReflectionClass( $controllerClass );

		$constructor = $reflector->getConstructor();

		if ( $constructor ) {
			$dependencies = $this->resolveDependencies( $constructor );
			$instance = $reflector->newInstanceArgs( $dependencies );
		} else {
			$instance = new $controllerClass();
		}

		$called_method = $reflector->getMethod( $method );
		$method_dependencies = $this->resolveDependencies( $called_method, $request );

		if ( is_wp_error( $method_dependencies ) ) {
			return $method_dependencies;
		}

		$response = call_user_func_array( [ $instance, $method ], $method_dependencies );

		if ( $response instanceof ResourceCollection ) {
			return rest_ensure_response( $response->toArray() );
		}

		if ( $response instanceof JsonResource ) {
			return rest_ensure_response( $response->toArray() );
		}

		return rest_ensure_response( $response );
	}

	protected function parseUriParameters( $uri ) {
		preg_match_all( '/\{([a-zA-Z0-9_]+)\}/', $uri, $matches );

		foreach ( $matches[1] as $param ) {
			$uri = str_replace( '{' . $param . '}', '(?P<' . $param . '>[^/]+)', $uri );
		}

		return $uri;
	}

	/**
	 * Resolve dependencies for the given method.
	 *
	 * @param \ReflectionMethod $method
	 * @param \WP_REST_Request|null $request
	 * 
	 * @return array|\WP_Error
	 */
	protected function resolveDependencies( \ReflectionMethod $method, $request = null ) {
		$resolved = [];

		foreach ( $method->getParameters() as $param ) {
			$type = $param->getType();
			if ( $type && ! $type->isBuiltin() ) {
				$className = $type->getName();
				if ( is_subclass_of( $className, \AvelPress\Http\FormRequest::class) && $request ) {
					$form_request = new $className( $request );
					$form_request->validate();

					if ( $form_request->fails() ) {
						$errors = $form_request->errors();
						return new \WP_Error( 'validation_error', 'Validation failed', $errors );
					}

					$resolved[] = $form_request;
				} elseif ( $className === '\\WP_REST_Request' || $className === 'WP_REST_Request' ) {
					if ( $request ) {
						$resolved[] = $request;
					} else {
						throw new \Exception( "WP_REST_Request requested but no request available for parameter: {$param->getName()}" );
					}
				} else {
					$resolved[] = AvelPress::app()->make( $className );
				}
			} elseif ( $param->isDefaultValueAvailable() ) {
				$resolved[] = $param->getDefaultValue();
			} else {
				throw new \Exception( "Cannot resolve dependency: {$param->getName()}" );
			}
		}

		return $resolved;
	}


	public function prefix( $prefix ) {
		$this->prefixStack[] = trim( $prefix, '/' );
		return $this;
	}

	public function group( callable $callback ) {
		$prefixStackSizeBefore = count( $this->prefixStack );
		$guardStackSizeBefore = count( $this->guardStack );

		$callback( $this );

		$this->prefixStack = array_slice( $this->prefixStack, 0, $prefixStackSizeBefore - 1 );
		$this->guardStack = array_slice( $this->guardStack, 0, $guardStackSizeBefore - 1 );

		return $this;
	}

	public function guards( $guards ) {
		$this->guardStack[] = $guards;
		return $this;
	}

	protected function applyPrefix() {
		if ( ! empty( $this->prefixStack ) ) {
			$fullPrefix = implode( '/', $this->prefixStack );
			return '/' . $fullPrefix;
		}

		return '/';
	}

	public function getRoutes() {
		return $this->routes;
	}
}
<?php
/**
 * Simple dependency injection container.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Container;

/**
 * Service container with singleton support.
 */
class Container {

	/**
	 * @var array<string, callable|object>
	 */
	private $bindings = array();

	/**
	 * @var array<string, object>
	 */
	private $instances = array();

	/**
	 * Bind a service factory or instance.
	 *
	 * @param string               $id       Service identifier (class name).
	 * @param callable|object|null $concrete Factory or instance.
	 */
	public function bind( string $id, $concrete = null ): void {
		$this->bindings[ $id ] = $concrete ?? $id;
	}

	/**
	 * Bind as singleton.
	 *
	 * @param string               $id       Service identifier.
	 * @param callable|object|null $concrete Factory or instance.
	 */
	public function singleton( string $id, $concrete = null ): void {
		$this->bind(
			$id,
			function ( Container $container ) use ( $id, $concrete ) {
				if ( isset( $this->instances[ $id ] ) ) {
					return $this->instances[ $id ];
				}
				$resolved = $container->resolve( $concrete ?? $id );
				$this->instances[ $id ] = $resolved;
				return $resolved;
			}
		);
	}

	/**
	 * Resolve a service.
	 *
	 * @param string $id Service identifier.
	 * @return mixed
	 * @throws \RuntimeException When service cannot be resolved.
	 */
	public function get( string $id ) {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->bindings[ $id ] ) ) {
			return $this->resolve( $id );
		}

		$binding = $this->bindings[ $id ];

		if ( is_callable( $binding ) ) {
			return $binding( $this );
		}

		if ( is_object( $binding ) ) {
			return $binding;
		}

		return $this->resolve( $binding );
	}

	/**
	 * @param string $class Class name.
	 * @return object
	 */
	private function resolve( string $class ): object {
		if ( ! class_exists( $class ) ) {
			throw new \RuntimeException( sprintf( 'Cannot resolve service: %s', esc_html( $class ) ) );
		}

		$reflector = new \ReflectionClass( $class );
		$constructor = $reflector->getConstructor();

		if ( null === $constructor ) {
			return new $class();
		}

		$dependencies = array();
		foreach ( $constructor->getParameters() as $parameter ) {
			$type = $parameter->getType();
			if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
				$dependencies[] = $this->get( $type->getName() );
			} elseif ( $parameter->isDefaultValueAvailable() ) {
				$dependencies[] = $parameter->getDefaultValue();
			} else {
				throw new \RuntimeException(
					sprintf( 'Unresolvable dependency %s for %s', $parameter->getName(), $class )
				);
			}
		}

		return $reflector->newInstanceArgs( $dependencies );
	}
}

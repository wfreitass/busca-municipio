<?php declare(strict_types = 1);

// osfsl-/home/workspace/ipmedia/backend/vendor/composer/../osteel/openapi-httpfoundation-testing/src/ValidatorBuilder.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Osteel\OpenApi\Testing\ValidatorBuilder
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-d53b1776858238ee7499452e54f7fe8391632c2987f583e38362fa51ee7e4f2a-8.3-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'filename' => '/home/workspace/ipmedia/backend/vendor/composer/../osteel/openapi-httpfoundation-testing/src/ValidatorBuilder.php',
      ),
    ),
    'namespace' => 'Osteel\\OpenApi\\Testing',
    'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
    'shortName' => 'ValidatorBuilder',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * This class creates Validator objects based on OpenAPI definitions.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 18,
    'endLine' => 222,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'adapter' => 
      array (
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'name' => 'adapter',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\\Osteel\\OpenApi\\Testing\\Adapters\\HttpFoundationAdapter::class',
          'attributes' => 
          array (
            'startLine' => 21,
            'endLine' => 21,
            'startTokenPos' => 78,
            'startFilePos' => 650,
            'endTokenPos' => 80,
            'endFilePos' => 677,
          ),
        ),
        'docComment' => '/** @var class-string<MessageAdapterInterface> */',
        'attributes' => 
        array (
        ),
        'startLine' => 21,
        'endLine' => 21,
        'startColumn' => 5,
        'endColumn' => 59,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'cacheAdapter' => 
      array (
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'name' => 'cacheAdapter',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\\Osteel\\OpenApi\\Testing\\Cache\\Psr16Adapter::class',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 24,
            'startTokenPos' => 93,
            'startFilePos' => 768,
            'endTokenPos' => 95,
            'endFilePos' => 786,
          ),
        ),
        'docComment' => '/** @var class-string<CacheAdapterInterface> */',
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 24,
        'startColumn' => 5,
        'endColumn' => 55,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'validatorBuilder' => 
      array (
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'name' => 'validatorBuilder',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'League\\OpenAPIValidation\\PSR7\\ValidatorBuilder',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 26,
        'endLine' => 26,
        'startColumn' => 33,
        'endColumn' => 78,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'validatorBuilder' => 
          array (
            'name' => 'validatorBuilder',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'League\\OpenAPIValidation\\PSR7\\ValidatorBuilder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 26,
            'endLine' => 26,
            'startColumn' => 33,
            'endColumn' => 78,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 26,
        'endLine' => 28,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromYaml' => 
      array (
        'name' => 'fromYaml',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 35,
            'endLine' => 35,
            'startColumn' => 37,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition
 */',
        'startLine' => 35,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromJson' => 
      array (
        'name' => 'fromJson',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 37,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition
 */',
        'startLine' => 49,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'isUrl' => 
      array (
        'name' => 'isUrl',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 35,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 58,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromYamlFile' => 
      array (
        'name' => 'fromYamlFile',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 74,
            'endLine' => 74,
            'startColumn' => 41,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition\'s file
 */',
        'startLine' => 74,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromJsonFile' => 
      array (
        'name' => 'fromJsonFile',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 84,
            'endLine' => 84,
            'startColumn' => 41,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition\'s file
 */',
        'startLine' => 84,
        'endLine' => 87,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromYamlString' => 
      array (
        'name' => 'fromYamlString',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 94,
            'endLine' => 94,
            'startColumn' => 43,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition as YAML text
 */',
        'startLine' => 94,
        'endLine' => 97,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromJsonString' => 
      array (
        'name' => 'fromJsonString',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 104,
            'endLine' => 104,
            'startColumn' => 43,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition as JSON text
 */',
        'startLine' => 104,
        'endLine' => 107,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromYamlUrl' => 
      array (
        'name' => 'fromYamlUrl',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 117,
            'endLine' => 117,
            'startColumn' => 40,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition\'s URL
 *
 * @throws InvalidArgumentException if the URL is invalid
 * @throws RuntimeException         if the content of the URL cannot be read
 */',
        'startLine' => 117,
        'endLine' => 120,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromJsonUrl' => 
      array (
        'name' => 'fromJsonUrl',
        'parameters' => 
        array (
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 130,
            'endLine' => 130,
            'startColumn' => 40,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @inheritDoc
 *
 * @param string $definition the OpenAPI definition\'s URL
 *
 * @throws InvalidArgumentException if the URL is invalid
 * @throws RuntimeException         if the content of the URL cannot be read
 */',
        'startLine' => 130,
        'endLine' => 133,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'getUrlContent' => 
      array (
        'name' => 'getUrlContent',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 139,
            'endLine' => 139,
            'startColumn' => 43,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @throws InvalidArgumentException if the URL is invalid
 * @throws RuntimeException         if the content of the URL cannot be read
 */',
        'startLine' => 139,
        'endLine' => 148,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'fromMethod' => 
      array (
        'name' => 'fromMethod',
        'parameters' => 
        array (
          'method' => 
          array (
            'name' => 'method',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 40,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'definition' => 
          array (
            'name' => 'definition',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 56,
            'endColumn' => 73,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a Validator object based on an OpenAPI definition.
 *
 * @param string $method     the ValidatorBuilder object\'s method to use
 * @param string $definition the OpenAPI definition
 */',
        'startLine' => 156,
        'endLine' => 161,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'setCache' => 
      array (
        'name' => 'setCache',
        'parameters' => 
        array (
          'cache' => 
          array (
            'name' => 'cache',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'object',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 164,
            'endLine' => 164,
            'startColumn' => 30,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilderInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 164,
        'endLine' => 171,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'getValidator' => 
      array (
        'name' => 'getValidator',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorInterface',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 174,
        'endLine' => 181,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'setMessageAdapter' => 
      array (
        'name' => 'setMessageAdapter',
        'parameters' => 
        array (
          'class' => 
          array (
            'name' => 'class',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 190,
            'endLine' => 190,
            'startColumn' => 39,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Change the adapter to use. The provided class must implement \\Osteel\\OpenApi\\Testing\\Adapters\\AdapterInterface.
 *
 * @param string $class the adapter\'s class
 *
 * @throws InvalidArgumentException
 */',
        'startLine' => 190,
        'endLine' => 201,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
      'setCacheAdapter' => 
      array (
        'name' => 'setCacheAdapter',
        'parameters' => 
        array (
          'class' => 
          array (
            'name' => 'class',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 210,
            'endLine' => 210,
            'startColumn' => 37,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Change the cache adapter to use. The provided class must implement \\Osteel\\OpenApi\\Testing\\Cache\\AdapterInterface.
 *
 * @param string $class the cache adapter\'s class
 *
 * @throws InvalidArgumentException
 */',
        'startLine' => 210,
        'endLine' => 221,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Osteel\\OpenApi\\Testing',
        'declaringClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'implementingClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'currentClassName' => 'Osteel\\OpenApi\\Testing\\ValidatorBuilder',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));
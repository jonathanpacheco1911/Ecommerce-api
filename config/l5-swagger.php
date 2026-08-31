<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API de E-commerce Segura',
            ],

            'routes' => [
                /*
                 * Ruta para acceder a la interfaz de documentación
                 */
                'api' => 'api/documentation',
            ],

            'paths' => [
                /*
                 * Ruta absoluta donde se almacenará el archivo .json/.yaml generado
                 */
                'docs' => storage_path('api-docs'),

                /*
                 * Nombre del archivo de documentación generado
                 */
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Define si se debe usar JSON o YAML para mostrar la documentación
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Rutas absolutas donde se buscarán las anotaciones @OA\... (solo dentro de la app)
                 */
                'annotations' => [
                    base_path('app'),
                ],

                'excludes' => [],

                /*
                 * Ruta base del proyecto usada por el generador (null = raíz del proyecto)
                 */
                'base' => env('L5_SWAGGER_BASE_PATH', null),
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => [],
        ],

        'paths' => [
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs.yaml',
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
            'annotations' => base_path('app'),

            /*
             * Ruta base del proyecto usada por el generador (null = raíz del proyecto)
             */
            'base' => env('L5_SWAGGER_BASE_PATH', null),
        ],

        'scanOptions' => [
            'default_processors_configuration' => [],
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'pattern' => null,
            'exclude' => [],
            //'open_api_spec_version' => \L5Swagger\GeneratorFactory::OPEN_API_DEFAULT_SPEC_VERSION,
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', '3.0.0'),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'Token',
                    'description' => 'Autenticación mediante Bearer Token de Laravel Sanctum',
                ],
            ],
            'security' => [],
        ],

        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,

        'ui' => [
            'display' => [
                'dark_mode' => false,
                'doc_expansion' => 'none',
                'filter' => true,
            ],
            'authorization' => [
                'persist_authorization' => true,
            ],
        ],

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
        ],
    ],
];
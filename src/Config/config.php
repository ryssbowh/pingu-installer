<?php

return [
	'minPhpVersion' => '7.0.0',
	'requirements' => [
		'php' => [
            'openssl',
            'pdo',
            'mbstring',
            'tokenizer',
            'JSON',
            'cURL',
        ],
        'apache' => [
            'mod_rewrite',
        ],
        'commands' => [
        	'npm -v' => '4'
        ]
	],
    'permissions' => [
	    'storage/framework/'     => '775',
	    'storage/logs/'          => '775',
	    'bootstrap/cache/'       => '775'
	],
	'env' => [
		'APP_NAME' => [
			'name' => 'Site name',
			'type' => 'open'
		],
		'APP_ENV' => [
			'name' => 'Environment',
			'type' => 'choice',
			'values' => ['local', 'production']
		],
		'APP_DEBUG' => [
			'name' => 'Debug mode',
			'type' => 'choice',
			'values' => ['true', 'false']
		],
		'APP_URL' => [
			'name' => 'Site url',
			'type' => 'open',
			'filter' => FILTER_VALIDATE_URL
		]
	]
];
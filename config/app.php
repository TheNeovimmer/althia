<?php
return [
    'name' => 'Medicase',
    'url' => 'https://oumaima.ddev.site',
    'env' => 'development',
    'debug' => true,
    'openrouter_api_key' => getenv('OPENROUTER_API_KEY'),
    'openrouter_model' => 'openai/gpt-oss-120b:free',
];

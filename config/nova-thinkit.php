<?php

return [
    'feature-images'   => [
        'default' => [
            'disk'                 => 'feature-images',
            'immutable_extensions' => [ '.svg', '.gif' ],
            'original'             => [
                'methods' => [
                    'fit'      => [ \Spatie\Image\Manipulations::FIT_CROP, 2800, 1800 ],
                    'optimize' => [],
                ],
                'srcset'  => '2800w',
            ],
            'deletedFormats'       => [],
            'formats'              => [
                'thumb' => [
                    'methods' => [
                        'fit'      => [ \Spatie\Image\Manipulations::FIT_CONTAIN, 450, 300 ],
                        'optimize' => [],
                    ],
                    'srcset'  => '450w',
                ],
            ],
        ],
    ],
];

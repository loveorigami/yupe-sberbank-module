<?php

return [
    'module' => [
        'class' => 'application.modules.sberbank.SberbankModule',
    ],
    'component' => [
        'paymentManager' => [
            'paymentSystems' => [
                'sberbank' => [
                    'class' => 'application.modules.sberbank.components.payments.SberbankPaymentSystem',
                ]
            ],
        ],
    ],
];

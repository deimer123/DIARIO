<?php

return [
    'temporary_file_upload' => [
        'disk' => 'public', // Asegura que se use el disco correcto
        'rules' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // Reglas de validación
    ],
];

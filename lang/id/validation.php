<?php

return [
    'required' => ':Attribute wajib diisi.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
        'array' => ':Attribute minimal :min item.',
        'file' => ':Attribute minimal :min kilobyte.',
    ],
    'max' => [
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
        'array' => ':Attribute maksimal :max item.',
        'file' => ':Attribute maksimal :max kilobyte.',
    ],
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'between' => [
        'numeric' => ':Attribute harus di antara :min dan :max.',
        'string' => ':Attribute harus di antara :min dan :max karakter.',
        'array' => ':Attribute harus di antara :min dan :max item.',
        'file' => ':Attribute harus di antara :min dan :max kilobyte.',
    ],
    'file' => ':Attribute harus berupa berkas.',
    'mimes' => ':Attribute harus berformat: :values.',
    'attributes' => [
        'name' => 'nama',
        'nis' => 'NIS',
        'classroom_id' => 'rombel',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
    ],
];

<?php

return [
    'accepted' => ':attribute wajib disetujui.',
    'accepted_if' => ':attribute wajib disetujui saat :other bernilai :value.',
    'active_url' => ':attribute harus berupa URL yang valid.',
    'after' => ':attribute harus setelah tanggal :date.',
    'after_or_equal' => ':attribute harus setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':attribute tidak valid.',
    'array' => ':attribute harus berupa daftar data.',
    'ascii' => ':attribute hanya boleh menggunakan karakter standar.',
    'before' => ':attribute harus sebelum tanggal :date.',
    'before_or_equal' => ':attribute harus sebelum atau sama dengan :date.',

    'between' => [
        'array' => ':attribute harus berisi antara :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min hingga :max KB.',
        'numeric' => ':attribute harus bernilai antara :min hingga :max.',
        'string' => ':attribute harus terdiri dari :min sampai :max karakter.',
    ],

    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'can' => ':attribute mengandung nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => ':attribute belum lengkap.',
    'current_password' => 'Password yang dimasukkan salah.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'date_equals' => ':attribute harus sama dengan tanggal :date.',
    'date_format' => 'Format :attribute harus :format.',
    'decimal' => ':attribute harus memiliki :decimal angka di belakang koma.',
    'declined' => ':attribute harus ditolak.',
    'different' => ':attribute tidak boleh sama dengan :other.',
    'digits' => ':attribute harus terdiri dari :digits digit.',
    'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => 'Dimensi gambar pada :attribute tidak sesuai.',
    'distinct' => ':attribute memiliki data yang duplikat.',

    'email' => 'Masukkan alamat email yang valid.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'extensions' => ':attribute harus memiliki format: :values.',
    'file' => ':attribute harus berupa file.',
    'filled' => ':attribute wajib diisi.',
    'hex_color' => ':attribute harus berupa kode warna HEX yang valid.',
    'image' => ':attribute harus berupa gambar.',

    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa angka bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa format JSON yang valid.',

    'max' => [
        'array' => ':attribute tidak boleh lebih dari :max item.',
        'file' => 'Ukuran :attribute maksimal :max KB.',
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],

    'min' => [
        'array' => ':attribute minimal memiliki :min item.',
        'file' => 'Ukuran :attribute minimal :min KB.',
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],

    'numeric' => ':attribute harus berupa angka.',

    'password' => [
        'letters' => ':attribute harus mengandung minimal satu huruf.',
        'mixed' => ':attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':attribute harus mengandung minimal satu angka.',
        'symbols' => ':attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':attribute terlalu lemah atau pernah bocor. Gunakan password lain.',
    ],

    'present' => ':attribute wajib tersedia.',
    'prohibited' => ':attribute tidak diperbolehkan.',
    'regex' => 'Format :attribute tidak sesuai.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi saat :other bernilai :value.',
    'required_with' => ':attribute wajib diisi saat :values tersedia.',
    'same' => ':attribute harus sama dengan :other.',

    'size' => [
        'array' => ':attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size KB.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus terdiri dari :size karakter.',
    ],

    'starts_with' => ':attribute harus diawali dengan: :values.',
    'string' => ':attribute harus berupa teks.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'uppercase' => ':attribute harus menggunakan huruf kapital.',
    'url' => ':attribute harus berupa URL yang valid.',
    'uuid' => ':attribute harus berupa UUID yang valid.',
];

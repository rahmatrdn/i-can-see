<?php

namespace App\Constants;

class ResponseConst
{
    const HTTP_SUCCESS = 200;

    const HTTP_CREATED = 201;

    const HTTP_BAD_REQUEST = 400;

    const HTTP_UNAUTHORIZED = 401;

    const HTTP_FORBIDDEN = 403;

    const HTTP_NOT_FOUND = 404;

    const HTTP_INTERNAL_ERROR = 500;

    const SUCCESS_MESSAGE_UPDATED = 'Data berhasil diperbarui';

    const SUCCESS_MESSAGE_DELETED = 'Data berhasil dihapus';

    const SUCCESS_MESSAGE_CREATED = 'Data berhasil disimpan';

    const ERROR_MESSAGE_NOT_FOUND = 'Data tidak ditemukan';

    const ERROR_MESSAGE_SERVICE = 'Terjadi kesalahan pada server';

    const ERROR_MESSAGE_VALIDATION = 'Data tidak valid';

    const DEFAULT_ERROR_MESSAGE = 'Maaf Terjadi kesalahan, silakan coba lagi atau hubungi tim pengembang!';
}

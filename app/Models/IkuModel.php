<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// base model IKU: satu tempat untuk menentukan koneksi database (dari config/iku.php),
abstract class IkuModel extends Model
{
    public function getConnectionName()
    {
        return config('iku.connection');
    }
}
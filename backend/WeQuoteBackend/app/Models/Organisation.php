<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Organisation extends Model
{
    protected $table = 'organisation';

    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['logo']) && $array['logo'] === 'undefined') {
            $array['logo'] = null;
        }
        if (isset($array['logo_url']) && $array['logo_url'] === 'undefined') {
            $array['logo_url'] = null;
        }
        return $array;
    }

    public function customers(): HasMany {
        return $this->hasMany(Customer::class);
    }

    public function quotes(): HasMany {
        return $this->hasMany(Quote::class);
    }

    public function projects(): HasMany {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany {
        return $this->hasMany(Invoice::class);
    }
}

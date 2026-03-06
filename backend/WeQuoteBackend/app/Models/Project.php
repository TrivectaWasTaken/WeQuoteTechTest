<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $table = 'project';

    public function customer():BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function organisation():BelongsTo {
        return $this->belongsTo(Organisation::class);
    }

    public function invoices():HasMany {
        return $this->hasMany(Invoice::class);
    }

    public function quotes():HasMany {
        return $this->hasMany(Quote::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
}

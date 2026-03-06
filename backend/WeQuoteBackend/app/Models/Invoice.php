<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoice';

    public function quote():BelongsTo {
        return $this->belongsTo(Quote::class);
    }

    public function organisation():BelongsTo {
        return $this->belongsTo(Organisation::class);
    }

    public function project():BelongsTo {
        return $this->belongsTo(Project::class);
    }
}

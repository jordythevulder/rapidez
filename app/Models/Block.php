<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Scopes\ForCurrentStoreScope;
use App\Models\Scopes\IsActiveScope;
use App\Models\Traits\HasContentAttributeWithVariables;

class Block extends Model
{
    use HasContentAttributeWithVariables;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_block';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'block_id';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IsActiveScope);
        static::addGlobalScope(new ForCurrentStoreScope);
    }
}

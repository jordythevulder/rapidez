<?php

namespace App\Models;

use App\Scopes\ForCurrentStoreScope;
use App\Scopes\IsActiveScope;
use App\Traits\HasContentAttributeWithVariables;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasContentAttributeWithVariables;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_page';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

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
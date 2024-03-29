<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model {

    protected $table = 'srm_owner.category';
    protected $primaryKey = 'category_id';

    public function systems() : HasMany {
        return $this->hasMany(System::class, 'category_id', 'category_id');
    }

    public function plantId() : string {
        return "HCO_CATEGORY_ID-{$this->category_id}";
    }
}

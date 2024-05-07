<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends Model {

    protected $table = 'srm_owner.application';

    protected $primaryKey = 'application_id';

    public function systems(): BelongsToMany {
        return $this->belongsToMany(System::class, 'srm_owner.system_application', 'application_id', 'system_id');
    }

}

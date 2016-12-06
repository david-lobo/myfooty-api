<?php

namespace App\Models;

use App\Models\Team;
use App\Models\Competition;
use App\Models\Broadcaster;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\Model as Model;

class BaseModel extends Model
{
    protected static $rules;
    private $errors;

    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, static::$rules);

        // check for failure
        if ($v->fails())
        {
            // set errors and return false
            $this->errors = $v->errors();
            return false;
        }

        // validation pass
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }
}

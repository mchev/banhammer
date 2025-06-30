<?php

namespace Mchev\Banhammer\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Mchev\Banhammer\Traits\Bannable;

class TestBannableModel extends Model
{
    use Bannable;

    protected $table = 'test_bannable_models';

    protected $guarded = [];
}

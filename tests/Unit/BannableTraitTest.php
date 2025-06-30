<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mchev\Banhammer\Models\Ban;
use Mchev\Banhammer\Tests\TestCase;
use Mchev\Banhammer\Tests\Unit\Models\TestBannableModel;
use Mchev\Banhammer\Traits\Bannable;

class BannableTraitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set the ban model config
        config(['ban.model' => Ban::class]);

        // Create the test bannable model table
        Schema::create('test_bannable_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    protected function getBannableModel(array $attributes = []): Model
    {
        return new class($attributes) extends Model
        {
            use Bannable;

            protected $table = 'bannable_models';

            protected $guarded = [];
        };
    }

    public function test_bans_relationship(): void
    {
        $model = TestBannableModel::create();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $model->bans);
    }

    public function test_is_banned_and_is_not_banned(): void
    {
        $model = TestBannableModel::create();
        $this->assertFalse($model->isBanned());
        $this->assertTrue($model->isNotBanned());
        $model->ban();
        $model->refresh();
        $this->assertTrue($model->isBanned());
        $this->assertFalse($model->isNotBanned());
    }

    public function test_ban_and_ban_until(): void
    {
        $model = TestBannableModel::create();
        $ban = $model->ban(['comment' => 'test']);
        $this->assertInstanceOf(Ban::class, $ban);
        $this->assertSame('test', $ban->comment);
        $date = now()->addDays(2)->toDateTimeString();
        $ban2 = $model->banUntil($date);
        $this->assertSame($date, $ban2->expired_at->toDateTimeString());
    }

    public function test_unban_deletes_all_bans(): void
    {
        $model = TestBannableModel::create();
        $model->ban();
        $model->ban();
        $model->refresh();
        $this->assertTrue($model->isBanned());
        $model->unban();
        $model->refresh();
        $this->assertFalse($model->isBanned());
    }

    public function test_scope_banned_and_not_banned(): void
    {
        $model1 = TestBannableModel::create();
        $model2 = TestBannableModel::create();
        $model2->ban();
        $banned = $model1->newQuery()->banned()->get();
        $notBanned = $model1->newQuery()->notBanned()->get();
        $this->assertTrue($banned->contains($model2));
        $this->assertTrue($notBanned->contains($model1));
    }

    public function test_scope_where_bans_meta(): void
    {
        $model = TestBannableModel::create();
        $model->ban(['metas' => ['reason' => 'spam']]);
        $result = $model->newQuery()->whereBansMeta('reason', 'spam')->get();
        $this->assertTrue($result->contains($model));
    }

    public function test_scope_banned_by_type(): void
    {
        $model = TestBannableModel::create();
        $model->ban(['created_by_type' => 'App\\Models\\User']);
        $result = $model->newQuery()->bannedByType('App\\Models\\User')->get();
        $this->assertTrue($result->contains($model));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_bannable_models');
        parent::tearDown();
    }
}

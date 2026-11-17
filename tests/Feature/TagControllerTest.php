<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグ編集画面にアクセスできる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->get(route('admin.tags.edit', $tag->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
        $response->assertViewHas('tag');
    }

    /** @test */
    public function 存在しないタグの編集画面にアクセスしようとすると404エラーが返る(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->get(route('admin.tags.edit', 999));

        $response->assertStatus(404);
    }

    /** @test */
    public function 新しいタグを作成できる(): void
    {
        $user = User::factory()->create();
        $data = ['name' => 'テストタグ'];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 既存のタグの名前を更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => '更新後のタグ'];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新後のタグ',
        ]);
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 存在しないタグを更新しようとすると404エラーが返る(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => '更新後のタグ'];

        $response = $this->actingAs($user)->put(route('admin.tags.update', 999), $data);

        $response->assertStatus(404);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 既存のタグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->delete(route('admin.tags.destroy', $tag->id));

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 存在しないタグを削除しようとすると404エラーが返る(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->delete(route('admin.tags.destroy', 999));

        $response->assertStatus(404);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 未認証ユーザーはタグ編集画面にアクセスするとログインページにリダイレクトされる(): void
    {
        $tag = Tag::create(['name' => 'テストタグ']);
        $response = $this->get(route('admin.tags.edit', $tag->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタグを作成しようとするとログインページにリダイレクトされる(): void
    {
        $data = ['name' => 'テストタグ'];

        $response = $this->post(route('admin.tags.store'), $data);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタグを更新しようとするとログインページにリダイレクトされる(): void
    {
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => '更新後のタグ'];

        $response = $this->put(route('admin.tags.update', $tag->id), $data);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタグを削除しようとするとログインページにリダイレクトされる(): void
    {
        $tag = Tag::create(['name' => 'テストタグ']);

        $response = $this->delete(route('admin.tags.destroy', $tag->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function タグ名が空欄だとバリデーションエラーが起きる（store）(): void
    {
        $user = User::factory()->create();
        $data = ['name' => ''];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function タグ名が空欄だとバリデーションエラーが起きる（update）(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => ''];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
        $this->assertDatabaseMissing('tags', [
            'name' => '',
        ]);
    }

    /** @test */
    public function タグ名が文字列ではないとバリデーションエラーが起きる（store）(): void
    {
        $user = User::factory()->create();
        $data = ['name' => ['invalid']];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function タグ名が文字列ではないとバリデーションエラーが起きる（update）(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => ['invalid']];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function タグ名は50文字まで入力できる（store）(): void
    {
        $user = User::factory()->create();
        $data = ['name' => str_repeat('あ', 50)];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'name' => str_repeat('あ', 50),
        ]);
    }

    /** @test */
    public function タグ名は50文字まで入力できる（update）(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => str_repeat('あ', 50)];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'name' => str_repeat('あ', 50),
        ]);
        $this->assertDatabaseMissing('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function タグ名が51文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $user = User::factory()->create();
        $data = ['name' => str_repeat('あ', 51)];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function タグ名が51文字以上だとバリデーションエラーが起きる（update）(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => str_repeat('あ', 51)];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
        $this->assertDatabaseMissing('tags', [
            'name' => str_repeat('あ', 51),
        ]);
    }

    /** @test */
    public function すでに登録されているタグ名を登録しようとするとバリデーションエラーが起きる（store）(): void
    {
        $user = User::factory()->create();
        Tag::create(['name' => 'テストタグ']);
        $data = ['name' => 'テストタグ'];

        $response = $this->actingAs($user)->post(route('admin.tags.store'), $data);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('tags', 1);
    }

    /** @test */
    public function もともとのタグ名と同じ名前で更新することはできる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => 'テストタグ'];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function すでに登録されている別のタグ名と同じ名前に更新しようとするとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        Tag::create(['name' => 'すでに登録されているタグ']);
        $tag = Tag::create(['name' => 'テストタグ']);
        $data = ['name' => 'すでに登録されているタグ'];

        $response = $this->actingAs($user)->put(route('admin.tags.update', $tag->id), $data);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('tags', [
            'name' => 'すでに登録されているタグ',
        ]);
    }
}

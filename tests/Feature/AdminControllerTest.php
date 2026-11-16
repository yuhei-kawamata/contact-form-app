<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証されたユーザーが管理ダッシュボードにアクセスできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.contacts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    /** @test */
    public function 未認証ユーザーが管理ダッシュボードにアクセスしようとすると、ログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 管理ダッシュボードで7件ごとにページネーションされて表示される(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);
        Contact::factory()->count(8)->create([
            'category_id' => $category->id,
        ]);

        $responsePage1 = $this->actingAs($user)->get('/admin');
        $responsePage2 = $this->actingAs($user)->get('/admin?page=2');

        $responsePage1->assertStatus(200);
        $responsePage1->assertViewHas('contacts', function ($paginator) {
            return $paginator->count() === 7 && $paginator->total() === 8;
        });

        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('contacts', function ($paginator) {
            return $paginator->count() === 1;
        });
    }

    /** @test */
    public function 管理ダッシュボードでkeywordでフィルタができる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'first_name' => 'test',
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'first_name' => 'テスト',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->first_name === 'テスト';
        });
    }

    /** @test */
    public function 管理ダッシュボードでgenderでフィルタができる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'gender' => '1',
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'gender' => '2',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?gender=1');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->gender === 1;
        });
    }

    /** @test */
    public function 管理ダッシュボードでcategor_idでフィルタができる(): void
    {
        $user = User::factory()->create();
        $category1 = Category::create(['content' => 'テスト1']);
        $category2 = Category::create(['content' => 'テスト2']);

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);
        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?category_id=1');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->category_id === 1;
        });
    }

    /** @test */
    public function 管理ダッシュボードでdateでフィルタができる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-02 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/admin?date=2026-01-01');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->created_at->format('Y-m-d') === '2026-01-01';
        });
    }

    /** @test */
    public function 管理ダッシュボードで複数の項目でページネーションできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(8)->create([
            'gender' => 1,
            'category_id' => $category->id,
        ]);
        Contact::factory()->count(2)->create([
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?gender=1&page=2');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 8
                && $paginator->count() === 1;
        });
    }

    /** @test */
    public function 文字列でないkeywordでフィルタするとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = [
            'keyword' => ['invalid'],
        ];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('keyword');
    }

    /** @test */
    public function 文字数が255文字以下であればkeywordでフィルタできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'first_name' => str_repeat('あ', 255),
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'first_name' => 'テスト',
            'category_id' => $category->id,
        ]);

        $params = [
            'keyword' => str_repeat('あ', 255),
        ];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->first_name === str_repeat('あ', 255);
        });
    }

    public function 文字数が256文字以上だとkeywordでフィルタした際にバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['keyword' => str_repeat('あ', 256)];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('keyword');
    }

    /** @test */
    public function genderが整数でないとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが0であればフィルタ検索ができる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'gender' => '1',
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'gender' => '2',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?gender=0');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 2;
        });
    }

    /** @test */
    public function genderが3であればフィルタ検索ができる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->create([
            'gender' => '1',
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'gender' => '3',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin?gender=3');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($paginator) {
            return $paginator->total() === 1
                && $paginator->first()->gender === 3;
        });
    }

    /** @test */
    public function genderに0未満の整数を指定して検索するとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => -1];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('gender');
    }

    public function genderに4以上の整数を指定して検索するとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => 4];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function category_idが整数でないとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['category_id' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function categoriesテーブルに存在しないidで検索するとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        Category::create(['content' => 'テスト1']);
        $params = ['category_id' => 999];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function 日付形式ではないdateで検索するとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['date' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.index', $params));

        $response->assertSessionHasErrors('date');
    }

    /** @test */
    public function 指定したお問い合わせの詳細ページをカテゴリ情報付きで表示できる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テスト']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.contacts.show', $contact->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee('テスト');
    }

    /** @test */
    public function 存在しないお問い合わせの詳細ページを開こうとすると404エラーとなる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テスト']);
        Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.contacts.show', 999));

        $response->assertStatus(404);
    }

    /** @test */
    public function 指定したお問い合わせを削除できる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テスト']);
        $contact = Contact::factory()->create([
            'first_name' => 'テスト',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.contacts.destroy', $contact->id));

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせを削除しようとすると404エラーが出る(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テスト']);
        Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.contacts.destroy', 999));

        $response->assertStatus(404);
    }
}

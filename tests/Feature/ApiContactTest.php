<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 問い合わせ一覧を取得できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);

        Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ])->each(function ($contact) use ($tag) {
            $contact->tags()->attach($tag->id);
        });

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment([
            'id' => $category->id,
            'content' => 'テストカテゴリ',
        ]);
        $response->assertJsonFragment([
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function お問い合わせが0件の場合は空の配列を返す(): void
    {
        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $response->assertJson(['data' => []]);
    }

    /** @test */
    public function keywordで検索ができる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=test');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => 'test',
            'last_name' => 'test',
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function keywordが文字列でないとバリデーションエラーが起きる(): void
    {
        $params = [
            'keyword' => ['invalid'],
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keyword']);
    }

    /** @test */
    public function keywordが255文字以下であれば検索ができる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'test',
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category->id,
        ]);
        $params = str_repeat('あ', 255);

        $response = $this->getJson('/api/v1/contacts?keyword='.$params);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'test',
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function keywordが256文字以上だとバリデーションエラーが起きる(): void
    {
        $params = [
            'keyword' => str_repeat('あ', 256),
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keyword']);
    }

    /** @test */
    public function genderで検索ができる（genderが1）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'gender' => 1,
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => 'test',
            'last_name' => 'test',
            'gender' => 1,
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function genderで検索ができる（genderが3）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'gender' => 3,
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=3');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => 'test',
            'last_name' => 'test',
            'gender' => 3,
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function genderが整数でないとバリデーションエラーが起きる(): void
    {
        $params = [
            'gender' => ['invalid'],
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function genderが0だとバリデーションエラーが起きる(): void
    {
        $params = [
            'gender' => 0,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function genderが4だとバリデーションエラーが起きる(): void
    {
        $params = [
            'gender' => 4,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function category_idで検索ができる(): void
    {
        $category1 = Category::create(['content' => 'テストカテゴリ1']);
        $category2 = Category::create(['content' => 'テストカテゴリ2']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'category_id' => $category1->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category2->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?category_id='.$category1->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => 'test',
            'last_name' => 'test',
        ]);
        $response->assertJsonFragment([
            'id' => $category1->id,
            'content' => 'テストカテゴリ1',
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function category_idが整数でないとバリデーションエラーが起きる(): void
    {
        $params = [
            'category_id' => ['invalid'],
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function 存在しないcategory_idを指定するとバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $params = [
            'category_id' => 999,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function dateで検索ができる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'category_id' => $category->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category->id,
            'created_at' => '2026-01-20 10:00:00',
        ]);

        $response = $this->getJson('/api/v1/contacts?date=2026-01-01');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $contact1->id,
            'first_name' => 'test',
            'last_name' => 'test',
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $response->assertJsonMissing([
            'first_name' => 'テスト',
        ]);
    }

    /** @test */
    public function dateが日付形式でないとバリデーションエラーが起きる(): void
    {
        $params = [
            'date' => ['invalid'],
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function per_pageでフィルタをかけられる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(10)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=3');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('meta.per_page', 3);
        $response->assertJsonPath('meta.total', 10);
    }

    /** @test */
    public function per_pageが整数でないとバリデーションエラーが起きる(): void
    {
        $params = [
            'per_page' => ['invalid'],
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function per_pageに1を指定してフィルタをかけられる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(10)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('meta.per_page', 1);
        $response->assertJsonPath('meta.total', 10);
    }

    /** @test */
    public function per_pageに100を指定してフィルタをかけられる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(101)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=100');

        $response->assertStatus(200);
        $response->assertJsonCount(100, 'data');
        $response->assertJsonPath('meta.per_page', 100);
        $response->assertJsonPath('meta.total', 101);
    }

    /** @test */
    public function per_pageに0を指定するとバリデーションエラーが起きる(): void
    {
        $params = [
            'per_page' => 0,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function per_pageに101を指定するとバリデーションエラーが起きる(): void
    {
        $params = [
            'per_page' => 101,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function per_pageを指定しない場合デフォルトで20となる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(10)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.total', 10);
    }

    /** @test */
    public function page指定で2ページ目のデータが取得できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contacts = Contact::factory()->count(10)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=3&page=2');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 3);
        $response->assertJsonPath('meta.total', 10);

        $response->assertJsonMissing([
            'email' => $contacts[0]->email,
        ]);
    }

    /** @test */
    public function pageに0を指定するとバリデーションエラーが起きる(): void
    {
        $params = [
            'page' => 0,
        ];

        $response = $this->getJson('/api/v1/contacts?'.http_build_query($params));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    /** @test */
    public function 指定したidのお問い合わせ詳細を取得できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'email' => $contact->email,
        ]);
        $response->assertJsonFragment([
            'id' => $category->id,
            'content' => 'テストカテゴリ',
        ]);
    }

    /** @test */
    public function 存在しないidのお問い合わせ詳細を取得しようとすると404エラーが返る(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function 新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'detail' => 'テスト',
        ]);
        $response->assertJsonFragment([
            'id' => $category->id,
            'content' => 'テストカテゴリ',
        ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function 入力必須項目を空欄にしてお問い合わせを作成するとバリデーションエラーが起きる(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => '',
            'email' => '',
            'tel' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function first_nameが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'first_name' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function first_nameは255文字以下であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'first_name' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'テスト',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function first_nameが256文字以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'first_name' => str_repeat('あ', 256),
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function last_nameが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'last_name' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function last_nameは255文字以下であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => str_repeat('あ', 255),
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'last_name' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => str_repeat('あ', 255),
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function last_nameが256文字以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'last_name' => str_repeat('あ', 256),
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function genderが整数でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'gender' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function genderが0以下の整数だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'gender' => 0,
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function genderが4以上の整数だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'gender' => 4,
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function emailが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'email' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function emailがメールアドレス形式でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'email' => 'invalid-email',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function emailは255文字以下であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => str_repeat('a', 243).'@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'email' => str_repeat('a', 243).'@example.com',
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'email' => str_repeat('a', 243).'@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function emailが256文字以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'email' => str_repeat('a', 244).'@example.com',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function telが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tel' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function telは11文字であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '01234567890',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'tel' => '01234567890',
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'tel' => '01234567890',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function telが9桁以下だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tel' => '012345678',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function telが12桁以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tel' => '012345678901',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function telが10桁でも間にハイフンが入っているとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tel' => '0123-456-7890',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function addressが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'address' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function addressは255文字以下であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => str_repeat('あ', 255),
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'address' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'address' => str_repeat('あ', 255),
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function addressが256文字以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'address' => str_repeat('あ', 256),
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function category_idが整数でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'category_id' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function 存在しないcategory_idを指定すると、お問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'category_id' => 999,
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function detailが文字列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'detail' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['detail']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function detailは120文字以下であれば新しいお問い合わせを作成できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'detail' => str_repeat('あ', 120),
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ]);
    }

    /** @test */
    public function detailが121文字以上だとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'detail' => str_repeat('あ', 121),
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['detail']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function tag_idsが配列でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tag_ids' => 1,
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids']);
        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('contact_tag', 0);
    }

    /** @test */
    public function tag_idsの要素が整数でないとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        $data = [
            'tag_ids' => ['invalid'],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids.0']);
        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('contact_tag', 0);
    }

    /** @test */
    public function tag_idsに存在しないidを指定するとお問い合わせ作成時にバリデーションエラーが起きる(): void
    {
        Tag::create(['name' => 'テストタグ']);

        $data = [
            'tag_ids' => [999],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids.0']);
        $this->assertDatabaseCount('contacts', 0);
    }

    /** @test */
    public function 既存のお問い合わせの内容を更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'first_name' => 'テスト更新前',
            'last_name' => 'テスト更新前',
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'detail' => 'テスト',
        ]);
        $response->assertJsonFragment([
            'id' => $category->id,
            'content' => 'テストカテゴリ',
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseMissing('contacts', [
            'first_name' => 'テスト更新前',
            'last_name' => 'テスト更新前',
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせの内容を更新しようとすると、404エラーが返る(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'first_name' => 'テスト更新前',
            'last_name' => 'テスト更新前',
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/999', $data);

        $response->assertStatus(404);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト更新前',
            'last_name' => 'テスト更新前',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function 入力必須項目を空欄にしてお問い合わせを更新するとバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => '',
            'last_name' => '',
            'gender' => '',
            'email' => '',
            'tel' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function first_nameが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
        $this->assertDatabaseHas('contacts', [
            'first_name' => $contact->first_name,
        ]);
    }

    /** @test */
    public function first_nameは255文字以下であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'first_name' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => str_repeat('あ', 255),
            'last_name' => 'テスト',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function first_nameが256文字以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => str_repeat('あ', 256),
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function last_nameが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'last_name' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function last_nameは255文字以下であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => str_repeat('あ', 255),
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'last_name' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => str_repeat('あ', 255),
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function last_nameが256文字以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'last_name' => str_repeat('あ', 256),
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function genderが整数でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'gender' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function genderが0以下の整数だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'gender' => 0,
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function genderが4以上の整数だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'gender' => 4,
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function emailが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'email' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function emailがメールアドレス形式でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'email' => 'invalid-email',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function emailは255文字以下であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => str_repeat('a', 243).'@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => str_repeat('a', 243).'@example.com',
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'email' => str_repeat('a', 243).'@example.com',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function emailが256文字以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'email' => str_repeat('a', 244).'@example.com',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function telが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'tel' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function telは11文字であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '01234567890',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'tel' => '01234567890',
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'tel' => '01234567890',
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function telが9桁以下だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'tel' => '012345678',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function telが12桁以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'tel' => '012345678901',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function telが10桁でも間にハイフンが入っているとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'tel' => '0123-456-7890',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tel']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function addressが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'address' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function addressは255文字以下であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => str_repeat('あ', 255),
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => 'テスト',
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'address' => str_repeat('あ', 255),
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'address' => str_repeat('あ', 255),
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function addressが256文字以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'address' => str_repeat('あ', 256),
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function category_idが整数でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'category_id' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function 存在しないcategory_idを指定すると、お問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'category_id' => 999,
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function detailが文字列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'detail' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['detail']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function detailは120文字以下であればお問い合わせを更新できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '0123456789',
            'address' => 'テスト',
            'building' => 'テスト',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'detail' => str_repeat('あ', 120),
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ]);
    }

    /** @test */
    public function detailが121文字以上だとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'detail' => str_repeat('あ', 121),
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['detail']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function tag_idsが配列でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'tag_ids' => 1,
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function tag_idsの要素が整数でないとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $data = [
            'tag_ids' => ['invalid'],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids.0']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseCount('contact_tag', 1);
    }

    /** @test */
    public function tag_idsに存在しないidを指定するとお問い合わせ更新時にバリデーションエラーが起きる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $data = [
            'tag_ids' => [999],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids.0']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseCount('contact_tag', 1);
    }

    /** @test */
    public function お問い合わせを削除できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->deleteJson('/api/v1/contacts/'.$contact->id);

        $response->assertStatus(204);
        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('contact_tag', 0);
    }

    /** @test */
    public function 存在しないお問い合わせを削除しようとすると404エラーとなる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->deleteJson('/api/v1/contacts/999');

        $response->assertStatus(404);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseCount('contact_tag', 1);

    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせフォームにアクセスできる(): void
    {
        Category::create(['content' => 'テストカテゴリ']);
        Tag::create(['name' => 'テストタグ']);

        $response = $this->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        $response = $this->get(route('contacts.thanks'));

        $response->assertStatus(200);
    }

    /** @test */
    public function 入力したお問い合わせ内容が確認ページに表示される(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
        ])->toArray();

        $contact['tag_ids'] = [$tag->id];

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
        $response->assertSee($contact['first_name']);
        $response->assertSee($contact['last_name']);
        $response->assertSee($contact['gender']);
        $response->assertSee($contact['email']);
        $response->assertSee($contact['tel']);
        $response->assertSee($contact['address']);
        $response->assertSee($contact['building']);
        $response->assertSee($contact['detail']);
    }

    /** @test */
    public function first_nameが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'first_name' => '',
        ]));

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function first_nameが文字列ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'first_name' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function first_nameは255文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'first_name' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('あ', 255));
    }

    /** @test */
    public function first_nameが256文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'first_name' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function last_nameが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'last_name' => '',
        ]));

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function last_nameが文字列ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'last_name' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function last_nameは255文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'last_name' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('あ', 255));
    }

    /** @test */
    public function last_nameが256文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'last_name' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function genderが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'gender' => '',
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが整数ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'gender' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが1～3の値だとバリデーションをクリアする（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'gender' => 3,
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee($contact['gender']);
    }

    /** @test */
    public function genderが4以上の値だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'gender' => 4,
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが0以下の値だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'gender' => 0,
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function emailが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'email' => '',
        ]));

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function emailが不正なメールアドレス形式だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'email' => 'invalid-email',
        ]));

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function emailは255文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'email' => str_repeat('a', 243).'@example.com',
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('a', 243).'@example.com');
    }

    /** @test */
    public function emailが256文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'email' => str_repeat('a', 244).'@example.com',
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function telが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => '',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが不正な形式だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => ['invalid-tel'],
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが9桁以下だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => '012345678',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが12桁以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => '012345678901',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが10桁ならバリデーションをクリアする（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'tel' => '0123456789',
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('0123456789');
    }

    /** @test */
    public function telが11桁ならバリデーションをクリアする（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'tel' => '01234567890',
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('01234567890');
    }

    /** @test */
    public function telが10桁だが「ー」が入っているとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => '0123-456-890',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが11桁だが「ー」が入っているとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tel' => '0123-456-7890',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function addressが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'address' => '',
        ]));

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function addressが文字列ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'address' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function addressは255文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'address' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('あ', 255));
    }

    /** @test */
    public function addressが256文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'address' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function buildingは255文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'building' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('あ', 255));
    }

    /** @test */
    public function buildingが256文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'building' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('building');
    }

    /** @test */
    public function category_idが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'category_id' => '',
        ]));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_idが整数でないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'category_id' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function 存在しないcategory_idだとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm'), [
            'category_id' => 999,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function detailが空だとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'detail' => '',
        ]));

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function detailが文字列ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'detail' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function detailは120文字まで入力できる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee(str_repeat('あ', 120));
    }

    /** @test */
    public function detailが121文字以上だとバリデーションエラーが起きる（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function tag_idsが空でもバリデーションをクリアする（confirm）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
        ])->toArray();

        $contact['tag_ids'] = null;

        $response = $this->post(route('contacts.confirm'), $contact);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
    }

    /** @test */
    public function tag_idsが配列ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tag_ids' => 'a',
        ]));

        $response->assertSessionHasErrors('tag_ids');
    }

    /** @test */
    public function tag_idsの中身が整数ではないとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tag_ids' => ['tag-id'],
        ]));

        $response->assertSessionHasErrors('tag_ids.0');
    }

    /** @test */
    public function 存在しないtag_idだとバリデーションエラーが起きる（confirm）(): void
    {
        $response = $this->post(route('contacts.confirm', [
            'tag_ids' => [999],
        ]));

        $response->assertSessionHasErrors('tag_ids.0');
    }

    /** @test */
    public function 入力したお問い合わせ内容がデータベースに登録され、thanksページにリダイレクトされる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
        ])->toArray();

        $contact['tag_ids'] = [$tag->id];

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'category_id' => $category->id,
        ]);

        $savedContact = Contact::latest('id')->first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $savedContact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function first_nameが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'first_name' => '',
        ]));

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function first_nameが文字列ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'first_name' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function first_nameは255文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'first_name' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'first_name' => str_repeat('あ', 255),
        ]);
    }

    /** @test */
    public function first_nameが256文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'first_name' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function last_nameが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'last_name' => '',
        ]));

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function last_nameが文字列ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'last_name' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function last_nameは255文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'last_name' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'last_name' => str_repeat('あ', 255),
        ]);
    }

    /** @test */
    public function last_nameが256文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'last_name' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function genderが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'gender' => '',
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが整数ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'gender' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが1～3の値だとバリデーションをクリアする（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'gender' => 3,
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'gender' => $contact['gender'],
        ]);
    }

    /** @test */
    public function genderが4以上の値だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'gender' => 4,
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが0以下の値だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'gender' => 0,
        ]));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function emailが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'email' => '',
        ]));

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function emailが不正なメールアドレス形式だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'email' => 'invalid-email',
        ]));

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function emailは255文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'email' => str_repeat('a', 243).'@example.com',
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'email' => str_repeat('a', 243).'@example.com',
        ]);
    }

    /** @test */
    public function emailが256文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'email' => str_repeat('a', 244).'@example.com',
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function telが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => '',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが不正な形式だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => ['invalid-tel'],
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが9桁以下だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => '012345678',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが12桁以上だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => '012345678901',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが10桁ならバリデーションをクリアする（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'tel' => '0123456789',
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'tel' => '0123456789',
        ]);
    }

    /** @test */
    public function telが11桁ならバリデーションをクリアする（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'tel' => '01234567890',
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'tel' => '01234567890',
        ]);
    }

    /** @test */
    public function telが10桁だが「ー」が入っているとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => '0123-456-890',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function telが11桁だが「ー」が入っているとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tel' => '0123-456-7890',
        ]));

        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function addressが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'address' => '',
        ]));

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function addressが文字列ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'address' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function addressは255文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'address' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'address' => str_repeat('あ', 255),
        ]);
    }

    /** @test */
    public function addressが256文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'address' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function buildingは255文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'building' => str_repeat('あ', 255),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'building' => str_repeat('あ', 255),
        ]);
    }

    /** @test */
    public function buildingが256文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'building' => str_repeat('あ', 256),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('building');
    }

    /** @test */
    public function category_idが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'category_id' => '',
        ]));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_idが整数でないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'category_id' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function 存在しないcategory_idだとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store'), [
            'category_id' => 999,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function detailが空だとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'detail' => '',
        ]));

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function detailが文字列ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'detail' => ['invalid'],
        ]));

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function detailは120文字まで入力できる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'detail' => str_repeat('あ', 120),
        ]);
    }

    /** @test */
    public function detailが121文字以上だとバリデーションエラーが起きる（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function tag_idsが空でもバリデーションをクリアする（store）(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->make([
            'category_id' => $category->id,
        ])->toArray();

        $contact['tag_ids'] = null;

        $response = $this->post(route('contacts.store'), $contact);

        $response->assertRedirect(route('contacts.thanks'));
    }

    /** @test */
    public function tag_idsが配列ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tag_ids' => 'a',
        ]));

        $response->assertSessionHasErrors('tag_ids');
    }

    /** @test */
    public function tag_idsの中身が整数ではないとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tag_ids' => ['tag-id'],
        ]));

        $response->assertSessionHasErrors('tag_ids.0');
    }

    /** @test */
    public function 存在しないtag_idだとバリデーションエラーが起きる（store）(): void
    {
        $response = $this->post(route('contacts.store', [
            'tag_ids' => [999],
        ]));

        $response->assertSessionHasErrors('tag_ids.0');
    }

    /** @test */
    public function １つのカテゴリから複数のお問い合わせが取得できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $otherCategory = Category::create(['content' => '別のカテゴリ']);
        Contact::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        $contacts = $category->contacts;

        $this->assertCount(3, $contacts);
        $this->assertInstanceOf(Contact::class, $contacts->first());
        $this->assertEquals($category->id, $contacts->first()->category_id);
    }

    /** @test */
    public function １つのお問い合わせが特定のカテゴリに属し、複数のタグと同期できる(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag1 = Tag::create(['name' => 'テストタグ1']);
        $tag2 = Tag::create(['name' => 'テストタグ2']);
        $tag3 = Tag::create(['name' => 'テストタグ3']);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->sync([$tag1->id, $tag2->id, $tag3->id]);
        $contact->tags()->sync([$tag1->id, $tag2->id]);

        $contact->load('tags');

        $this->assertEquals($category->id, $contact->category_id);
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
        $this->assertFalse($contact->tags->contains($tag3));

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);
    }

    /** @test */
    public function 中間テーブルを介して1つのタグが複数のお問い合わせに紐づいている(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => 'テストタグ']);

        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $otherContact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag->contacts()->sync($contacts->pluck('id'));

        $this->assertCount(3, $tag->contacts);
        $this->assertFalse($tag->contacts->contains($otherContact));

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    /** @test */
    public function 未認証ユーザーが_cs_vファイルをダウンロードしようとすると、ログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('admin.contacts.export'));

        $response->assertRedirect(route('login'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function 認証済みユーザーは_cs_vデータをダウンロードできる(): void
    {
        $fixedNow = Carbon::create(2026, 11, 17, 15, 30, 0);
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => 'テスト',
            'building' => 'テスト',
            'detail' => 'テスト',
            'created_at' => $fixedNow,
        ]);

        $response = $this->actingAs($user)->get(route('admin.contacts.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertDownload('20261117.csv');

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $this->assertStringContainsString("\r\n", $content);

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow = [
            (string) $contact->id,
            'テストテスト',
            $contact->gender_label,
            'taro@example.com',
            '09012345678',
            'テスト',
            'テスト',
            'テストカテゴリ',
            'テスト',
            '2026-11-17 15:30:00',
        ];
        $this->assertSame($expectedRow, str_getcsv($lines[1]));
    }

    /** @test */
    public function 検索項目を指定しない場合、お問い合わせが新着順で含まれた_cs_vファイルが出力される(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-20 10:00:00',
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('admin.contacts.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(3, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            $contact1->first_name.$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

        $expectedRow2 = [
            (string) $contact2->id,
            $contact2->first_name.$contact2->last_name,
            $contact2->gender_label,
            $contact2->email,
            $contact2->tel,
            $contact2->address,
            $contact2->building,
            $contact2->category->content,
            $contact2->detail,
            $contact2->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow2, str_getcsv($lines[2]));
    }

    /** @test */
    public function keywordで_cs_vファイルに含まれる内容をフィルタできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => 'テスト',
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'first_name' => 'test',
            'category_id' => $category->id,
        ]);

        $params = ['keyword' => 'テスト'];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            'テスト'.$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

    }

    /** @test */
    public function genderで_cs_vファイルに含まれる内容をフィルタできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'gender' => 3,
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'gender' => 1,
            'category_id' => $category->id,
        ]);

        $params = ['gender' => 3];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            $contact1->first_name.$contact1->last_name,
            'その他',
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

    }

    /** @test */
    public function category_idで_cs_vファイルに含まれる内容をフィルタできる(): void
    {
        $user = User::factory()->create();
        $category1 = Category::create(['content' => 'テストカテゴリ']);
        $category2 = Category::create(['content' => 'testcategory']);

        $contact1 = Contact::factory()->create([
            'category_id' => $category1->id,
        ]);
        $contact2 = Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $params = ['category_id' => $category1->id];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            $contact1->first_name.$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            'テストカテゴリ',
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

    }

    /** @test */
    public function dateで_cs_vファイルに含まれる内容をフィルタできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-01-20 10:00:00',
        ]);

        $params = ['date' => '2026-01-01'];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            $contact1->first_name.$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            '2026-01-01 10:00:00',
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

    }

    /** @test */
    public function keywordを文字列以外で指定して_cs_vファイルを出力しようとするとバリデーションエラーが出る(): void
    {
        $user = User::factory()->create();
        $params = ['keyword' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('keyword');
    }

    /** @test */
    public function keywordが255文字以下であれば_cs_vファイルを出力できる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'first_name' => str_repeat('あ', 255),
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $params = ['keyword' => str_repeat('あ', 255)];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(2, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            str_repeat('あ', 255).$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));
    }

    /** @test */
    public function keywordが256文字だと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['keyword' => str_repeat('あ', 256)];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('keyword');
    }

    /** @test */
    public function genderが整数でないと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが0だと性別問わずすべてのお問い合わせが_cs_vファイルに含まれる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::factory()->create([
            'gender' => 1,
            'category_id' => $category->id,
            'created_at' => '2026-01-03 10:00:00',
        ]);
        $contact2 = Contact::factory()->create([
            'gender' => 2,
            'category_id' => $category->id,
            'created_at' => '2026-01-02 10:00:00',
        ]);
        $contact3 = Contact::factory()->create([
            'gender' => 3,
            'category_id' => $category->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);

        $params = ['gender' => 0];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(4, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));

        $expectedRow1 = [
            (string) $contact1->id,
            $contact1->first_name.$contact1->last_name,
            $contact1->gender_label,
            $contact1->email,
            $contact1->tel,
            $contact1->address,
            $contact1->building,
            $contact1->category->content,
            $contact1->detail,
            $contact1->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow1, str_getcsv($lines[1]));

        $expectedRow2 = [
            (string) $contact2->id,
            $contact2->first_name.$contact2->last_name,
            $contact2->gender_label,
            $contact2->email,
            $contact2->tel,
            $contact2->address,
            $contact2->building,
            $contact2->category->content,
            $contact2->detail,
            $contact2->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow2, str_getcsv($lines[2]));

        $expectedRow3 = [
            (string) $contact3->id,
            $contact3->first_name.$contact3->last_name,
            $contact3->gender_label,
            $contact3->email,
            $contact3->tel,
            $contact3->address,
            $contact3->building,
            $contact3->category->content,
            $contact3->detail,
            $contact3->created_at->format('Y-m-d H:i:s'),
        ];
        $this->assertSame($expectedRow3, str_getcsv($lines[3]));
    }

    /** @test */
    public function genderが0未満の整数だと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => -1];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function genderが4以上の整数だと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['gender' => 4];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function category_idが整数でないと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['category_id' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function categoriesテーブルに存在しないcategory_idを指定して_cs_vファイルを出力しようとするとバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        Category::create(['content' => 'テストカテゴリ']);

        $params = ['category_id' => 999];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function dateが日付形式でないと_cs_vファイルを出力しようとしたときにバリデーションエラーが起きる(): void
    {
        $user = User::factory()->create();
        $params = ['date' => ['invalid']];

        $response = $this->actingAs($user)->get(route('admin.contacts.export', $params));

        $response->assertSessionHasErrors('date');
    }

    /** @test */
    public function お問い合わせが0件の場合、ヘッダー行のみの_cs_vファイルが出力される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.contacts.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $contentWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\r\n", trim($contentWithoutBom));

        $this->assertCount(1, $lines);

        $expectedHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];
        $this->assertSame($expectedHeader, str_getcsv($lines[0]));
    }
}

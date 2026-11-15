<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
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
            'gender' => random_int(1, 3),
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
            'email' => ['invalid'],
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
    public function 存在しないcategory_idだとバリデーションをエラーが起きる（confirm）(): void
    {
        $contact = Contact::factory()->make([
            'category_id' => 999,
        ])->toArray();

        $response = $this->post(route('contacts.confirm'), $contact);

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
            'gender' => random_int(1, 3),
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
            'email' => ['invalid'],
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
    public function 存在しないcategory_idだとバリデーションをエラーが起きる（store）(): void
    {
        $contact = Contact::factory()->make([
            'category_id' => 999,
        ])->toArray();

        $response = $this->post(route('contacts.store'), $contact);

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
}

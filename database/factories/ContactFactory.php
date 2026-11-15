<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => fn () => fake()->randomElement(Category::pluck('id')->toArray()),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail(),
            'tel' => $this->faker->regexify('0[789]0[0-9]{8}'),
            'address' => fake()->prefecture().fake()->city().fake()->streetAddress(),
            'building' => fake()->secondaryAddress(),
            'detail' => $this->faker->randomElement([
                '商品の発送状況について確認したいのですが、現在の状況はどうなっていますか。',
                '以前購入した商品に不具合が見つかったため、交換または返品の手続きを教えてください。',
                'パスワードの再設定メールが届かないため、再送してください。',
                '店舗の営業時間を教えてください。',
                '誤って注文してしまった商品があるのでキャンセルの手続きについて教えてください。',
            ]),
        ];
    }
}

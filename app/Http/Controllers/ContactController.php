<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        if (session()->has('contact_input')) {
            session()->flash('_old_input', session('contact_input'));
        }

        return view('contact.index', compact('categories', 'tags'));
    }

    public function confirm(StoreContactRequest $storeContactRequest)
    {
        $validated = $storeContactRequest->validated();

        $validated['tel1'] = $storeContactRequest->input('tel1');
        $validated['tel2'] = $storeContactRequest->input('tel2');
        $validated['tel3'] = $storeContactRequest->input('tel3');

        session(['contact_input' => $validated]);

        $category = Category::find($validated['category_id']);

        $tags = ! empty($validated['tag_ids'])
            ? Tag::find($validated['tag_ids'])
            : collect();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    public function store(StoreContactRequest $storeContactRequest)
    {

        $validated = $storeContactRequest->validated();
        $contact = Contact::create($validated);

        if (! empty($validated['tag_ids'])) {
            $contact->tags()->sync($validated['tag_ids']);
        }

        session()->forget('contact_input');

        return redirect()->route('contacts.thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $indexContactRequest)
    {
        $indexContactRequest->validated();

        $keyword = $indexContactRequest->input('keyword');
        $gender = $indexContactRequest->input('gender');
        $categoryId = $indexContactRequest->input('category_id');
        $date = $indexContactRequest->input('date');

        $contacts = Contact::with('category', 'tags')
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                        ->orwhere('last_name', 'like', "%{$keyword}%")
                        ->orwhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($gender, function ($query, $gender) {
                return $query->where('gender', $gender);
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->paginate(7);

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index');
    }
}

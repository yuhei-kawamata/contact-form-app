<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index(IndexContactRequest $indexContactRequest)
    {
        $validated = $indexContactRequest->validated();

        $keyword = $validated['keyword'] ?? null;
        $gender = $validated['gender'] ?? null;
        $categoryId = $validated['category_id'] ?? null;
        $date = $validated['date'] ?? null;
        $perPage = $validated['per_page'] ?? 20;

        $contacts = Contact::with(['category', 'tags'])
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
            ->latest()
            ->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function store(StoreContactRequest $storeContactRequest)
    {
        $validated = $storeContactRequest->validated();
        $contact = Contact::create($validated);
        $tagIds = $validated['tag_ids'] ?? [];

        if (! empty($tagIds)) {
            $contact->tags()->attach($tagIds);
        }

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function update(UpdateContactRequest $updateContactRequest, Contact $contact)
    {
        $validated = $updateContactRequest->validated();
        $contact->update($validated);

        $tagIds = $validated['tag_ids'];

        if (! empty($tagIds)) {
            $contact->tags()->sync($tagIds);
        }

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}

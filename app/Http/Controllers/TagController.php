<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function store(StoreTagRequest $storeTagRequest)
    {
        $validated = $storeTagRequest->validated();
        Tag::create($validated);

        return redirect()->route('admin.contacts.index');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $updateTagRequest, Tag $tag)
    {
        $validated = $updateTagRequest->validated();
        $tag->update($validated);

        return redirect()->route('admin.contacts.index');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.contacts.index');
    }
}

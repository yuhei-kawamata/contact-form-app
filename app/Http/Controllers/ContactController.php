<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

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

        $tags = !empty($validated['tag_ids'])
            ? Tag::find($validated['tag_ids'])
            : collect();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    public function store(StoreContactRequest $storeContactRequest)
    {

        $validated = $storeContactRequest->validated();
        $contact = Contact::create($validated);
        $tagIds = $validated['tag_ids'] ?? [];

        if (!empty($tagIds)) {
            $contact->tags()->attach($tagIds);
        }

        session()->forget('contact_input');

        return redirect()->route('contacts.thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $exportContactRequest)
    {
        $exportContactRequest->validated();

        $keyword = $exportContactRequest->input('keyword');
        $gender = $exportContactRequest->input('gender');
        $categoryId = $exportContactRequest->input('category_id');
        $date = $exportContactRequest->input('date');

        $query = Contact::with(['category'])
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
            ->latest();

        $now = Carbon::now();
        $filename = "検索結果一覧（" . $now->format('Y年m月d日') . "）.csv";

        return Response::streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");

            $csvHeader = [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ];

            $this->fputcsvCrlf($stream, $csvHeader);

            $query->chunk(1000, function ($contacts) use ($stream) {
                foreach ($contacts as $contact) {
                    $row = [
                        $contact->id,
                        $contact->first_name . $contact->last_name,
                        $contact->gender_label,
                        $contact->email,
                        $contact->tel,
                        $contact->address,
                        $contact->building,
                        $contact->category->content,
                        $contact->detail,
                        $contact->created_at->format('Y-m-d H:i:s'),
                    ];
                    $this->fputcsvCrlf($stream, $row);
                }
            });

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function fputcsvCrlf($stream, array $fields): void
    {
        $tempStream = fopen('php://temp', 'r+b');
        fputcsv($tempStream, $fields);
        rewind($tempStream);
        $line = str_replace(PHP_EOL, "\r\n", stream_get_contents($tempStream));
        fclose($tempStream);

        fwrite($stream, $line);
    }
}

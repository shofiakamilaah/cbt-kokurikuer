<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WednesdayCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->filled('keyword')) {
            $request->validate([
                'keyword' => ['string', 'min:3'],
            ]);
        }

        $studentMatches = collect();
        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword')->toString();
            $studentMatches = User::with('classroom')
                ->where('role', 'student')
                ->where('nis', 'like', '%'.$keyword.'%')
                ->orderBy('name')
                ->limit(5)
                ->get();
        }

        return view('duty.collections', [
            'studentMatches' => $studentMatches,
            'keyword' => $request->input('keyword', ''),
            'collections' => $this->todayCollections(),
        ]);
    }

    /**
     * @return Collection<int, array{contributor: User, cooking_oil_amount: float, has_eggshell: bool}>
     */
    private function todayCollections(): Collection
    {
        return WednesdayCollection::with('contributor.classroom')
            ->whereDate('collection_date', today())
            ->latest()
            ->get()
            ->groupBy('contributor_id')
            ->map(function (Collection $entries): array {
                $cookingOilEntries = $entries->where('waste_type', 'cooking_oil');

                return [
                    'contributor' => $entries->first()->contributor,
                    'cooking_oil_amount' => $cookingOilEntries->sum(
                        fn (WednesdayCollection $entry): float => (float) $entry->amount,
                    ),
                    'has_eggshell' => $entries->where('waste_type', 'eggshell')->isNotEmpty(),
                ];
            });
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contributor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query->where('role', 'student')),
            ],
            'cooking_oil_amount' => ['nullable', 'required_without:eggshell_submitted', 'numeric', 'between:0.01,999'],
            'eggshell_submitted' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $collectionAttributes = [
                'contributor_id' => $validated['contributor_id'],
                'user_id' => $request->user()->id,
                'collection_date' => today()->toDateString(),
            ];

            if (isset($validated['cooking_oil_amount'])) {
                WednesdayCollection::create([
                    ...$collectionAttributes,
                    'waste_type' => 'cooking_oil',
                    'amount' => $validated['cooking_oil_amount'],
                ]);
            }

            if ($validated['eggshell_submitted'] ?? false) {
                WednesdayCollection::create([
                    ...$collectionAttributes,
                    'waste_type' => 'eggshell',
                    'amount' => null,
                ]);
            }
        });

        return redirect()->route('duty.collections.index')->with('success', 'Data pengumpulan berhasil disimpan.');
    }
}

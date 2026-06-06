@forelse($products as $product)
    <div class="col-12" data-pid="{{ $product->id }}">
        <div class="wow-card-sm-wrap">
            <div class="result-view-map">
                @include('partials.product_card_search_v4', ['product' => $product])
            </div>
            <div class="result-view-list">
                @include('partials.product_card_v4', ['product' => $product, 'preferredLocation' => null])
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card p-6 text-ink-600">No results matched your filters. Try widening your search.</div>
    </div>
@endforelse

@extends('layouts.app')
@section('content')
<main>
        <div class="grid wide">
            <h2>Kết quả tìm kiếm cho: "{{ $keyword }}"</h2>

            @if($products->count() > 0)
                <div class="row">
                    @foreach ($products as $product)
                        <div class="col l-3 m-6 c-6">
                            <div class="product-card">
                                <a href="" class="product-img-link">
                                    <div class="product-img" style="
                                                    background-image: url('{{ asset('images/products/'.$product->image) }}');
                                                "></div>
                                </a>
                                <a href="product_detail.html">
                                    <h3 class="product-name">{{ $product->name }}</h3>
                                </a>
                                <p class="product-price">
                                    <span class="product-price__new">{{ number_format($product->price) }} đ</span>
                                    <span class="product-price__old">40.000₫</span>
                                </p>
                                <div class="product-footer">
                                    <div>
                                        <span><a href="products.html"><i
                                                    class="fa-solid fa-location-dot"></i>{{ $product->shop->name }}</a></span>

                                    </div>
                                    <button data-product-id="{{ $product->id }}" class="cart-btn"><i
                                            class="fa-solid fa-cart-shopping"></i></button>
                                    <!-- <button class="cart-btn"><i class="fa-solid fa-cart-shopping"></i></button> -->
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $products->appends(['keyword' => $keyword])->links() }}
                </div>
            @else
                <p>Không tìm thấy sản phẩm nào phù hợp.</p>
            @endif
        </div>
    </main>
@endsection

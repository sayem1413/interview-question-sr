@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control w-100">
                        @foreach ($variants as $variant)
                            <option value=""></option>
                            <optgroup label="{{ $variant->title }}">
                                @foreach ($variant->productVariants as $item)
                                    <option value="{{ $item->id }}">{{ $item->variant }}</option>
                                    <option value="{{ $item->id }}">{{ $item->variant }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>

                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->title }} <br> Created at : {{ $product->day . " " . $product->month . " " . $product->year }}</td>
                                <td>{{ $product->description }}</td>
                                <td>
                                    @foreach ($product->productVariantPrices as $productVariantPrice)
                                        <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
            
                                            <dt class="col-sm-3 pb-0">
                                                {{ $productVariantPrice->variantText }}
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4 pb-0">Price : {{ number_format($productVariantPrice->price,2) }}</dt>
                                                    <dd class="col-sm-8 pb-0">InStock : {{ number_format($productVariantPrice->stock,2) }}</dd>
                                                </dl>
                                            </dd>
                                        </dl> 
                                    @endforeach
                                    <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- <tr>
                            <td>1</td>
                            <td>T-Shirt <br> Created at : 25-Aug-2020</td>
                            <td>Quality product in low cost</td>
                            <td>
                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
    
                                    <dt class="col-sm-3 pb-0">
                                        SM/ Red/ V-Nick
                                    </dt>
                                    <dd class="col-sm-9">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 pb-0">Price : {{ number_format(200,2) }}</dt>
                                            <dd class="col-sm-8 pb-0">InStock : {{ number_format(50,2) }}</dd>
                                        </dl>
                                    </dd>
                                </dl>
                                <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', 1) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr> --}}

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{$products->firstItem()}} to {{$products->lastItem()}} out of {{$products->total()}}</p>
                </div>
                <div class="col-md-6 float-right">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection

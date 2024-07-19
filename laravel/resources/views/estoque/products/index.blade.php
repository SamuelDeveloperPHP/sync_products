
@extends('layouts.app')

@section('content')

    <div class="container mt-5">
        <h1 class="mb-4">Produtos</h1>
        <form method="GET" action="{{ route('products.search') }}" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="category">Categoria</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">Escolha...</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="subcategory">Subcategoria</label>
                    <select id="subcategory" name="subcategory" class="form-control">
                        <option value="">Escolha...</option>
                        @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="brand">Marca</label>
                    <select id="brand" name="brand" class="form-control">
                        <option value="">Escolha...</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="name">Nome do Produto</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nome do Produto">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Pesquisar</button>
        </form>

        <div class="row">
            @foreach($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="{{ $product->thumbnail }}" class="card-img-top" alt="{{ $product->title }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->title }}</h5>
                        <p class="card-text">{{ $product->description }}</p>
                        <p class="card-text"><strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection
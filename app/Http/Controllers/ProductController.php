<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;

class ProductController extends Controller
{
    public function create()
    {
        $users = User::all();
        return view('products.create',compact('users'));
    }


    public function index()
    {
        $products = Product::with('users')->get();
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        $productWithUsers = Product::with('users')->find($product->id);
        return view('products.show', compact('productWithUsers'));
    }

    public function store(CreateProductRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price']
        ]);
        if (!isset($validatedData['users_id'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['users_id' => 'One or more selected users do not exist.']);

        }
        $product->users()->attach($validatedData['users_id']);
        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }
    public function edit($id)
    {

        $productWithUsers = Product::with('users')->findOrFail($id);
        $users = User::all();
        $selectedUserIds = $productWithUsers->users->pluck('id')->toArray();

        return view('products.edit', compact('productWithUsers', 'users', 'selectedUserIds'));
    }

    public function update(UpdateProductRequest $request, $id)
    {

        $product = Product::findOrFail($id);

        $product->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
        ]);

        $userIds= $request->input('users_id',[]);

        $existingUsersIds = User::whereIn('id', $userIds)->pluck('id')->toArray();
        $nonExistingProductIds = array_diff($userIds, $existingUsersIds);

        if (!empty($nonExistingProductIds)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['product_id' => 'One or more selected products do not exist.']);
        }

        $product->users()->sync($request->input('users_id', []));

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {

        $product = Product::findOrFail($id);
        $product->users()->detach();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }
}


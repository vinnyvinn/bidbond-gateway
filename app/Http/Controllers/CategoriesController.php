<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Traits\ApiResponser;

class CategoriesController extends Controller
{
    public $categoryService;

    use ApiResponser;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return $this->categoryService->index();
    }
    public function store(Request $request)
    {
        return $this->categoryService->create($request->all());
    }

    public function show($secret)
    {
        return $this->categoryService->show($secret);
    }

    public function edit($secret)
    {
        return $this->categoryService->edit($secret);
    }

    public function update(Request $request, $secret)
    {
        return $this->categoryService->update($request->all(), $secret);
    }

    public function destroy($secret)
    {
        return $this->categoryService->destroy($secret);
    }
}

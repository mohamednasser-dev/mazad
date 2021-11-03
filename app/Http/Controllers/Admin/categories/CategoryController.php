<?php
namespace App\Http\Controllers\Admin\categories;

use App\Http\Controllers\Admin\AdminController;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Http\Request;
use App\Category;

class CategoryController extends AdminController{
    // type : get -> to add new
    public function AddGet(){
        return view('admin.categories.create');
    }
    // type : post -> add new category
    public function AddPost(Request $request){
        $image_name = $request->file('image')->getRealPath();
        Cloudder::upload($image_name, null);
        $imagereturned = Cloudder::getResult();
        $image_id = $imagereturned['public_id'];
        $image_format = $imagereturned['format'];
        $image_new_name = $image_id.'.'.$image_format;

        $category = new Category();
        $category->title_en = $request->title_en;
        $category->title_ar = $request->title_ar;
        $category->desc_ar = $request->desc_ar;
        $category->desc_en = $request->desc_en;
        $category->image = $image_new_name;


        $category->save();

        session()->flash('success', trans('messages.added_s'));
        return redirect('admin-panel/categories/show');
    }
    // get all categories
    public function show(){
        $data['categories'] = Category::where('deleted' , 0)->orderBy('sort' , 'asc')->get();
        return view('admin.categories.index' , ['data' => $data]);
    }
    // get edit page
    public function EditGet(Request $request){
        $data['category'] = Category::find($request->id);
        return view('admin.categories.edit' , ['data' => $data ]);
    }
    // edit category
    public function EditPost(Request $request){
        $category = Category::find($request->id);
        if($request->file('image')){
            $image = $category->image;
            $publicId = substr($image, 0 ,strrpos($image, "."));
            if($publicId != null ){
                Cloudder::delete($publicId);
            }
            $image_name = $request->file('image')->getRealPath();
            Cloudder::upload($image_name, null);
            $imagereturned = Cloudder::getResult();
            $image_id = $imagereturned['public_id'];
            $image_format = $imagereturned['format'];
            $image_new_name = $image_id.'.'.$image_format;
            $category->image = $image_new_name;
        }

        $category->title_en = $request->title_en;
        $category->title_ar = $request->title_ar;
        $category->desc_ar = $request->desc_ar;
        $category->desc_en = $request->desc_en;
        $category->save();
        return redirect('admin-panel/categories/show');
    }
    // delete category
    public function delete(Request $request){
        $category = Category::find($request->id);
        $category->deleted = 1;
        $category->save();
        return redirect()->back();
    }
    // get category products
    public function category_products(Category $category) {
        $data['products'] = $category->products;
        if (app()->getLocale() == 'en') {
            $data['category'] = $category->title_en;
        }else {
            $data['category'] = $category->title_ar;
        }
        return view('admin.products.products', ['data' => $data]);
    }

    // sorting
    public function sort(Request $request) {
        $post = $request->all();
        $count = 0;
        for ($i = 0; $i < count($post['id']); $i ++) :
            $index = $post['id'][$i];
            $home_section = Category::findOrFail($index);
            $count ++;
            $newPosition = $count;
            $data['sort'] = $newPosition;
            if($home_section->update($data)) {
                echo "success";
            }else {
                echo "failed";
            }
        endfor;
        exit('success');
    }
}
